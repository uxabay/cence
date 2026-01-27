<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Filament\Facades\Filament;

class EnsurePasswordResetIsDone
{
    public function handle(Request $request, Closure $next)
    {
        $auth = Filament::auth();

        if (! $auth->check()) {
            return $next($request);
        }

        $user = $auth->user();

        if (
            Filament::auth()->check()
            && Filament::auth()->user()->force_password_reset
            && ! request()->routeIs('filament.admin.pages.force-password-reset')
        ) {
            return redirect()->route('filament.admin.pages.force-password-reset');
        }

        return $next($request);
    }
}
