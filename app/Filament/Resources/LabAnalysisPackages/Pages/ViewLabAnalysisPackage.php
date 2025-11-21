<?php

namespace App\Filament\Resources\LabAnalysisPackages\Pages;

use App\Filament\Resources\LabAnalysisPackages\LabAnalysisPackageResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewLabAnalysisPackage extends ViewRecord
{
    protected static string $resource = LabAnalysisPackageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
