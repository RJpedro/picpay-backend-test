<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Account;

class AccountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(Account::all(), 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $account = new Account();

        $account->user_id = $request->user_id;
        $account->account_balance = $request->account_balance;

        try {
            $account->save();

            return response()->json(['message' => 'Account has been created.', 'data' => $account], 201);
        } catch (\Throwable $th) {
            return [
                'message' => 'Error on create account.',
                'status' => 400,
                'data' => $th->getMessage() . ' - ' . $th->getLine()
            ];
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $account = Account::where('user_id', $id)->first();
        if (!$account) $this->data_not_found('Account not found.');
        return response()->json($account, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $account = Account::where('user_id', $id)->first();
        if (!$account) $this->data_not_found('Account not found.');

        $account->account_balance = $request->account_balance;

        try {
            $account->save();

            return response()->json($account, 200);
        } catch (\Throwable $th) {
            return [
                'message' => 'Error on update account.',
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
        $account = Account::where('user_id', $id);
        if (!$account) $this->data_not_found('Account not found.');

        // Try to save transaction
        try {
            $account->active = 'N';
            $account->save();

            return response()->json(['message' => 'Account has been deleted.'], 200);
        } catch (\Throwable $th) {
            return [
                'message' => 'Error on delete Account.',
                'status' => 400,
                'data' => $th->getMessage() . ' - ' . $th->getLine()
            ];
        }
    }
}
