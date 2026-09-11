<?php

declare(strict_types=1);

use Dallanj\QueryFilters\Filters\NumericFilter;
use Dallanj\QueryFilters\FilterSet;
use Dallanj\QueryFilters\Tests\Fixtures\Record;

beforeEach(function (): void {
    Record::query()->create(['title' => 'Low', 'status' => 'open', 'priority' => 1]);
    Record::query()->create(['title' => 'Mid', 'status' => 'open', 'priority' => 5]);
    Record::query()->create(['title' => 'High', 'status' => 'open', 'priority' => 9]);
});

it('defaults to equals when no operator is requested', function (): void {
    $titles = FilterSet::make(['priority' => NumericFilter::column('priority')])
        ->apply(Record::query(), ['priority' => 5])
        ->pluck('title')->all();

    expect($titles)->toBe(['Mid']);
});

it('applies each allowed operator when explicitly requested', function (string $operator, mixed $value, array $expected): void {
    $titles = FilterSet::make(['priority' => NumericFilter::column('priority')])
        ->apply(Record::query(), ['priority' => ['operator' => $operator, 'value' => $value]])
        ->orderBy('priority')
        ->pluck('title')->all();

    expect($titles)->toBe($expected);
})->with([
    'equals' => ['equals', 5, ['Mid']],
    'gt' => ['gt', 5, ['High']],
    'lt' => ['lt', 5, ['Low']],
    'between' => ['between', [2, 9], ['Mid', 'High']],
]);

it('falls back to the default operator when an unrecognized operator is requested', function (): void {
    $titles = FilterSet::make(['priority' => NumericFilter::column('priority')])
        ->apply(Record::query(), ['priority' => ['operator' => 'drop_table', 'value' => 5]])
        ->pluck('title')->all();

    expect($titles)->toBe(['Mid']);
});

it('describes itself with type and allowed operators', function (): void {
    expect(NumericFilter::column('priority')->describe())->toBe([
        'type' => 'numeric',
        'operators' => ['equals', 'gt', 'lt', 'between'],
    ]);
});
