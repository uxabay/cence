<?php

namespace App\Filament\Resources\LabCustomers\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\IconEntry;

class LabCustomerInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                /*
                |--------------------------------------------------------------------------
                | 🟩 1. Βασικές Πληροφορίες
                |--------------------------------------------------------------------------
                */
                Section::make('Βασικές Πληροφορίες')
                    ->icon('heroicon-o-user-group')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('name')
                                    ->label('Επωνυμία')
                                    ->icon('heroicon-o-building-office-2')
                                    ->weight('medium')
                                    ->color('primary')
                                    ->placeholder('-'),

                                TextEntry::make('category.name')
                                    ->label('Κατηγορία')
                                    ->badge()
                                    ->icon('heroicon-o-rectangle-stack')
                                    ->placeholder('-'),

                                TextEntry::make('status')
                                    ->label('Κατάσταση')
                                    ->badge()
                                    ->color(fn($state) => $state?->getColor())
                                    ->icon(fn($state) => $state?->getIcon())
                                    ->placeholder('-'),
                            ])
                            ->columnSpanFull(),
                    ])
                    ->columns(3)
                    ->columnSpanFull(),

                /*
                |--------------------------------------------------------------------------
                | 🟦 2. Επικοινωνία
                |--------------------------------------------------------------------------
                */
                Section::make('Επικοινωνία')
                    ->icon('heroicon-o-phone')
                    ->collapsible()
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('contact_person')
                                    ->label('Υπεύθυνος επικοινωνίας')
                                    ->icon('heroicon-o-user')
                                    ->placeholder('-'),

                                TextEntry::make('phone')
                                    ->label('Τηλέφωνο')
                                    ->icon('heroicon-o-device-phone-mobile')
                                    ->copyable()
                                    ->copyMessage('Αντιγράφηκε')
                                    ->copyMessageDuration(1500)
                                    ->placeholder('-'),

                                TextEntry::make('email_primary')
                                    ->label('Κύριο Email')
                                    ->icon('heroicon-o-envelope')
                                    ->copyable()
                                    ->copyMessage('Αντιγράφηκε')
                                    ->copyMessageDuration(1500)
                                    ->placeholder('-'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('encryption_key')
                                    ->label('Κλειδί κρυπτογράφησης')
                                    ->placeholder('-')
                                    ->icon('heroicon-o-key'),
                            ]),
                    ])
                    ->columnSpanFull(),

                /*
                |--------------------------------------------------------------------------
                | 🏠 3. Διεύθυνση
                |--------------------------------------------------------------------------
                */
                Section::make('Διεύθυνση')
                    ->icon('heroicon-o-map-pin')
                    ->collapsible()
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
                    ])
                    ->columnSpanFull(),

                /*
                |--------------------------------------------------------------------------
                | 💼 4. Οικονομικά & Σύστημα
                |--------------------------------------------------------------------------
                */
                Section::make('Οικονομικά & Σύστημα')
                    ->icon('heroicon-o-briefcase')
                    ->collapsible()
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('tax_id')
                                    ->label('Α.Φ.Μ.')
                                    ->placeholder('-')
                                    ->icon('heroicon-o-identification'),

                                TextEntry::make('organization_code')
                                    ->label('Κωδικός Οργάνωσης')
                                    ->placeholder('-')
                                    ->icon('heroicon-o-tag'),

                                TextEntry::make('createdBy.name')
                                    ->label('Δημιουργήθηκε από')
                                    ->placeholder('-')
                                    ->icon('heroicon-o-user-circle'),
                            ]),
                    ])
                    ->columnSpanFull(),

                /*
                |--------------------------------------------------------------------------
                | 🕓 5. Λοιπά Στοιχεία
                |--------------------------------------------------------------------------
                */
                Section::make('Λοιπά στοιχεία')
                    ->icon('heroicon-o-clock')
                    ->collapsible()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('last_update_at')
                                    ->label('Τελευταία ενημέρωση')
                                    ->date('d/m/Y H:i')
                                    ->icon('heroicon-o-calendar')
                                    ->placeholder('-'),

                                TextEntry::make('notes')
                                    ->label('Σημειώσεις')
                                    ->markdown()
                                    ->placeholder('-')
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
