<?php

namespace App\Http\Controllers\Api;

use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->general_response(function (){
            return Transaction::all();
        }, 200, 'Successfully recovering transactions.', 'Error when retrieving transactions.');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->only(['value', 'payer_id', 'payee_id']);

        // Verify if transaction access is user
        if (!$this->verify_account_access($data)) return response()->json(['message' => 'Shopkeeper dont can send funds to user.', 'data' => []], 400);   
        // Verify if payer has enough balance
        if (!$this->verify_account_billing($data)) return response()->json(['message' => 'Insufficient funds.', 'data' => []], 400);
        // Verify if transaction is authorized
        if ($this->consult_extern_api('https://run.mocky.io/v3/5794d450-d2e2-4412-8131-73d0293ac1cc')['message'] != 'Autorizado') return response()->json(['message' => 'Transaction not authorized.', 'data' => []], 400);

        // Data verifications
        $this->verify_empty_data($data, 'value', 'Value is required.');
        $this->verify_empty_data($data, 'payer_id', 'Payer id is required.');
        $this->verify_empty_data($data, 'payee_id', 'Payee id is required.');

        return $this->general_response(function () use($data){
            // Create transaction
            $transaction = Transaction::create($data);
            return $transaction;
        }, 202, 'Transaction created successfully.', 'Error on created transaction.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return $this->general_response(function () use($id) {       
            $transaction = Transaction::where('id', $id)->first();
            if (!$transaction) $this->data_not_found('Transaction not found.');
            return $transaction;
        }, 200, 'Successfully recovering transactions.', 'Error when retrieving transactions.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        return $this->general_response(function () use($request, $id){
            $transaction = Transaction::where('id', $id)->first();
            
            if (!$transaction) $this->data_not_found('Transaction not found.');
            if (in_array($transaction->status, ['success', 'refund'])) {
                $data['message'] = 'This transaction cannot have its status changed.';
                return $data;
            }

            $transaction->update(['status' => $request->status]);
            return $transaction;
        }, 202, 'Transaction updated successfully.', 'Error on update transaction.');
    }

    /**
     * Refund user
     */
    public function refund_to_user(string $transaction_id)
    {
        $transaction = Transaction::where('id', $transaction_id)->first();
        if (!$transaction) $this->data_not_found('Transaction not found.');

        $payer_account = Account::where('user_id', $transaction->payer_id)->first();
        $payee_account = Account::where('user_id', $transaction->payee_id)->first();

        $data = [
            'message' => '',
            'status' => 400
        ];

        // If transaction is pending not is possible to refund
        if (!in_array($transaction->status, ['success', 'refund'])) {
            $data['message'] = 'This transaction was not successful so it is not possible to refund.';
            return $data;
        }

        // If transaction is already refunded not is possible to refund again
        if ($transaction->status == 'refund') {
            $data['message'] = 'This transaction has already been refunded.';
            return $data;
        }

        $payer_account->account_balance += $transaction->value;
        $payee_account->account_balance -= $transaction->value;
        $transaction->status = 'refund';

        try {
            // Try to save transaction, payer account and payee account
            $payer_account->save();
            $payee_account->save();
            $transaction->save();

            // verify extern api to send email
            if($this->consult_extern_api('https://run.mocky.io/v3/54dc2cf1-3add-45b5-b5a9-6bf7e7f1f4a6')['data']['message']) {
                $payer_data = $this->user_data($payer_account->user_id);
                $this->send_email([
                    'email' => $payer_data->email,
                    'name' => $payer_data->name,
                    'subject' => 'Refund transaction',
                    'message' => "Your transaction was refunded. Transaction id: $transaction->id"
                ]);

                $payee_data = $this->user_data($payee_account->user_id);
                $this->send_email([
                    'email' => $payee_data->email,
                    'name' => $payee_data->name,
                    'subject' => 'Refund transaction',
                    'message' => "Your transaction was refunded. Transaction id: $transaction->id"
                ]);
            }
           
            return response()->json(['message' => 'Transaction has been refunded.', 'data' => $transaction], 200);
        } catch (\Throwable $th) {
            return [
                'message' => 'Error on refund transaction.',
                'status' => 400,
                'data' => $th->getMessage() . ' - ' . $th->getLine()
            ];
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        return response()->json(['message' => 'Cannot destroy a transaction.'], 200);
    }

    /**
     * Verify account balance before transaction
     */
    public function verify_account_billing(Array $request)
    {
        try {  
            $user = Account::where('user_id', $request['payer_id'])->first();

            if (!$user) $this->data_not_found('User not found.');
            if ((float) $user['account_balance'] < (float) $request['value']) return false;
    
            return true;
        } catch (\Throwable $th) {
            return [
                'message' => $th->getMessage() . ' - ' . $th->getLine(),
                'data' => []
            ];
        }
    }

    /**
     * Verify account is user or not
     */
    public function verify_account_access(Array $request)
    {
        try {  
            $user = User::where('id', $request['payer_id'])->first();

            if (!$user) $this->data_not_found('User not found.');
            if ($user['access_type'] != 'U') return false;
            return true;
        } catch (\Throwable $th) {
            return [
                'message' => $th->getMessage() . ' - ' . $th->getLine(),
                'data' => []
            ];
        }
    }

    /**
     * Consult external API to verify if transaction is authorized 
     */
    public static function consult_extern_api(string $url_api)
    {
        try {
            $response = Http::get($url_api);

            if ($response->successful()) {
                return $response->json();
            } else {
                return (object) [
                    'message' => 'Error on consult external API.',
                    'status' => $response->status()
                ];
            }
        } catch (\Throwable $th) {
            return [
                'message' => $th->getMessage() . ' - ' . $th->getLine(),
            ];
        }
    }
}
