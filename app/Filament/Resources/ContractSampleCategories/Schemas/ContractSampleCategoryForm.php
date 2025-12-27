<?php

namespace App\Filament\Resources\ContractSampleCategories\Schemas;

use App\Enums\RecordStatusEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;

class ContractSampleCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Ενοποίηση όλων των πεδίων σε μία Section για μέγιστη ευκολία χρήσης
                Section::make('Πληροφορίες Κατηγορίας Δειγμάτων')
                    ->description('Ορισμός κωδικού, ονόματος και κατάστασης για την κατηγορία.')
                    ->icon('heroicon-o-folder-open')
                    ->compact() // Κάνει την ενότητα πιο compact οπτικά
                    ->schema([
                        // Grid 3: Κωδικός, Όνομα, Κατάσταση - Όλα στην ίδια γραμμή
                        Grid::make(3)
                            ->schema([
                                TextInput::make('code')
                                    ->label('Κωδικός')
                                    ->placeholder('Π.χ. ΚΑΤ-01')
                                    ->maxLength(50)
                                    ->required()
                                    ->autofocus(),

                                TextInput::make('name')
                                    ->label('Όνομα Κατηγορίας')
                                    ->placeholder('Π.χ. Δείγματα Νερού')
                                    ->maxLength(255)
                                    ->required(),

                                Select::make('status')
                                    ->label('Κατάσταση')
                                    ->options(RecordStatusEnum::class)
                                    ->default(RecordStatusEnum::Active)
                                    ->required()
                                    ->native(false),
                            ]),

                        // Περιγραφή: Πλήρες πλάτος από κάτω
                        Textarea::make('description')
                            ->label('Περιγραφή')
                            ->placeholder('Σύντομη περιγραφή της κατηγορίας...')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(), // Εξασφαλίζει πλήρες πλάτος φόρμας
            ]);
    }
}
