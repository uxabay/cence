<?php

namespace App\Filament\Resources\LabAnalysisPackages\Schemas;

use App\Models\LabAnalysisPackage;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Infolists\Components\Component; // Χρησιμοποιούμε Component για να επιστρέψουμε Section
use Filament\Support\Enums\FontWeight;
use Filament\Support\Colors\Color;
use Filament\Infolists\Components\Actions\EditAction; // Import για το EditAction

class LabAnalysisPackageInfolist
{
    /**
     * Ορίζει το σχήμα Infolist για το Πακέτο Αναλύσεων.
     * Χρησιμοποιεί το μοτίβο configure για να επιστρέψει μια συλλογή Components.
     */
    public static function configure(Schema $schema): Schema
    {

        return $schema
            ->components([
                Section::make('Στοιχεία Πακέτου Αναλύσεων')
                    ->description('Βασικά στοιχεία και κατηγοριοποίηση του πακέτου.')
                    ->icon('heroicon-o-rectangle-stack')
                    ->compact()
                    ->columns(2)
                    ->schema([
                        TextEntry::make('labSampleCategory.name')
                            ->label('Κατηγορία Δείγματος')
                            ->placeholder('-')
                            ->weight(FontWeight::Medium)
                            ->color('primary')
                            ->badge()
                            ->columnSpan(1),

                        TextEntry::make('name')
                            ->label('Όνομα Πακέτου')
                            ->weight(FontWeight::SemiBold)
                            ->columnSpan(1),

                        TextEntry::make('status')
                            ->label('Κατάσταση')
                            ->badge()
                            ->color(fn ($state) => $state?->getColor())
                            ->icon(fn ($state) => $state?->getIcon())
                            ->columnSpan(1),

                        TextEntry::make('description')
                            ->label('Περιγραφή')
                            ->placeholder('-')
                            ->markdown()
                            ->prose()
                            ->columnSpanFull(),
                    ]),


                // Στοιχεία Καταγραφής (Audit) σε ξεχωριστή ενότητα
                Section::make('Στοιχεία Καταγραφής')
                    ->description('Χρονοσφραγίδες και χρήστες που διαχειρίστηκαν την καταχώρηση.')
                    ->icon('heroicon-o-clock')
                    ->compact()
                    ->schema([
                        TextEntry::make('created_by')
                            ->label('Καταχωρήθηκε από')
                            ->placeholder('-'),

                        TextEntry::make('updated_by')
                            ->label('Ενημερώθηκε από')
                            ->placeholder('-'),

                        TextEntry::make('created_at')
                            ->label('Ημ/νία Καταχώρησης')
                            ->dateTime('d/m/Y H:i')
                            ->placeholder('-'),

                        TextEntry::make('updated_at')
                            ->label('Ημ/νία Ενημέρωσης')
                            ->dateTime('d/m/Y H:i')
                            ->placeholder('-'),

                        // Εμφάνιση deleted_at μόνο αν έχει διαγραφεί
                        TextEntry::make('deleted_at')
                            ->label('Ημ/νία Διαγραφής')
                            ->dateTime('d/m/Y H:i')
                            ->badge()
                            ->color(Color::Red)
                            ->visible(fn (LabAnalysisPackage $record) => $record->trashed()),
                    ])
                    ->columns(2)
                    ->collapsed(),
            ]);
    }

}
