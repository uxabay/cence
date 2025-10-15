<?php

namespace App\Filament\Imports;

use App\Models\LabCustomer;
use App\Models\CustomerCategory;
use App\Models\LabCustomerEmail;
use App\Enums\CustomerStatusEnum;
use Carbon\Carbon;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Number;

class LabCustomerImporter extends Importer
{
    protected static ?string $model = LabCustomer::class;
    protected static ?string $modelName = "Πελάτες";

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
                ->label('Ονομασία')
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
                ->helperText('Αναζητείται με βάση το όνομα της κατηγορίας, όχι το ID.')
                ->example('Δήμοι'),

            ImportColumn::make('contact_person')
                ->label('Υπεύθυνος επικοινωνίας')
                ->rules(['max:255'])
                ->example('Ιωάννης Παπαδόπουλος'),

            ImportColumn::make('phone')
                ->label('Τηλέφωνο')
                ->rules(['max:50'])
                ->castStateUsing(fn (?string $state) => $state ? preg_replace('/[^0-9+]/', '', $state) : null)
                ->example('+302410123456'),

            ImportColumn::make('address')
                ->label('Διεύθυνση')
                ->rules(['max:255'])
                ->example('Βασιλέως Γεωργίου 12'),

            ImportColumn::make('city')
                ->label('Πόλη')
                ->rules(['max:100'])
                ->example('Καρδίτσα'),

            ImportColumn::make('postal_code')
                ->label('Τ.Κ.')
                ->rules(['max:20'])
                ->example('43100'),

            ImportColumn::make('encryption_key')
                ->label('Κλειδί κρυπτογράφησης')
                ->sensitive()
                ->rules(['max:255'])
                ->helperText('Προαιρετικό – δεν εμφανίζεται στα αρχεία αποτυχίας.'),

            ImportColumn::make('last_update_at')
                ->label('Τελευταία ενημέρωση')
                ->castStateUsing(function (?string $state) {
                    if (blank($state)) return null;
                    try {
                        return Carbon::parse($state);
                    } catch (\Exception $e) {
                        return null;
                    }
                })
                ->example('2024-12-15'),

            ImportColumn::make('status')
                ->label('Κατάσταση')
                ->requiredMapping()
                ->rules(['required', 'max:20'])
                ->castStateUsing(function (?string $state) {
                    if (blank($state)) return CustomerStatusEnum::Active->value;
                    $normalized = mb_strtolower(trim($state));
                    return match ($normalized) {
                        'ενεργός', 'ενεργοσ', 'active', '1', 'ναι' => CustomerStatusEnum::Active->value,
                        'ανενεργός', 'inactive', '0', 'όχι' => CustomerStatusEnum::Inactive->value,
                        default => CustomerStatusEnum::Active->value,
                    };
                })
                ->example('Ενεργός'),

            ImportColumn::make('notes')
                ->label('Σημειώσεις')
                ->castStateUsing(fn (?string $state) => $state ? trim(preg_replace("/\r\n|\r|\n/", ' ', $state)) : null)
                ->example('Σχόλια ή πρόσθετες πληροφορίες για τον πελάτη.'),

            // 🟨 ΝΕΑ ΣΤΗΛΗ EMAILS
            ImportColumn::make('emails')
                ->label('Emails επικοινωνίας')
                ->multiple(',')
                ->castStateUsing(fn (?array $state) => collect($state)
                    ->map(fn ($v) => trim($v))
                    ->filter()
                    ->unique()
                    ->values()
                    ->toArray()
                )
                ->fillRecordUsing(function (LabCustomer $record, array $emails): void {
                    if (empty($emails)) return;

                    foreach ($emails as $index => $email) {
                        $record->emails()->firstOrCreate(
                            ['email' => $email],
                            [
                                'is_primary' => $index === 0,
                                'created_by' => Auth::id(),
                                'updated_by' => Auth::id(),
                            ]
                        );
                    }
                })
                ->helperText('Πολλαπλά emails χωρισμένα με κόμμα (π.χ. info@domain.gr, support@domain.gr).')
                ->example('info@karditsa.gr, mayor@karditsa.gr'),
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
    }

    public function getValidationMessages(): array
    {
        return [
            'name.required' => 'Το πεδίο Ονομασία είναι υποχρεωτικό.',
            'customer_category_id.required' => 'Η κατηγορία πελάτη είναι υποχρεωτική.',
            'status.required' => 'Η κατάσταση είναι υποχρεωτική.',
            'last_update_at.date' => 'Η ημερομηνία τελευταίας ενημέρωσης δεν είναι έγκυρη.',
        ];
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Η εισαγωγή των πελατών ολοκληρώθηκε με επιτυχία. '
            . Number::format($import->successful_rows)
            . ' γραμμές εισήχθησαν.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' '
                . Number::format($failedRowsCount)
                . ' γραμμές απέτυχαν να εισαχθούν.';
        }

        return $body;
    }
}
