<?php

declare(strict_types=1);

use Innobrain\OnOfficeAdapter\Query\Builder;

describe('select', function () {
    it('should set the columns property to the given columns', function () {
        $builder = new Builder;

        $builder->select(['ID', 'Name']);

        expect($builder->columns)->toBe(['ID', 'Name']);
    });

    it('should wrap the given columns in an array if it is a string', function () {
        $builder = new Builder;

        $builder->select('ID');

        expect($builder->columns)->toBe(['ID']);
    });

    it('should return the builder instance', function () {
        $builder = new Builder;

        $result = $builder->select('ID');

        expect($result)->toBeInstanceOf(Builder::class);
    });

    it('should add the given column to the columns property', function () {
        $builder = new Builder;

        $builder->select('ID');
        $builder->addSelect('Name');

        expect($builder->columns)->toBe(['ID', 'Name']);
    });

    it('should wrap the given column in an array if it is a string', function () {
        $builder = new Builder;

        $builder->select('ID');
        $builder->addSelect('Name');

        expect($builder->columns)->toBe(['ID', 'Name']);
    });
});

describe('modifies', function () {
    it('should return the builder instance', function () {
        $builder = new Builder;

        $result = $builder->addModify('Name', 'Foo');

        expect($result)->toBeInstanceOf(Builder::class);
    });

    it('should add modifies parameters', function () {
        $builder = new Builder;

        $builder->addModify('Name', 'Foo');

        expect($builder->modifies)->toBe(['Name' => 'Foo']);
    });

    it('should add multiple modifies parameters', function () {
        $builder = new Builder;

        $builder->addModify('Name', 'Foo');
        $builder->addModify('ID', 1);

        expect($builder->modifies)->toBe(['Name' => 'Foo', 'ID' => 1]);
    });

    it('should add multiple modifies parameters with the same key', function () {
        $builder = new Builder;

        $builder->addModify('Name', 'Foo');
        $builder->addModify('Name', 'Bar');

        expect($builder->modifies)->toBe(['Name' => 'Bar']);
    });

    it('should add multiple modifies parameters as array', function () {
        $builder = new Builder;

        $builder->addModify([
            'Name' => 'Foo',
            'ID' => 1,
        ]);

        expect($builder->modifies)->toBe(['Name' => 'Foo', 'ID' => 1]);
    });
});

describe('orderBy', function () {
    it('should add the given column and direction to the orderBy property', function () {
        $builder = new Builder;

        $builder->orderBy('ID');

        expect($builder->orderBy)->toBe([['ID', 'ASC']]);
    });

    it('should return the builder instance', function () {
        $builder = new Builder;

        $result = $builder->orderBy('ID');

        expect($result)->toBeInstanceOf(Builder::class);
    });

    it('should convert the direction to uppercase', function () {
        $builder = new Builder;

        $builder->orderBy('ID', 'desc');

        expect($builder->orderBy)->toBe([['ID', 'DESC']]);
    });

    it('should add multiple order by columns', function () {
        $builder = new Builder;

        $builder->orderBy('ID', 'desc');
        $builder->orderBy('Name');

        expect($builder->orderBy)->toBe([['ID', 'DESC'], ['Name', 'ASC']]);
    });

    it('should order desc', function () {
        $builder = new Builder;

        $builder->orderByDesc('ID');

        expect($builder->orderBy)->toBe([['ID', 'DESC']]);
    });
});

describe('offset', function () {
    it('should set the offset property to the given value', function () {
        $builder = new Builder;

        $builder->offset(10);

        expect($builder->offset)->toBe(10);
    });

    it('should return the builder instance', function () {
        $builder = new Builder;

        $result = $builder->offset(10);

        expect($result)->toBeInstanceOf(Builder::class);
    });

    it('should not allow negative values', function () {
        $builder = new Builder;

        $builder->offset(-10);

        expect($builder->offset)->toBe(0);
    });
});

describe('limit', function () {
    it('should set the limit property to the given value', function () {
        $builder = new Builder;

        $builder->limit(10);

        expect($builder->limit)->toBe(10);
    });

    it('should return the builder instance', function () {
        $builder = new Builder;

        $result = $builder->limit(10);

        expect($result)->toBeInstanceOf(Builder::class);
    });

    it('should not allow negative values', function () {
        $builder = new Builder;

        $builder->limit(-10);

        expect($builder->limit)->toBe(-1);
    });
});

describe('where', function () {
    it('should add the given filter to the filters property', function () {
        $builder = new Builder;

        $builder->where('ID', '=', 1);

        expect($builder->filters)->toBe([
            'ID' => [
                ['=', 1],
            ],
        ]);
    });

    it('should return the builder instance', function () {
        $builder = new Builder;

        $result = $builder->where('ID', '=', 1);

        expect($result)->toBeInstanceOf(Builder::class);
    });

    it('should add multiple filters', function () {
        $builder = new Builder;

        $builder->where('ID', '=', 1);
        $builder->where('Name', '=', 'John');

        expect($builder->filters)->toBe([
            'ID' => [
                ['=', 1],
            ],
            'Name' => [
                ['=', 'John'],
            ],
        ]);
    });

    it('should add multiple filters with different operators', function () {
        $builder = new Builder;

        $builder->where('ID', '=', 1);
        $builder->where('Name', 'LIKE', 'John');

        expect($builder->filters)->toBe([
            'ID' => [
                ['=', 1],
            ],
            'Name' => [
                ['LIKE', 'John'],
            ],
        ]);
    });

    it('should default the operator to equality', function () {
        $builder = new Builder;

        $builder->where('ID', 1);

        expect($builder->filters)->toBe([
            'ID' => [
                ['=', 1],
            ],
        ]);
    });
});

describe('getFilters', function () {
    it('should return the filters property', function () {
        $builder = new Builder;

        $builder->where('ID', '=', 1);

        $m = new ReflectionMethod($builder, 'getFilters');
        $m->setAccessible(true);
        $filters = $m->invoke($builder);

        expect($filters)->toBe([
            'ID' => [
                [
                    'op' => '=',
                    'val' => 1,
                ],
            ],
        ]);
    });

    it('should return the filters property with multiple filters', function () {
        $builder = new Builder;

        $builder->where('ID', '=', 1);
        $builder->where('Name', 'LIKE', 'John');

        $m = new ReflectionMethod($builder, 'getFilters');
        $m->setAccessible(true);
        $filters = $m->invoke($builder);

        expect($filters)->toBe([
            'ID' => [
                [
                    'op' => '=',
                    'val' => 1,
                ],
            ],
            'Name' => [
                [
                    'op' => 'LIKE',
                    'val' => 'John',
                ],
            ],
        ]);
    });
});

describe('getOrderBy', function () {
    it('should return the orderBy property', function () {
        $builder = new Builder;

        $builder->orderBy('ID', 'desc');

        $m = new ReflectionMethod($builder, 'getOrderBy');
        $m->setAccessible(true);
        $orderBy = $m->invoke($builder);

        expect($orderBy)->toBe([
            'ID' => 'DESC',
        ]);
    });

    it('should return the orderBy property with multiple columns', function () {
        $builder = new Builder;

        $builder->orderBy('ID', 'desc');
        $builder->orderBy('Name');

        $m = new ReflectionMethod($builder, 'getOrderBy');
        $m->setAccessible(true);
        $orderBy = $m->invoke($builder);

        expect($orderBy)->toBe([
            'ID' => 'DESC',
            'Name' => 'ASC',
        ]);
    });
});
