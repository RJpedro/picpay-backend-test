<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function __construct() {
        $this->middleware('auth:sanctum')->only(['update', 'show', 'index', 'destroy']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->general_response(function (){
            return User::all();
        }, 200, 'Successfully recovering users.', 'Error when retrieving users.');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        return $this->general_response(function () use ($request){
            $user = User::create($request->only(['name', 'cpf', 'email', 'password', 'access_type']));
            return $user;
        }, 202, 'User created successfully.', 'Error on created user.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return $this->general_response(function () use ($id){
            $user = User::where('id', $id);
            if (!$user) $this->data_not_found('User not found.');
            return $user;
        }, 200, 'User found successfully.', 'Error on find user.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        return $this->general_response(function () use ($request, $id){
            $user = User::where('id', $id);
            if (!$user) $this->data_not_found('User not found.');
            $user->update($request->only(['name', 'cpf', 'email', 'password', 'access_type']));
            
            return $user;
        }, 200, 'User updated successfully.', 'Error on update user.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        return $this->general_response(function () use ($id){
            $user = User::where('id', $id);
            if (!$user) $this->data_not_found('User not found.');
            $user->update(['active' => 'N']);  

            return $user;
        }, 200, 'User deleted successfully.', 'Error on delete user.');
    }
}
