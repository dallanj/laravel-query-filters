<?php

declare(strict_types=1);

namespace Dallanj\QueryFilters;

final readonly class FilterInput
{
    /** @param array<string, mixed> $values */
    public function __construct(private array $values) {}

    /** @param array<string, mixed> $values */
    public static function from(array $values): self
    {
        return new self($values);
    }

    public function hasValue(string $key): bool
    {
        if (! array_key_exists($key, $this->values)) {
            return false;
        }

        $value = $this->values[$key];

        return $value !== null && $value !== '' && $value !== [];
    }

    public function value(string $key): mixed
    {
        return $this->values[$key] ?? null;
    }

    /** @return array<string, mixed> */
    public function all(): array
    {
        return $this->values;
    }
}
