<?php

namespace App\Filament\Resources\CustomerCategories;

use App\Enums\CustomerCategoryStatus;
use App\Filament\Resources\CustomerCategories\Pages\ManageCustomerCategories;
use App\Models\CustomerCategory;
use BackedEnum;
use UnitEnum;
use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Section;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CustomerCategoryResource extends Resource
{
    protected static ?string $model = CustomerCategory::class;
    protected static ?string $recordTitleAttribute = 'name';
    protected static ?string $modelLabel = 'Κατηγορίας Πελατών';
    protected static ?string $pluralModelLabel = 'Κατηγορίες Πελατών';
    protected static ?string $navigationLabel = 'Κατηγορίες Πελατών';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::RectangleStack;
    protected static string|UnitEnum|null $navigationGroup = 'Εργαστήριο';
    protected static ?int $navigationSort = 410;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Fieldset::make('Βασικά Στοιχεία')
                    ->schema([
                        TextInput::make('name')
                            ->label('Όνομα')
                            ->required()
                            ->maxLength(255)
                            ->autofocus()
                            ->placeholder('Πληκτρολογήστε το όνομα της κατηγορίας'),

                        Textarea::make('description')
                            ->label('Περιγραφή')
                            ->placeholder('Προαιρετική περιγραφή')
                            ->rows(3)
                            ->maxLength(500)
                            ->columnSpanFull(),

                        Select::make('status')
                            ->label('Κατάσταση')
                            ->options(CustomerCategoryStatus::class)
                            ->default('active')
                            ->required()
                            ->native(false)
                            ->helperText('Ορίστε αν η κατηγορία είναι ενεργή ή ανενεργή.'),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Βασικές Πληροφορίες')
                    ->schema([
                        TextEntry::make('name')
                            ->label('Όνομα Κατηγορίας')
                            ->icon('heroicon-o-rectangle-stack')
                            ->weight('medium')
                            ->color('primary'),

                        TextEntry::make('description')
                            ->label('Περιγραφή')
                            ->placeholder('-')
                            ->columnSpanFull()
                            ->markdown()
                            ->prose(),
                    ])
                    ->columns(2),

                Section::make('Κατάσταση & Πληροφορίες')
                    ->schema([
                        TextEntry::make('status')
                            ->label('Κατάσταση')
                            ->badge()
                            ->color(fn ($state) => $state->value === 'active' ? 'success' : 'gray')
                            ->icon(fn ($state) => $state->value === 'active' ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle'),

                        TextEntry::make('customers_count')
                            ->label('Σύνολο Πελατών')
                            ->counts('customers')
                            ->badge()
                            ->color('info')
                            ->icon('heroicon-o-users')
                            ->placeholder('0'),

                        TextEntry::make('created_at')
                            ->label('Δημιουργήθηκε')
                            ->dateTime('d/m/Y H:i')
                            ->since()
                            ->icon('heroicon-o-calendar-days'),

                        TextEntry::make('updated_at')
                            ->label('Ενημερώθηκε')
                            ->dateTime('d/m/Y H:i')
                            ->since()
                            ->icon('heroicon-o-arrow-path'),
                    ])
                    ->columns(2),

                Actions\EditAction::make('edit')
                    ->label('Επεξεργασία')
                    ->icon('heroicon-o-pencil-square')
                    ->color('primary'),

                Section::make('Ιστορικό Καταγραφών')
                    ->schema([
                        TextEntry::make('createdBy.name')
                            ->label('Δημιουργήθηκε από')
                            ->placeholder('-')
                            ->icon('heroicon-o-user'),

                        TextEntry::make('updatedBy.name')
                            ->label('Τελευταία ενημέρωση από')
                            ->placeholder('-')
                            ->icon('heroicon-o-user-circle'),

                        TextEntry::make('deleted_at')
                            ->label('Ημερομηνία Διαγραφής')
                            ->dateTime('d/m/Y H:i')
                            ->color('danger')
                            ->visible(fn (CustomerCategory $record): bool => $record->trashed())
                            ->icon('heroicon-o-trash'),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),



            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')

            /**
             * --------------- COLUMNS ---------------
             */
            ->columns([
                TextColumn::make('name')
                    ->label('Κατηγορία')
                    ->icon('heroicon-o-rectangle-stack')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->tooltip(fn ($record) => $record->description)
                    ->weight('medium'),

                TextColumn::make('status')
                    ->label('Κατάσταση')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state?->getLabel())
                    ->colors([
                        'success' => fn ($state) => $state === CustomerCategoryStatus::Active,
                        'gray'    => fn ($state) => $state === CustomerCategoryStatus::Inactive,
                    ])
                    ->icons([
                        'heroicon-o-check-circle' => CustomerCategoryStatus::Active,
                        'heroicon-o-x-circle'     => CustomerCategoryStatus::Inactive,
                    ])
                    ->sortable(),

                TextColumn::make('customers_count')
                    ->label('Πελάτες')
                    ->badge()
                    ->counts('customers') // counts() υποστηρίζεται σε Filament 4.x
                    ->color('info')
                    ->icon('heroicon-o-users')
                    ->alignCenter()
                    ->tooltip('Αριθμός πελατών που ανήκουν στην κατηγορία'),

                TextColumn::make('created_at')
                    ->label('Δημιουργήθηκε')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
            ])

            /**
             * --------------- FILTERS ---------------
             */
            ->filters([
                TrashedFilter::make()
                    ->label('Διεγραμμένα'),
            ])

            /**
             * --------------- ACTIONS ---------------
             */
            ->recordActions([
                Actions\ActionGroup::make([
                    Actions\ViewAction::make()
                        ->label('Προβολή')
                        ->icon('heroicon-o-eye'),

                    Actions\EditAction::make()
                        ->label('Επεξεργασία')
                        ->icon('heroicon-o-pencil-square'),

                    Actions\DeleteAction::make()
                        ->label('Διαγραφή')
                        ->icon('heroicon-o-trash'),

                    Actions\ForceDeleteAction::make()
                        ->label('Οριστική διαγραφή')
                        ->icon('heroicon-o-x-circle'),

                    Actions\RestoreAction::make()
                        ->label('Επαναφορά')
                        ->icon('heroicon-o-arrow-path'),
                ])
                ->icon('heroicon-o-ellipsis-vertical')
                ->tooltip('Ενέργειες'),
            ])

            /**
             * --------------- BULK ACTIONS ---------------
             */
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                    Actions\ForceDeleteBulkAction::make(),
                    Actions\RestoreBulkAction::make(),
                ]),
            ])

            /**
             * --------------- TABLE CONFIGURATION ---------------
             */
            ->striped()
            ->defaultPaginationPageOption(25)
            ->paginated([10, 25, 50, 100])
            ->poll('live')
            ->reorderable(false)
            ->emptyStateHeading('Δεν υπάρχουν κατηγορίες πελατών')
            ->emptyStateDescription('Δημιουργήστε μια νέα κατηγορία για να ξεκινήσετε.');
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageCustomerCategories::route('/'),
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
