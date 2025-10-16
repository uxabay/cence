<?php

namespace App\Filament\Resources\LabSampleCategories\Pages;

use App\Filament\Resources\LabSampleCategories\LabSampleCategoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLabSampleCategories extends ListRecords
{
    protected static string $resource = LabSampleCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
