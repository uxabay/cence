<?php

namespace App\Filament\Resources\LabCustomers\Pages;

use App\Filament\Resources\LabCustomers\LabCustomerResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use phpDocumentor\Reflection\Types\Boolean;

class CreateLabCustomer extends CreateRecord
{
    protected static string $resource = LabCustomerResource::class;

    protected static null|string $breadcrumb = 'Δημιουργία';

    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()
            ->label('Αποθήκευση') // ✅ custom label for main create button
            ->color('primary')    // optionally change color, icon, etc
            ->icon('heroicon-o-check-circle'); // example icon if you want
    }

    protected function getCreateAnotherFormAction(): Action
    {
        return parent::getCreateAnotherFormAction()
            ->label('Αποθήκευση & Προσθήκη νέου'); // ✅ Your custom Greek label
    }

}
