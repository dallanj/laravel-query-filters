<?php

declare(strict_types=1);

use Dallanj\QueryFilters\Filters\TextFilter;
use Dallanj\QueryFilters\FilterSet;
use Dallanj\QueryFilters\Tests\Fixtures\Record;

beforeEach(function (): void {
    Record::query()->create(['title' => 'Senior Engineer', 'status' => 'open', 'location' => 'Calgary']);
    Record::query()->create(['title' => 'Designer', 'status' => 'open', 'location' => 'Edmonton']);
});

it('defaults to contains when no operator is requested', function (): void {
    $titles = FilterSet::make(['title' => TextFilter::column('title')])
        ->apply(Record::query(), ['title' => 'Engineer'])
        ->pluck('title')->all();

    expect($titles)->toBe(['Senior Engineer']);
});

it('applies each allowed operator when explicitly requested', function (string $operator, string $value, array $expected): void {
    $titles = FilterSet::make(['title' => TextFilter::column('title')])
        ->apply(Record::query(), ['title' => ['operator' => $operator, 'value' => $value]])
        ->pluck('title')->all();

    expect($titles)->toBe($expected);
})->with([
    'contains' => ['contains', 'Engineer', ['Senior Engineer']],
    'does_not_contain' => ['does_not_contain', 'Engineer', ['Designer']],
    'equals' => ['equals', 'Designer', ['Designer']],
    'starts_with' => ['starts_with', 'Senior', ['Senior Engineer']],
    'ends_with' => ['ends_with', 'Engineer', ['Senior Engineer']],
]);

it('falls back to the default operator when an unrecognized operator is requested', function (): void {
    $titles = FilterSet::make(['title' => TextFilter::column('title')])
        ->apply(Record::query(), ['title' => ['operator' => 'drop_table', 'value' => 'Engineer']])
        ->pluck('title')->all();

    expect($titles)->toBe(['Senior Engineer']);
});

it('describes itself with type and allowed operators', function (): void {
    expect(TextFilter::column('title')->describe())->toBe([
        'type' => 'text',
        'operators' => ['contains', 'does_not_contain', 'equals', 'starts_with', 'ends_with'],
    ]);
});
