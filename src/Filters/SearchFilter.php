<?php

declare(strict_types=1);

namespace Dallanj\QueryFilters\Filters;

use Dallanj\QueryFilters\Contracts\Filter;
use Dallanj\QueryFilters\Contracts\Searchable;
use Dallanj\QueryFilters\Enums\SearchBoolean;
use Dallanj\QueryFilters\Enums\WildcardMode;
use Dallanj\QueryFilters\FilterContext;
use Illuminate\Database\Eloquent\Builder;
use InvalidArgumentException;

final readonly class SearchFilter implements Filter
{
    public function __construct(private WildcardMode $wildcardMode = WildcardMode::Literal) {}

    public static function make(): self
    {
        return new self;
    }

    public function apply(Builder $query, mixed $value, FilterContext $context): Builder
    {
        $model = $query->getModel();

        if (! $model instanceof Searchable) {
            throw new InvalidArgumentException(sprintf(
                '%s must implement %s to use %s.',
                $model::class,
                Searchable::class,
                self::class,
            ));
        }

        $pattern = (new PartialFilter('', $this->wildcardMode))->pattern((string) $value);

        return $query->where(function (Builder $query) use ($model, $pattern): void {
            foreach ($model->searchableFields() as $index => $target) {
                $target->apply($query, $pattern, $index === 0 ? SearchBoolean::And : SearchBoolean::Or);
            }
        });
    }
}
