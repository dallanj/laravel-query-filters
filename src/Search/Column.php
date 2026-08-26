<?php

declare(strict_types=1);

namespace Dallanj\QueryFilters\Search;

use Dallanj\QueryFilters\Contracts\SearchTarget;
use Dallanj\QueryFilters\Enums\SearchBoolean;
use Illuminate\Database\Eloquent\Builder;

final readonly class Column implements SearchTarget
{
    public function __construct(public string $column) {}

    public function apply(Builder $query, string $pattern, SearchBoolean $boolean): void
    {
        $method = $boolean === SearchBoolean::Or ? 'orWhereLike' : 'whereLike';
        $query->{$method}($query->getModel()->qualifyColumn($this->column), $pattern);
    }
}
