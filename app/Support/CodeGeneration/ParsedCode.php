<?php

declare(strict_types=1);

namespace App\Support\CodeGeneration;

final class ParsedCode
{
    public function __construct(
        public readonly bool $isAuto,
        public readonly ?string $prefix,
    ) {}
}
