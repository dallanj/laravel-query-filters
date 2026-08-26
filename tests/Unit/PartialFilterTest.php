<?php

declare(strict_types=1);

use Dallanj\QueryFilters\Enums\WildcardMode;
use Dallanj\QueryFilters\Filters\PartialFilter;

it('builds explicit wildcard patterns', function (): void {
    expect((new PartialFilter('name'))->pattern('50%_off'))->toBe('%50\\%\\_off%')
        ->and((new PartialFilter('name', WildcardMode::Prefix))->pattern('foo'))->toBe('foo%')
        ->and((new PartialFilter('name', WildcardMode::Pattern))->pattern('foo%'))->toBe('foo%');
});
