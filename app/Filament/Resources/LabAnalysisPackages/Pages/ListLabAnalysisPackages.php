<?php

namespace App\Filament\Resources\LabAnalysisPackages\Pages;

use App\Filament\Resources\LabAnalysisPackages\LabAnalysisPackageResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLabAnalysisPackages extends ListRecords
{
    protected static string $resource = LabAnalysisPackageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Προσθήκη Πακέτου Αναλύσεων'),
        ];
    }
}
