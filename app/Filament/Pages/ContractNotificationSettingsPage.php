<?php

namespace App\Filament\Pages;

use App\Settings\ContractNotificationSettings;
use Filament\Actions\Action;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Pages\SettingsPage;
use Illuminate\Support\Facades\Auth;
use BackedEnum;
use UnitEnum;

class ContractNotificationSettingsPage extends SettingsPage
{
    protected static string $settings = ContractNotificationSettings::class;
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-bell-alert';
    protected static string|UnitEnum|null $navigationGroup = 'Σύστημα';
    protected static ?string $navigationLabel = 'Ειδοποιήσεις Συμβάσεων';
    protected static ?string $title = 'Ρυθμίσεις Ειδοποιήσεων Συμβάσεων';

    public static function canAccess(): bool
    {
        return Auth::User()->can('manage_notification_settings') ?? false;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([

                // ---------------------------------------------------
                // GENERAL
                // ---------------------------------------------------
                Section::make('Γενικά')
                    ->description('Κεντρικές ρυθμίσεις ενεργοποίησης του συστήματος ειδοποιήσεων.')
                    ->schema([
                        Toggle::make('enable_notifications')
                            ->label('Ενεργοποίηση Ειδοποιήσεων')
                            ->default(true)
                            ->inline(false),
                    ]),

                // ---------------------------------------------------
                // THRESHOLDS
                // ---------------------------------------------------
                Section::make('Όρια Ειδοποιήσεων')
                    ->description('Τα ποσοστά στα οποία το σύστημα ενεργοποιεί warning notifications.')
                    ->schema([
                        Grid::make(4)->schema([
                            TextInput::make('warning_threshold_50')
                                ->label('50%')
                                ->numeric()
                                ->default(50)
                                ->required(),

                            TextInput::make('warning_threshold_75')
                                ->label('75%')
                                ->numeric()
                                ->default(75)
                                ->required(),

                            TextInput::make('warning_threshold_90')
                                ->label('90%')
                                ->numeric()
                                ->default(90)
                                ->required(),

                            TextInput::make('warning_threshold_100')
                                ->label('100%')
                                ->numeric()
                                ->default(100)
                                ->required(),
                        ]),
                    ]),

                // ---------------------------------------------------
                // RECIPIENTS
                // ---------------------------------------------------
                Section::make('Παραλήπτες')
                    ->description('Ρόλοι που λαμβάνουν όλα τα notifications των συμβάσεων.')
                    ->schema([
                        Select::make('notify_roles')
                            ->label('Ρόλοι που θα ειδοποιούνται')
                            ->multiple()
                            ->options(fn () =>
                                \Spatie\Permission\Models\Role::query()
                                    ->orderBy('name')
                                    ->pluck('name', 'name')
                            )
                            ->required()
                            ->preload()
                            ->searchable(),
                    ]),

                // ---------------------------------------------------
                // EVENT OPTIONS
                // ---------------------------------------------------
                Section::make('Ειδοποιήσεις για Συμβάντα')
                    ->description('Επιλέξτε ποια συμβάντα θα ενεργοποιούν ειδοποιήσεις.')
                    ->schema([
                        Toggle::make('notify_on_warning_levels')
                            ->label('Ειδοποιήσεις Όταν Ξεπερνιούνται Τα Thresholds')
                            ->inline(false),

                        Toggle::make('notify_on_contract_completion')
                            ->label('Ειδοποίηση Όταν Ολοκληρωθεί Η Σύμβαση (100%)')
                            ->inline(false),
                    ]),

            ]);
    }


    public function getSaveFormAction(): Action
    {
        return parent::getSaveFormAction()
            ->label('Αποθήκευση αλλαγών'); // ✅ Your custom Greek label
    }
}
