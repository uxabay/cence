<?php

namespace App\Filament\Resources\Contracts;

use App\Filament\Resources\Contracts\Pages\ContractOverview;
use App\Filament\Resources\Contracts\Pages\ContractActivity;
use App\Filament\Resources\Contracts\Pages\CreateContract;
use App\Filament\Resources\Contracts\Pages\EditContract;
use App\Filament\Resources\Contracts\Pages\ListContracts;
use App\Filament\Resources\Contracts\Pages\ViewContract;
use App\Filament\Resources\Contracts\Schemas\ContractForm;
use App\Filament\Resources\Contracts\Schemas\ContractInfolist;
use App\Filament\Resources\Contracts\Tables\ContractsTable;

use App\Models\Contract;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Resources\RelationManagers\RelationGroup;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Eloquent\Model;

class ContractResource extends Resource
{
    protected static ?string $model = Contract::class;
    protected static ?string $recordTitleAttribute = 'contract_number';
    protected static ?string $modelLabel = 'Σύμβαση';
    protected static ?string $pluralModelLabel = 'Συμβάσεις';
    protected static ?string $navigationLabel = 'Συμβάσεις';
    protected static string|UnitEnum|null $navigationGroup = 'Συμβάσεις';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::DocumentText;
    protected static ?int $navigationSort = 300;

    public static function form(Schema $schema): Schema
    {
        return ContractForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ContractInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ContractsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [

            RelationManagers\SamplesRelationManager::class,
            RelationManagers\RegistrationsRelationManager::class,
            // ...
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListContracts::route('/'),
            'create' => CreateContract::route('/create'),
            'view' => ViewContract::route('/{record}'),
            'edit' => EditContract::route('/{record}/edit'),

            'overview' => ContractOverview::route('/{record}/overview'),
            'activities' => ContractActivity::route(path: '{record}/activities'),
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
