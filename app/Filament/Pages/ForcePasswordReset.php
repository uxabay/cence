<?php

namespace App\Filament\Pages;

use Filament\Facades\Filament;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Hash;

class ForcePasswordReset extends Page
{
    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $slug = 'force-password-reset';

    protected static ?string $title = 'Υποχρεωτική Αλλαγή Κωδικού';

    // ⚠️ NON-static (σωστό για Page)
    protected string $view = 'filament.pages.force-password-reset';

    public string $password = '';
    public string $password_confirmation = '';

    public function save(): void
    {
        $this->validate([
            'password' => ['required', 'min:8', 'same:password_confirmation'],
            'password_confirmation' => ['required'],
        ]);

        $user = Filament::auth()->user();
        abort_if(! $user, 403);

        $user->update([
            'password' => Hash::make($this->password),
            'force_password_reset' => false,
        ]);

        // refresh auth state
        Filament::auth()->login($user->fresh());

        redirect()->intended(
            Filament::getCurrentPanel()->getUrl()
        );
    }

    public function logout(): void
    {
        Filament::auth()->logout();

        session()->invalidate();
        session()->regenerateToken();

        redirect()->route('filament.admin.auth.login');
    }

}
