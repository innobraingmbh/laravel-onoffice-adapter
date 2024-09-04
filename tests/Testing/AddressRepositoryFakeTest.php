<?php

declare(strict_types=1);

use Katalam\OnOfficeAdapter\Facades\AddressRepository;
use Katalam\OnOfficeAdapter\Facades\Testing\AddressRepositoryFake;
use Katalam\OnOfficeAdapter\Facades\Testing\RecordFactories\AddressFactory;

describe('get', function () {
    it('can be faked', function () {
        $fake = AddressRepository::fake();

        expect($fake)->toBeInstanceOf(AddressRepositoryFake::class);
    });

    it('can count', function () {
        AddressRepository::fake([
            [
                AddressFactory::make(),
            ],
        ]);

        $fake = AddressRepository::query()
            ->count();

        expect($fake)->toBe(1);
    });
});

describe('nullable first', function () {
    it('can fake a null return', function () {
        AddressRepository::fake([
            [
                null,
            ],
        ]);

        $fake = AddressRepository::query()
            ->first();

        expect($fake)->toBeNull();
    });
});
