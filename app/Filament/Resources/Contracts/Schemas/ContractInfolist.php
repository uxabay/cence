<?php

namespace App\Filament\Resources\Contracts\Schemas;

use App\Models\Contract;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ContractInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('contract_number'),
                TextEntry::make('title'),
                TextEntry::make('subject')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('contract_type')
                    ->badge(),
                TextEntry::make('status')
                    ->badge(),
                TextEntry::make('lab_customer_id')
                    ->numeric(),
                TextEntry::make('parent_id')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('start_date')
                    ->date()
                    ->placeholder('-'),
                TextEntry::make('end_date')
                    ->date()
                    ->placeholder('-'),
                TextEntry::make('total_value')
                    ->numeric(),
                TextEntry::make('funding_source')
                    ->placeholder('-'),
                TextEntry::make('scope')
                    ->placeholder('-'),
                TextEntry::make('remarks')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('created_by')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('updated_by')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('deleted_at')
                    ->dateTime()
                    ->visible(fn (Contract $record): bool => $record->trashed()),
            ]);
    }
}
