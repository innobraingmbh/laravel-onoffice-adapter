<?php

declare(strict_types=1);

use Katalam\OnOfficeAdapter\Query\AddressBuilder;
use Katalam\OnOfficeAdapter\Repositories\AddressRepository;

describe('recordIds', function () {
    it('should set the recordIds property to the given recordIds', function () {
        $builder = new AddressBuilder;
        $builder->setRepository(app(AddressRepository::class));

        $builder->recordIds([1]);

        expect($builder->recordIds)->toBe([1]);
    });

    it('should wrap the given recordIds in an array if it is a int', function () {
        $builder = new AddressBuilder;
        $builder->setRepository(app(AddressRepository::class));

        $builder->recordIds(1);

        expect($builder->recordIds)->toBe([1]);
    });

    it('should return the builder instance', function () {
        $builder = new AddressBuilder;
        $builder->setRepository(app(AddressRepository::class));

        $result = $builder->recordIds([1]);

        expect($result)->toBeInstanceOf(AddressBuilder::class);
    });

    it('should add the given recordId to the recordIds property', function () {
        $builder = new AddressBuilder;
        $builder->setRepository(app(AddressRepository::class));

        $builder->recordIds([1]);
        $builder->addRecordIds([2]);

        expect($builder->recordIds)->toBe([1, 2]);
    });

    it('should wrap the given recordId in an array if it is a int', function () {
        $builder = new AddressBuilder;
        $builder->setRepository(app(AddressRepository::class));

        $builder->recordIds([1]);
        $builder->addRecordIds(2);

        expect($builder->recordIds)->toBe([1, 2]);
    });
});

describe('customParameters', function () {
    it('should set the country iso code type property to the given type', function () {
        $builder = new AddressBuilder;
        $builder->setRepository(app(AddressRepository::class));

        $builder->addCountryIsoCodeType('ISO-3166-2');

        expect($builder->customParameters['countryIsoCodeType'])->toBe('ISO-3166-2');
    });
});
