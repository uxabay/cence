<?php

namespace App\Filament\Resources\LabSampleCategories\Pages;

use App\Filament\Resources\LabSampleCategories\LabSampleCategoryResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewLabSampleCategory extends ViewRecord
{
    protected static string $resource = LabSampleCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
