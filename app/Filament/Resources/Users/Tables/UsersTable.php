<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Spatie\Permission\Models\Role;
use Filament\Tables\Table;
use App\Enums\UserStatus;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Όνομα')
                    ->description(fn ($record) => $record->email)
                    ->searchable()
                    ->sortable(),

                TextColumn::make('roles.name')
                    ->label('Ρόλοι')
                    ->badge()
                    ->separator(', ')
                    ->toggleable(),

                TextColumn::make('status')
                    ->label('Κατάσταση')
                    ->badge()
                    ->sortable()
                    ->searchable(),

                TextColumn::make('last_login_at')
                    ->label('Τελευταία σύνδεση')
                    ->since()
                    ->tooltip(fn ($record) => optional($record->last_login_at)?->format('d/m/Y H:i'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('last_activity_at')
                    ->label('Τελευταία δραστηριότητα')
                    ->since()
                    ->tooltip(fn ($record) => optional($record->last_activity_at)?->format('d/m/Y H:i'))
                    ->sortable(),

                IconColumn::make('force_password_reset')
                    ->label('Υποχρεωτική αλλαγή')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('last_activity_at', 'desc')


            ->filters([
                SelectFilter::make('status')
                    ->label('Κατάσταση')
                    ->options(UserStatus::class),

                SelectFilter::make('roles')
                    ->label('Ρόλος')
                    ->relationship('roles', 'name')
                    // ή: ->options(fn () => Role::query()->pluck('name', 'name')->all())
                    ,

                TrashedFilter::make(),
            ])

            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()->label('Προβολή'),
                    EditAction::make()->label('Επεξεργασία'),
                    DeleteAction::make()->label('Διαγραφή')->requiresConfirmation(),
                    RestoreAction::make()->label('Επαναφορά'),
                    ForceDeleteAction::make()->label('Οριστική διαγραφή')->requiresConfirmation(),
                ])
                ->icon('heroicon-m-ellipsis-vertical')
                ->label('') // χωρίς label
                ->tooltip('Ενέργειες'),
            ]);
    }
}
