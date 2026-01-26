<?php

declare(strict_types=1);

namespace App\Filament\Forms\Components;

use Filament\Forms\Components\TextInput;
use Filament\Actions\Action;
use App\Support\CodeGeneration\CodeParser;
use App\Support\CodeGeneration\CodeGenerator;

class CodeInput extends TextInput
{
    protected string $modelClass;
    protected string $column;

    protected function setUp(): void
    {
        parent::setUp();

        $this->suffixAction(fn () => $this->generateAction());
    }

    public function target(string $modelClass, string $column): static
    {
        $this->modelClass = $modelClass;
        $this->column     = $column;

        return $this;
    }

    protected function generateAction(): Action
    {
        return Action::make('generateCode')
            ->icon('heroicon-o-bolt')
            ->tooltip('Αυτόματη δημιουργία κωδικού')
            ->action(function ($state, callable $set) {
                $parser = app(CodeParser::class);
                $parsed = $parser->parse($state);

                if (! $parsed->isAuto) {
                    return;
                }

                $code = app(CodeGenerator::class)->next(
                    $this->modelClass,
                    $this->column,
                    $parsed->prefix
                );

                $set($this->getName(), $code);
            });
    }
}
