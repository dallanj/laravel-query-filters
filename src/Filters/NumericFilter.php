<?php

declare(strict_types=1);

namespace Dallanj\QueryFilters\Filters;

use Dallanj\QueryFilters\Concerns\ResolvesOperator;
use Dallanj\QueryFilters\Contracts\OperableFilter;
use Dallanj\QueryFilters\FilterContext;
use Illuminate\Database\Eloquent\Builder;

final readonly class NumericFilter implements OperableFilter
{
    use ResolvesOperator;

    private const array OPERATORS = ['equals', 'gt', 'lt', 'between'];

    public function __construct(private string $column) {}

    public static function column(string $column): self
    {
        return new self($column);
    }

    /** @return list<string> */
    public function allowedOperators(): array
    {
        return self::OPERATORS;
    }

    public function defaultOperator(): string
    {
        return 'equals';
    }

    public function apply(Builder $query, mixed $value, FilterContext $context): Builder
    {
        $column = $query->getModel()->qualifyColumn($this->column);

        return match ($this->resolveOperator($context)) {
            'gt' => $query->where($column, '>', $value),
            'lt' => $query->where($column, '<', $value),
            'between' => $this->applyBetween($query, $column, $value),
            default => $query->where($column, '=', $value),
        };
    }

    /** @return array{type: string, operators: list<string>} */
    public function describe(): array
    {
        return ['type' => 'numeric', 'operators' => self::OPERATORS];
    }

    /**
     * @template TModel of \Illuminate\Database\Eloquent\Model
     *
     * @param  Builder<TModel>  $query
     * @return Builder<TModel>
     */
    private function applyBetween(Builder $query, string $column, mixed $value): Builder
    {
        if (! is_array($value) || count($value) !== 2) {
            return $query;
        }

        return $query->whereBetween($column, array_values($value));
    }
}
