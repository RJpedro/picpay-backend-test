<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\Account;

class AccountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->general_response(function (){
            return Account::all();
        }, 200, 'Successfully recovering accounts.', 'Error when retrieving accounts.');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        return $this->general_response(function () use ($request){
            return Account::create($request->only(['user_id', 'account_balance']));
        }, 202, 'Account created successfully.', 'Error on created account.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return $this->general_response(function () use ($id){
            $account = Account::where('user_id', $id)->first();
            if (!$account) $this->data_not_found('Account not found.');
            return $account;
        }, 202, 'Account found successfully.', 'Error on find account.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        return $this->general_response(function () use ($request, $id){
            $user = Account::where('user_id', $id)->first();
            if (!$user) $this->data_not_found('Account not found.');
            $user->update($request->only(['account_balance']));
            
            return $user;
        }, 200, 'Account updated successfully.', 'Error on update account.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        return $this->general_response(function () use ($id){
            $account = Account::where('user_id', $id);
            if (!$account) $this->data_not_found('Account not found.');
            $account->update(['active' => 'N']);  

            return $account;
        }, 200, 'Account deleted successfully.', 'Error on delete account.');
    }
}
