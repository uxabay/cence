<?php

namespace App\Filament\Resources\LabCustomers;

use App\Filament\Resources\LabCustomers\Pages\CreateLabCustomer;
use App\Filament\Resources\LabCustomers\Pages\EditLabCustomer;
use App\Filament\Resources\LabCustomers\Pages\ListLabCustomers;
use App\Filament\Resources\LabCustomers\Pages\ViewLabCustomer;
use App\Filament\Resources\LabCustomers\Schemas\LabCustomerForm;
use App\Filament\Resources\LabCustomers\Schemas\LabCustomerInfolist;
use App\Filament\Resources\LabCustomers\Tables\LabCustomersTable;
use App\Models\LabCustomer;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LabCustomerResource extends Resource
{
    protected static ?string $model = LabCustomer::class;
    protected static ?string $recordTitleAttribute = 'name';
    protected static ?string $modelLabel = 'Πελάτης';
    protected static ?string $pluralModelLabel = 'Διαχείριση Πελατών';
    protected static ?string $navigationLabel = 'Πελάτες';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::UserGroup;
    protected static string|UnitEnum|null $navigationGroup = 'Εργαστήριο';

    public static function getNavigationBadge(): ?string
    {
        try {
            // Υπολογίζει μόνο ενεργούς πελάτες
            return number_format(LabCustomer::where('status', 'active')->count());
        } catch (\Throwable $e) {
            return null; // σε περίπτωση που δεν υπάρχει πίνακας ακόμα
        }
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'info'; // επιλογές: primary, success, warning, danger, gray, info
    }

    public static function form(Schema $schema): Schema
    {
        return LabCustomerForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return LabCustomerInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LabCustomersTable::configure($table);
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
            'index' => ListLabCustomers::route('/'),
            'create' => CreateLabCustomer::route('/create'),
            'view' => ViewLabCustomer::route('/{record}'),
            'edit' => EditLabCustomer::route('/{record}/edit'),
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
