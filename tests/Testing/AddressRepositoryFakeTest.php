<?php

declare(strict_types=1);

use Katalam\OnOfficeAdapter\Facades\AddressRepository;
use Katalam\OnOfficeAdapter\Facades\EstateRepository;
use Katalam\OnOfficeAdapter\Facades\Testing\EstateRepositoryFake;
use Katalam\OnOfficeAdapter\Facades\Testing\RecordFactories\AddressFactory;

describe('get', function () {
    it('can be faked', function () {
        $fake = EstateRepository::fake();

        expect($fake)->toBeInstanceOf(EstateRepositoryFake::class);
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
