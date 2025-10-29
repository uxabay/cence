<?php

namespace App\Filament\Resources\Contracts\Pages;

use App\Filament\Resources\Contracts\ContractResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Actions;
use Filament\Actions\Action;

class CreateContract extends CreateRecord
{
    protected static string $resource = ContractResource::class;

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
