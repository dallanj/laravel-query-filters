<?php

declare(strict_types=1);

namespace Dallanj\QueryFilters\Filters;

use Dallanj\QueryFilters\Concerns\ResolvesOperator;
use Dallanj\QueryFilters\Contracts\OperableFilter;
use Dallanj\QueryFilters\Enums\WildcardMode;
use Dallanj\QueryFilters\FilterContext;
use Illuminate\Database\Eloquent\Builder;

final readonly class TextFilter implements OperableFilter
{
    use ResolvesOperator;

    private const array OPERATORS = ['contains', 'does_not_contain', 'equals', 'starts_with', 'ends_with'];

    public function __construct(
        private string $column,
        private WildcardMode $wildcardMode = WildcardMode::Literal,
    ) {}

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
        return 'contains';
    }

    public function apply(Builder $query, mixed $value, FilterContext $context): Builder
    {
        $column = $query->getModel()->qualifyColumn($this->column);
        $value = (string) $value;

        return match ($this->resolveOperator($context)) {
            'does_not_contain' => $query->whereNotLike($column, $this->containsPattern($value)),
            'equals' => $query->where($column, $value),
            'starts_with' => $query->whereLike($column, $this->startsWithPattern($value)),
            'ends_with' => $query->whereLike($column, $this->endsWithPattern($value)),
            default => $query->whereLike($column, $this->containsPattern($value)),
        };
    }

    /** @return array{type: string, operators: list<string>} */
    public function describe(): array
    {
        return ['type' => 'text', 'operators' => self::OPERATORS];
    }

    private function containsPattern(string $value): string
    {
        return match ($this->wildcardMode) {
            WildcardMode::Literal => '%'.$this->escape($value).'%',
            WildcardMode::Pattern => $value,
            WildcardMode::Prefix => $this->escape($value).'%',
        };
    }

    private function startsWithPattern(string $value): string
    {
        return $this->escape($value).'%';
    }

    private function endsWithPattern(string $value): string
    {
        return '%'.$this->escape($value);
    }

    private function escape(string $value): string
    {
        return addcslashes($value, '%_\\');
    }
}
