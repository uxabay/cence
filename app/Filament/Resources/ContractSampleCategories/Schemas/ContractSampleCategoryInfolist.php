<?php

namespace App\Filament\Resources\ContractSampleCategories\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;

class ContractSampleCategoryInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Βασικές Πληροφορίες')
                    ->schema([
                        TextEntry::make('code')
                            ->label('Κωδικός')
                            ->icon('heroicon-o-hashtag')
                            ->weight('medium')
                            ->color('primary'),

                        TextEntry::make('name')
                            ->label('Όνομα Κατηγορίας')
                            ->icon('heroicon-o-rectangle-stack')
                            ->weight('medium'),

                        TextEntry::make('description')
                            ->label('Περιγραφή')
                            ->placeholder('-')
                            ->columnSpanFull()
                            ->markdown()
                            ->prose(),
                    ])
                    ->columnSpanFull()
                    ->columns(2),

                Section::make('Κατάσταση & Πληροφορίες')
                    ->schema([
                        TextEntry::make('status')
                            ->label('Κατάσταση')
                            ->badge()
                            ->color(fn ($state) => $state?->getColor())
                            ->icon(fn ($state) => $state?->getIcon()),

                        TextEntry::make('updated_at')
                            ->label('Τελευταία ενημέρωση')
                            ->dateTime('d/m/Y H:i')
                            ->placeholder('-'),
                    ])
                    ->columns(2),
            ]);
    }
}
