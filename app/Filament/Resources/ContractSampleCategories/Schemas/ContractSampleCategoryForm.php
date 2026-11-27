<?php

namespace App\Filament\Resources\ContractSampleCategories\Schemas;

use App\Enums\RecordStatusEnum;
use App\Filament\Forms\Components\CodeInput;
use App\Models\ContractSampleCategory;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

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
                                CodeInput::make('code')
                                    ->label('Κωδικός')
                                    ->placeholder('Π.χ. ΚΑΤ-01')
                                    ->target(ContractSampleCategory::class, 'code')
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

                        Toggle::make('exclude_from_lab_totals')
                            ->label('Εξαίρεση από τα εργαστηριακά σύνολα')
                            ->helperText('Για τις γραμμές συμβάσεων που χρησιμοποιούν αυτή την κατηγορία, τα αντίστοιχα πρωτόκολλα εξακολουθούν να υπολογίζονται στη σύμβαση, αλλά εξαιρούνται από τα συνολικά εργαστηριακά στατιστικά.')
                            ->default(false)
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(), // Εξασφαλίζει πλήρες πλάτος φόρμας
            ]);
    }
}
