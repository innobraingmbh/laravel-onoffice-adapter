<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Katalam\OnOfficeAdapter\Facades\AddressRepository;
use Katalam\OnOfficeAdapter\Query\AddressBuilder;
use Katalam\OnOfficeAdapter\Services\OnOfficeService;
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

describe('recordIds', function () {
    it('should set the recordIds property to the given recordIds', function () {
        $builder = new AddressBuilder(app(OnOfficeService::class));

        $builder->recordIds([1]);

        expect($builder->recordIds)->toBe([1]);
    });

    it('should wrap the given recordIds in an array if it is a int', function () {
        $builder = new AddressBuilder(app(OnOfficeService::class));

        $builder->recordIds(1);

        expect($builder->recordIds)->toBe([1]);
    });

    it('should return the builder instance', function () {
        $builder = new AddressBuilder(app(OnOfficeService::class));

        $result = $builder->recordIds([1]);

        expect($result)->toBeInstanceOf(AddressBuilder::class);
    });

    it('should add the given recordId to the recordIds property', function () {
        $builder = new AddressBuilder(app(OnOfficeService::class));

        $builder->recordIds([1]);
        $builder->addRecordIds([2]);

        expect($builder->recordIds)->toBe([1, 2]);
    });

    it('should wrap the given recordId in an array if it is a int', function () {
        $builder = new AddressBuilder(app(OnOfficeService::class));

        $builder->recordIds([1]);
        $builder->addRecordIds(2);

        expect($builder->recordIds)->toBe([1, 2]);
    });
});

describe('customParameters', function () {
    it('should set the country iso code type property to the given type', function () {
        $builder = new AddressBuilder(app(OnOfficeService::class));

        $builder->addCountryIsoCodeType('ISO-3166-2');

        expect($builder->customParameters['countryIsoCodeType'])->toBe('ISO-3166-2');
    });
});
