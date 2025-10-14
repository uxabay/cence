<?php

namespace App\Filament\Resources\LabCustomers\Pages;

use App\Filament\Resources\LabCustomers\LabCustomerResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewLabCustomer extends ViewRecord
{
    protected static string $resource = LabCustomerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
