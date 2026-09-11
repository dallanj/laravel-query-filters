<?php

declare(strict_types=1);

namespace Dallanj\QueryFilters\Filters;

use Dallanj\QueryFilters\Contracts\OperableFilter;
use Dallanj\QueryFilters\FilterContext;
use Illuminate\Database\Eloquent\Builder;
use InvalidArgumentException;

final readonly class DateFilter implements OperableFilter
{
    private const array SQL_OPERATORS = ['=', '<', '<=', '>', '>='];

    /** @var list<string> */
    private const array REQUEST_OPERATORS = ['equals', 'before', 'after', 'between'];

    public function __construct(
        private string $column,
        private string $operator = '=',
    ) {
        if (! in_array($operator, self::SQL_OPERATORS, true)) {
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

    /** @return list<string> */
    public function allowedOperators(): array
    {
        return self::REQUEST_OPERATORS;
    }

    public function defaultOperator(): string
    {
        return match ($this->operator) {
            '=' => 'equals',
            '<', '<=' => 'before',
            '>', '>=' => 'after',
            default => 'equals',
        };
    }

    public function apply(Builder $query, mixed $value, FilterContext $context): Builder
    {
        $column = $query->getModel()->qualifyColumn($this->column);
        $requested = $context->operator;

        if ($requested !== null && in_array($requested, self::REQUEST_OPERATORS, true)) {
            return match ($requested) {
                'equals' => $query->whereDate($column, '=', $value),
                'before' => $query->whereDate($column, '<', $value),
                'after' => $query->whereDate($column, '>', $value),
                'between' => $this->applyBetween($query, $column, $value),
            };
        }

        return $query->whereDate($column, $this->operator, $value);
    }

    /** @return array{type: string, operators: list<string>} */
    public function describe(): array
    {
        return ['type' => 'date', 'operators' => self::REQUEST_OPERATORS];
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
