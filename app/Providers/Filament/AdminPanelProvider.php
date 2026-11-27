<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Dashboard;
use App\Filament\Pages\ForcePasswordReset;
use App\Filament\Support\NavigationGroupsNames;
use App\Http\Middleware\EnsurePasswordResetIsDone;
use App\Http\Middleware\TrackUserActivity;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\FontProviders\GoogleFontProvider;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\PanelProvider;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->topbar(true)

            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->profile()

            ->font('Ubuntu Sans', provider: GoogleFontProvider::class)

            ->navigationGroups([
                NavigationGroup::make()
                    ->label(NavigationGroupsNames::CONTRACTS->getLabel())
                    ->icon('heroicon-o-briefcase'),

                NavigationGroup::make()
                    ->label(NavigationGroupsNames::LABORATORY->getLabel())
                    ->icon('heroicon-o-beaker'),

                NavigationGroup::make()
                    ->label(NavigationGroupsNames::REPORTS->getLabel())
                    ->icon('heroicon-o-chart-bar'),

                NavigationGroup::make()
                    ->label(NavigationGroupsNames::SYSTEM->getLabel())
                    ->icon('heroicon-o-cog'),
            ])

            ->spa(hasPrefetching: true)

            ->databaseNotifications(true)

            ->sidebarCollapsibleOnDesktop()

            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
                ForcePasswordReset::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
            ])

            ->favicon(asset('images/cence_fav_icon.png'))
            ->brandLogo(asset('images/cence_logo_full.png'))

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
                TrackUserActivity::class,
                EnsurePasswordResetIsDone::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])

            ->viteTheme('resources/css/filament/admin/theme.css')

            ->plugins([
                FilamentShieldPlugin::make()
                    ->globallySearchable(false)
                    ->gridColumns([
                        'default' => 1,
                        'sm' => 2,
                        'lg' => 2,
                    ])
                    ->sectionColumnSpan(1)
                    ->checkboxListColumns([
                        'default' => 1,
                        'sm' => 2,
                        'lg' => 4,
                    ])
                    ->resourceCheckboxListColumns([
                        'default' => 1,
                        'sm' => 2,
                    ])
                    ->recordTitleAttribute(false)
                    ->localizePermissionLabels()
                    ->simpleResourcePermissionView(false),
            ]);
    }
}
