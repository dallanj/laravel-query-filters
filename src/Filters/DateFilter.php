<?php

declare(strict_types=1);

namespace Dallanj\QueryFilters\Filters;

use Dallanj\QueryFilters\Contracts\Filter;
use Dallanj\QueryFilters\FilterContext;
use Illuminate\Database\Eloquent\Builder;
use InvalidArgumentException;

final readonly class DateFilter implements Filter
{
    private const array OPERATORS = ['=', '<', '<=', '>', '>='];

    public function __construct(
        private string $column,
        private string $operator = '=',
    ) {
        if (! in_array($operator, self::OPERATORS, true)) {
            throw new InvalidArgumentException("Unsupported date operator [{$operator}].");
        }
    }

    public static function column(string $column): self
    {
        return new self($column);
    }

    public function greaterThanOrEqual(): self
    {
        return new self($this->column, '>=');
    }

    public function lessThanOrEqual(): self
    {
        return new self($this->column, '<=');
    }

    public function apply(Builder $query, mixed $value, FilterContext $context): Builder
    {
        return $query->whereDate(
            $query->getModel()->qualifyColumn($this->column),
            $this->operator,
            $value,
        );
    }
}
