<?php

namespace App\Filament\Resources\LabAnalyses;

use App\Enums\RecordStatusEnum;
use App\Filament\Resources\LabAnalyses\Pages\ManageLabAnalyses;
use App\Models\LabAnalysis;
use BackedEnum;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class LabAnalysisResource extends Resource
{
    protected static ?string $model = LabAnalysis::class;

    protected static ?string $recordTitleAttribute = 'name';
    protected static ?string $modelLabel = 'Ανάλυσης';
    protected static ?string $pluralModelLabel = 'Αναλύσεις';
    protected static ?string $navigationLabel = 'Αναλύσεις';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSparkles;
    protected static string|UnitEnum|null $navigationGroup = 'Εργαστήριο';
    protected static ?int $navigationSort = 430;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([

                Section::make('Βασικά Στοιχεία')
                    ->icon('heroicon-o-beaker')
                    ->schema([

                        Select::make('lab_sample_category_id')
                            ->relationship('labSampleCategory', 'name')
                            ->label('Κατηγορία Δείγματος')
                            ->searchable()
                            ->preload()
                            ->required(),

                        TextInput::make('name')
                            ->label('Όνομα Ανάλυσης')
                            ->placeholder('π.χ. Χημική Ανάλυση pH')
                            ->required(),

                        Textarea::make('description')
                            ->label('Περιγραφή')
                            ->placeholder('Προαιρετική περιγραφή της ανάλυσης.')
                            ->rows(2)
                            ->columnSpanFull(),

                    ])
                    ->columns(2)
                    ->columnSpanFull(),


                Section::make('Τιμολόγηση & Κατάσταση')
                    ->icon('heroicon-o-currency-euro')
                    ->schema([

                        TextInput::make('unit_price')
                            ->label('Τιμή (€)')
                            ->numeric()
                            ->default(0)
                            ->required()
                            ->columnSpan(1),

                        TextInput::make('currency_code')
                            ->label('Νόμισμα')
                            ->default('EUR')
                            ->readOnly()
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
                    ])
                    ->columns(3)
                    ->columnSpanFull(),

            ]);
    }


    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([

                Section::make('Βασικά Στοιχεία Ανάλυσης')
                    ->icon('heroicon-o-beaker')
                    ->schema([
                        TextEntry::make('labSampleCategory.name')
                            ->label('Κατηγορία Δείγματος')
                            ->placeholder('-')
                            ->weight('medium')
                            ->color('primary'),

                        TextEntry::make('name')
                            ->label('Όνομα Ανάλυσης')
                            ->weight('medium'),

                        TextEntry::make('description')
                            ->label('Περιγραφή')
                            ->placeholder('-')
                            ->markdown()
                            ->prose()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Τιμολόγηση & Κατάσταση')
                    ->icon('heroicon-o-currency-euro')
                    ->schema([
                        TextEntry::make('unit_price')
                            ->label('Τιμή (€)')
                            ->numeric()
                            ->alignRight(),

                        TextEntry::make('currency_code')
                            ->label('Νόμισμα'),

                        TextEntry::make('status')
                            ->label('Κατάσταση')
                            ->badge()
                            ->color(fn ($state) => $state?->getColor())
                            ->icon(fn ($state) => $state?->getIcon()),
                    ])
                    ->columns(3),

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
                            ->visible(fn (LabAnalysis $record) => $record->trashed()),
                    ])
                    ->columns(2)
                    ->collapsed(), // collapsed για καθαρότερη προβολή

            ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')

            ->columns([
                TextColumn::make('labSampleCategory.name')
                    ->label('Κατηγορία')
                    ->sortable()
                    ->searchable()
                    ->weight('medium')
                    ->color('primary'),

                TextColumn::make('name')
                    ->label('Ανάλυση')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('unit_price')
                    ->label('Τιμή (€)')
                    ->numeric(decimalPlaces: 2)
                    ->alignRight()
                    ->sortable(),

                TextColumn::make('currency_code')
                    ->label('Νόμισμα')
                    ->alignCenter()
                    ->sortable(),

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

            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()->label('Προβολή')->icon('heroicon-o-eye'),
                    EditAction::make()->label('Επεξεργασία')->icon('heroicon-o-pencil-square'),
                    DeleteAction::make(),
                    ForceDeleteAction::make(),
                    RestoreAction::make(),
                ])->icon('heroicon-o-ellipsis-vertical'),
            ])

            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }


    public static function getPages(): array
    {
        return [
            'index' => ManageLabAnalyses::route('/'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
