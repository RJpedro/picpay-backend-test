<?php

namespace App\Observers\Api;

use App\Events\SendEmail;
use App\Http\Controllers\Api\TransactionController;
use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Support\Facades\Log;

class TransactionObserver
{
    /**
     * Handle the Transaction "created" event.
     */
    public function created(Transaction $transaction): void
    {
        try {
            // Try to modify payer account balance and payee account balance
            $this->modify_account($transaction, 'Payer not found.', 'payer_id', 'sub');
            $this->modify_account($transaction, 'Payee not found.', 'payee_id', 'add');

            // Try to update transaction
            $transaction->update(['status' => 'success']);

            // verify extern api to send email
            if(TransactionController::consult_extern_api('https://run.mocky.io/v3/54dc2cf1-3add-45b5-b5a9-6bf7e7f1f4a6')['data']['message']) {
                // Send email to payer and payee
                SendEmail::dispatch($transaction->payer_id, 'Your transaction was successful.');
                SendEmail::dispatch($transaction->payee_id, 'You have just received a transaction. Transaction id: $transaction->id.');
            }
        } catch (\Throwable $th) {
            Log::error('Error on create transaction.', ['data' => $th->getMessage() . ' - ' . $th->getLine()]);
        }
    }

    /**
     * Handle the Transaction "updated" event.
     */
    public function updated(Transaction $transaction): void
    {
        //
    }

    /**
     * Handle the Transaction "deleted" event.
     */
    public function deleted(Transaction $transaction): void
    {
        //
    }

    /**
     * Handle the Transaction "restored" event.
     */
    public function restored(Transaction $transaction): void
    {
        //
    }

    /**
     * Handle the Transaction "force deleted" event.
     */
    public function forceDeleted(Transaction $transaction): void
    {
        //
    }

    /**
     * Try to modify payer account balance
     */
    public function modify_account(Transaction $transaction, String $message, String $account_type, String $type_transaction): void
    {
        try {
            $account = Account::where('user_id', $transaction->$account_type)->first();
            if (!$account) response()->json(['message' => "$message."], 404);
            if ($type_transaction == 'add') $account->account_balance += $transaction->value;
            else $account->account_balance -= $transaction->value;
            $account->update(['account_balance' => $account->account_balance]);
            $account->refresh();
        } catch (\Throwable $th) {
            Log::error('Error on modify account balance.', ['data' => $th->getMessage() . ' - ' . $th->getLine()]);
        }
    }
}
