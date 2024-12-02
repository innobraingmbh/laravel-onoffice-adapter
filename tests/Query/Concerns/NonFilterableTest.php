<?php

declare(strict_types=1);

use Innobrain\OnOfficeAdapter\Query\Builder;
use Innobrain\OnOfficeAdapter\Query\Concerns\NonFilterable;

class NonFilterableTestClass extends Builder
{
    use NonFilterable;
}

describe('NonFilterable', function () {
    it('where should return instance without modifying filters', function () {
        $instance = new NonFilterableTestClass;

        $result = $instance->where('column', '=', 'value');

        expect($result)->toBe($instance)
            ->and($result)->toBeInstanceOf(NonFilterableTestClass::class)
            ->and($result->filters)->toBe([]);
    });

    it('whereNot should return instance without modifying filters', function () {
        $instance = new NonFilterableTestClass;

        $result = $instance->whereNot('column', 'value');

        expect($result)->toBe($instance)
            ->and($result)->toBeInstanceOf(NonFilterableTestClass::class)
            ->and($result->filters)->toBe([]);
    });

    it('whereIn should return instance without modifying filters', function () {
        $instance = new NonFilterableTestClass;

        $result = $instance->whereIn('column', ['value1', 'value2']);

        expect($result)->toBe($instance)
            ->and($result)->toBeInstanceOf(NonFilterableTestClass::class)
            ->and($result->filters)->toBe([]);
    });

    it('whereNotIn should return instance without modifying filters', function () {
        $instance = new NonFilterableTestClass;

        $result = $instance->whereNotIn('column', ['value1', 'value2']);

        expect($result)->toBe($instance)
            ->and($result)->toBeInstanceOf(NonFilterableTestClass::class)
            ->and($result->filters)->toBe([]);
    });

    it('whereBetween should return instance without modifying filters', function () {
        $instance = new NonFilterableTestClass;

        $result = $instance->whereBetween('column', 1, 10);

        expect($result)->toBe($instance)
            ->and($result)->toBeInstanceOf(NonFilterableTestClass::class)
            ->and($result->filters)->toBe([]);

        // Test with string values
        $result = $instance->whereBetween('date', '2023-01-01', '2023-12-31');

        expect($result)->toBe($instance)
            ->and($result)->toBeInstanceOf(NonFilterableTestClass::class)
            ->and($result->filters)->toBe([]);
    });

    it('whereLike should return instance without modifying filters', function () {
        $instance = new NonFilterableTestClass;

        $result = $instance->whereLike('column', 'value');

        expect($result)->toBe($instance)
            ->and($result)->toBeInstanceOf(NonFilterableTestClass::class)
            ->and($result->filters)->toBe([]);
    });

    it('whereNotLike should return instance without modifying filters', function () {
        $instance = new NonFilterableTestClass;

        $result = $instance->whereNotLike('column', 'value');

        expect($result)->toBe($instance)
            ->and($result)->toBeInstanceOf(NonFilterableTestClass::class)
            ->and($result->filters)->toBe([]);
    });

    it('should chain multiple where methods without modifying filters', function () {
        $instance = new NonFilterableTestClass;

        $result = $instance
            ->where('column1', 'value1')
            ->whereNot('column2', 'value2')
            ->whereIn('column3', ['value3'])
            ->whereNotIn('column4', ['value4'])
            ->whereBetween('column5', 1, 10)
            ->whereLike('column6', 'value6')
            ->whereNotLike('column7', 'value7');

        expect($result)->toBe($instance)
            ->and($result)->toBeInstanceOf(NonFilterableTestClass::class)
            ->and($result->filters)->toBe([]);
    });
});
