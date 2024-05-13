<?php

use Illuminate\Support\Facades\Http;
use Katalam\OnOfficeAdapter\Facades\UserRepository;
use Katalam\OnOfficeAdapter\Tests\Stubs\ReadUserResponse;

it('works', function () {
    Http::preventStrayRequests();
    Http::fake([
        '*' => Http::sequence([
            // Each response will have 1500 users to simulate pagination
            ReadUserResponse::make(userId: 1, count: 1500),
            ReadUserResponse::make(userId: 2, count: 1500),
            ReadUserResponse::make(userId: 3, count: 1500),
        ]),
    ]);

    $estates = UserRepository::query()
        ->get();

    expect($estates)
        ->toHaveCount(3)
        ->and($estates->first()['id'])->toBe(1)
        ->and($estates->last()['id'])->toBe(3);
});
