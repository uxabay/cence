<?php

namespace App\Providers;

use Filament\Support\Facades\FilamentColor;
use Illuminate\Support\ServiceProvider;

class FilamentColorServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        FilamentColor::register([

            // ----------------------------------------------------
            // PRIMARY COLOR (brand)
            // ----------------------------------------------------
            'primary' => [
                50  => '#F0F8FC',
                100 => '#D9EDF7',
                150 => '#C1E2F2',
                200 => '#A7D6EC',
                250 => '#8DCBE6',
                300 => '#73C0E0',
                350 => '#5CB3D7',
                400 => '#45A7CE',
                450 => '#2F9BC5',
                500 => '#0074BA',
                550 => '#0069A7',
                600 => '#005B8F',
                650 => '#004F7C',
                700 => '#00446A',
                750 => '#003A59',
                800 => '#002D45',
                850 => '#002234',
                900 => '#001620',
                950 => '#000E14',
            ],

            // ----------------------------------------------------
            // ACCENT COLOR
            // ----------------------------------------------------
            'accent' => [
                50  => '#EAFBF6',
                100 => '#CFF5E9',
                150 => '#B3EFDC',
                200 => '#99E9CF',
                250 => '#7DE3C2',
                300 => '#60DDB5',
                350 => '#45D4A4',
                400 => '#2BCB94',
                450 => '#19BE89',
                500 => '#11A683',
                550 => '#0F9575',
                600 => '#0E8C70',
                650 => '#0C7A62',
                700 => '#0B735C',
                750 => '#095F4E',
                800 => '#075A48',
                850 => '#054F3F',
                900 => '#034034',
                950 => '#012A23',
            ],

            // ----------------------------------------------------
            // SUPPORTING COLORS
            // (αλλάζεις μόνο αν χρειάζεται)
            // ----------------------------------------------------
            'success' => \Filament\Support\Colors\Color::Green,
            'info'    => \Filament\Support\Colors\Color::Blue,
            'warning' => \Filament\Support\Colors\Color::Amber,
            'danger'  => \Filament\Support\Colors\Color::Red,
            'gray'    => \Filament\Support\Colors\Color::Slate,
        ]);
    }
}
