<?php

declare(strict_types=1);

namespace Dallanj\QueryFilters\Search;

use Dallanj\QueryFilters\Contracts\SearchTarget;
use Dallanj\QueryFilters\Enums\SearchBoolean;
use Illuminate\Database\Eloquent\Builder;

final readonly class Relation implements SearchTarget
{
    public function __construct(
        public string $relation,
        public string $column,
    ) {}

    public function apply(Builder $query, string $pattern, SearchBoolean $boolean): void
    {
        $method = $boolean === SearchBoolean::Or ? 'orWhereHas' : 'whereHas';

        $query->{$method}(
            $this->relation,
            fn (Builder $relationQuery): Builder => $relationQuery->whereLike(
                $relationQuery->getModel()->qualifyColumn($this->column),
                $pattern,
            ),
        );
    }
}
