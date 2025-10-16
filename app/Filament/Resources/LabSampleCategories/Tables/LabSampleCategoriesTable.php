<?php

namespace App\Filament\Resources\LabSampleCategories\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Filament\Tables\Enums\FiltersLayout;
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
                TextColumn::make('name')
                    ->label('Ονομασία')
                    ->searchable()
                    ->sortable()
                    ->limit(60)
                    ->tooltip(fn($record) => $record->name),

                TextColumn::make('short_name')
                    ->label('Σύντομη ονομασία')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('code')
                    ->label('Κωδικός')
                    ->sortable()
                    ->searchable()
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('lab.name')
                    ->label('Εργαστήριο')
                    ->sortable()
                    ->badge()
                    ->color('info')
                    ->icon('heroicon-o-building-office'),

                TextColumn::make('sampleType.name')
                    ->label('Τύπος δείγματος')
                    ->sortable()
                    ->badge()
                    ->color('success')
                    ->icon('heroicon-o-beaker'),

                TextColumn::make('price')
                    ->label('Τιμή (€)')
                    ->money('EUR', true)
                    ->sortable()
                    ->alignRight()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('status')
                    ->label('Κατάσταση')
                    ->formatStateUsing(fn ($state) => $state?->getLabel())
                    ->icon(fn ($state) => match ($state) {
                        RecordStatusEnum::Active => 'heroicon-o-check-circle',
                        RecordStatusEnum::Inactive => 'heroicon-o-x-circle',
                        default => null,
                    })
                    ->color(fn ($state) => match ($state) {
                        RecordStatusEnum::Active => 'success',
                        RecordStatusEnum::Inactive => 'gray',
                        default => 'secondary',
                    })
                    ->sortable(),

                TextColumn::make('sort_order')
                    ->label('Σειρά')
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Ημ/νία Δημιουργίας')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Τελευταία ενημέρωση')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])

            /**
             * --------------- FILTERS ---------------
             */
            ->filters([
                SelectFilter::make('status')
                    ->label('Κατάσταση')
                    ->options(collect(RecordStatusEnum::cases())->mapWithKeys(fn($case) => [
                        $case->value => $case->getLabel(),
                    ]))
                    ->attribute('status'),

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
                ActionGroup::make([
                    ViewAction::make()
                        ->label('Προβολή')
                        ->icon('heroicon-o-eye'),

                    EditAction::make()
                        ->label('Επεξεργασία')
                        ->icon('heroicon-o-pencil-square'),

                    DeleteAction::make()
                        ->label('Διαγραφή')
                        ->icon('heroicon-o-trash'),

                    ForceDeleteAction::make()
                        ->label('Οριστική διαγραφή')
                        ->icon('heroicon-o-x-circle'),

                    RestoreAction::make()
                        ->label('Επαναφορά')
                        ->icon('heroicon-o-arrow-path'),
                ])
                    ->icon('heroicon-o-ellipsis-vertical')
                    ->tooltip('Ενέργειες'),
            ])

            /**
             * --------------- BULK ACTIONS ---------------
             */
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])

            /**
             * --------------- TABLE CONFIGURATION ---------------
             */
            ->striped()
            ->defaultPaginationPageOption(25)
            ->paginated([10, 25, 50, 100])
            ->extremePaginationLinks()
            ->reorderable('sort_order')
            ->poll('30s')
            ->emptyStateHeading(('Δεν υπάρχουν κατηγορίες δειγμάτων'))
            ->emptyStateDescription('Δημιουργήστε μία νέα κατηγορία για να ξεκινήσετε.');
    }
}
