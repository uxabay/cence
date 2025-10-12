<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsurePasswordResetIsDone
{
    public function handle(Request $request, Closure $next)
    {
        // Αν δεν υπάρχει συνδεδεμένος χρήστης, προχώρησε
        if (! Auth::hasUser()) {
            return $next($request);
        }

        $user = Auth::user();

        // Αγνόησε το route της αλλαγής κωδικού για να μην δημιουργηθεί loop
        if ($request->routeIs('filament.admin.pages.force-password-reset')) {
            return $next($request);
        }

        // Αν το flag είναι true, ανακατεύθυνση στην υποχρεωτική αλλαγή κωδικού
        if ($user->force_password_reset) {
            return redirect()->route('filament.admin.pages.force-password-reset');
        }

        return $next($request);
    }
}
