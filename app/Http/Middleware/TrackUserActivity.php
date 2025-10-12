<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TrackUserActivity
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Αν δεν υπάρχει συνδεδεμένος χρήστης, σταμάτα εδώ
        if (! Auth::hasUser()) {
            return $response;
        }

        // Throttle για αποφυγή υπερβολικών ενημερώσεων (default: 5')
        $sessionKey = 'last-activity-next-touch-at';
        $interval   = (int) config('activity.touch_interval', 5);
        $nextTouch  = session($sessionKey);

        if ($nextTouch && now()->lt($nextTouch)) {
            return $response;
        }

        // Ήσυχη ενημέρωση χωρίς events ή activity logs
        Auth::user()->updateQuietly([
            'last_activity_at' => now(),
        ]);

        // Επόμενη επιτρεπτή ενημέρωση
        session([$sessionKey => now()->addMinutes($interval)]);

        return $response;
    }
}
