<?php

declare(strict_types=1);

namespace Dallanj\QueryFilters\Contracts;

use Dallanj\QueryFilters\Enums\SearchBoolean;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

interface SearchTarget
{
    /**
     * @template TModel of Model
     *
     * @param  Builder<TModel>  $query
     */
    public function apply(Builder $query, string $pattern, SearchBoolean $boolean): void;
}
