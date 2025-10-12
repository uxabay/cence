<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use BezhanSalleh\FilamentShield\Facades\FilamentShield;
use Filament\Pages\BasePage as Page;
use Filament\Resources\Resource;
use Filament\Widgets\Widget;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Διατηρούμε για ΠΑΝΤΑ το pattern: {affix_snake}_{subject_snake}
        FilamentShield::buildPermissionKeyUsing(
            function (string $entity, string $affix, string $subject, string $case, string $separator) {
                // Μόνο για Resources (τα υπόλοιπα δεν τα χρησιμοποιούμε τώρα)
                if (is_subclass_of($entity, Resource::class)) {
                    return Str::of($affix)->snake() . '_' . Str::of($subject)->snake();
                }

                // Αν ποτέ θελήσεις Page/Widget perms, μπορείς να τα φτιάξεις εδώ.
                return Str::of($affix)->snake() . '_' . Str::of($subject)->snake();
            }
        );

        // (Προαιρετικό) Μπλόκαρε destructive commands σε production
        // use BezhanSalleh\FilamentShield\Commands;
        // use BezhanSalleh\FilamentShield\Facades\FilamentShield as ShieldFacade;
        // Commands\GenerateCommand::prohibit($this->app->isProduction());
        // Commands\InstallCommand::prohibit($this->app->isProduction());
        // Commands\PublishCommand::prohibit($this->app->isProduction());
        // Commands\SetupCommand::prohibit($this->app->isProduction());
        // Commands\SeederCommand::prohibit($this->app->isProduction());
        // Commands\SuperAdminCommand::prohibit($this->app->isProduction());
        // ShieldFacade::prohibitDestructiveCommands($this->app->isProduction());
    }
}
