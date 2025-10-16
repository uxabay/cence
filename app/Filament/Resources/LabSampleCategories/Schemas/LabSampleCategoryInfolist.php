<?php

namespace App\Filament\Resources\LabSampleCategories\Schemas;

use App\Enums\RecordStatusEnum;
use App\Models\LabSampleCategory;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\IconEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Schema;

class LabSampleCategoryInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        Fieldset::make('Βασικά στοιχεία')
                            ->schema([
                                TextEntry::make('name')
                                    ->label('Ονομασία')
                                    ->weight('bold'),

                                TextEntry::make('short_name')
                                    ->label('Σύντομη ονομασία')
                                    ->placeholder('-'),

                                TextEntry::make('code')
                                    ->label('Κωδικός')
                                    ->placeholder('-'),

                                TextEntry::make('description')
                                    ->label('Περιγραφή')
                                    ->placeholder('-')
                                    ->columnSpanFull(),
                            ]),

                        Fieldset::make('Αντιστοιχίσεις')
                            ->schema([
                                TextEntry::make('lab.name')
                                    ->label('Εργαστήριο'),

                                TextEntry::make('sampleType.name')
                                    ->label('Τύπος δείγματος'),
                            ])
                            ->columns(2),

                        Fieldset::make('Συμμετοχή στα στατιστικά')
                            ->schema([
                                IconEntry::make('is_counted_in_lab')
                                    ->label('Μετρά στο εργαστήριο')
                                    ->boolean(),

                                IconEntry::make('is_counted_in_contract')
                                    ->label('Μετρά στη σύμβαση')
                                    ->boolean(),

                                IconEntry::make('is_virtual')
                                    ->label('Εικονική κατηγορία')
                                    ->boolean(),
                            ])
                            ->columns(3),
                    ])
                    ->compact()
                    ->columns(2)
                    ->columnSpanFull(),

                Section::make('Οικονομικά & Πρότυπα')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('price')
                                    ->label('Τιμή (€)')
                                    ->money('EUR', true)
                                    ->alignRight(),

                                TextEntry::make('method_ref')
                                    ->label('Μέθοδος')
                                    ->placeholder('-'),
                            ]),
                        TextEntry::make('standard_ref')
                            ->label('Πρότυπο / Νομοθεσία')
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),

                Section::make('Κατάσταση & Σειρά')
                    ->schema([
                        TextEntry::make('status')
                            ->label('Κατάσταση')
                            ->badge()
                            ->color(fn (RecordStatusEnum $state) => match ($state) {
                                RecordStatusEnum::Active => 'success',
                                RecordStatusEnum::Inactive => 'gray',
                            })
                            ->icon(fn (RecordStatusEnum $state) => match ($state) {
                                RecordStatusEnum::Active => 'heroicon-o-check-circle',
                                RecordStatusEnum::Inactive => 'heroicon-o-x-circle',
                            }),

                        TextEntry::make('sort_order')
                            ->label('Σειρά εμφάνισης')
                            ->numeric()
                            ->placeholder('-'),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                Section::make('Μεταδεδομένα')
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('Δημιουργήθηκε')
                            ->dateTime('d/m/Y H:i')
                            ->placeholder('-'),

                        TextEntry::make('updated_at')
                            ->label('Τελευταία ενημέρωση')
                            ->dateTime('d/m/Y H:i')
                            ->placeholder('-'),

                        TextEntry::make('deleted_at')
                            ->label('Ημ/νία διαγραφής')
                            ->dateTime('d/m/Y H:i')
                            ->visible(fn (LabSampleCategory $record): bool => $record->trashed()),
                    ])
                    ->columns(3)
                    ->columnSpanFull(),
            ]);
    }
}
