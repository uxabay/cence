<?php

namespace App\Filament\Resources\Contracts\Schemas;

use App\Enums\RecordStatusEnum;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;

class ContractForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // 📄 Βασικά Στοιχεία
                Section::make('Βασικά Στοιχεία Σύμβασης')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        TextInput::make('contract_number')
                            ->label('Αριθμός Σύμβασης')
                            ->required()
                            ->placeholder('π.χ. ΚΠ 6358/2025')
                            ->columnSpan(1),

                        TextInput::make('title')
                            ->label('Τίτλος')
                            ->required()
                            ->placeholder('π.χ. Προγραμματική Σύμβαση ΕΟΔΥ - Πανεπιστημίου Θεσσαλίας')
                            ->columnSpan(1),

                        Select::make('lab_customer_id')
                            ->label('Πελάτης')
                            ->relationship(
                                name: 'customer',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn ($query) => $query->where('status', 'active')
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->placeholder('Επιλέξτε πελάτη')
                            ->columnSpan(1),

                        Select::make('status')
                            ->label('Κατάσταση')
                            ->options(RecordStatusEnum::class)
                            ->default(RecordStatusEnum::Active)
                            ->selectablePlaceholder(false)
                            ->columnSpan(1),
                    ])
                    ->columnSpanFull()
                    ->columns(2),

                Grid::make(2)
                    ->schema([
                        // 📅 Χρονική Διάρκεια
                        Section::make('Χρονική Διάρκεια')
                            ->icon('heroicon-o-calendar-days')
                            ->schema([
                                DatePicker::make('date_start')
                                    ->label('Ημερομηνία Έναρξης')
                                    ->native(false)
                                    ->required()
                                    ->closeOnDateSelection()
                                    ->columnSpan(1),

                                DatePicker::make('date_end')
                                    ->label('Ημερομηνία Λήξης')
                                    ->native(false)
                                    ->closeOnDateSelection()
                                    ->afterOrEqual('date_start')
                                    ->helperText('Αν παραμείνει κενό, η σύμβαση θεωρείται ενεργή χωρίς λήξη.')
                                    ->columnSpan(1),
                            ])
                            ->columns(2),

                            // 📎 Συνημμένο Έγγραφο
                            Section::make('Συνημμένο Έγγραφο')
                                ->icon('heroicon-o-paper-clip')
                                ->schema([
                                    FileUpload::make('file_attachment_id')
                                        ->label('Αρχείο Σύμβασης')
                                        ->directory('contracts')
                                        ->preserveFilenames()
                                        ->downloadable()
                                        ->openable()
                                        ->acceptedFileTypes(['application/pdf'])
                                        ->hint('Επιτρεπόμενος τύπος αρχείου: PDF')
                                        ->columnSpanFull(),
                                ]),
                    ])
                    ->columnSpanFull(),

                // 📝 Περιγραφή & Παρατηρήσεις
                Section::make('Περιγραφή & Παρατηρήσεις')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->schema([
                        RichEditor::make('description')
                            ->label('Περιγραφή')
                            ->toolbarButtons(['bold', 'italic', 'bulletList', 'orderedList'])
                            ->placeholder('Προσθέστε συνοπτική περιγραφή της σύμβασης...')
                            ->columnSpanFull(),

                        Textarea::make('remarks')
                            ->label('Παρατηρήσεις')
                            ->placeholder('Πρόσθετες σημειώσεις ή ειδικοί όροι...')
                            ->rows(2)
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull()
                    ->columns(1),
            ]);
    }
}
