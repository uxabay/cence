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
                // Main Grid Container: 2:1 column layout
                Grid::make(3)
                    ->columnSpanFull()
                    ->schema([

                        // === LEFT COLUMN (2/3 width) - Identity, Content, and Attachments ===
                        Grid::make(1) // Single column container for sections
                            ->columnSpan(2)
                            ->schema([

                                // 1. 📄 Βασικά Στοιχεία
                                Section::make('Βασικά Στοιχεία Σύμβασης')
                                    ->description('Αριθμός, τίτλος, και ο πελάτης στον οποίο αντιστοιχεί η σύμβαση.')
                                    ->icon('heroicon-o-document-text')
                                    ->compact()
                                    ->schema([
                                        Grid::make(3)
                                            ->schema([
                                                TextInput::make('contract_number')
                                                    ->label('Αριθμός Σύμβασης')
                                                    ->required()
                                                    ->placeholder('π.χ. ΚΠ 6358/2025'),

                                                TextInput::make('title')
                                                    ->label('Τίτλος')
                                                    ->required()
                                                    ->placeholder('π.χ. Προγραμματική Σύμβαση ΕΟΔΥ - Πανεπιστημίου Θεσσαλίας')
                                                    ->columnSpan(2),
                                            ]),

                                        // Customer Select remains full width for prominence
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
                                            ->columnSpanFull(),
                                    ]),

                                // 2. 📝 Περιγραφή & Παρατηρήσεις
                                Section::make('Περιγραφή & Παρατηρήσεις')
                                    ->description('Πλήρης περιγραφή των όρων και ειδικές παρατηρήσεις.')
                                    ->icon('heroicon-o-clipboard-document-list')
                                    ->compact()
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
                                    ]),

                                // 3. 📎 Συνημμένο Έγγραφο (ΜΕΤΑΚΙΝΗΘΗΚΕ ΕΔΩ)
                                Section::make('Συνημμένο Έγγραφο')
                                    ->description('Αρχείο PDF της υπογεγραμμένης σύμβασης. Προσθέστε εδώ όλα τα σχετικά έγγραφα.')
                                    ->icon('heroicon-o-paper-clip')
                                    ->compact()
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
                                    ])
                                    ->columns(1),
                            ]),


                        // === RIGHT COLUMN (1/3 width) - Duration and Status ===
                        Grid::make(1)
                            ->columnSpan(1)
                            ->schema([

                                // 4. 📅 Χρονική Διάρκεια (ΠΡΩΤΟ)
                                Section::make('Χρονική Διάρκεια')
                                    ->description('Ημερομηνίες έναρξης και λήξης ισχύος.')
                                    ->icon('heroicon-o-calendar-days')
                                    ->compact()
                                    ->schema([
                                        DatePicker::make('date_start')
                                            ->label('Ημερομηνία Έναρξης')
                                            ->native(false)
                                            ->required()
                                            ->closeOnDateSelection(),

                                        DatePicker::make('date_end')
                                            ->label('Ημερομηνία Λήξης')
                                            ->native(false)
                                            ->closeOnDateSelection()
                                            ->afterOrEqual('date_start')
                                            ->hint('Αν κενό, θεωρείται ενεργή χωρίς λήξη.'),
                                    ])
                                    ->columns(1), // Stacking date pickers in the sidebar

                                // 5. ⚙️ Διαχείριση & Κατάσταση (ΔΕΥΤΕΡΟ)
                                Section::make('Διαχείριση & Κατάσταση')
                                    ->description('Κατάσταση σύμβασης.')
                                    ->icon('heroicon-o-briefcase')
                                    ->compact()
                                    ->schema([
                                        Select::make('status')
                                            ->label('Κατάσταση')
                                            ->options(RecordStatusEnum::class)
                                            ->default(RecordStatusEnum::Active)
                                            ->selectablePlaceholder(false),
                                    ])
                                    ->columns(1), // Force 1 column within the sidebar for stacking

                            ]),
                    ]),
            ]);
    }
}
