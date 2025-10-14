<?php

namespace App\Filament\Resources\LabCustomers\Schemas;

use App\Models\LabCustomer;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Fieldset;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\IconEntry;

class LabCustomerInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                // 🟩 1. Γενικά στοιχεία
                Section::make('Γενικά στοιχεία')
                    ->icon('heroicon-o-user-group')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('name')
                                    ->label('Ονομασία')
                                    ->weight('medium')
                                    ->columnSpan(1),

                                TextEntry::make('category.name')
                                    ->label('Κατηγορία')
                                    ->badge()
                                    ->placeholder('-'),

                                TextEntry::make('status')
                                    ->label('Κατάσταση')
                                    ->badge(),
                            ]),
                    ])
                    ->columnSpanFull(),

                // 🟦 2. Στοιχεία επικοινωνίας
                Section::make('Στοιχεία επικοινωνίας')
                    ->icon('heroicon-o-phone')
                    ->collapsible()
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('contact_person')
                                    ->label('Υπεύθυνος')
                                    ->placeholder('-')
                                    ->icon('heroicon-o-user'),

                                TextEntry::make('phone')
                                    ->label('Τηλέφωνο')
                                    ->icon('heroicon-o-device-phone-mobile')
                                    ->placeholder('-'),

                                TextEntry::make('encryption_key')
                                    ->label('Κλειδί κρυπτογράφησης')
                                    ->placeholder('-'),
                            ])
                            ->columnSpanFull(),

                        Fieldset::make('Διεύθυνση')
                            ->schema([
                                Grid::make(3)
                                    ->schema([
                                        TextEntry::make('address')
                                            ->label('Διεύθυνση')
                                            ->placeholder('-'),

                                        TextEntry::make('postal_code')
                                            ->label('Τ.Κ.')
                                            ->placeholder('-'),

                                        TextEntry::make('city')
                                            ->label('Πόλη')
                                            ->placeholder('-'),
                                    ]),
                            ]),
                    ])
                    ->columnSpanFull(),

                // 🟨 3. Emails επικοινωνίας
                Section::make('Emails επικοινωνίας')
                    ->icon('heroicon-o-envelope')
                    ->collapsible()
                    ->schema([
                        RepeatableEntry::make('emails')
                            ->label('')
                            ->schema([
                                Grid::make(3)
                                    ->schema([
                                        TextEntry::make('email')
                                            ->label('Email')
                                            ->icon('heroicon-o-envelope')
                                            ->copyable()
                                            ->copyMessage('Αντιγράφηκε')
                                            ->copyMessageDuration(1500),

                                        IconEntry::make('is_primary')
                                            ->label('Κύριο')
                                            ->boolean()
                                            ->trueIcon('heroicon-s-check-circle')
                                            ->falseIcon('heroicon-s-minus-circle')
                                            ->trueColor('success')
                                            ->falseColor('gray'),

                                        TextEntry::make('notes')
                                            ->label('Σημειώσεις')
                                            ->placeholder('-'),
                                    ]),
                            ]),
                    ])
                    ->columnSpanFull(),

                // 🟪 4. Πρόσθετα στοιχεία
                Section::make('Πρόσθετα στοιχεία')
                    ->icon('heroicon-o-clock')
                    ->collapsible()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('last_update_at')
                                    ->label('Τελευταία ενημέρωση')
                                    ->date('d/m/Y')
                                    ->icon('heroicon-o-calendar')
                                    ->placeholder('-'),

                                TextEntry::make('notes')
                                    ->label('Σημειώσεις')
                                    ->placeholder('-')
                                    ->columnSpanFull()
                                    ->markdown(),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
