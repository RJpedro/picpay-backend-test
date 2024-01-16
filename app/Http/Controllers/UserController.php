<?php

namespace App\Http\Controllers;

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
        return response()->json(User::all(), 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = new User();

        $user->name = $request->name;
        $user->cpf = $request->cpf;
        $user->email = $request->email;
        $user->password = $request->password;
        $user->access_type = $request->access_type;

        try {
            $user->save();

            return response()->json(['message' => 'User has been created.', 'data' => $user], 201);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error on save user.', 'data' => $th->getMessage() . ' - ' . $th->getLine()], 400);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = User::where('id', $id);
        if (!$user) $this->data_not_found('User not found.');

        return response()->json($user->first(), 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = User::where('id', $id)->first();
        
        if (!$user) $this->data_not_found('User not found.');

        if ($request->name) $user->name = $request->name;
        if ($request->cpf) $user->cpf = $request->cpf;
        if ($request->email) $user->email = $request->email;
        if ($request->password) $user->password = $request->password;
        if ($request->access_type) $user->access_type = $request->access_type;

        // Try to save transaction
        try {
            $user->save();

            return response()->json(['message' => 'User has been updated.', 'data' => $user], 200);
        } catch (\Throwable $th) {
            return [
                'message' => 'Error on update user.',
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
        $user = User::where('id', $id);
        if (!$user) $this->data_not_found('User not found.');
        
        // Try to save transaction
        try {
            $user->active = 'N';
            $user->save();

            return response()->json(['message' => 'User has been deleted.'], 200);
        } catch (\Throwable $th) {
            return [
                'message' => 'Error on delete user.',
                'status' => 400,
                'data' => $th->getMessage() . ' - ' . $th->getLine()
            ];
        }
    }
}
