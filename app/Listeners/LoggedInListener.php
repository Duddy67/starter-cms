<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Carbon\Carbon;

class LoggedInListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  Login  $event
     * @return void
     */
    public function handle(Login $event)
    {
        $event->user->last_seen_at = ($event->user->last_logged_in_at) ? $event->user->last_logged_in_at : Carbon::now();

        $event->user->last_logged_in_at = Carbon::now();
        $event->user->last_logged_in_ip = $_SERVER['REMOTE_ADDR'];
        $event->user->save();
    }
}
