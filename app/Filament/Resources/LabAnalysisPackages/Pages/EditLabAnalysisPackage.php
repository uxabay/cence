<?php

namespace App\Filament\Resources\LabAnalysisPackages\Pages;

use App\Filament\Resources\LabAnalysisPackages\LabAnalysisPackageResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditLabAnalysisPackage extends EditRecord
{
    protected static string $resource = LabAnalysisPackageResource::class;

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
