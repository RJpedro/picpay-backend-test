<?php

namespace App\Providers;

use App\Models\Transaction;
use App\Models\User;
use App\Observers\Api\TransactionObserver;
use App\Observers\Api\UserObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Transaction::observe(TransactionObserver::class);
        User::observe(UserObserver::class);
    }
}
