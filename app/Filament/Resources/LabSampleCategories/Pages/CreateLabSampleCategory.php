<?php

namespace App\Filament\Resources\LabSampleCategories\Pages;

use App\Filament\Resources\LabSampleCategories\LabSampleCategoryResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Actions;

class CreateLabSampleCategory extends CreateRecord
{
    protected static string $resource = LabSampleCategoryResource::class;

    protected static null|string $breadcrumb = 'Δημιουργία Κατηγορίας Δειγμάτων';

    protected function getCreateFormAction(): Actions\Action
    {
        return Actions\Action::make('create')
            ->label('Αποθήκευση')
            ->submit('create');
    }

    protected function getCreateAnotherFormAction(): Actions\Action
    {
        return Actions\Action::make('createAnother')
            ->label('Αποθήκευση & Προσθήκη νέου')
            ->submit('createAnother');
    }
}
