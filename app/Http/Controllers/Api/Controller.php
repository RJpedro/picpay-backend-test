<?php

namespace App\Http\Controllers\Api;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Mail;
use App\Mail\Api\Contact;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    public static function send_email($data)
    {
        Mail::to($data['email'], $data['name'])->send(new Contact([
            'fromName' => $data['name'],
            'fromEmail' => $data['email'],
            'subject' => $data['subject'],
            'message' => $data['message']
        ]));
    }

    public static function user_data($user_id)
    {
        return User::where('id', $user_id)->first();
    }

    public static function verify_empty_data($data, $property, $message)
    {
        if (isset($data) && isset($data->$property)) return $data;

        return response()->json(['message' => "$message."], 400);
    }

    public static function data_not_found($message)
    {
        return response()->json(['message' => "$message."], 404);
    }

    public static function all_routes()
    {
        return [
            'GET' => [
                'api/user' => [
                    'description' => 'Returns all users',
                ],
                'api/user/{user_id}' => [
                    'description' => 'Returns the user corresponding to the id sent',
                    'parameters' => [
                        'user_id' => [
                            'type' => 'integer',
                            'description' => 'User id',
                            'required' => true,
                        ],
                    ],
                ],
                'api/account' => [
                    'description' => 'Returns all accounts',
                ],
                'api/account/{account_id}' => [
                    'description' => 'Returns the user account corresponding to the id sent',
                    'parameters' => [
                        'account_id' => [
                            'type' => 'integer',
                            'description' => 'Account id',
                            'required' => true,
                        ],
                    ],
                ],
                'api/transaction' => [
                    'description' => 'Returns all transactions',
                ],
                'api/transaction/{transaction_id}' => [
                    'description' => 'Returns the transaction corresponding to the id sent',
                    'parameters' => [
                        'transaction_id' => [
                            'type' => 'integer',
                            'description' => 'Transaction id',
                            'required' => true,
                        ],
                    ],
                ],
            ],
            'POST' => [
                'api/user' => [
                    'description' => 'Create a new user',
                    'parameters' => [
                        'name' => [
                            'type' => 'string',
                            'description' => 'User name',
                            'required' => true,
                            'unique' => false,
                        ],
                        'cpf' => [
                            'type' => 'string',
                            'description' => 'User cpf',
                            'required' => true,
                            'unique' => true,
                        ],
                        'email' => [
                            'type' => 'string',
                            'description' => 'User email',
                            'required' => true,
                            'unique' => true,
                        ],
                        'password' => [
                            'type' => 'string',
                            'description' => 'User password',
                            'required' => true,
                            'unique' => false,
                        ],
                        'access_type' => [
                            'type' => 'string',
                            'description' => 'User Access (U = User, S = Shopkeeper)',
                            'required' => true,
                            'unique' => false,
                        ],
                    ],
                ],
                'api/account' => [
                    'description' => 'Create a new account to specific user',
                    'parameters' => [
                        'user_id' => [
                            'type' => 'integer',
                            'description' => 'User Id',
                            'required' => true,
                            'unique' => true,
                        ],
                        'account_balance' => [
                            'type' => 'float',
                            'description' => 'User Account Balance',
                            'required' => true,
                            'unique' => false,
                        ],
                    ],
                ],
                'api/transaction' => [
                    'description' => 'Create a new transaction',
                    'parameters' => [
                        'value' => [
                            'type' => 'float',
                            'description' => 'Transaction value',
                            'required' => true,
                        ],
                        'payer_id' => [
                            'type' => 'integer',
                            'description' => 'Transaction payer ID',
                            'required' => true,
                        ],
                        'payee_id' => [
                            'type' => 'integer',
                            'description' => 'Transaction payee ID',
                            'required' => true,
                        ],
                    ],
                ]
            ],
            'PUT' => [
                'api/user/{user_id}' => [
                    'description' => 'Updated a specific user',
                    'parameters_body' => [
                        'name' => [
                            'type' => 'string',
                            'description' => 'User name',
                            'required' => true,
                        ],
                        'cpf' => [
                            'type' => 'string',
                            'description' => 'User cpf',
                            'required' => true,
                        ],
                        'email' => [
                            'type' => 'string',
                            'description' => 'User email',
                            'required' => true,
                        ],
                        'password' => [
                            'type' => 'string',
                            'description' => 'User password',
                            'required' => true,
                        ],
                        'access_type' => [
                            'type' => 'string',
                            'description' => 'User Access (U = User, S = Shopkeeper)',
                            'required' => true,
                        ],
                    ],
                    'parameters_query' => [
                        'user_id' => [
                            'type' => 'integer',
                            'description' => 'User Id',
                            'required' => true,
                        ],
                    ],
                ],
                'api/account/{user_id}' => [
                    'description' => 'Updated a specific account',
                    'parameters_body' => [
                        'account_balance' => [
                            'type' => 'float',
                            'description' => 'User Account Balance',
                            'required' => true,
                        ],
                    ],
                    'parameters_query' => [
                        'user_id' => [
                            'type' => 'integer',
                            'description' => 'User Id',
                            'required' => true,
                        ],
                    ],
                ],
                'api/transaction/{user_id}' => [
                    'description' => 'Updated a specific Transaction',
                    'parameters_body' => [
                        'status' => [
                            'type' => 'string',
                            'description' => 'Transaction status (success, refund, pending)',
                            'required' => true,
                        ],
                    ],
                    'parameters_query' => [
                        'user_id' => [
                            'type' => 'integer',
                            'description' => 'User Id',
                            'required' => true,
                        ],
                    ],
                ],
            ],
            'PATCH' => [
                'api/refund-transaction/{transaction_id}' => [
                    'description' => 'Refund a transaction corresponding to the id sent',
                    'parameters' => [
                        'transaction_id' => [
                            'type' => 'integer',
                            'description' => 'Transaction id',
                            'required' => true,
                        ],
                    ],
                ],
                'api/user/{user_id}' => [
                    'description' => 'Updated a specific user',
                    'parameters_body' => [
                        'name' => [
                            'type' => 'string',
                            'description' => 'User name',
                            'required' => false,
                        ],
                        'cpf' => [
                            'type' => 'string',
                            'description' => 'User cpf',
                            'required' => false,
                        ],
                        'email' => [
                            'type' => 'string',
                            'description' => 'User email',
                            'required' => false,
                        ],
                        'password' => [
                            'type' => 'string',
                            'description' => 'User password',
                            'required' => false,
                        ],
                        'access_type' => [
                            'type' => 'string',
                            'description' => 'User Access (U = User, S = Shopkeeper)',
                            'required' => false,
                        ],
                    ],
                    'parameters_query' => [
                        'user_id' => [
                            'type' => 'integer',
                            'description' => 'User Id',
                            'required' => true,
                        ],
                    ],
                ],
                'api/account/{user_id}' => [
                    'description' => 'Updated a specific account',
                    'parameters_body' => [
                        'account_balance' => [
                            'type' => 'float',
                            'description' => 'User Account Balance',
                            'required' => false,
                        ],
                    ],
                    'parameters_query' => [
                        'user_id' => [
                            'type' => 'integer',
                            'description' => 'User Id',
                            'required' => true,
                        ],
                    ],
                ],
                'api/transaction/{user_id}' => [
                    'description' => 'Updated a specific Transaction',
                    'parameters_body' => [
                        'status' => [
                            'type' => 'string',
                            'description' => 'Transaction status (success, refund, pending)',
                            'required' => false,
                        ],
                    ],
                    'parameters_query' => [
                        'user_id' => [
                            'type' => 'integer',
                            'description' => 'User Id',
                            'required' => true,
                        ],
                    ],
                ],
            ],
            'DELETE' => [
                'api/user/{user_id}' => [
                    'description' => 'Delete a specific user',
                    'parameters' => [
                        'user_id' => [
                            'type' => 'integer',
                            'description' => 'User Id',
                            'required' => true,
                        ],
                    ],
                ],
                'api/account/{user_id}' => [
                    'description' => 'Delete a specific account',
                    'parameters' => [
                        'user_id' => [
                            'type' => 'integer',
                            'description' => 'User Id',
                            'required' => true,
                        ],
                    ],
                ],
            ],
        ];
    }

    public static function login(Request $request)
    {
        if(Auth::attempt($request->only('email', 'password'))) {
            $token = Auth::user()->createToken('user_token')->plainTextToken;

            return response()->json(['message' => 'Authorized', 'access_token' => $token], 200);
        }

        return response()->json(['message' => 'Invalid login credentials.'], 401);
    }

    public static function general_response($callback, $status, $success_message, $error_message)
    {
        try {
            $data = $callback();
            return response()->json(['message' => $success_message, 'data' => $data], $status);
        } catch (\Throwable $th) {
            return response()->json(['message' => $error_message, 'data' => $th->getMessage() .' - '. $th->getLine()], 500);
        }
    }
}
