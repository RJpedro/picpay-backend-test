<?php

namespace App\Http\Controllers;

use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Transaction;
use App\Models\User;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(Transaction::all(), 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Verify if payer has enough balance
        if (!$this->verify_account_billing($request)) return response()->json(['message' => 'Insufficient funds.'], 400);

        // Verify if transaction is authorized
        if ($this->consult_extern_api('https://run.mocky.io/v3/5794d450-d2e2-4412-8131-73d0293ac1cc')['message'] != 'Autorizado') return response()->json(['message' => 'Transaction not authorized.'], 400);
 
        // Verify if transaction is authorized
        if (!$this->verify_account_access($request)) return response()->json(['message' => 'Shopkeeper dont can send funds to user.'], 400);

        $transaction = new Transaction();

        // Data verifications
        $transaction->value = $this->verify_empty_data($request, 'value', 'Value is required.');
        $transaction->payer_id = $this->verify_empty_data($request, 'payer_id', 'Payer id is required.');
        $transaction->payee_id = $this->verify_empty_data($request, 'payee_id', 'Payee id is required.');;

        try {
            // Try to modify payer account balance
            $payer = Account::where('user_id', $request->payer_id)->first();
            if (!$payer) $this->data_not_found('Payer not found.');
            $payer->account_balance -= $request->value;
            $payer->save();

            // Try to modify payee account balance
            $payee = Account::where('user_id', $request->payee_id)->first();
            if (!$payee) $this->data_not_found('Payee not found.');
            $payee->account_balance += $request->value;
            $payee->save();

            // Try to save transaction
            $transaction->status = 'success';
            $transaction->save();

            // verify extern api to send email
            if($this->consult_extern_api('https://run.mocky.io/v3/54dc2cf1-3add-45b5-b5a9-6bf7e7f1f4a6')['message']) {
                // Send email to payer
                $payer_data = $this->user_data($payer->user_id);
                $this->send_email([
                    'email' => $payer_data->email,
                    'name' => $payer_data->name,
                    'subject' => 'Transaction',
                    'message' => 'Your transaction was successful.'
                ]);
                
                // Send email to payee
                $payee_data = $this->user_data($payee->user_id);
                $this->send_email([
                    'email' => $payee_data->email,
                    'name' => $payee_data->name,
                    'subject' => 'Transaction',
                    'message' => "You have just received a transaction. Transaction id: $transaction->id."
                ]);
            }

            return response()->json(['message' => 'Transaction is created.', 'data' => $transaction], 201);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error on create transaction.', 'data' => $th->getMessage() . ' - ' . $th->getLine()], 400);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $transaction = Transaction::where('id', $id)->first();
        if (!$transaction) $this->data_not_found('Transaction not found.');
        return response()->json($transaction, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $transaction = Transaction::where('id', $id)->first();
        
        if (!$transaction) $this->data_not_found('Transaction not found.');

        if (in_array($transaction->status, ['success', 'refund'])) {
            $data['message'] = 'This transaction cannot have its status changed.';
            return $data;
        }

        $transaction->status = $request->status;

        try {
            // Try to save transaction
            $transaction->save();

            return response()->json(['message' => 'Transaction has been updated.', 'data' => $transaction], 200);
        } catch (\Throwable $th) {
            return [
                'message' => 'Error on update transaction.',
                'status' => 400,
                'data' => $th->getMessage() . ' - ' . $th->getLine()
            ];
        }
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
            if($this->consult_extern_api('https://run.mocky.io/v3/54dc2cf1-3add-45b5-b5a9-6bf7e7f1f4a6')['message']) {
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
    public function verify_account_billing(Request $request)
    {
        $user = Account::where('user_id', $request->payer_id)->first();

        if (!$user) $this->data_not_found('User not found.');

        if ($user['account_balance'] < $request->value) return false;

        return true;
    }

    /**
     * Verify account is user or not
     */
    public function verify_account_access(Request $request)
    {
        $user = User::where('id', $request->payer_id)->first();

        if (!$user) $this->data_not_found('User not found.');
        
        if ($user['access_type'] != 'U') return false;

        return true;
    }

    /**
     * Consult external API to verify if transaction is authorized 
     */
    public function consult_extern_api(string $url_api)
    {
        $response = Http::get($url_api);

        if ($response->successful()) {
            return $response->json();
        } else {
            return (object) [
                'message' => 'Error on consult external API.',
                'status' => $response->status()
            ];
        }
    }
}
