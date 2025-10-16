<?php

namespace App\Filament\Resources\LabSampleCategories\Pages;

use App\Filament\Resources\LabSampleCategories\LabSampleCategoryResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditLabSampleCategory extends EditRecord
{
    protected static string $resource = LabSampleCategoryResource::class;

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
