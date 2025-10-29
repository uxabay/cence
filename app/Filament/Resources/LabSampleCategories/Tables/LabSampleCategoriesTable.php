<?php

namespace App\Filament\Resources\LabSampleCategories\Tables;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Actions; // Ενοποιημένο namespace Filament 4.x
use App\Enums\RecordStatusEnum;

class LabSampleCategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            /**
             * --------------- COLUMNS ---------------
             */
            ->columns([
                TextColumn::make('code')
                    ->label('Κωδικός')
                    ->alignCenter()
                    ->sortable()
                    ->searchable(),

                TextColumn::make('name')
                    ->label('Ονομασία')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->limit(40)
                    ->tooltip(fn ($record) => $record->name),

                TextColumn::make('lab.name')
                    ->label('Εργαστήριο')
                    ->badge()
                    ->color('info')
                    ->icon('heroicon-o-building-office')
                    ->sortable(),

                TextColumn::make('sampleType.name')
                    ->label('Τύπος')
                    ->badge()
                    ->color('success')
                    ->icon('heroicon-o-beaker')
                    ->sortable(),

                TextColumn::make('default_price')
                    ->label('Τιμή (€)')
                    ->numeric(decimalPlaces: 2)
                    ->alignRight()
                    ->sortable(),

                TextColumn::make('status')
                    ->badge()
                    ->label('Κατάσταση')
                    ->formatStateUsing(fn ($state) => $state?->getLabel())
                    ->colors([
                        'success' => fn ($state) => $state === RecordStatusEnum::Active,
                        'gray' => fn ($state) => $state === RecordStatusEnum::Inactive,
                    ])
                    ->icons([
                        'heroicon-o-check-circle' => RecordStatusEnum::Active,
                        'heroicon-o-x-circle' => RecordStatusEnum::Inactive,
                    ])
                    ->sortable(),
            ])

            /**
             * --------------- FILTERS ---------------
             */
            ->filters([
                SelectFilter::make('status')
                    ->label('Κατάσταση')
                    ->options(collect(RecordStatusEnum::cases())
                        ->mapWithKeys(fn ($case) => [$case->value => $case->getLabel()])),
                SelectFilter::make('lab_id')
                    ->label('Εργαστήριο')
                    ->relationship('lab', 'name'),
                SelectFilter::make('sample_type_id')
                    ->label('Τύπος δείγματος')
                    ->relationship('sampleType', 'name'),
                TrashedFilter::make()
                    ->label('Διεγραμμένα'),
            ], layout: FiltersLayout::AboveContentCollapsible)

            /**
             * --------------- ACTIONS ---------------
             */
            ->recordActions([
                Actions\ActionGroup::make([
                    Actions\ViewAction::make(),
                    Actions\EditAction::make(),
                    Actions\DeleteAction::make(),
                    Actions\ForceDeleteAction::make(),
                    Actions\RestoreAction::make(),
                ])->icon('heroicon-o-ellipsis-vertical')
                  ->tooltip('Ενέργειες'),
            ])

            /**
             * --------------- BULK ACTIONS ---------------
             */
            ->toolbarActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                    Actions\ForceDeleteBulkAction::make(),
                    Actions\RestoreBulkAction::make(),
                ]),
            ])

            /**
             * --------------- TABLE CONFIG ---------------
             */
            ->poll('live')
            ->striped()
            ->defaultPaginationPageOption(25)
            ->paginated([10, 25, 50, 100])
            ->reorderable('sort_order')
            ->emptyStateHeading('Δεν υπάρχουν κατηγορίες δειγμάτων')
            ->emptyStateDescription('Δημιουργήστε μια νέα κατηγορία για να ξεκινήσετε.');
    }
}
