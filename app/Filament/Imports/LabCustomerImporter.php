<?php

namespace App\Filament\Imports;

use App\Models\LabCustomer;
use App\Models\CustomerCategory;
use App\Models\LabCustomerEmail;
use App\Enums\CustomerStatusEnum;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Number;

class LabCustomerImporter extends Importer
{
    protected static ?string $model = LabCustomer::class;
    protected static ?string $modelName = 'Πελάτες';

    public static function getColumns(): array
    {
        return [
            /*
            |--------------------------------------------------------------------------
            | 🟩 Βασικά στοιχεία
            |--------------------------------------------------------------------------
            */
            ImportColumn::make('name')
                ->label('Επωνυμία')
                ->requiredMapping()
                ->rules(['required', 'max:255'])
                ->example('Δήμος Καρδίτσας'),

            ImportColumn::make('customer_category_id')
                ->label('Κατηγορία')
                ->relationship(
                    name: 'category',
                    resolveUsing: fn (?string $state) =>
                        CustomerCategory::where('name', $state)->first()
                )
                ->requiredMapping()
                ->rules(['required'])
                ->helperText('Αναζητείται με βάση το όνομα της κατηγορίας (όχι το ID).')
                ->example('Δήμοι'),

            ImportColumn::make('status')
                ->label('Κατάσταση')
                ->requiredMapping()
                ->rules(['required'])
                ->castStateUsing(function (?string $state) {
                    $normalized = mb_strtolower(trim($state ?? ''));
                    return match ($normalized) {
                        'ενεργός', 'ενεργοσ', 'active', '1', 'ναι' => CustomerStatusEnum::Active->value,
                        'ανενεργός', 'inactive', '0', 'όχι' => CustomerStatusEnum::Inactive->value,
                        'αρχειοθετημένος', 'archived' => CustomerStatusEnum::Archived->value,
                        default => CustomerStatusEnum::Active->value,
                    };
                })
                ->example('Ενεργός'),

            /*
            |--------------------------------------------------------------------------
            | 🟦 Επικοινωνία
            |--------------------------------------------------------------------------
            */
            ImportColumn::make('contact_person')
                ->label('Υπεύθυνος επικοινωνίας')
                ->rules(['max:255'])
                ->example('Ιωάννης Παπαδόπουλος'),

            ImportColumn::make('phone')
                ->label('Τηλέφωνο')
                ->rules(['max:50'])
                ->castStateUsing(fn (?string $state) => $state ? preg_replace('/[^0-9+]/', '', $state) : null)
                ->example('+302410123456'),

            ImportColumn::make('email_primary')
                ->label('Κύριο Email')
                ->email()
                ->rules(['nullable', 'email', 'max:255'])
                ->example('info@karditsa.gr')
                ->helperText('Το κύριο email επικοινωνίας του πελάτη.'),

            ImportColumn::make('emails')
                ->label('Δευτερεύοντα Emails')
                ->multiple(',')
                ->castStateUsing(fn (?array $state) => collect($state)
                    ->map(fn ($v) => trim($v))
                    ->filter()
                    ->unique()
                    ->values()
                    ->toArray()
                )
                ->helperText('Πολλαπλές διευθύνσεις χωρισμένες με κόμμα. π.χ. info@domain.gr, support@domain.gr.')
                ->example('mayor@karditsa.gr, press@karditsa.gr')
                ->fillRecordUsing(function (LabCustomer $record, array $emails): void {
                    if (empty($emails)) {
                        return;
                    }

                    foreach ($emails as $email) {
                        $record->emails()->firstOrCreate(
                            ['email' => $email],
                            [
                                'is_primary' => false,
                                'created_by' => Auth::id(),
                                'updated_by' => Auth::id(),
                            ]
                        );
                    }
                }),

            /*
            |--------------------------------------------------------------------------
            | 🏠 Διεύθυνση
            |--------------------------------------------------------------------------
            */
            ImportColumn::make('address')
                ->label('Διεύθυνση')
                ->rules(['max:255'])
                ->example('Βασ. Γεωργίου 12'),

            ImportColumn::make('postal_code')
                ->label('Τ.Κ.')
                ->rules(['max:20'])
                ->example('43100'),

            ImportColumn::make('city')
                ->label('Πόλη')
                ->rules(['max:100'])
                ->example('Καρδίτσα'),

            /*
            |--------------------------------------------------------------------------
            | 💼 Οικονομικά & Σύστημα
            |--------------------------------------------------------------------------
            */
            ImportColumn::make('tax_id')
                ->label('Α.Φ.Μ.')
                ->rules(['nullable', 'max:20'])
                ->example('099999999'),

            ImportColumn::make('organization_code')
                ->label('Κωδικός Οργάνωσης')
                ->rules(['nullable', 'max:50'])
                ->example('ORG-001'),

            /*
            |--------------------------------------------------------------------------
            | 🟪 Λοιπά στοιχεία
            |--------------------------------------------------------------------------
            */
            ImportColumn::make('notes')
                ->label('Σημειώσεις')
                ->castStateUsing(fn (?string $state) => $state ? trim(preg_replace("/\r\n|\r|\n/", ' ', $state)) : null)
                ->example('Σχόλια ή πρόσθετες πληροφορίες για τον πελάτη.'),
        ];
    }

    public function resolveRecord(): LabCustomer
    {
        return LabCustomer::firstOrNew([
            'name' => $this->data['name'],
        ]);
    }

    protected function beforeSave(): void
    {
        if (Auth::check()) {
            $this->record->created_by ??= Auth::id();
            $this->record->updated_by = Auth::id();
        }

        // Αν υπάρχει κύριο email στο import, ενημερώνεται ή δημιουργείται
        if (!empty($this->data['email_primary'])) {
            $this->record->email_primary = $this->data['email_primary'];

            // Δημιουργία ή ενημέρωση στη σχετική λίστα emails
            $this->record->emails()->updateOrCreate(
                ['email' => $this->data['email_primary']],
                [
                    'is_primary' => true,
                    'created_by' => Auth::id(),
                    'updated_by' => Auth::id(),
                ]
            );
        }
    }

    public function getValidationMessages(): array
    {
        return [
            'name.required' => 'Το πεδίο Επωνυμία είναι υποχρεωτικό.',
            'customer_category_id.required' => 'Η κατηγορία πελάτη είναι υποχρεωτική.',
            'status.required' => 'Η κατάσταση είναι υποχρεωτική.',
            'email_primary.email' => 'Το κύριο email δεν είναι έγκυρο.',
        ];
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Η εισαγωγή των πελατών ολοκληρώθηκε επιτυχώς. '
            . Number::format($import->successful_rows)
            . ' γραμμές εισήχθησαν.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' γραμμές απέτυχαν να εισαχθούν.';
        }

        return $body;
    }
}
