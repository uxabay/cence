<?php

namespace App\Filament\Resources\LabCustomers\Pages;

use App\Filament\Resources\LabCustomers\LabCustomerResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLabCustomers extends ListRecords
{
    protected static string $resource = LabCustomerResource::class;

    protected static null|string $title = 'Κατάλογος Πελατών';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Προσθήκη Πελάτη'),
        ];
    }
}
