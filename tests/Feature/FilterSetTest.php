<?php

declare(strict_types=1);

use Dallanj\QueryFilters\Contracts\Filter;
use Dallanj\QueryFilters\FilterContext;
use Dallanj\QueryFilters\Filters\DateFilter;
use Dallanj\QueryFilters\Filters\ExactFilter;
use Dallanj\QueryFilters\Filters\PartialFilter;
use Dallanj\QueryFilters\Filters\SearchFilter;
use Dallanj\QueryFilters\FilterSet;
use Dallanj\QueryFilters\Tests\Fixtures\Company;
use Dallanj\QueryFilters\Tests\Fixtures\Record;
use Illuminate\Database\Eloquent\Builder;

beforeEach(function (): void {
    $acme = Company::query()->create(['name' => 'Acme Industries']);
    $other = Company::query()->create(['name' => 'Other Corp']);

    Record::query()->create([
        'company_id' => $acme->id, 'title' => 'Senior Engineer', 'status' => 'open',
        'location' => 'Calgary', 'active' => true, 'published_at' => '2026-08-01',
    ]);
    Record::query()->create([
        'company_id' => $other->id, 'title' => 'Designer', 'status' => 'closed',
        'location' => 'Edmonton', 'active' => false, 'published_at' => '2026-07-01',
    ]);
});

it('composes allowlisted filters and ignores unknown input', function (): void {
    $query = FilterSet::make([
        'status' => ExactFilter::column('status'),
        'location' => PartialFilter::column('location'),
        'from' => DateFilter::column('published_at')->greaterThanOrEqual(),
    ])->apply(Record::query(), [
        'status' => 'open',
        'location' => 'alg',
        'from' => '2026-08-01',
        'arbitrary_column' => 'ignored',
    ]);

    expect($query->pluck('title')->all())->toBe(['Senior Engineer']);
});

it('searches grouped columns and relationships', function (string $term, string $expected): void {
    $records = FilterSet::make(['search' => SearchFilter::make()])
        ->apply(Record::query(), ['search' => $term])
        ->pluck('title')
        ->all();

    expect($records)->toBe([$expected]);
})->with([
    ['Engineer', 'Senior Engineer'],
    ['Acme', 'Senior Engineer'],
    ['Other', 'Designer'],
]);

it('treats zero and false as present while skipping null empty strings and arrays', function (): void {
    $seen = [];
    $capture = new class($seen) implements Filter
    {
        /** @param array<int, mixed> $seen */
        public function __construct(private array &$seen) {}

        public function apply(Builder $query, mixed $value, FilterContext $context): Builder
        {
            $this->seen[] = $value;

            return $query;
        }
    };

    FilterSet::make([
        'zero' => $capture, 'string_zero' => $capture, 'false' => $capture,
        'null' => $capture, 'empty' => $capture, 'array' => $capture,
    ])->apply(Record::query(), [
        'zero' => 0, 'string_zero' => '0', 'false' => false,
        'null' => null, 'empty' => '', 'array' => [],
    ]);

    expect($seen)->toBe([0, '0', false]);
});

it('returns a lazy builder', function (): void {
    $query = FilterSet::make(['status' => ExactFilter::column('status')])
        ->apply(Record::query(), ['status' => 'open']);

    expect($query)->toBeInstanceOf(Builder::class)
        ->and($query->toSql())->toContain('where');
});
