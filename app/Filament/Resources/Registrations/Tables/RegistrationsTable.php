<?php

namespace App\Filament\Resources\Registrations\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\Action;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Forms\Components\DatePicker;
use App\Enums\RecordStatusEnum;
use App\Models\Registration;
use App\Filament\Resources\Registrations\RegistrationResource;

class RegistrationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('registration_number', 'desc')
            ->columns([

                // 📄 Αριθμός Πρωτοκόλλου + Ημερομηνία
                TextColumn::make('registration_number')
                    ->label('Αριθμός')
                    ->icon('heroicon-o-document-text')
                    ->weight('bold')
                    ->sortable()
                    ->searchable()
                    ->description(fn ($record) => $record->date?->format('d/m/Y') ?? '-'),

                    // 🏢 Πελάτης
                TextColumn::make('customer.name')
                    ->label('Πελάτης')
                    ->icon('heroicon-o-building-office')
                    ->description(fn ($record) => $record->customer?->organization_code ?? null)
                    ->limit(25)
                    ->sortable()
                    ->searchable(),


                // 🧪 Κατηγορία δείγματος εργαστηρίου + Σύνολο
                TextColumn::make('labCategory.name')
                    ->label('Κατηγορία Δείγματος')
                    ->icon('heroicon-o-beaker')
                    ->color('primary')
                    ->description(fn ($record) => $record->sample_summary)
                    ->limit(25)
                    ->sortable()
                    ->searchable(),

                // 📘 Σύμβαση / Κατηγορία δείγματος σύμβασης
                TextColumn::make('contract.contract_number')
                    ->label('Σύμβαση')
                    ->icon('heroicon-o-clipboard-document')
                    ->description(fn ($record) => $record->contractSample?->category->name ?? '-')
                    ->limit(25)
                    ->tooltip(fn ($record) => $record->contract?->title ?? null)
                    ->sortable(),

                // 🟢 Κατάσταση
                TextColumn::make('status')
                    ->label('Κατάσταση')
                    ->badge()
                    ->color(fn ($state) => $state?->getColor())
                    ->icon(fn ($state) => $state?->getIcon())
                    ->sortable(),
            ])

            ->filters([
                SelectFilter::make('status')
                    ->label('Κατάσταση')
                    ->options(RecordStatusEnum::class),

                SelectFilter::make('lab_sample_category_id')
                    ->label('Κατηγορία Εργαστηρίου')
                    ->relationship('labCategory', 'name'),

                SelectFilter::make('contract_id')
                    ->label('Σύμβαση')
                    ->relationship('contract', 'title'),

                SelectFilter::make('customer_id')
                    ->label('Πελάτης')
                    ->relationship('customer', 'name'),

                Filter::make('Ημερομηνία')
                    ->schema([
                        DatePicker::make('from')->label('Από'),
                        DatePicker::make('to')->label('Έως'),
                    ])
                    ->query(fn ($query, array $data) => $query->betweenDates($data['from'] ?? null, $data['to'] ?? null)),

                TrashedFilter::make(),
            ])

            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    Action::make('activities')
                        ->label('Καταγραφές')
                        ->icon('heroicon-o-list-bullet')
                        ->authorize('view_activity_log')
                        ->url(fn ($record) => RegistrationResource::getUrl('activities', ['record' => $record])),
                    EditAction::make(),
                    DeleteAction::make(),
                    RestoreAction::make(),
                    ForceDeleteAction::make(),
                ]),
            ])

            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                ]),
            ])
            ->poll('live')
            ->striped()
            ->paginated([10, 20, 50])
            ->defaultPaginationPageOption(10)
            ->reorderable(false)
            ->emptyStateHeading('Δεν υπάρχουν καταχωρημένες εγγραφές.')
            ->emptyStateDescription('Δεν έχει δημιουργηθεί καμία εγγραφή ακόμη.')
            ->emptyStateIcon('heroicon-o-document-text');
    }
}
