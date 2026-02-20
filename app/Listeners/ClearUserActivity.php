<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Logout;

class ClearUserActivity
{
    /**
     * Handle the event.
     */
    public function handle(Logout $event): void
    {
        $event->user->update([
            'last_activity_at' => null,
        ]);
    }
}
