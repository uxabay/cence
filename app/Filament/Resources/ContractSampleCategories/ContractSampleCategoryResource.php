<?php

namespace App\Filament\Resources\ContractSampleCategories;

use App\Filament\Resources\ContractSampleCategories\Pages\CreateContractSampleCategory;
use App\Filament\Resources\ContractSampleCategories\Pages\EditContractSampleCategory;
use App\Filament\Resources\ContractSampleCategories\Pages\ListContractSampleCategories;
use App\Filament\Resources\ContractSampleCategories\Pages\ViewContractSampleCategory;
use App\Filament\Resources\ContractSampleCategories\Schemas\ContractSampleCategoryForm;
use App\Filament\Resources\ContractSampleCategories\Schemas\ContractSampleCategoryInfolist;
use App\Filament\Resources\ContractSampleCategories\Tables\ContractSampleCategoriesTable;
use App\Models\ContractSampleCategory;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ContractSampleCategoryResource extends Resource
{
    protected static ?string $model = ContractSampleCategory::class;
    protected static ?string $recordTitleAttribute = 'name';
    protected static ?string $modelLabel = 'Δείγμα Συμβάσεων';
    protected static ?string $pluralModelLabel = 'Δείγματα Συμβάσεων';
    protected static ?string $navigationLabel = 'Δείγματα Συμβάσεων';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::Beaker;
    protected static string|UnitEnum|null $navigationGroup = 'Συμβάσεις';
    protected static ?int $navigationSort = 390;

    public static function form(Schema $schema): Schema
    {
        return ContractSampleCategoryForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ContractSampleCategoryInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ContractSampleCategoriesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListContractSampleCategories::route('/'),
            'create' => CreateContractSampleCategory::route('/create'),
            'view' => ViewContractSampleCategory::route('/{record}'),
            'edit' => EditContractSampleCategory::route('/{record}/edit'),
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
