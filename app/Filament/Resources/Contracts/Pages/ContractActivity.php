<?php

namespace App\Filament\Resources\Contracts\Pages;

use App\Filament\Resources\Contracts\ContractResource;
use pxlrbt\FilamentActivityLog\Pages\ListActivities;

class ContractActivity extends ListActivities
{
    protected static string $resource = ContractResource::class;

}
