<?php

namespace App\Filament\Resources\LabAnalysisPackages\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class LabAnalysisPackagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')

            ->columns([

                TextColumn::make('labSampleCategory.name')
                    ->label('Κατηγορία')
                    ->sortable()
                    ->searchable()
                    ->weight('medium')
                    ->color('primary'),

                TextColumn::make('name')
                    ->label('Όνομα Πακέτου')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('analyses_count')
                    ->label('Αναλύσεις')
                    ->badge()
                    ->color('blue')
                    ->formatStateUsing(fn ($state) => $state ?? 0)
                    ->alignCenter()
                    ->toggleable(),

                TextColumn::make('status')
                    ->label('Κατάσταση')
                    ->badge()
                    ->color(fn ($state) => $state?->getColor())
                    ->icon(fn ($state) => $state?->getIcon())
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Δημιουργία')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Ενημέρωση')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])

            ->filters([
                TrashedFilter::make(),
            ])

            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()->label('Προβολή')->icon('heroicon-o-eye'),
                    EditAction::make()->label('Επεξεργασία')->icon('heroicon-o-pencil-square'),
                    DeleteAction::make(),
                    ForceDeleteAction::make(),
                    RestoreAction::make(),
                ])->icon('heroicon-o-ellipsis-vertical'),
            ])

            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }


}
