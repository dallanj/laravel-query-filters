<?php

declare(strict_types=1);

namespace Dallanj\QueryFilters\Concerns;

use Dallanj\QueryFilters\Contracts\OperableFilter;
use Dallanj\QueryFilters\FilterContext;

/**
 * @phpstan-require-implements OperableFilter
 */
trait ResolvesOperator
{
    /** @return list<string> */
    abstract public function allowedOperators(): array;

    abstract public function defaultOperator(): string;

    private function resolveOperator(FilterContext $context): string
    {
        $requested = $context->operator;

        if ($requested !== null && in_array($requested, $this->allowedOperators(), true)) {
            return $requested;
        }

        return $this->defaultOperator();
    }
}
