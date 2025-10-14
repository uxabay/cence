<?php

namespace App\Filament\Resources\LabCustomers\Components;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;

class CustomerEmailsForm
{
    public static function make(): Section
    {
        return Section::make('Emails επικοινωνίας')
            ->icon('heroicon-o-envelope')
            ->description('Προσθέστε ή επεξεργαστείτε τα emails επικοινωνίας για τον πελάτη.')
            ->collapsible()
            ->schema([
                Repeater::make('emails')
                    ->relationship('emails')
                    ->label('')
                    ->addActionLabel('Προσθήκη email')
                    ->columns(3)
                    ->minItems(0)
                    ->reorderable(false)
                    ->schema([
                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->placeholder('π.χ. info@example.com'),

                        Toggle::make('is_primary')
                            ->label('Κύριο')
                            ->inline(false)
                            ->helperText('Ορίστε το κύριο email επικοινωνίας.'),

                        Textarea::make('notes')
                            ->label('Σημειώσεις')
                            ->rows(2)
                            ->columnSpanFull()
                            ->maxLength(500),
                    ])
                    ->defaultItems(1),
            ]);
    }
}
