<div align="center">
    <h1>Laravel Query Filters</h1>
</div>

<p align="center">
    <a href="https://packagist.org/packages/dallanj/laravel-query-filters"><img src="https://img.shields.io/packagist/v/dallanj/laravel-query-filters.svg?style=flat-square" alt="Packagist"></a>
    <a href="https://packagist.org/packages/dallanj/laravel-query-filters"><img src="https://img.shields.io/packagist/php-v/dallanj/laravel-query-filters.svg?style=flat-square" alt="PHP from Packagist"></a>
    <a href="https://packagist.org/packages/dallanj/laravel-query-filters"><img src="https://badge.laravel.cloud/badge/dallanj/laravel-query-filters?style=flat" alt="Laravel versions"></a>
    <a href="https://github.com/dallanj/laravel-query-filters/actions"><img alt="GitHub Workflow Status (main)" src="https://img.shields.io/github/actions/workflow/status/dallanj/laravel-query-filters/tests.yml?branch=main&label=Tests&style=flat-square"></a>
    <a href="https://packagist.org/packages/dallanj/laravel-query-filters"><img src="https://img.shields.io/packagist/dt/dallanj/laravel-query-filters.svg?style=flat-square" alt="Total Downloads"></a>
</p>

A composable, allowlist-based filtering pipeline for Laravel Eloquent queries.

## Installation

You can install the package via Composer:

```bash
composer require dallanj/laravel-query-filters:^0.1
```

The package does not require a service provider, configuration file, or published
resources.

## Usage

Create a `FilterSet` that maps accepted request parameters to filter behavior, then
apply validated input to an Eloquent builder:

```php
use App\Models\JobApplication;
use Dallanj\QueryFilters\FilterSet;
use Dallanj\QueryFilters\Filters\DateFilter;
use Dallanj\QueryFilters\Filters\ExactFilter;
use Dallanj\QueryFilters\Filters\PartialFilter;
use Dallanj\QueryFilters\Filters\SearchFilter;

$filters = FilterSet::make([
    'search' => SearchFilter::make(),
    'company_id' => ExactFilter::column('company_id'),
    'status' => ExactFilter::column('status'),
    'location' => PartialFilter::column('location'),
    'date_from' => DateFilter::column('applied_at')->greaterThanOrEqual(),
    'date_to' => DateFilter::column('applied_at')->lessThanOrEqual(),
]);

$applications = $filters
    ->apply(JobApplication::query(), $request->validated())
    ->latest('applied_at')
    ->paginate();
```

The array keys are the only request parameters that may alter the query. Unknown
parameters are ignored, and column names come from application code rather than user
input.

### Built-in filters

| Filter | Behavior | Example |
| --- | --- | --- |
| `ExactFilter` | Adds a qualified equality condition | `ExactFilter::column('status')` |
| `PartialFilter` | Adds a qualified `LIKE` condition | `PartialFilter::column('location')` |
| `DateFilter` | Compares the date portion of a column | `DateFilter::column('created_at')->lessThanOrEqual()` |
| `SearchFilter` | Searches the columns and relationships declared by the model | `SearchFilter::make()` |

Filters are composed in declaration order and return the same lazy Eloquent builder.
You may continue chaining scopes, ordering, eager loading, pagination, or execution
after `apply()`.

### Searchable models

A model used with `SearchFilter` must implement `Searchable`. The supplied trait
provides convenient factories for column and relationship targets:

```php
namespace App\Models;

use Dallanj\QueryFilters\Concerns\HasSearchableFields;
use Dallanj\QueryFilters\Contracts\Searchable;
use Illuminate\Database\Eloquent\Model;

final class JobApplication extends Model implements Searchable
{
    use HasSearchableFields;

    public function searchableFields(): array
    {
        return [
            $this->searchColumn('role_title'),
            $this->searchRelation('company', 'name'),
        ];
    }
}
```

The targets are grouped into one nested `where` clause and combined with `OR`. This
keeps the search expression correctly grouped when it is composed with other filters.
Relationship names may use Eloquent's dot notation for nested relationships.

Using `SearchFilter` on a model that does not implement `Searchable` throws an
`InvalidArgumentException` with the model and required contract names.

### Wildcard modes

`PartialFilter` and `SearchFilter` default to `WildcardMode::Literal`. User-provided
`%`, `_`, and backslash characters are escaped and the value is matched anywhere in
the field.

```php
use Dallanj\QueryFilters\Enums\WildcardMode;
use Dallanj\QueryFilters\Filters\PartialFilter;
use Dallanj\QueryFilters\Filters\SearchFilter;

$containsLiteralText = new PartialFilter('location', WildcardMode::Literal);
$startsWithLiteralText = new PartialFilter('location', WildcardMode::Prefix);
$acceptsSqlWildcards = new PartialFilter('location', WildcardMode::Pattern);

$prefixSearch = new SearchFilter(WildcardMode::Prefix);
```

Use `Pattern` only when callers are intentionally allowed to provide SQL wildcard
patterns.

### Value presence

The package uses explicit presence rules instead of PHP truthiness:

| Input value | Applied? |
| --- | --- |
| Missing key | No |
| `null` | No |
| `''` | No |
| `[]` | No |
| `0` or `'0'` | Yes |
| `false` | Yes |

You can pass an array directly or construct a `FilterInput` explicitly:

```php
use Dallanj\QueryFilters\FilterInput;

$query = $filters->apply(
    JobApplication::query(),
    FilterInput::from($request->validated()),
);
```

### Custom filters

Application-specific filters can implement the `Filter` contract. The value is
provided separately from the filter definition, and `FilterContext` exposes the
current parameter name and the complete input array:

```php
namespace App\QueryFilters;

use Dallanj\QueryFilters\Contracts\Filter;
use Dallanj\QueryFilters\FilterContext;
use Illuminate\Database\Eloquent\Builder;

final class HasUpcomingInterview implements Filter
{
    public function apply(
        Builder $query,
        mixed $value,
        FilterContext $context,
    ): Builder {
        return $query->whereHas(
            'interviews',
            fn (Builder $interviews): Builder => $interviews
                ->whereFuture('scheduled_at'),
        );
    }
}
```

Register it exactly like a built-in filter:

```php
$filters = FilterSet::make([
    'has_upcoming_interview' => new HasUpcomingInterview(),
]);
```

### Custom search targets

Implement `SearchTarget` when a search must cover JSON data, translated fields,
database-specific expressions, or another target not represented by `Column` and
`Relation`:

```php
use Dallanj\QueryFilters\Contracts\SearchTarget;
use Dallanj\QueryFilters\Enums\SearchBoolean;
use Illuminate\Database\Eloquent\Builder;

final class JsonSearchTarget implements SearchTarget
{
    public function __construct(private readonly string $column) {}

    public function apply(
        Builder $query,
        string $pattern,
        SearchBoolean $boolean,
    ): void {
        $method = $boolean === SearchBoolean::Or ? 'orWhere' : 'where';

        $query->{$method}("{$this->column}->en", 'like', $pattern);
    }
}
```

Return the custom target from the model alongside the supplied targets:

```php
public function searchableFields(): array
{
    return [
        $this->searchColumn('name'),
        new JsonSearchTarget('description'),
    ];
}
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Thank you for considering contributing to Laravel Query Filters! Please review our [contributing guide](.github/CONTRIBUTING.md) to get started.

## Security Vulnerabilities

Please review [our security policy](.github/SECURITY.md) on how to report security vulnerabilities.

## Credits

- [Dallan Jones](https://github.com/dallanj)
- [All Contributors](../../contributors)

## License

Laravel Query Filters is open-sourced software licensed under the [MIT license](LICENSE.md).
