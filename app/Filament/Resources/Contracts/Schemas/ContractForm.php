<?php

namespace App\Filament\Resources\Contracts\Schemas;

use App\Enums\ContractStatusEnum;
use App\Enums\ContractTypeEnum;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class ContractForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('contract_number')
                    ->required(),
                TextInput::make('title')
                    ->required(),
                Textarea::make('subject')
                    ->default(null)
                    ->columnSpanFull(),
                Select::make('contract_type')
                    ->options(ContractTypeEnum::class)
                    ->default('programmatiki')
                    ->required(),
                Select::make('status')
                    ->options(ContractStatusEnum::class)
                    ->default('draft')
                    ->required(),
                TextInput::make('lab_customer_id')
                    ->required()
                    ->numeric(),
                TextInput::make('parent_id')
                    ->numeric()
                    ->default(null),
                DatePicker::make('start_date'),
                DatePicker::make('end_date'),
                TextInput::make('total_value')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('funding_source')
                    ->default(null),
                TextInput::make('scope')
                    ->default(null),
                Textarea::make('remarks')
                    ->default(null)
                    ->columnSpanFull(),
                TextInput::make('created_by')
                    ->numeric()
                    ->default(null),
                TextInput::make('updated_by')
                    ->numeric()
                    ->default(null),
            ]);
    }
}
