<?php

namespace App\Filament\Resources\LabAnalysisPackages\Schemas;

use App\Models\LabAnalysisPackage;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section; 

class LabAnalysisPackageInfolist
    {public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Section::make('Στοιχεία Πακέτου Αναλύσεων')
                    ->icon('heroicon-o-rectangle-stack')
                    ->schema([
                        TextEntry::make('labSampleCategory.name')
                            ->label('Κατηγορία Δείγματος')
                            ->placeholder('-')
                            ->weight('medium')
                            ->color('primary'),

                        TextEntry::make('name')
                            ->label('Όνομα Πακέτου')
                            ->weight('medium'),

                        TextEntry::make('description')
                            ->label('Περιγραφή')
                            ->placeholder('-')
                            ->markdown()
                            ->prose()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),


                Section::make('Κατάσταση')
                    ->icon('heroicon-o-check-circle')
                    ->schema([
                        TextEntry::make('status')
                            ->label('Κατάσταση')
                            ->badge()
                            ->color(fn ($state) => $state?->getColor())
                            ->icon(fn ($state) => $state?->getIcon()),
                    ])
                    ->columns(1),


                Section::make('Στοιχεία Καταγραφής')
                    ->icon('heroicon-o-clock')
                    ->schema([
                        TextEntry::make('created_by')
                            ->label('Καταχωρήθηκε από')
                            ->placeholder('-'),

                        TextEntry::make('updated_by')
                            ->label('Ενημερώθηκε από')
                            ->placeholder('-'),

                        TextEntry::make('created_at')
                            ->label('Ημ/νία Καταχώρησης')
                            ->dateTime()
                            ->placeholder('-'),

                        TextEntry::make('updated_at')
                            ->label('Ημ/νία Ενημέρωσης')
                            ->dateTime()
                            ->placeholder('-'),

                        TextEntry::make('deleted_at')
                            ->label('Διαγράφηκε')
                            ->dateTime()
                            ->visible(fn (LabAnalysisPackage $record) => $record->trashed()),
                    ])
                    ->columns(2)
                    ->collapsed(),
            ]);
    }

}
