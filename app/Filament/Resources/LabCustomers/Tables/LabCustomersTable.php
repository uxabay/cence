<?php

namespace App\Filament\Resources\LabCustomers\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ReplicateAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use App\Filament\Imports\LabCustomerImporter;
use Filament\Actions\ImportAction;
use App\Models\CustomerCategory;
use App\Enums\CustomerStatusEnum;

class LabCustomersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Ονομασία')
                    ->searchable()
                    ->sortable()
                    ->description(fn($record) => $record->contact_person)
                    ->tooltip('Ονομασία πελάτη'),

                TextColumn::make('category.name')
                    ->label('Κατηγορία')
                    ->sortable()
                    ->toggleable()
                    ->badge(),

                TextColumn::make('phone')
                    ->label('Τηλέφωνο')
                    ->toggleable()
                    ->icon('heroicon-o-phone'),

                TextColumn::make('emails.email')
                    ->label('Emails')
                    ->listWithLineBreaks()
                    ->limitList(3)
                    ->toggleable()
                    ->wrap(),

                TextColumn::make('status')
                    ->label('Κατάσταση')
                    ->badge()
                    ->sortable(),

                TextColumn::make('last_update_at')
                    ->label('Τελευταία ενημέρωση')
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('customer_category_id')
                    ->label('Κατηγορία')
                    ->options(CustomerCategory::query()->pluck('name', 'id'))
                    ->searchable(),

                SelectFilter::make('status')
                    ->label('Κατάσταση')
                    ->options(collect(CustomerStatusEnum::cases())->mapWithKeys(fn($case) => [$case->value => $case->getLabel()])),

                TrashedFilter::make(),
            ])
            ->headerActions([
                ImportAction::make()
                    ->label('Εισαγωγή πελατών')
                    ->modalHeading('Εισαγωγή αρχείου CSV')
                    ->modalSubmitActionLabel('Εκκίνηση εισαγωγής')
                    ->modalCancelActionLabel('Ακύρωση')
                    ->importer(LabCustomerImporter::class),
            ])

            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    ReplicateAction::make()
                        ->tooltip('Δημιουργία αντιγράφου εγγραφής'),

                    DeleteAction::make(),
                    ForceDeleteAction::make(),
                    RestoreAction::make(),
                ]),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->defaultPaginationPageOption(25)
            ->paginated([10, 25, 50, 100])
            ->extremePaginationLinks()
            ->poll('30s')
            ->emptyStateHeading(('Δεν υπάρχουν πελατες'))
            ->emptyStateDescription('Δημιουργήστε έναν νέο πελάτη για να ξεκινήσετε.');
    }
}
