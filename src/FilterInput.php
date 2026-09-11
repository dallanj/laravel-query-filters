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

        $value = $this->unwrap($this->values[$key]);

        return $value !== null && $value !== '' && $value !== [];
    }

    public function value(string $key): mixed
    {
        return $this->unwrap($this->values[$key] ?? null);
    }

    public function operator(string $key): ?string
    {
        $raw = $this->values[$key] ?? null;

        if (is_array($raw) && array_key_exists('value', $raw) && is_string($raw['operator'] ?? null)) {
            return $raw['operator'];
        }

        return null;
    }

    /** @return array<string, mixed> */
    public function all(): array
    {
        return $this->values;
    }

    private function unwrap(mixed $raw): mixed
    {
        if (is_array($raw) && array_key_exists('value', $raw)) {
            return $raw['value'];
        }

        return $raw;
    }
}
