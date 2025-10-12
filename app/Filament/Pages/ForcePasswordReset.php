<?php

namespace App\Filament\Pages;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Form as FormComponent;
use Filament\Schemas\Components\Actions as ActionsComponent;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Heading;
use Filament\Schemas\Components\Text;
use Filament\Actions\Action;
use Filament\Support\Enums\Alignment;
use Filament\Forms\Components\TextInput;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Filament\Pages\Page;

/**
 * @property-read Schema $form
 */
class ForcePasswordReset extends Page
{
    /** Δεν το εμφανίζουμε στο sidebar */
    protected static bool $shouldRegisterNavigation = false;

    /** Route slug της σελίδας (π.χ. /admin/force-password-reset) */
    protected static ?string $slug = 'force-password-reset';

    /** Τίτλος σελίδας */
    protected static ?string $title = 'Αλλαγή Κωδικού Πρόσβασης';

    /** ΜΗΝ κάνεις το $view static */
    protected string $view = 'filament.pages.force-password-reset';

    /**
     * Κατάσταση φόρμας (state path: data)
     * @var array<string,mixed>|null
     */
    public ?array $data = [];

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Υποχρεωτική Αλλαγή Κωδικού')->schema ([
                    Text::make('Για λόγους ασφαλείας, πρέπει να αλλάξετε τον κωδικό σας πριν συνεχίσετε.')
                        ->color('gray')
                        ->size('sm'),

                    FormComponent::make([
                        TextInput::make('password')
                            ->label('Νέος Κωδικός')
                            ->password()
                            ->revealable()
                            ->required()
                            ->minLength(8)
                            ->same('password_confirmation')
                            ->helperText('Ο κωδικός πρέπει να έχει τουλάχιστον 8 χαρακτήρες.'),

                        TextInput::make('password_confirmation')
                            ->label('Επιβεβαίωση Κωδικού')
                            ->password()
                            ->revealable()
                            ->required(),
                    ])
                        ->columns(1)
                        ->columnSpan(1)
                        ->statePath('data')
                        ->livewireSubmitHandler('save')
                        ->footer([
                            ActionsComponent::make([
                                Action::make('save')
                                    ->label('Αποθήκευση')
                                    ->submit('save')
                                    ->icon('heroicon-o-check')
                                    ->color('primary'),

                                Action::make('backToLogin')
                                    ->label('Επιστροφή στη σελίδα σύνδεσης')
                                    ->action('logoutAndRedirect')
                                    ->icon('heroicon-o-arrow-left')
                                    ->color('gray'),
                            ]),
                    ])
                ])->maxWidth('2xl'),
            ])
            ->statePath('data');
    }


    public function save(): void
    {
        // validation πάνω στο state της φόρμας
        $this->validate([
            'data.password' => ['required', 'min:8', 'same:data.password_confirmation'],
            'data.password_confirmation' => ['required'],
        ]);

        /** @var \App\Models\User $user */
        $user = Filament::auth()->user();

        $user->forceFill([
            'password' => Hash::make(data_get($this->data, 'password')),
            'force_password_reset' => false,
        ])->save();

        Notification::make()
            ->title('Ο κωδικός πρόσβασης άλλαξε με επιτυχία.')
            ->success()
            ->send();

        // redirect στο dashboard του panel
        $this->redirect(Filament::getCurrentPanel()->getUrl());
    }

    public function logoutAndRedirect(): void
    {
        Auth::logout();
        Filament::auth()->logout();
        session()->invalidate();
        session()->regenerateToken();

        $this->redirect(route('filament.admin.auth.login'));
    }
}
