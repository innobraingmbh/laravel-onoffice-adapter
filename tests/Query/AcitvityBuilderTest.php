<?php

use Illuminate\Support\Facades\Http;
use Katalam\OnOfficeAdapter\Facades\ActivityRepository;
use Katalam\OnOfficeAdapter\Facades\AddressRepository;
use Katalam\OnOfficeAdapter\Query\AddressBuilder;
use Katalam\OnOfficeAdapter\Services\OnOfficeService;
use Katalam\OnOfficeAdapter\Tests\Stubs\ReadActivityResponse;
use Katalam\OnOfficeAdapter\Tests\Stubs\ReadAddressResponse;

it('works', function () {
    Http::preventStrayRequests();
    Http::fake([
        '*' => Http::sequence([
            ReadActivityResponse::make(activityId: 1, count: 1500),
            ReadActivityResponse::make(activityId: 2, count: 1500),
            ReadActivityResponse::make(activityId: 3, count: 1500),
        ]),
    ]);

    $activities = ActivityRepository::query()
        ->get();

    expect($activities)
        ->toHaveCount(3)
        ->and($activities->first()['id'])->toBe(1)
        ->and($activities->last()['id'])->toBe(3);
});
