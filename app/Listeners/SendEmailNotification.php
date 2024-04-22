<?php

namespace App\Listeners;

use App\Events\SendEmail;
use App\Http\Controllers\Api\Controller;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendEmailNotification
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(SendEmail $event): void
    {
        try {
            $user = Controller::user_data($event->id);
            Controller::send_email([
                'email' => $user->email,
                'name' => $user->name,
                'subject' => 'Transaction',
                'message' => $event->message
            ]);
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
