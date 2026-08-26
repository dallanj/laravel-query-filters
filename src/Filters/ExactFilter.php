<?php

declare(strict_types=1);

namespace Dallanj\QueryFilters\Filters;

use Dallanj\QueryFilters\Contracts\Filter;
use Dallanj\QueryFilters\FilterContext;
use Illuminate\Database\Eloquent\Builder;

final readonly class ExactFilter implements Filter
{
    public function __construct(private string $column) {}

    public static function column(string $column): self
    {
        return new self($column);
    }

    public function apply(Builder $query, mixed $value, FilterContext $context): Builder
    {
        return $query->where($query->getModel()->qualifyColumn($this->column), $value);
    }
}
