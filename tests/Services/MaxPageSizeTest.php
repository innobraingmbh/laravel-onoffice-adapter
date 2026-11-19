<?php

declare(strict_types=1);

use Innobrain\OnOfficeAdapter\Facades\EstateRepository;
use Innobrain\OnOfficeAdapter\Services\OnOfficeService;

it('exposes the onOffice page-size cap as a single named constant', function () {
    expect(OnOfficeService::MAX_PAGE_SIZE)->toBe(500);
});

it('defaults a builder page size to the max page size', function () {
    expect(EstateRepository::query()->pageSize)
        ->toBe(OnOfficeService::MAX_PAGE_SIZE);
});

it('caps pageSize() at the max page size', function () {
    $builder = EstateRepository::query()->pageSize(10_000);

    expect($builder->pageSize)->toBe(OnOfficeService::MAX_PAGE_SIZE);
});

it('clamps pageSize() to a minimum of one', function () {
    expect(EstateRepository::query()->pageSize(0)->pageSize)->toBe(1);
});
