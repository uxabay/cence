<?php

namespace App\Filament\Resources\Contracts\RelationManagers;

use App\Enums\RecordStatusEnum;
use App\Models\ContractSample;
use App\Models\ContractSampleCategory;
use App\Models\LabSampleCategory;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Schemas\Infolist;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Tables\Grouping\Group;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\ViewAction;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Support\Number;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\FontWeight;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Model;

class SamplesRelationManager extends RelationManager
{
    protected static string $relationship = 'samples';
    protected static ?string $recordTitleAttribute = 'display_label';


    /**
     * Defines the contract sample form schema.
     * Changed from static configure to instance method form() as requested.
     */
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Στοιχεία Γραμμής Σύμβασης')
                    ->description('Ορισμός του δείγματος, της τιμολόγησης και των ορίων εκτέλεσης.')
                    ->icon('heroicon-o-beaker')
                    ->compact() // Κάνει την ενότητα πιο compact, ιδανικό για modal
                    ->schema([

                        // 1. Ταυτότητα Γραμμής (3 στήλες)
                        Fieldset::make('Ταυτότητα Γραμμής')
                            ->schema([

                                Select::make('contract_sample_category_id')
                                    ->label('Κατηγορία Δείγματος')
                                    ->options(fn () => ContractSampleCategory::query()
                                        ->active()
                                        ->orderBy('name')
                                        ->pluck('name', 'id'))
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->columnSpan(2), // Δίνει περισσότερο πλάτος για την κατηγορία

                                TextInput::make('year')
                                    ->label('Έτος')
                                    ->numeric()
                                    ->default(now()->year)
                                    ->columnSpan(1),

                                TextInput::make('name')
                                    ->label('Όνομα Γραμμής Σύμβασης')
                                    ->placeholder('π.χ. Αρχική Σύμβαση, Τροποποιητική 1')
                                    ->helperText('Ο τίτλος της γραμμής σύμβασης.')
                                    ->required()
                                    ->columnSpan(2),

                                Toggle::make('is_master')
                                    ->label('Κύρια Γραμμή (Master)')
                                    ->inline(false)
                                    ->helperText('Η κύρια γραμμή χρησιμοποιείται ως σημείο αναφοράς σε ορισμένες συμβάσεις.')
                                    ->columnSpan(1),

                            ])
                            ->columns(3)
                            ->columnSpanFull(),


                        // 2. Τιμολόγηση & Υπολογισμός (3 στήλες)
                        Fieldset::make('Τιμολόγηση & Όρια')
                            ->schema([

                                Select::make('cost_calculation_type')
                                    ->label('Τύπος Κόστους')
                                    ->options([
                                        'fix'            => 'Σταθερή Τιμή',
                                        'variable'       => 'Με βάση Αναλύσεις',
                                        'variable_count' => 'Με βάση Αριθμό Αναλύσεων',
                                    ])
                                    ->required()
                                    ->default('fix')
                                    ->live()
                                    ->columnSpan(1),

                                TextInput::make('price')
                                    ->label('Τιμή Δείγματος')
                                    ->numeric()
                                    ->prefix('€') // Διορθώθηκε: Αντικαταστάθηκε το money() με prefix('€')
                                    ->default(0)
                                    ->live(onBlur: true)
                                    ->required()
                                    ->afterStateUpdated(
                                        fn (Set $set, Get $get) =>
                                            $set('forecasted_amount',
                                                round(
                                                    ($get('forecasted_samples') ?? 0) * ($get('price') ?? 0),
                                                    2
                                                )
                                            )
                                    )
                                    ->columnSpan(1),

                                TextInput::make('analysis_unit_price')
                                    ->label('Τιμή Ανάλυσης')
                                    ->numeric()
                                    ->prefix('€')
                                    ->default(0)
                                    ->required(fn (Get $get) => $get('cost_calculation_type') === 'variable_count')
                                    ->visible(fn (Get $get) => $get('cost_calculation_type') === 'variable_count')
                                    ->columnSpan(1),

                                TextInput::make('max_analyses')
                                    ->label('Μέγιστο Όριο Αναλύσεων')
                                    ->numeric()
                                    ->default(0)
                                    ->visible(fn (Get $get) =>
                                        in_array($get('cost_calculation_type'), ['variable', 'variable_count'])
                                    )
                                    ->helperText('Αν οι αναλύσεις υπερβούν το όριο, εφαρμόζεται η σταθερή τιμή.')
                                    ->columnSpan(1),

                            ])
                            ->columns(3)
                            ->columnSpanFull(),


                        // 3. Ποσότητες & Ποσά (3 στήλες)
                        Fieldset::make('Ποσότητες & Προϋπολογισμός')
                            ->schema([

                                TextInput::make('forecasted_samples')
                                    ->label('Προβλεπόμενα Δείγματα')
                                    ->numeric()
                                    ->default(0)
                                    ->live(onBlur: true)
                                    ->required()
                                    ->afterStateUpdated(
                                        fn (Set $set, Get $get) =>
                                            $set('forecasted_amount',
                                                round(
                                                    ($get('forecasted_samples') ?? 0) * ($get('price') ?? 0),
                                                    2
                                                )
                                            )
                                    )
                                    ->columnSpan(1),

                                TextInput::make('forecasted_amount')
                                    ->label('Προϋπολογισθέν Ποσό')
                                    ->numeric()
                                    ->prefix('€') // Διορθώθηκε: Αντικαταστάθηκε το money() με prefix('€')
                                    ->default(0)
                                    ->readOnly()
                                    ->columnSpan(2), // Μεγαλύτερο πλάτος για το ReadOnly πεδίο

                            ])
                            ->columns(3)
                            ->columnSpanFull(),


                        // 4. Κατηγορίες Εργαστηρίου (Full Width)
                        Select::make('labCategories')
                            ->label('Κατηγορίες Εργαστηρίου')
                            ->relationship(
                                name: 'labCategories',
                                titleAttribute: 'name'
                            )
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->columnSpanFull()
                            ->hint('Συνδέστε μία ή περισσότερες κατηγορίες εργαστηριακών δειγμάτων.'),

                        // 5. Παρατηρήσεις & Κατάσταση (2 στήλες)
                        Grid::make(2)
                            ->columnSpanFull()
                            ->schema([
                                Textarea::make('remarks')
                                    ->label('Παρατηρήσεις')
                                    ->rows(2)
                                    ->columnSpan(1),

                                Select::make('status')
                                    ->label('Κατάσταση')
                                    ->options([
                                        RecordStatusEnum::Active->value => RecordStatusEnum::Active->getLabel(),
                                        RecordStatusEnum::Inactive->value => RecordStatusEnum::Inactive->getLabel(),
                                    ])
                                    ->default(RecordStatusEnum::Active->value)
                                    ->required()
                                    ->columnSpan(1),
                            ]),

                    ])
                    ->columns(1)
                    ->columnSpanFull(),
            ]);
    }

    // ─────────────────────────────── TABLE ───────────────────────────────
    public function table(Table $table): Table
    {
        return $table
            ->heading('Διαχείριση Δειγμάτων Σύμβασης')

            ->recordTitleAttribute('display_label')

            ->modifyQueryUsing(
                fn (Builder $query) => $query->withoutGlobalScopes([SoftDeletingScope::class])
            )

            ->groups([
                Group::make('name')
                    ->label('Σύμβαση')
                    ->collapsible()
                    ->getTitleFromRecordUsing(fn ($record) => $record->name),

                Group::make('category.name')
                    ->label('Κατηγορία Εργαστηρίου')
                    ->getTitleFromRecordUsing(fn ($record) => $record->category?->name),
            ])
            ->defaultGroup('name')

            ->columns([
                TextColumn::make('name')
                    ->label('Σύμβαση')
                    ->weight('medium')
                    ->color('primary')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('category.name')
                    ->label('Κατηγορία Δείγματος')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('forecasted_samples')
                    ->label('Πλήθος')
                    ->numeric()
                    ->alignRight()
                    ->sortable(),

                TextColumn::make('price')
                    ->label('Τιμή (€)')
                    ->numeric(decimalPlaces: 2)
                    ->alignRight()
                    ->sortable(),

                TextColumn::make('forecasted_amount')
                    ->label('Προϋπολογισμός (€)')
                    ->numeric(decimalPlaces: 2)
                    ->alignRight()
                    ->sortable(),

                TextColumn::make('cost_calculation_type')
                    ->label('Τύπος Κόστους')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state?->getLabel() ?? '—')
                    ->color(fn ($state) => $state?->getColor() ?? 'gray')
                    ->icon(fn ($state) => $state?->getIcon()),

                TextColumn::make('max_analyses')
                    ->label('Όριο Αναλύσεων')
                    ->alignCenter()
                    ->formatStateUsing(fn ($state) => $state ?: '—')
                    ->visible(fn ($record) =>
                        filled($record?->cost_calculation_type)
                        && in_array($record->cost_calculation_type, ['variable', 'variable_count'])
                    ),

                TextColumn::make('status')
                    ->label('Κατάσταση')
                    ->badge()
                    ->color(fn ($state) => $state?->getColor())
                    ->icon(fn ($state) => $state?->getIcon())
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])

            ->filters([
                TrashedFilter::make(),
            ])

            ->headerActions([
                CreateAction::make()
                    ->label('Προσθήκη Δείγματος')
                    ->icon('heroicon-o-plus-circle')
                    ->modalHeading('Νέο Δείγμα Σύμβασης')
                    ->modalSubmitActionLabel('Αποθήκευση')
                    ->modalCancelActionLabel('Ακύρωση')
                    ->createAnotherAction(
                        fn (Action $action) => $action->label('Αποθήκευση & Προσθήκη νέου')
                    ),
            ])

            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()->label('Προβολή')->icon('heroicon-o-eye'),
                    EditAction::make()->label('Επεξεργασία')->icon('heroicon-o-pencil-square'),
                    DeleteAction::make(),
                    ForceDeleteAction::make(),
                    RestoreAction::make(),
                ])->icon('heroicon-o-ellipsis-vertical'),
            ])

            ->pushToolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getTabComponent(Model $ownerRecord, string $pageClass): \Filament\Schemas\Components\Tabs\Tab
    {
        return \Filament\Schemas\Components\Tabs\Tab::make('Κατηγορίες Δειγμάτων')
            ->icon('heroicon-o-rectangle-stack')
            ->badge($ownerRecord->samples()->count())
            ->badgeColor('info')
            ->badgeTooltip('Σύνολο κατηγοριών δειγμάτων');
    }

}
