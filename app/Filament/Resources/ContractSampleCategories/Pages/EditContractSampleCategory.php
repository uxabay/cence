<?php

namespace App\Filament\Resources\ContractSampleCategories\Pages;

use App\Filament\Resources\ContractSampleCategories\ContractSampleCategoryResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditContractSampleCategory extends EditRecord
{
    protected static string $resource = ContractSampleCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
