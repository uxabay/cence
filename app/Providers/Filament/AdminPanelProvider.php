<?php

namespace App\Providers\Filament;

use App\Filament\Support\NavigationGroupsNames;
use BezhanSalleh\FilamentShield\Facades\FilamentShield;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Illuminate\Support\Facades\Auth;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->profile()

            ->navigationGroups([
                NavigationGroup::make()
                    ->label(NavigationGroupsNames::CONTRACTS->getLabel())
                    ->icon('heroicon-o-briefcase'),

                NavigationGroup::make()
                    ->label(NavigationGroupsNames::LABORATORY->getLabel())
                    ->icon('heroicon-o-beaker'),

                NavigationGroup::make()
                    ->label(NavigationGroupsNames::SYSTEM->getLabel())
                    ->icon('heroicon-o-cog'),

                NavigationGroup::make()
                    ->label(NavigationGroupsNames::REPORTS->getLabel())
                    ->icon('heroicon-o-chart-bar'),
            ])

            ->colors([
                'primary' => '#2563eb',
            ])

            ->spa(hasPrefetching: true)

            ->databaseNotifications()

            ->sidebarCollapsibleOnDesktop()

            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])

            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                \App\Http\Middleware\TrackUserActivity::class,
                \App\Http\Middleware\EnsurePasswordResetIsDone::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])

            ->plugins([
                FilamentShieldPlugin::make()
                    ->gridColumns([
                        'default' => 1,
                        'sm' => 2,
                        'lg' => 2
                    ])
                    ->sectionColumnSpan(1)
                    ->checkboxListColumns([
                        'default' => 1,
                        'sm' => 2,
                        'lg' => 4
                    ])
                    ->resourceCheckboxListColumns([
                        'default' => 1,
                        'sm' => 2
                    ])
                    ->recordTitleAttribute(false)
                    ->localizePermissionLabels()
                    ->simpleResourcePermissionView(false),
            ]);
    }
}

