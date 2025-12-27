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
use Filament\Schemas\Components\Grid;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\FontWeight;

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
                // Χρησιμοποιούμε Section αντί για Fieldset, καθώς έχει καλύτερη οπτική εμφάνιση
                Section::make('Βασικές Πληροφορίες Κατηγορίας')
                    ->description('Επεξεργασία του ονόματος, της περιγραφής και της κατάστασης.')
                    ->icon('heroicon-o-pencil-square') // Εικονίδιο για επαγγελματική εμφάνιση
                    ->compact()
                    ->schema([
                        // Inner Grid: Χωρίζει Όνομα και Κατάσταση σε 2 στήλες
                        Grid::make(2)
                            ->schema([
                                TextInput::make('name')
                                    ->label('Όνομα')
                                    ->required()
                                    ->maxLength(255)
                                    ->autofocus()
                                    ->placeholder('Πληκτρολογήστε το όνομα'), // Χωρίς hint

                                Select::make('status')
                                    ->label('Κατάσταση')
                                    ->options(CustomerCategoryStatus::class)
                                    ->default('active')
                                    ->required()
                                    ->native(false)
                                    ->placeholder('Ενεργή ή ανενεργή'), // Χωρίς helperText/hint
                            ]),

                        // Περιγραφή: Πάντα σε πλήρες πλάτος
                        Textarea::make('description')
                            ->label('Περιγραφή')
                            ->placeholder('Προαιρετική περιγραφή')
                            ->rows(3)
                            ->maxLength(500)
                            ->columnSpanFull(),
                    ])
                    ->columns(1) // Η κύρια ενότητα λειτουργεί σε μία στήλη μέσα στο modal
                    ->columnSpanFull(),
            ]);
    }

    /**
     * Infolist schema for a Modal View, structured in a clean 2:1 column layout.
     * The EditAction is moved to the header of the primary Section for better UX.
     */
    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Main Grid Container: Creates a 2:1 column layout (2/3 width for main, 1/3 for sidebar)
                Grid::make(3)
                    ->columnSpanFull()
                    ->schema([

                        // === LEFT COLUMN (2/3 width) - Primary and Audit Data ===
                        Grid::make(1) // Single column container for main sections
                            ->columnSpan(2) // Span 2 out of 3 columns
                            ->schema([

                                // 1. ΒΑΣΙΚΕΣ ΠΛΗΡΟΦΟΡΙΕΣ (PRIMARY DETAILS)
                                Section::make('Βασικές Πληροφορίες')
                                    ->description('Όνομα και λεπτομερής περιγραφή της κατηγορίας.')
                                    ->icon('heroicon-o-tag')
                                    ->compact()
                                    ->headerActions([
                                        // ΤΟ ΚΟΥΜΠΙ ΕΠΕΞΕΡΓΑΣΙΑΣ ΜΕΤΑΚΙΝΗΘΗΚΕ ΕΔΩ
                                        Actions\EditAction::make('edit')
                                            ->label('Επεξεργασία')
                                            ->icon('heroicon-o-pencil-square')
                                            ->color('primary'),
                                    ])
                                    ->schema([
                                        // Inner grid for better label alignment
                                        Grid::make(2)
                                            ->schema([
                                                TextEntry::make('name')
                                                    ->label('Όνομα Κατηγορίας')
                                                    ->weight(FontWeight::SemiBold)
                                                    ->color('primary'),
                                            ]),

                                        TextEntry::make('description')
                                            ->label('Περιγραφή')
                                            ->placeholder('Δεν έχει καταχωρηθεί περιγραφή.')
                                            ->columnSpanFull()
                                            ->markdown()
                                            ->prose(),
                                    ]),

                                // 3. ΙΣΤΟΡΙΚΟ ΚΑΤΑΓΡΑΦΩΝ (AUDIT LOGS)
                                Section::make('Ιστορικό Καταγραφών')
                                    ->description('Πληροφορίες δημιουργίας και τελευταίας ενημέρωσης.')
                                    ->icon('heroicon-o-clock')
                                    ->compact()
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
                                    ->columns(2),
                            ]),

                        // === RIGHT COLUMN (1/3 width) - Status and Meta Data ===
                        Grid::make(1)
                            ->columnSpan(1)
                            ->schema([
                                // 2. ΚΑΤΑΣΤΑΣΗ & ΠΛΗΡΟΦΟΡΙΕΣ (STATUS & META)
                                Section::make('Κατάσταση & Μεταδεδομένα')
                                    ->description('Διαχειριστικές πληροφορίες.')
                                    ->icon('heroicon-o-information-circle')
                                    ->compact()
                                    ->schema([
                                        TextEntry::make('status')
                                            ->label('Κατάσταση')
                                            ->badge()
                                            ->color(fn ($state): string => match ($state->value) {
                                                'active' => 'success',
                                                default => 'gray',
                                            })
                                            ->icon(fn ($state): string => match ($state->value) {
                                                'active' => 'heroicon-o-check-circle',
                                                default => 'heroicon-o-x-circle',
                                            }),

                                        TextEntry::make('customers_count')
                                            ->label('Σύνολο Πελατών')
                                            ->counts('customers')
                                            ->badge()
                                            ->color('info')
                                            ->icon('heroicon-o-users')
                                            ->placeholder('0'),
                                    ])
                                    ->columns(1), // Force 1 column within the sidebar for stacking

                                // Ενότητα Ημερομηνιών (Separate section for clean stacking)
                                Section::make('Ημερομηνίες')
                                    ->icon('heroicon-o-calendar')
                                    ->compact()
                                    ->schema([
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
                                    ->columns(1),
                            ]),
                    ]),
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
