<?php

namespace App\Filament\Resources\LabAnalysisPackages\RelationManagers;

use App\Models\LabAnalysis;
use Filament\Actions\ActionGroup;
use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AnalysesRelationManager extends RelationManager
{
    protected static string $relationship = 'analyses';
    protected static ?string $recordTitleAttribute = 'name';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Αναλύσεις Πακέτου')
            ->columns([
                // Snapshot name (pivot)
                TextColumn::make('pivot.analysis_name')
                    ->label('Όνομα Snapshot')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->icon('heroicon-o-document-text'),

                // Current analysis name from master table
                TextColumn::make('name')
                    ->label('Τρέχουσα Ονομασία Ανάλυσης')
                    ->color('gray')
                    ->icon('heroicon-o-beaker')
                    ->wrap(),

                // Snapshot price (pivot)
                TextColumn::make('pivot.analysis_price')
                    ->label('Τιμή Snapshot (€)')
                    ->money('EUR')
                    ->weight('medium'),

                // Current analysis price
                TextColumn::make('unit_price')
                    ->label('Τρέχουσα Τιμή (€)')
                    ->money('EUR')
                    ->color('gray'),

                // Current analysis status
                TextColumn::make('status')
                    ->label('Κατάσταση')
                    ->badge()
                    ->sortable(),
            ])
            ->contentGrid([
                'md' => 2, // two-column view
                'xl' => 2,
            ])
            ->headerActions([
                AttachAction::make()
                    ->label('Προσθήκη Ανάλυσης')
                    ->modalHeading('Προσθήκη Ανάλυσης στο Πακέτο')
                    ->preloadRecordSelect()
                    ->schema(function (AttachAction $action) {

                        return [

                            // Select analysis
                            $action->getRecordSelect()
                                ->label('Ανάλυση')
                                ->required()
                                ->live()   // IMPORTANT
                                ->afterStateUpdated(function ($state, callable $set) {
                                    $analysis = \App\Models\LabAnalysis::find($state);

                                    $set('analysis_name', $analysis?->name);
                                    $set('analysis_price', $analysis?->unit_price);
                                }),

                            // Pivot snapshot fields
                            TextInput::make('analysis_name')
                                ->label('Όνομα Snapshot')
                                ->required(),

                            TextInput::make('analysis_price')
                                ->label('Τιμή Snapshot (€)')
                                ->numeric()
                                ->required(),
                        ];
                    }),

            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make()
                        ->label('Επεξεργασία Snapshot')
                        ->modalHeading('Επεξεργασία Snapshot Ανάλυσης')
                        ->schema([
                            TextInput::make('analysis_name')
                                ->label('Όνομα Snapshot')
                                ->required(),

                            TextInput::make('analysis_price')
                                ->label('Τιμή Snapshot (€)')
                                ->numeric()
                                ->required(),
                        ]),

                    DetachAction::make()
                        ->label('Αφαίρεση')
                        ->icon('heroicon-o-x-mark'),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DetachBulkAction::make()
                        ->label('Αφαίρεση Επιλεγμένων'),
                ]),
            ]);
    }

}
