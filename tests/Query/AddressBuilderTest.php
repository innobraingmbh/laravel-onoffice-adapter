<?php

use Illuminate\Support\Facades\Http;
use Katalam\OnOfficeAdapter\Facades\AddressRepository;
use Katalam\OnOfficeAdapter\Tests\Stubs\ReadAddressResponse;

it('works', function () {
    Http::preventStrayRequests();
    Http::fake([
        '*' => Http::sequence([
            // Each response will have 600 estates to simulate pagination
            ReadAddressResponse::make(addressId: 1, count: 1500),
            ReadAddressResponse::make(addressId: 2, count: 1500),
            ReadAddressResponse::make(addressId: 3, count: 1500),
        ]),
    ]);

    $addresses = AddressRepository::query()
        ->get();

    expect($addresses)
        ->toHaveCount(3)
        ->and($addresses->first()['id'])->toBe(1)
        ->and($addresses->last()['id'])->toBe(3);
});
