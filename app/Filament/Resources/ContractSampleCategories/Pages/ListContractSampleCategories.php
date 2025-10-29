<?php

namespace App\Filament\Resources\ContractSampleCategories\Pages;

use App\Filament\Resources\ContractSampleCategories\ContractSampleCategoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListContractSampleCategories extends ListRecords
{
    protected static string $resource = ContractSampleCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
