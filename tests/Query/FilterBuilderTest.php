<?php

declare(strict_types=1);

use Innobrain\OnOfficeAdapter\Repositories\FilterRepository;
use Innobrain\OnOfficeAdapter\Query\FilterBuilder;

describe('withModule', function () {
    it('should set the module property to the given module', function () {
        $builder = new FilterBuilder;
        $builder->setRepository(app(FilterRepository::class));

        $builder->estate();

        expect($builder->module)->toBe('estate');
    });

    it('should return the builder instance', function () {
        $builder = new FilterBuilder;
        $builder->setRepository(app(FilterRepository::class));

        $result = $builder->address();

        expect($result)->toBeInstanceOf(FilterBuilder::class);
    });

    it('should overwrite module in the module property', function () {
        $builder = new FilterBuilder;
        $builder->setRepository(app(FilterRepository::class));

        $builder->estate();
        $builder->address();

        expect($builder->module)->toBe('address');
    });
});
