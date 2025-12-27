<?php

namespace App\Filament\Resources\ContractSampleCategories\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Support\Enums\FontWeight;

class ContractSampleCategoryInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // 1. Βασικές Πληροφορίες - Ενοποίηση
                Section::make('Βασικές Πληροφορίες Κατηγορίας')
                    ->description('Κωδικός, όνομα, κατάσταση και περιγραφή.')
                    ->icon('heroicon-o-folder-open')
                    ->compact()
                    ->schema([
                        // Row 1: Code, Name, Status (3 columns)
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('code')
                                    ->label('Κωδικός')
                                    ->icon('heroicon-o-hashtag')
                                    ->weight(FontWeight::SemiBold)
                                    ->color('primary'),

                                TextEntry::make('name')
                                    ->label('Όνομα Κατηγορίας')
                                    ->icon('heroicon-o-rectangle-stack')
                                    ->weight(FontWeight::Medium),

                                TextEntry::make('status')
                                    ->label('Κατάσταση')
                                    ->badge()
                                    ->color(fn ($state) => $state?->getColor())
                                    ->icon(fn ($state) => $state?->getIcon()),
                            ]),

                        // Row 2: Description (Full width)
                        TextEntry::make('description')
                            ->label('Περιγραφή')
                            ->placeholder('Δεν έχει καταχωρηθεί περιγραφή.')
                            ->columnSpanFull()
                            ->markdown()
                            ->prose(),
                    ])
                    ->columnSpanFull(),

                // 2. Ιστορικό Καταγραφών (Audit) - Ξεχωριστή ενότητα
                Section::make('Ιστορικό Καταγραφών')
                    ->description('Χρονοσφραγίδες δημιουργίας και ενημέρωσης.')
                    ->icon('heroicon-o-clock')
                    ->compact()
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('Δημιουργήθηκε')
                            ->dateTime('d/m/Y H:i')
                            ->icon('heroicon-o-calendar-days')
                            ->placeholder('-'),

                        TextEntry::make('updated_at')
                            ->label('Τελευταία ενημέρωση')
                            ->dateTime('d/m/Y H:i')
                            ->icon('heroicon-o-arrow-path')
                            ->placeholder('-'),
                    ])
                    ->columns(2),
            ]);
    }
}
