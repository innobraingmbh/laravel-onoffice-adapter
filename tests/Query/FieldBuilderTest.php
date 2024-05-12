<?php

use Illuminate\Support\Facades\Http;
use Katalam\OnOfficeAdapter\Facades\FieldRepository;
use Katalam\OnOfficeAdapter\Services\OnOfficeService;
use Katalam\OnOfficeAdapter\Tests\Stubs\GetFieldsResponse;
use Katalam\OnOfficeAdapter\Query\FieldBuilder;

it('works', function () {
    Http::preventStrayRequests();
    Http::fake([
        '*' => Http::sequence([
            GetFieldsResponse::make(),
        ]),
    ]);

    $fields = FieldRepository::query()
        ->get();

    expect($fields)
        ->toHaveCount(13);
});

describe('withModules', function () {
    it('should set the modules property to the given modules', function () {
        $builder = new FieldBuilder(app(OnOfficeService::class));

        $builder->withModules(['estate']);

        expect($builder->modules)->toBe(['estate']);
    });

    it('should wrap the given modules in an array if it is a string', function () {
        $builder = new FieldBuilder(app(OnOfficeService::class));

        $builder->withModules('estate');

        expect($builder->modules)->toBe(['estate']);
    });

    it('should return the builder instance', function () {
        $builder = new FieldBuilder(app(OnOfficeService::class));

        $result = $builder->withModules('estate');

        expect($result)->toBeInstanceOf(FieldBuilder::class);
    });

    it('should add multiple modules to the modules property', function () {
        $builder = new FieldBuilder(app(OnOfficeService::class));

        $builder->withModules('estate');
        $builder->withModules('address');

        expect($builder->modules)->toBe(['estate', 'address']);
    });
});
