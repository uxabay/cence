<?php

namespace App\Filament\Resources\LabCustomers\Pages;

use App\Filament\Resources\LabCustomers\LabCustomerResource;
use App\Filament\Resources\LabCustomers\Widgets\CustomerStatsOverview;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Pages\Concerns\ExposesTableToWidgets;


class ListLabCustomers extends ListRecords
{
    use ExposesTableToWidgets;

    protected static string $resource = LabCustomerResource::class;

    protected static null|string $title = 'Κατάλογος Πελατών';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Προσθήκη Πελάτη'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            CustomerStatsOverview::class,
        ];
    }

}
