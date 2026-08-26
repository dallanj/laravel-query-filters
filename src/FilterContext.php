<?php

declare(strict_types=1);

namespace Dallanj\QueryFilters;

final readonly class FilterContext
{
    /** @param array<string, mixed> $input */
    public function __construct(
        public string $parameter,
        public array $input,
    ) {}
}
