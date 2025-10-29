<?php

namespace App\Filament\Resources\ContractSampleCategories\Schemas;

use App\Enums\RecordStatusEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Section;

class ContractSampleCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Βασικές Πληροφορίες')
                    ->schema([
                        TextInput::make('code')
                            ->label('Κωδικός')
                            ->placeholder('Π.χ. ΚΑΤ-01')
                            ->maxLength(50)
                            ->required(),

                        TextInput::make('name')
                            ->label('Όνομα Κατηγορίας')
                            ->placeholder('Π.χ. Δείγματα Νερού')
                            ->maxLength(255)
                            ->required(),
                            
                        Textarea::make('description')
                            ->label('Περιγραφή')
                            ->placeholder('Σύντομη περιγραφή της κατηγορίας...')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull()
                    ->columns(2),



                Section::make('Κατάσταση')
                    ->schema([
                        Select::make('status')
                            ->label('Κατάσταση')
                            ->options(RecordStatusEnum::class)
                            ->default(RecordStatusEnum::Active)
                            ->required()
                            ->native(false),
                    ]),
            ]);
    }
}
