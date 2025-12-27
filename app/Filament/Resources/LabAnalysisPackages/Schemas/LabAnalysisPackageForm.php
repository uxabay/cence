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
                    ->description('Ορισμός του ονόματος, της κατηγορίας δείγματος και της κατάστασης του πακέτου.')
                    ->icon('heroicon-o-rectangle-stack')
                    ->compact() // Κάνει την ενότητα πιο συμπαγή
                    ->schema([

                        // 1. Κατηγορία & Όνομα (2/3 πλάτος)
                        Select::make('lab_sample_category_id')
                            ->relationship('labSampleCategory', 'name')
                            ->label('Κατηγορία Δείγματος')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->columnSpan(1),

                        TextInput::make('name')
                            ->label('Όνομα Πακέτου')
                            ->placeholder('π.χ. Βασικό Πακέτο Χημικών Αναλύσεων')
                            ->required()
                            ->columnSpan(1),

                        // 2. Περιγραφή (Πλήρες Πλάτος)
                        Textarea::make('description')
                            ->label('Περιγραφή')
                            ->placeholder('Προαιρετική περιγραφή του πακέτου αναλύσεων.')
                            ->rows(2)
                            ->columnSpanFull(),

                        // 3. Κατάσταση (1/3 πλάτος, στο κάτω μέρος της ενότητας)
                        Select::make('status')
                            ->label('Κατάσταση')
                            ->options([
                                RecordStatusEnum::Active->value => RecordStatusEnum::Active->getLabel(),
                                RecordStatusEnum::Inactive->value => RecordStatusEnum::Inactive->getLabel(),
                            ])
                            ->default(RecordStatusEnum::Active->value)
                            ->required()
                            ->columnSpan(1), // Στήλη 1 για να ευθυγραμμιστεί με τα παραπάνω πεδία

                    ])
                    ->columns(2) // Κύρια διάταξη σε 2 στήλες
                    ->columnSpanFull(),

            ]);
    }

}
