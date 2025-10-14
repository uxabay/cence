<?php

namespace App\Filament\Resources\LabCustomers\Pages;

use App\Filament\Resources\LabCustomers\LabCustomerResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Actions;

class CreateLabCustomer extends CreateRecord
{
    protected static string $resource = LabCustomerResource::class;

    protected static null|string $breadcrumb = 'Δημιουργία Πελάτη';

    protected function getCreateFormAction(): Actions\Action
    {
        return Actions\Action::make('create')
            ->label('Αποθήκευση')
            ->submit('create');
    }

    protected function getCreateAnotherFormAction(): Actions\Action
    {
        return Actions\Action::make('createAnother')
            ->label('Αποθήκευση & Προσθήκη νέου')
            ->submit('createAnother');
    }
}
