<?php

namespace App\Models;

use App\Observers\Api\TransactionObserver;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'value',
        'payer_id',
        'payee_id',
        'status',
    ];

    /**
     * The model observers for your application.
     *
     * @var array
     */
    protected $observers = [
        Transaction::class => [TransactionObserver::class],
    ];
}
