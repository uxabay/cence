<?php

namespace App\Filament\Resources\ContractSampleCategories\Pages;

use App\Filament\Resources\ContractSampleCategories\ContractSampleCategoryResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewContractSampleCategory extends ViewRecord
{
    protected static string $resource = ContractSampleCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
