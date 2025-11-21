<?php

namespace App\Filament\Resources\LabAnalysisPackages\Schemas;

use App\Enums\RecordStatusEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;

class LabAnalysisPackageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Section::make('Στοιχεία Πακέτου Αναλύσεων')
                    ->icon('heroicon-o-rectangle-stack')
                    ->schema([

                        Select::make('lab_sample_category_id')
                            ->relationship('labSampleCategory', 'name')
                            ->label('Κατηγορία Δείγματος')
                            ->searchable()
                            ->preload()
                            ->required(),

                        TextInput::make('name')
                            ->label('Όνομα Πακέτου')
                            ->placeholder('π.χ. Βασικό Πακέτο Χημικών Αναλύσεων')
                            ->required()
                            ->columnSpan(1),

                        Textarea::make('description')
                            ->label('Περιγραφή')
                            ->placeholder('Προαιρετική περιγραφή του πακέτου αναλύσεων.')
                            ->rows(2)
                            ->columnSpanFull(),

                    ])
                    ->columns(2)
                    ->columnSpanFull(),


                Section::make('Κατάσταση')
                    ->icon('heroicon-o-check-circle')
                    ->schema([

                        Select::make('status')
                            ->label('Κατάσταση')
                            ->options([
                                RecordStatusEnum::Active->value => RecordStatusEnum::Active->getLabel(),
                                RecordStatusEnum::Inactive->value => RecordStatusEnum::Inactive->getLabel(),
                            ])
                            ->default(RecordStatusEnum::Active->value)
                            ->required(),

                    ])
                    ->columns(1)
                    ->columnSpanFull(),

            ]);
    }

}
