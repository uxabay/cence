<?php

namespace App\Filament\Resources\Contracts\Tables;

use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Actions\ActionGroup;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\Action;
use Filament\Support\Enums\ActionSize;
use Filament\Support\Enums\ActionColor;
use Filament\Support\Facades\FilamentView;
use App\Enums\RecordStatusEnum;
use App\Filament\Resources\Contracts\Pages\ContractOverview;

class ContractsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('display_title')
            ->columns([
                // === ΣΥΜΒΑΣΗ ===
                TextColumn::make('contract_number')
                    ->label('Σύμβαση')
                    ->formatStateUsing(fn ($state, $record) => "{$state} — {$record->title}")
                    ->description(fn ($record) =>
                        $record->status
                            ? $record->status->getLabel()
                            : '—'
                    )
                    ->badge()
                    ->color(fn ($record) => match ($record->status) {
                        RecordStatusEnum::Active => 'success',
                        RecordStatusEnum::Inactive => 'gray',
                        default => 'secondary',
                    })
                    ->limit(45)
                    ->searchable()
                    ->sortable(),

                // === ΠΕΛΑΤΗΣ ===
                TextColumn::make('customer.name')
                    ->label('Πελάτης')
                    ->description(fn ($record) =>
                        'Από ' . $record->date_start?->format('d/m/Y') .
                        ' έως ' . $record->date_end?->format('d/m/Y')
                    )
                    ->wrap()
                    ->sortable()
                    ->searchable(),

                // === ΟΙΚΟΝΟΜΙΚΑ ===
                TextColumn::make('forecasted_amount')
                    ->label('Οικονομικά')
                    ->formatStateUsing(fn ($state, $record) =>
                        number_format($record->forecasted_amount, 2) . '€ → ' .
                        number_format($record->stats['actual_amount'] ?? 0, 2) . '€'
                    )
                    ->description(fn ($record) =>
                        $record->progress_percentage . '% υλοποίηση'
                    )
                    ->badge()
                    ->color(fn ($record) => match (true) {
                        $record->progress_percentage >= 100 => 'danger',
                        $record->progress_percentage >= 90 => 'warning',
                        $record->progress_percentage > 0 => 'success',
                        default => 'gray',
                    })
                    ->alignRight(),

                // === ALERT ===
                IconColumn::make('has_warning')
                    ->label('')
                    ->icon(fn ($state) => $state ? 'heroicon-o-exclamation-triangle' : null)
                    ->color('warning')
                    ->tooltip('Η σύμβαση πλησιάζει τα όρια εκτέλεσης')
                    ->alignCenter(),
            ])

            ->filters([
                SelectFilter::make('status')
                    ->label('Κατάσταση')
                    ->options(RecordStatusEnum::class),
                TrashedFilter::make(),
            ])

            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()->label('Προβολή')->icon('heroicon-o-eye'),

                    Action::make('overview')
                        ->label('Επισκόπηση')
                        ->icon('heroicon-o-chart-bar')
                        ->url(fn ($record) => ContractOverview::getUrl(['record' => $record]))
                        ->openUrlInNewTab(false),

                    EditAction::make()->label('Επεξεργασία')->icon('heroicon-o-pencil-square'),
                    DeleteAction::make()->label('Διαγραφή'),
                    RestoreAction::make(),
                    ForceDeleteAction::make(),
                ])->icon('heroicon-o-ellipsis-vertical'),
            ])

            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                ]),
            ])
            ->poll('live')
            ->defaultSort('date_end', 'desc')
            ->striped()
            ->paginated(['10', '25', '50'])
            ->emptyStateHeading('Δεν υπάρχουν καταχωρημένες συμβάσεις.')
            ->emptyStateIcon('heroicon-o-document-text');
    }
}
