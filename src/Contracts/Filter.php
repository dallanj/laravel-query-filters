<?php

declare(strict_types=1);

namespace Dallanj\QueryFilters\Contracts;

use Dallanj\QueryFilters\FilterContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

interface Filter
{
    /**
     * @template TModel of Model
     *
     * @param  Builder<TModel>  $query
     * @return Builder<TModel>
     */
    public function apply(Builder $query, mixed $value, FilterContext $context): Builder;
}
