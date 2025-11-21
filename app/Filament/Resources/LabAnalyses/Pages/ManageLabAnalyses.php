<?php

namespace App\Filament\Resources\LabAnalyses\Pages;

use App\Filament\Resources\LabAnalyses\LabAnalysisResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageLabAnalyses extends ManageRecords
{
    protected static string $resource = LabAnalysisResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
