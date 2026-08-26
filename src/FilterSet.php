<?php

declare(strict_types=1);

namespace Dallanj\QueryFilters;

use Dallanj\QueryFilters\Contracts\Filter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

final readonly class FilterSet
{
    /** @param array<string, Filter> $filters */
    private function __construct(private array $filters) {}

    /** @param array<string, Filter> $filters */
    public static function make(array $filters): self
    {
        return new self($filters);
    }

    /**
     * @template TModel of Model
     *
     * @param  Builder<TModel>  $query
     * @param  array<string, mixed>|FilterInput  $input
     * @return Builder<TModel>
     */
    public function apply(Builder $query, array|FilterInput $input): Builder
    {
        $input = is_array($input) ? FilterInput::from($input) : $input;

        foreach ($this->filters as $parameter => $filter) {
            if (! $input->hasValue($parameter)) {
                continue;
            }

            $query = $filter->apply(
                $query,
                $input->value($parameter),
                new FilterContext($parameter, $input->all()),
            );
        }

        return $query;
    }
}
