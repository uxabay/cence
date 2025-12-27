<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Actions\Action;

class ContractThresholdWarningNotification extends Notification
{
    use Queueable;

    public function __construct(
        public array $payload
    ) {}

    public function via($notifiable)
    {
        return ['database']; // μόνο database
    }

    public function toDatabase($notifiable): array
    {
        // Δημιουργούμε το Filament Notification
        $filamentMessage = FilamentNotification::make()
            ->title("Υπέρβαση ορίου {$this->payload['threshold']}%")
            ->icon('heroicon-o-exclamation-triangle')
            ->danger()
            ->body("Η σύμβαση {$this->payload['contract_title']} έφτασε στο {$this->payload['percentage']}%.")
            ->actions([
                Action::make('view')
                    ->label('Προβολή Σύμβασης')
                    ->url(route('filament.admin.resources.contracts.view', $this->payload['contract_id']))
                    ->markAsRead(),
            ]);

        /**
         * ΤΟ ΣΗΜΑΝΤΙΚΟ
         * Filament ->getDatabaseMessage() επιστρέφει πίνακα μόνο με:
         *   title, actions, icon, status κ.λπ.
         *
         * Το payload (contract_id, threshold) πρέπει να συνενωθεί χειροκίνητα
         * ώστε το shouldSendThresholdNotification() να το βρίσκει.
         */

        return array_merge(
            $filamentMessage->getDatabaseMessage(),
            $this->payload
        );
    }
}
