<?php

namespace App\Filament\Resources\Contracts\Pages;

use App\Filament\Resources\Contracts\ContractResource;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;

class ViewContract extends ViewRecord
{
    protected static string $resource = ContractResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('overview')
                ->label('Επισκόπηση')
                ->icon('heroicon-o-chart-bar')
                ->button()
                ->color('gray')
                ->url(fn ($record) => ContractOverview::getUrl(['record' => $record]))
                ->openUrlInNewTab(false),

            EditAction::make(),
        ];
    }
}
