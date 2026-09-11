<?php

declare(strict_types=1);

use Dallanj\QueryFilters\Contracts\OperableFilter;
use Dallanj\QueryFilters\Filters\DateFilter;
use Dallanj\QueryFilters\Filters\ExactFilter;
use Dallanj\QueryFilters\Filters\NumericFilter;
use Dallanj\QueryFilters\Filters\PartialFilter;
use Dallanj\QueryFilters\Filters\TextFilter;
use Dallanj\QueryFilters\FilterSet;
use Dallanj\QueryFilters\Tests\Fixtures\Record;

beforeEach(function (): void {
    Record::query()->create([
        'title' => 'Senior Engineer', 'status' => 'open', 'location' => 'Calgary',
        'published_at' => '2026-08-01', 'priority' => 5,
    ]);
});

it('never lets a crafted operator reach the query for any operable filter', function (OperableFilter $filter, string $key, mixed $value): void {
    $safeSql = FilterSet::make([$key => $filter])
        ->apply(Record::query(), [$key => $value])
        ->toSql();

    $craftedSql = FilterSet::make([$key => $filter])
        ->apply(Record::query(), [$key => ['operator' => "1) OR ('1'='1", 'value' => $value]])
        ->toSql();

    expect($craftedSql)->toBe($safeSql);
})->with([
    'text' => [TextFilter::column('title'), 'title', 'Engineer'],
    'numeric' => [NumericFilter::column('priority'), 'priority', 5],
    'date' => [DateFilter::column('published_at'), 'published_at', '2026-08-01'],
]);

it('leaves existing single-operator filters untouched by the new request shape', function (): void {
    $titles = FilterSet::make([
        'status' => ExactFilter::column('status'),
        'location' => PartialFilter::column('location'),
        'from' => DateFilter::column('published_at')->greaterThanOrEqual(),
    ])->apply(Record::query(), [
        'status' => 'open',
        'location' => 'alg',
        'from' => '2026-08-01',
    ])->pluck('title')->all();

    expect($titles)->toBe(['Senior Engineer']);
});
