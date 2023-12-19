<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MainTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test Main Transaction Flow.
     */
    public function test_main_transaction_flow(): void
    {
        // Create User
        $user = $this->post('/api/user', [
            'name' => 'user-test',
            'cpf' => '123456789-1',
            'email' => 'user@gmail.com',
            'password' => '123456',
            'access_type' => 'U',
        ]);

        // Create User Account
        $user_account = $this->post('/api/account', [
            'user_id' => 1,
            'account_balance' => 600.00
        ]);

        // Create Shopkeeper
        $shopkeeper = $this->post('/api/user', [
            'name' => 'shopkeeper-test',
            'cpf' => '156485215-0',
            'email' => 'shopkeeper@gmail.com',
            'password' => '123456',
            'access_type' => 'L',
        ]);

        // Create Shopkeeper Account
        $shopkeeper_account = $this->post('/api/account', [
            'user_id' => 2,
            'account_balance' => 1000.00
        ]);

        // Create Transaction
        $success_transaction = $this->post('/api/transaction', [
            'value' => 100,
            'payer_id' => 1,
            'payee_id' => 2
        ]);

        // Create a Failed Transaction
        $failed_transaction = $this->post('/api/transaction', [
            // 'value' => 500,
            'payer_id' => 2,
            'payee_id' => 1
        ]);

        // Refund Transaction
        $refund_transaction = $this->get('/api/refund-transaction/1');
  
        $user->assertCreated();
        $user_account->assertCreated();
        
        $shopkeeper->assertCreated();
        $shopkeeper_account->assertCreated();

        $success_transaction->assertCreated();
        $failed_transaction->assertStatus(400);
        $refund_transaction->assertStatus(200);
    }

    /**
     * Test Get type Routes.
     */
    public function test_get_type_routes(): void 
    {
        $this->get('/api/user')->assertStatus(200);
        $this->get('/api/account')->assertStatus(200);
        $this->get('/api/transaction')->assertStatus(200);
    }
}
