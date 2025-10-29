<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Actions\Action;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected static null|string $breadcrumb = 'Δημιουργία';

    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()
            ->label('Αποθήκευση') // ✅ custom label for main create button
            ->color('primary')    // optionally change color, icon, etc
            ->icon('heroicon-o-check-circle'); // example icon if you want
    }

    protected function getCreateAnotherFormAction(): Action
    {
        return parent::getCreateAnotherFormAction()
            ->label('Αποθήκευση & Προσθήκη νέου'); // ✅ Your custom Greek label
    }
}
