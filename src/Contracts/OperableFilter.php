<?php

declare(strict_types=1);

namespace Dallanj\QueryFilters\Contracts;

interface OperableFilter extends Filter
{
    /** @return list<string> */
    public function allowedOperators(): array;

    public function defaultOperator(): string;

    /** @return array{type: string, operators: list<string>, options?: array<int|string, mixed>} */
    public function describe(): array;
}
