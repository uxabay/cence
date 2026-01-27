<?php

namespace App\Filament\Resources\Registrations\Schemas\Components;

use App\Models\Registration;
use App\Support\Pricing\RegistrationPricingPresenter;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Illuminate\Support\Facades\Auth;

final class RegistrationPricingSection
{
    public static function make(): Section
    {
        return Section::make('Οικονομικά Πρωτοκόλλου')
            ->icon('heroicon-o-currency-euro')
            ->visible(fn (): bool => Auth::user()?->can('manage_financials') ?? false)
            ->compact()
            ->schema([
                Grid::make(1)->schema([
                    /*
                    |--------------------------------------------------------------------------
                    | Τρόπος Τιμολόγησης
                    |--------------------------------------------------------------------------
                    */
                    TextEntry::make('pricing_type')
                        ->label('Τρόπος Υπολογισμού')
                        ->badge()
                        ->getStateUsing(function (Registration $record) {
                            return RegistrationPricingPresenter::from($record)
                                ->toInfolist()['pricing_type_label'];
                        })
                        ->color(function (Registration $record) {
                            return RegistrationPricingPresenter::from($record)
                                ->toInfolist()['pricing_type_color'];
                        }),

                    /*
                    |--------------------------------------------------------------------------
                    | Αναλυτικές Γραμμές Τιμολόγησης
                    |--------------------------------------------------------------------------
                    */
                    Grid::make(1)
                        ->schema(function (Registration $record) {

                            $data = RegistrationPricingPresenter::from($record)->toInfolist();

                            return collect($data['rows'])->map(function (array $row) {

                                return TextEntry::make(uniqid('pricing_row_'))
                                    ->label($row['label'])
                                    ->state($row['value'])
                                    ->weight(($row['emphasis'] ?? false) ? 'bold' : null)
                                    ->color(($row['emphasis'] ?? false) ? 'success' : null);
                            })->all();
                        }),
                ]),
            ]);
    }
}
