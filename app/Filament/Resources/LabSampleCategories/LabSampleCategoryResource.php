<?php

namespace App\Filament\Resources\LabSampleCategories;

use App\Filament\Resources\LabSampleCategories\Pages\CreateLabSampleCategory;
use App\Filament\Resources\LabSampleCategories\Pages\EditLabSampleCategory;
use App\Filament\Resources\LabSampleCategories\Pages\ListLabSampleCategories;
use App\Filament\Resources\LabSampleCategories\Pages\ViewLabSampleCategory;
use App\Filament\Resources\LabSampleCategories\Schemas\LabSampleCategoryForm;
use App\Filament\Resources\LabSampleCategories\Schemas\LabSampleCategoryInfolist;
use App\Filament\Resources\LabSampleCategories\Tables\LabSampleCategoriesTable;
use App\Models\LabSampleCategory;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LabSampleCategoryResource extends Resource
{
    protected static ?string $model = LabSampleCategory::class;

    protected static ?string $recordTitleAttribute = 'name';
    protected static ?string $modelLabel = 'Κατηγορίας';
    protected static ?string $pluralModelLabel = 'Κατηγορίες Δειγμάτων';
    protected static ?string $navigationLabel = 'Κατηγορίες Δειγμάτων';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;
    protected static string|UnitEnum|null $navigationGroup = 'Εργαστήριο';
    protected static ?int $navigationSort = 420;

    public static function form(Schema $schema): Schema
    {
        return LabSampleCategoryForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return LabSampleCategoryInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LabSampleCategoriesTable::configure($table);
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
            'index' => ListLabSampleCategories::route('/'),
            'create' => CreateLabSampleCategory::route('/create'),
            'view' => ViewLabSampleCategory::route('/{record}'),
            'edit' => EditLabSampleCategory::route('/{record}/edit'),
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
