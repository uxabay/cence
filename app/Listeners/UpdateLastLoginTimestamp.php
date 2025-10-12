<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;

class UpdateLastLoginTimestamp
{
    /**
     * Handle the event.
     */
    public function handle(Login $event): void
    {
        /** @var \App\Models\User $user */
        $user = $event->user;

        $user->updateQuietly([
            'last_login_at' => now(),
        ]);

        // Προαιρετικό: για debugging/logging
        // Log::info("User {$event->user->id} logged in at " . now());
    }
}
