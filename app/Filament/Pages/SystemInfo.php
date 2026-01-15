<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use BackedEnum;
use Illuminate\Support\Facades\Auth;
use PhpParser\Node\Arg;
use UnitEnum;

class SystemInfo extends Page
{
    protected string $view = 'filament.pages.system-info';
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-information-circle';
    protected static ?string $navigationLabel = 'System Info';
    protected static string|UnitEnum|null $navigationGroup = 'Σύστημα';
    protected static ?int $navigationSort = 999;

    public static function canAccess(): bool
    {
        return Auth::User()->can('view_system_info') ?? false;
    }

    protected function getViewData(): array
    {
        return [
            'appName'   => config('app.name'),
            'version'   => config('app.version'),
            'env'       => config('app.env'),
            'debug'     => (bool) config('app.debug'),
            'php'       => PHP_VERSION,
            'laravel'   => app()->version(),
            'timezone'  => config('app.timezone'),
            'locale'    => config('app.locale'),
            'db'        => config('database.default'),
            'dbDriver'  => config('database.connections.' . config('database.default') . '.driver'),
        ];
    }
}
