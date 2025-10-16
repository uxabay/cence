<?php

namespace App\Filament\Resources\LabSampleCategories\Schemas;

use App\Enums\RecordStatusEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class LabSampleCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Section::make()
                    ->schema([
                        Fieldset::make('Βασικά στοιχεία')
                            ->schema([
                                TextInput::make('name')
                                    ->label('Ονομασία')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpanFull(),

                                TextInput::make('short_name')
                                    ->label('Σύντομη ονομασία')
                                    ->maxLength(100),

                                TextInput::make('code')
                                    ->label('Κωδικός')
                                    ->maxLength(50),

                                Textarea::make('description')
                                    ->label('Περιγραφή')
                                    ->rows(3)
                                    ->columnSpanFull(),
                        ]),

                        Grid::make(1)
                            ->schema([
                                Fieldset::make('Αντιστοιχίσεις')
                                    ->schema([
                                        Select::make('lab_id')
                                            ->label('Εργαστήριο')
                                            ->relationship('lab', 'name')
                                            ->required(),

                                        Select::make('sample_type_id')
                                            ->label('Τύπος δείγματος')
                                            ->relationship('sampleType', 'name')
                                            ->required(),
                                    ])
                                    ->columns(2),

                                Grid::make(1)
                                    ->schema([
                                        Toggle::make('is_counted_in_lab')
                                            ->label('Μετρά στα στατιστικά του εργαστηρίου')
                                            ->default(true),

                                        Toggle::make('is_counted_in_contract')
                                            ->label('Μετρά στα στατιστικά της σύμβασης')
                                            ->default(true),

                                        Toggle::make('is_virtual')
                                            ->label('Εικονική κατηγορία (χωρίς μέτρηση)')
                                            ->default(false),
                                    ]),
                            ]),
                    ])
                    ->compact()
                    ->columnSpanFull()
                    ->columns(2),

                Section::make('Οικονομικά & Πρότυπα')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('price')
                                    ->label('Τιμή (€)')
                                    ->numeric()
                                    ->step(0.01)
                                    ->prefix('€')
                                    ->default(0.00)
                                    ->required(),

                                TextInput::make('method_ref')
                                    ->label('Μέθοδος')
                                    ->maxLength(255),
                            ]),

                        Grid::make(1)
                            ->schema([
                                TextInput::make('standard_ref')
                                    ->label('Πρότυπο / Νομοθεσία')
                                    ->columnSpanFull()
                                    ->maxLength(255),
                            ])
                    ])
                    ->columnSpanFull()
                    ->columns(2),

                Section::make('Κατάσταση & σειρά εμφάνισης')
                    ->schema([
                        Select::make('status')
                            ->label('Κατάσταση')
                            ->options(collect(RecordStatusEnum::cases())->mapWithKeys(fn($case) => [
                                $case->value => $case->getLabel(),
                            ]))
                            ->default(RecordStatusEnum::Active->value)
                            ->required(),

                        TextInput::make('sort_order')
                            ->label('Σειρά εμφάνισης')
                            ->numeric()
                            ->default(0)
                            ->hint('Μικρότερος αριθμός → ψηλότερη θέση'),
                    ])
                    ->columnSpanFull()
                    ->columns(2),
            ]);
    }
}
