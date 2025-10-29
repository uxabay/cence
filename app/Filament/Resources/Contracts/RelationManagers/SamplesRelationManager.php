<?php

namespace App\Filament\Resources\Contracts\RelationManagers;

use App\Enums\RecordStatusEnum;
use App\Models\ContractSample;
use App\Models\ContractSampleCategory;
use App\Models\LabSampleCategory;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Fieldset;
use Filament\Tables\Grouping\Group;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
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

class SamplesRelationManager extends RelationManager
{
    protected static string $relationship = 'samples';
    protected static ?string $recordTitleAttribute = 'display_label';

    // ─────────────────────────────── FORM ───────────────────────────────
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Στοιχεία Δείγματος Σύμβασης')
                    ->icon('heroicon-o-beaker')
                    ->schema([
                        Fieldset::make()
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
                                    ->columnSpan(1),

                                TextInput::make('name')
                                    ->label('Είδος Σύμβασης')
                                    ->placeholder('π.χ. Αρχική, Τροποποιητική 1, Τελική')
                                    ->helperText('Δηλώστε αν πρόκειται για αρχική ή τροποποιητική σύμβαση.')
                                    ->required()
                                    ->columnSpan(1),

                                Toggle::make('is_master')
                                    ->label('Κύρια')
                                    ->inline(false)
                                    ->helperText('Ενεργοποιήστε αν πρόκειται για την κύρια γραμμή της σύμβασης.')
                                    ->columnSpan(1),

                                TextInput::make('year')
                                    ->label('Έτος')
                                    ->numeric()
                                    ->default(now()->year)
                                    ->columnSpan(1),
                            ])
                            ->columnSpanFull()
                            ->columns(2),

                        Fieldset::make('Ποσότητες & Ποσά')
                            ->schema([
                                TextInput::make('forecasted_samples')
                                    ->label('Προβλεπόμενα Δείγματα')
                                    ->numeric()
                                    ->default(0)
                                    ->live(onBlur: true)
                                    ->required()
                                    ->columnSpan(1),

                                TextInput::make('price')
                                    ->label('Τιμή (€)')
                                    ->numeric()
                                    ->default(0)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn ($set, $get) =>
                                        $set('forecasted_amount', round($get('forecasted_samples') * $get('price'), 2))
                                    )
                                    ->required()
                                    ->columnSpan(1),

                                TextInput::make('forecasted_amount')
                                    ->label('Προϋπολογισθέν Ποσό (€)')
                                    ->numeric()
                                    ->default(0)
                                    ->readOnly()
                                    ->columnSpan(1),

                            ])
                            ->columnSpanFull()
                            ->columns(3),

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
                            ->hint('Συνδέστε μία ή περισσότερες κατηγορίες δειγμάτων εργαστηρίου.'),

                        Textarea::make('remarks')
                            ->label('Παρατηρήσεις')
                            ->rows(2)
                            ->columnSpanFull(),

                        Select::make('status')
                            ->label('Κατάσταση')
                            ->options([
                                RecordStatusEnum::Active->value => RecordStatusEnum::Active->getLabel(),
                                RecordStatusEnum::Inactive->value => RecordStatusEnum::Inactive->getLabel(),
                            ])
                            ->default(RecordStatusEnum::Active->value)
                            ->required(),
                    ])
                    ->columnSpanFull()
                    ->columns(2),
            ]);
    }

    // ─────────────────────────────── TABLE ───────────────────────────────
    public function table(Table $table): Table
    {
        return $table
            ->heading('Διαχείριση Δειγμάτων')

            ->recordTitleAttribute('display_label')
            ->modifyQueryUsing(fn (Builder $query) =>
                $query->withoutGlobalScopes([SoftDeletingScope::class])
            )

            ->groups([
                Group::make('name')
                    ->collapsible()
                    ->label('Είδος Σύμβασης')
                    ->getTitleFromRecordUsing(fn ($record) => "{$record->name}"),

                Group::make('category.name')
                    ->label('Κατηγορία Δειγμάτων')
                    ->getTitleFromRecordUsing(fn ($record) => "{$record->category->name}"),
            ])
            ->defaultGroup('name')

            ->columns([
                TextColumn::make('name')
                    ->label('Είδος Σύμβασης')
                    ->tooltip(fn ($record) => 'Αρχική, Τροποποιητική ή Τελική σύμβαση')
                    ->weight('medium')
                    ->color('primary')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('category.name')
                    ->label('Κατηγορία Δείγματος')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('year')
                    ->label('Έτος')
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make('forecasted_samples')
                    ->label('Πλήθος')
                    ->numeric()
                    ->sortable()
                    ->alignRight(),

                TextColumn::make('price')
                    ->label('Τιμή (€)')
                    ->numeric(decimalPlaces: 2)
                    ->sortable()
                    ->alignRight(),

                TextColumn::make('forecasted_amount')
                    ->label('Ποσό (€)')
                    ->numeric(decimalPlaces: 2)
                    ->sortable()
                    ->alignRight(),

                TextColumn::make('status')
                    ->label('Κατάσταση')
                    ->badge()
                    ->color(fn ($state) => $state?->getColor())
                    ->icon(fn ($state) => $state?->getIcon())
                    ->sortable(),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Προσθήκη Κατηγορίας')
                    ->icon('heroicon-o-plus-circle')
                    ->modalHeading('Νέα Κατηγορία Δειγμάτων')
                    ->modalSubmitActionLabel('Αποθήκευση')
                    ->modalCancelActionLabel('Ακύρωση')
                    ->createAnotherAction(fn (Action $action) =>
                        $action->label('Αποθήκευση & Προσθήκη νέας')
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
}
