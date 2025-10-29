<?php

namespace App\Filament\Resources\ContractSampleCategories\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class ContractSampleCategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Κωδικός')
                    ->icon('heroicon-o-hashtag')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('name')
                    ->label('Όνομα Κατηγορίας')
                    ->icon('heroicon-o-rectangle-stack')
                    ->sortable()
                    ->searchable()
                    ->wrap()
                    ->limit(40)
                    ->tooltip(fn ($record) => $record->description),

                TextColumn::make('status')
                    ->label('Κατάσταση')
                    ->badge()
                    ->color(fn ($state) => $state?->getColor())
                    ->icon(fn ($state) => $state?->getIcon())
                    ->sortable(),

                TextColumn::make('updated_at')
                    ->label('Τελευταία ενημέρωση')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make()
                    ->label('Διαγραμμένα'),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),
                    ForceDeleteAction::make(),
                    RestoreAction::make(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Διαγραφή'),
                    ForceDeleteBulkAction::make()
                        ->label('Οριστική διαγραφή'),
                    RestoreBulkAction::make()
                        ->label('Επαναφορά'),
                ]),
            ])
            ->poll('live')
            ->defaultSort('name')
            ->defaultPaginationPageOption(25)
            ->paginated([10, 25, 50, 100])
            ->extremePaginationLinks()
            ->emptyStateHeading('Δεν υπάρχουν κατηγορίες δειγμάτων')
            ->emptyStateDescription('Δημιουργήστε μια νέα κατηγορία για να ξεκινήσετε.');
    }
}
