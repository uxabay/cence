<?php

namespace App\Providers\Filament;

use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\PanelProvider;
use App\Filament\Support\NavigationGroupsNames;

class NavigationGroupProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel->navigationGroups([
            NavigationGroup::make()
                ->label(NavigationGroupsNames::CONTRACTS->value)
                ->collapsible(false)
                ->icon('heroicon-o-document-text'),

            NavigationGroup::make()
                ->label(NavigationGroupsNames::LABORATORY->value)
                ->collapsible(false)
                ->icon('heroicon-o-beaker'),

            NavigationGroup::make()
                ->label(NavigationGroupsNames::SYSTEM->value)
                ->collapsible(false)
                ->icon('heroicon-o-cog-6-tooth'),

            NavigationGroup::make()
                ->label(NavigationGroupsNames::REPORTS->value)
                ->collapsible(false)
                ->icon('heroicon-o-chart-bar'),
        ]);
    }
}
