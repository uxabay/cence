<?php

namespace App\Filament\Resources\Contracts\RelationManagers;

use App\Filament\Resources\Registrations\RegistrationResource;
use App\Support\Pricing\RegistrationPricingPresenter;
use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Model;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;

class RegistrationsRelationManager extends RelationManager
{
    protected static string $relationship = 'registrations';

    protected static ?string $relatedResource = RegistrationResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('registration_number')
                    ->label('Αριθμός')
                    ->icon('heroicon-o-document-text')
                    ->weight('bold')
                    ->sortable()
                    ->searchable()
                    ->description(fn ($record) =>
                        $record->date?->format('d/m/Y') ?? '-'
                    ),

                TextColumn::make('customer.name')
                    ->label('Πελάτης')
                    ->icon('heroicon-o-building-office')
                    ->sortable()
                    ->searchable()
                    ->limit(28)
                    ->tooltip(fn ($record) =>
                        $record->customer?->organization_code ?: null
                    ),

                TextColumn::make('labCategory.name')
                    ->label('Κατηγορία Δείγματος')
                    ->icon('heroicon-o-beaker')
                    ->color('primary')
                    ->sortable()
                    ->searchable()
                    ->limit(30)
                    ->description(fn ($record) => $record->sample_summary),

                /*
                |--------------------------------------------------------------------------
                | ΤΙΜΟΛΟΓΗΣΗ
                |--------------------------------------------------------------------------
                */
                TextColumn::make('pricing')
                    ->label('Τιμολόγηση')
                    ->badge()
                    ->state(function ($record) {
                        return RegistrationPricingPresenter::from($record)
                            ->toTable()['pricing_label'];
                    })
                    ->color(function ($record) {
                        return RegistrationPricingPresenter::from($record)
                            ->toTable()['pricing_color'];
                    })
                    ->description(function ($record) {
                        return RegistrationPricingPresenter::from($record)
                            ->toTable()['pricing_description'];
                    }),

                /*
                |--------------------------------------------------------------------------
                | ΟΙΚΟΝΟΜΙΚΟ ΑΠΟΤΕΛΕΣΜΑ
                |--------------------------------------------------------------------------
                */
                TextColumn::make('pricing_total')
                    ->label('Οικονομικό Αποτέλεσμα')
                    ->badge()
                    ->color('success')
                    ->state(function ($record) {
                        return RegistrationPricingPresenter::from($record)
                            ->toTable()['total'];
                    })
                    ->description(function ($record) {
                        return RegistrationPricingPresenter::from($record)
                            ->toTable()['total'];
                    }),


            ])
            ->striped()
            ->headerActions([
                CreateAction::make(),
            ]);
    }

    public static function getTabComponent(Model $ownerRecord, string $pageClass): \Filament\Schemas\Components\Tabs\Tab
    {
        $count = $ownerRecord->registrations()->count();
        $hasWarnings = $ownerRecord->has_warning ?? false;

        return \Filament\Schemas\Components\Tabs\Tab::make('Πρωτόκολλα Σύμβασης')
            ->icon('heroicon-o-document-text')
            ->badge($count)
            ->badgeColor($hasWarnings ? 'warning' : 'primary')
            ->badgeTooltip('Σύνολο πρωτοκόλλων σύμβασης');
    }

}
