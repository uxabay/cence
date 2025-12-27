<?php

namespace App\Filament\Resources\LabAnalysisPackages;

use App\Filament\Resources\LabAnalysisPackages\Pages\CreateLabAnalysisPackage;
use App\Filament\Resources\LabAnalysisPackages\Pages\EditLabAnalysisPackage;
use App\Filament\Resources\LabAnalysisPackages\Pages\ListLabAnalysisPackages;
use App\Filament\Resources\LabAnalysisPackages\Pages\ViewLabAnalysisPackage;
use App\Filament\Resources\LabAnalysisPackages\Schemas\LabAnalysisPackageForm;
use App\Filament\Resources\LabAnalysisPackages\Schemas\LabAnalysisPackageInfolist;
use App\Filament\Resources\LabAnalysisPackages\Tables\LabAnalysisPackagesTable;
use App\Models\LabAnalysisPackage;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LabAnalysisPackageResource extends Resource
{
    protected static ?string $model = LabAnalysisPackage::class;


    protected static ?string $recordTitleAttribute = 'name';
    protected static ?string $modelLabel = 'Πακέτο Αναλύσεων';
    protected static ?string $pluralModelLabel = 'Πακέτα Αναλύσεων';
    protected static ?string $navigationLabel = 'Πακέτα Αναλύσεων';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleGroup;
    protected static string|UnitEnum|null $navigationGroup = 'Εργαστήριο';
    protected static ?int $navigationSort = 440;

    public static function form(Schema $schema): Schema
    {
        return LabAnalysisPackageForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return LabAnalysisPackageInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LabAnalysisPackagesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
            'analyses' => RelationManagers\AnalysesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLabAnalysisPackages::route('/'),
            'create' => CreateLabAnalysisPackage::route('/create'),
            'view' => ViewLabAnalysisPackage::route('/{record}'),
            'edit' => EditLabAnalysisPackage::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withCount('analyses')
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
