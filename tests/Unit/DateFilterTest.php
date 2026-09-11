<?php

declare(strict_types=1);

use Dallanj\QueryFilters\Filters\DateFilter;
use Dallanj\QueryFilters\FilterSet;
use Dallanj\QueryFilters\Tests\Fixtures\Record;

beforeEach(function (): void {
    Record::query()->create(['title' => 'Early', 'status' => 'open', 'published_at' => '2026-08-01']);
    Record::query()->create(['title' => 'Mid', 'status' => 'open', 'published_at' => '2026-08-05']);
    Record::query()->create(['title' => 'Late', 'status' => 'open', 'published_at' => '2026-08-10']);
});

it('keeps the existing fixed-operator behavior unchanged when no operator is requested', function (): void {
    $titles = FilterSet::make(['from' => DateFilter::column('published_at')->greaterThanOrEqual()])
        ->apply(Record::query(), ['from' => '2026-08-05'])
        ->orderBy('published_at')
        ->pluck('title')->all();

    expect($titles)->toBe(['Mid', 'Late']);
});

it('ignores an unrecognized requested operator and keeps the fixed construction-time operator', function (): void {
    $titles = FilterSet::make(['from' => DateFilter::column('published_at')->greaterThanOrEqual()])
        ->apply(Record::query(), ['from' => ['operator' => 'drop_table', 'value' => '2026-08-05']])
        ->orderBy('published_at')
        ->pluck('title')->all();

    expect($titles)->toBe(['Mid', 'Late']);
});

it('applies each allowed request operator when explicitly requested', function (string $operator, mixed $value, array $expected): void {
    $titles = FilterSet::make(['at' => DateFilter::column('published_at')])
        ->apply(Record::query(), ['at' => ['operator' => $operator, 'value' => $value]])
        ->orderBy('published_at')
        ->pluck('title')->all();

    expect($titles)->toBe($expected);
})->with([
    'equals' => ['equals', '2026-08-05', ['Mid']],
    'before' => ['before', '2026-08-05', ['Early']],
    'after' => ['after', '2026-08-05', ['Late']],
    'between' => ['between', ['2026-08-01', '2026-08-05'], ['Early', 'Mid']],
]);

it('describes itself with type and allowed request operators', function (): void {
    expect(DateFilter::column('published_at')->describe())->toBe([
        'type' => 'date',
        'operators' => ['equals', 'before', 'after', 'between'],
    ]);
});
