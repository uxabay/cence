<?php

namespace App\Filament\Resources\LabCustomers\Schemas;

use App\Enums\CustomerStatusEnum;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\IconEntry;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\FontWeight;

class LabCustomerInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Main Grid Container: Creates a 2:1 column layout (2/3 width for main, 1/3 for sidebar)
                Grid::make(3)
                    ->columnSpanFull()
                    ->schema([

                        // === LEFT COLUMN (2/3 width) - Primary, Contact, Address, and Notes Data ===
                        Grid::make(1) // Single column container for main sections
                            ->columnSpan(2) // Span 2 out of 3 columns
                            ->schema([

                                // 1. Βασικές Πληροφορίες & Διεύθυνση - Ενοποίηση των δύο ενοτήτων
                                Section::make('Βασικές Πληροφορίες & Διεύθυνση')
                                    ->description('Επωνυμία, κατηγορία, και πλήρης διεύθυνση πελάτη.')
                                    ->icon('heroicon-o-user-group')
                                    ->compact()
                                    ->schema([
                                        // Row 1: Name and Category (2 columns)
                                        Grid::make(2)
                                            ->schema([
                                                TextEntry::make('name')
                                                    ->label('Επωνυμία')
                                                    ->icon('heroicon-o-building-office-2')
                                                    ->weight(FontWeight::SemiBold)
                                                    ->color('primary'), // ΔΙΟΡΘΩΘΗΚΕ: Χρήση του string 'primary'

                                                TextEntry::make('category.name')
                                                    ->label('Κατηγορία')
                                                    ->badge()
                                                    ->icon('heroicon-o-rectangle-stack'),
                                            ]),

                                        // Row 2: Address fields (3 columns) - Μετακινήθηκαν εδώ
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

                                // 2. Επικοινωνία
                                Section::make('Επικοινωνία')
                                    ->description('Στοιχεία επικοινωνίας και υπεύθυνος.')
                                    ->icon('heroicon-o-phone')
                                    ->compact()
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
                                                    ->placeholder('-'),

                                                TextEntry::make('email_primary')
                                                    ->label('Κύριο Email')
                                                    ->icon('heroicon-o-envelope')
                                                    ->copyable()
                                                    ->placeholder('-'),
                                            ]),
                                        // Encryption key remains full-width
                                        TextEntry::make('encryption_key')
                                            ->label('Κλειδί κρυπτογράφησης')
                                            ->placeholder('-')
                                            ->icon('heroicon-o-key')
                                            ->columnSpanFull(),
                                    ]),

                                // 3. Σημειώσεις (Moved from right column)
                                Section::make('Σημειώσεις')
                                    ->description('Προαιρετικές πληροφορίες για τον πελάτη.')
                                    ->icon('heroicon-o-document-text')
                                    ->compact()
                                    ->schema([
                                        TextEntry::make('notes')
                                            ->label('Σημειώσεις')
                                            ->placeholder('Δεν υπάρχουν σημειώσεις.')
                                            ->markdown()
                                            ->prose()
                                            ->columnSpanFull(),
                                    ]),
                            ]),


                        // === RIGHT COLUMN (1/3 width) - Financial, Status, and Audit Data ===
                        Grid::make(1)
                            ->columnSpan(1)
                            ->schema([

                                // 4. Οικονομικά, Σύστημα & Κατάσταση
                                Section::make('Οικονομικά, Σύστημα & Κατάσταση')
                                    ->description('Φορολογικά, οργανωτικά στοιχεία και κατάσταση.')
                                    ->icon('heroicon-o-briefcase')
                                    ->compact()
                                    ->schema([
                                        // Status moved here
                                        TextEntry::make('status')
                                            ->label('Κατάσταση')
                                            ->badge()
                                            ->color(fn($state) => $state?->getColor())
                                            ->icon(fn($state) => $state?->getIcon())
                                            ->placeholder('-'),

                                        TextEntry::make('tax_id')
                                            ->label('Α.Φ.Μ.')
                                            ->placeholder('-')
                                            ->icon('heroicon-o-identification'),

                                        TextEntry::make('organization_code')
                                            ->label('Κωδικός Οργάνωσης')
                                            ->placeholder('-')
                                            ->icon('heroicon-o-tag'),

                                    ])
                                    ->columns(1), // Stacking fields vertically in the sidebar

                                // 5. Ιστορικό Καταγραφών (Audit)
                                Section::make('Ιστορικό Καταγραφών')
                                    ->description('Πληροφορίες δημιουργίας και ενημέρωσης.')
                                    ->icon('heroicon-o-clock')
                                    ->compact()
                                    ->schema([
                                        TextEntry::make('createdBy.name')
                                            ->label('Δημιουργήθηκε από')
                                            ->placeholder('-')
                                            ->icon('heroicon-o-user-circle'),

                                        TextEntry::make('updatedBy.name')
                                            ->label('Τελευταία ενημέρωση από')
                                            ->placeholder('-')
                                            ->icon('heroicon-o-arrow-path'),

                                    ])
                                    ->columns(1),
                            ]),
                    ]),
            ]);
    }
}
