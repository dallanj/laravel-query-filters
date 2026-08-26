<?php

declare(strict_types=1);

namespace Dallanj\QueryFilters\Filters;

use Dallanj\QueryFilters\Contracts\Filter;
use Dallanj\QueryFilters\Enums\WildcardMode;
use Dallanj\QueryFilters\FilterContext;
use Illuminate\Database\Eloquent\Builder;

final readonly class PartialFilter implements Filter
{
    public function __construct(
        private string $column,
        private WildcardMode $wildcardMode = WildcardMode::Literal,
    ) {}

    public static function column(string $column): self
    {
        return new self($column);
    }

    public function apply(Builder $query, mixed $value, FilterContext $context): Builder
    {
        return $query->whereLike(
            $query->getModel()->qualifyColumn($this->column),
            $this->pattern((string) $value),
        );
    }

    public function pattern(string $value): string
    {
        return match ($this->wildcardMode) {
            WildcardMode::Literal => '%'.addcslashes($value, '%_\\').'%',
            WildcardMode::Pattern => $value,
            WildcardMode::Prefix => addcslashes($value, '%_\\').'%',
        };
    }
}
