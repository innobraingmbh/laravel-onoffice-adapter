<?php

use Illuminate\Support\Facades\Http;
use Katalam\OnOfficeAdapter\Facades\EstateRepository;
use Katalam\OnOfficeAdapter\Tests\Stubs\ReadEstateResponse;

it('can get all', function () {
    Http::preventStrayRequests();
    Http::fake([
        '*' => Http::sequence([
            // Each response will have 600 estates to simulate pagination
            ReadEstateResponse::make(estateId: 1, count: 600),
            ReadEstateResponse::make(estateId: 2, count: 600),
            ReadEstateResponse::make(estateId: 3, count: 600),
        ]),
    ]);

    $estates = EstateRepository::all();

    expect($estates)
        ->toHaveCount(3)
        ->and($estates->first()['id'])->toBe(1)
        ->and($estates->last()['id'])->toBe(3);
});
