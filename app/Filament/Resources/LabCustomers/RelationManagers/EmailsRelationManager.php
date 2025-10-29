<?php

namespace App\Filament\Resources\LabCustomers\RelationManagers;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use BackedEnum;

class EmailsRelationManager extends RelationManager
{
    protected static string $relationship = 'emails';

    protected static ?string $title = 'Emails Επικοινωνίας';
    protected static string|BackedEnum|null $icon = 'heroicon-o-envelope';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('email')
                    ->label('Διεύθυνση Email')
                    ->email()
                    ->required()
                    ->placeholder('π.χ. info@domain.gr')
                    ->helperText('Πληκτρολογήστε τη διεύθυνση email του πελάτη.')
                    ->maxLength(255),

                Toggle::make('is_primary')
                    ->label('Κύριο email')
                    ->helperText('Ενεργοποιήστε αν πρόκειται για το κύριο email επικοινωνίας.')
                    ->inline(false),

                Textarea::make('notes')
                    ->label('Σημειώσεις')
                    ->placeholder('Προσθέστε προαιρετικές πληροφορίες.')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('email')
            ->columns([
                TextColumn::make('email')
                    ->label('Email')
                    ->icon('heroicon-o-envelope')
                    ->copyable()
                    ->copyMessage('Αντιγράφηκε')
                    ->copyMessageDuration(1500)
                    ->searchable(),

                IconColumn::make('is_primary')
                    ->label('Κύριο')
                    ->boolean()
                    ->trueIcon('heroicon-s-check-circle')
                    ->falseIcon('heroicon-s-minus-circle')
                    ->trueColor('success')
                    ->falseColor('gray'),

                TextColumn::make('notes')
                    ->label('Σημειώσεις')
                    ->limit(50)
                    ->placeholder('-'),

                TextColumn::make('created_at')
                    ->label('Δημιουργήθηκε')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Ενημερώθηκε')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])

            ->filters([
                TrashedFilter::make()
                    ->label('Εμφάνιση διεγραμμένων'),
            ])

            ->headerActions([
                CreateAction::make()
                    ->label('Προσθήκη Email')
                    ->icon('heroicon-o-plus-circle')
                    ->modalHeading('Νέα διεύθυνση Email')
                    ->modalSubmitActionLabel('Αποθήκευση')
                    ->modalCancelActionLabel('Ακύρωση')
                    ->createAnotherAction(fn (Action $action) =>
                        $action->label('Αποθήκευση & Προσθήκη νέου')
                    ),
            ])

            ->recordActions([
                ActionGroup::make([
                    EditAction::make()
                        ->label('Επεξεργασία')
                        ->tooltip('Επεξεργασία email'),

                    DeleteAction::make()
                        ->label('Διαγραφή')
                        ->tooltip('Μετακίνηση στο αρχείο'),

                    ForceDeleteAction::make()
                        ->label('Οριστική διαγραφή'),

                    RestoreAction::make()
                        ->label('Επαναφορά'),
                ]),
            ])

            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])

            ->modifyQueryUsing(fn (Builder $query) => $query->withoutGlobalScopes([SoftDeletingScope::class]))

            ->emptyStateHeading('Δεν υπάρχουν emails')
            ->emptyStateDescription('Προσθέστε νέα email επικοινωνίας για τον πελάτη.')
            ->defaultPaginationPageOption(10)
            ->paginated([10, 25, 50])
            ->poll(15)
            ->striped();
    }

}
