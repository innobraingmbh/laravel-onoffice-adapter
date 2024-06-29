<?php

declare(strict_types=1);

use Katalam\OnOfficeAdapter\Facades\Testing\RecordFactories\BaseFactory;

class Factory extends BaseFactory {}

describe('construct', function () {
    it('builds', function () {
        $factory = Factory::make();

        expect($factory)->toBeInstanceOf(Factory::class);
    });
});

describe('id', function () {
    it('defaults to 0', function () {
        $factory = Factory::make();

        expect($factory->id)->toBe(0);
    });

    it('can set an id', function () {
        $factory = Factory::make()
            ->id(2);

        expect($factory->id)->toBe(2);
    });
});

describe('type', function () {
    it('defaults to empty string', function () {
        $factory = Factory::make();

        expect($factory->type)->toBe('');
    });

    it('can set a type', function () {
        $factory = Factory::make()
            ->type('estate');

        expect($factory->type)->toBe('estate');
    });
});

describe('set', function () {
    it('defaults to empty array', function () {
        $factory = Factory::make();

        expect($factory->elements)->toBe([]);
    });

    it('can set data', function () {
        $factory = Factory::make()
            ->set('foo', 'bar');

        expect($factory->elements)->toBe(['foo' => 'bar']);
    });
});

describe('times', function () {
    it('defaults to 1', function () {
        $factories = Factory::make()
            ->times();

        expect($factories)->toBeArray()
            ->toHaveCount(1);
    });

    it('can make multiple times', function () {
        $factories = Factory::make()
            ->times(2);

        expect($factories)->toBeArray()
            ->toHaveCount(2);
    });
});

describe('__call', function () {
    it('can set data', function () {
        $factory = Factory::make()
            ->setFoo('bar');

        expect($factory->elements)->toBe(['foo' => 'bar']);
    });

    it('can set data with multiple words', function () {
        $factory = Factory::make()
            ->setFooBar('baz');

        expect($factory->elements)->toBe(['fooBar' => 'baz']);
    });

    it('will skip if name does not start with set', function () {
        $factory = Factory::make()
            ->fooBar('baz');

        expect($factory->elements)->toBe([]);
    });
});

describe('data', function () {
    it('can set data', function () {
        $factory = Factory::make()
            ->data([
                'foo' => 'bar',
            ]);

        expect($factory->elements)->toBe(['foo' => 'bar']);
    });

    it('can set data multiple times', function () {
        $factory = Factory::make()
            ->data([
                'foo' => 'bar',
            ])
            ->data([
                'baz' => 'qux',
            ]);

        expect($factory->elements)->toBe([
            'foo' => 'bar',
            'baz' => 'qux',
        ]);
    });

    it('can set empty data', function () {
        $factory = Factory::make()
            ->data([]);

        expect($factory->elements)->toBe([]);
    });
});
