<?php

namespace App\Filament\Resources\ContractSampleCategories\Pages;

use App\Filament\Resources\ContractSampleCategories\ContractSampleCategoryResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Actions\Action;

class CreateContractSampleCategory extends CreateRecord
{
    protected static string $resource = ContractSampleCategoryResource::class;
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
