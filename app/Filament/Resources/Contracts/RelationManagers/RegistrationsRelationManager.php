<?php

namespace App\Filament\Resources\Contracts\RelationManagers;

use App\Filament\Resources\Registrations\RegistrationResource;
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
                TextColumn::make('pricing_info')
                    ->label('Τιμολόγηση')
                    ->badge()
                    ->color(function ($record) {
                        $type = $record->contractSample?->cost_calculation_type->value ?? null;
                        return $type === 'variable' ? 'warning' : 'success';
                    })
                    ->state(function ($record) {
                        $type = $record->contractSample?->cost_calculation_type->value;

                        return match ($type) {
                            'variable' => 'Με βάση αναλύσεις',
                            'fixed', 'fix' => 'Σταθερή Τιμή',
                            default => '-'
                        };
                    })
                    ->description(function ($record) {
                        if ($record->calculated_unit_price === null) {
                            return '-';
                        }

                        return number_format($record->calculated_unit_price, 2)
                            . ' € / δείγμα';
                    }),

                /*
                |--------------------------------------------------------------------------
                | ΟΙΚΟΝΟΜΙΚΟ ΑΠΟΤΕΛΕΣΜΑ
                |--------------------------------------------------------------------------
                */
                TextColumn::make('financial_info')
                    ->label('Οικονομικό Αποτέλεσμα')
                    ->badge()
                    ->color('info')
                    ->state(function ($record) {
                        $samples = $record->total_samples;
                        $unit = $record->calculated_unit_price;

                        if ($samples === null || $unit === null) {
                            return '-';
                        }

                        return "{$samples} × " . number_format($unit, 2) . " €";
                    })
                    ->description(function ($record) {

                        if ($record->calculated_total === null) {
                            return '-';
                        }

                        return number_format($record->calculated_total, 2) . ' €';
                    })
                    ->tooltip(function ($record) {

                        if ($record->analyses->isEmpty()) {
                            return null;
                        }

                        return $record->analyses
                            ->map(fn ($a) =>
                                "• {$a->analysis_name} (" .
                                number_format($a->analysis_price, 2) . " €)"
                            )
                            ->join("\n");
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
