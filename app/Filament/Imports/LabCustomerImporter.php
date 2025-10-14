<?php

namespace App\Filament\Imports;

use App\Models\LabCustomer;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;

class LabCustomerImporter extends Importer
{
    protected static ?string $model = LabCustomer::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('customer_category_id')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'integer']),
            ImportColumn::make('contact_person')
                ->rules(['max:255']),
            ImportColumn::make('phone')
                ->rules(['max:50']),
            ImportColumn::make('address')
                ->rules(['max:255']),
            ImportColumn::make('city')
                ->rules(['max:100']),
            ImportColumn::make('postal_code')
                ->rules(['max:20']),
            ImportColumn::make('encryption_key')
                ->rules(['max:255']),
            ImportColumn::make('last_update_at')
                ->rules(['datetime']),
            ImportColumn::make('status')
                ->requiredMapping()
                ->rules(['required', 'max:20']),
            ImportColumn::make('notes'),
            ImportColumn::make('created_by')
                ->numeric()
                ->rules(['integer']),
            ImportColumn::make('updated_by')
                ->numeric()
                ->rules(['integer']),
        ];
    }

    public function resolveRecord(): LabCustomer
    {
        return LabCustomer::firstOrNew([
            'name' => $this->data['name'],
        ]);
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your lab customer import has completed and ' . Number::format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
