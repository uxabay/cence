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
                    ->label('Επωνυμία')
                    ->icon('heroicon-o-building-office-2')
                    ->searchable()
                    ->sortable()
                    ->description(fn($record) => $record->contact_person ? "Επικ. πρόσωπο: {$record->contact_person}" : null)
                    ->tooltip('Ονομασία πελάτη'),

                TextColumn::make('category.name')
                    ->label('Κατηγορία')
                    ->sortable()
                    ->badge()
                    ->color('info')
                    ->icon('heroicon-o-rectangle-stack'),

                TextColumn::make('primary_email')
                    ->label('Κύριο Email')
                    ->icon('heroicon-o-envelope')
                    ->toggleable()
                    ->copyable()
                    ->tooltip('Πρωτεύον email επικοινωνίας'),

                TextColumn::make('phone')
                    ->label('Τηλέφωνο')
                    ->icon('heroicon-o-phone')
                    ->toggleable()
                    ->searchable(),

                TextColumn::make('city')
                    ->label('Πόλη')
                    ->toggleable()
                    ->sortable()
                    ->tooltip(fn($record) => $record->full_address),

                TextColumn::make('organization_code')
                    ->label('Κωδ. Οργάνωσης')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('tax_id')
                    ->label('Α.Φ.Μ.')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('status')
                    ->label('Κατάσταση')
                    ->badge()
                    ->color(fn($state) => $state->getColor())
                    ->icon(fn($state) => $state->getIcon())
                    ->sortable(),

                TextColumn::make('last_update_at')
                    ->label('Τελευταία ενημέρωση')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])

            ->filters([
                SelectFilter::make('customer_category_id')
                    ->label('Κατηγορία')
                    ->options(CustomerCategory::query()->pluck('name', 'id'))
                    ->searchable(),

                SelectFilter::make('status')
                    ->label('Κατάσταση')
                    ->options(
                        collect(CustomerStatusEnum::cases())
                            ->mapWithKeys(fn($case) => [$case->value => $case->getLabel()])
                    ),

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
                    ReplicateAction::make()->tooltip('Δημιουργία αντιγράφου εγγραφής'),
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
            ->poll('live')
            ->defaultPaginationPageOption(25)
            ->paginated([10, 25, 50, 100])
            ->extremePaginationLinks()
            ->emptyStateHeading('Δεν υπάρχουν πελάτες')
            ->emptyStateDescription('Δημιουργήστε έναν νέο πελάτη για να ξεκινήσετε.');
    }
}
