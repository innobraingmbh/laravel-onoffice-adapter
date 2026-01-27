<?php

declare(strict_types=1);

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Facades\Http;
use Innobrain\OnOfficeAdapter\Facades\ActivityRepository;
use Innobrain\OnOfficeAdapter\Facades\AddressRepository;
use Innobrain\OnOfficeAdapter\Facades\EstateRepository;
use Innobrain\OnOfficeAdapter\Facades\Testing\RecordFactories\ActivityFactory;
use Innobrain\OnOfficeAdapter\Facades\Testing\RecordFactories\AddressFactory;
use Innobrain\OnOfficeAdapter\Facades\Testing\RecordFactories\EstateFactory;
use Innobrain\OnOfficeAdapter\Tests\Stubs\ReadEstateResponse;

describe('forPage', function () {
    it('should set offset, pageSize and limit correctly for page 1', function () {
        $builder = EstateRepository::query();

        $builder->forPage(1, 15);

        expect($builder->offset)->toBe(0)
            ->and($builder->pageSize)->toBe(15)
            ->and($builder->limit)->toBe(15);
    });

    it('should set offset correctly for page 2', function () {
        $builder = EstateRepository::query();

        $builder->forPage(2, 15);

        expect($builder->offset)->toBe(15)
            ->and($builder->pageSize)->toBe(15)
            ->and($builder->limit)->toBe(15);
    });

    it('should set offset correctly for page 3', function () {
        $builder = EstateRepository::query();

        $builder->forPage(3, 20);

        expect($builder->offset)->toBe(40)
            ->and($builder->pageSize)->toBe(20)
            ->and($builder->limit)->toBe(20);
    });

    it('should cap perPage at 500', function () {
        $builder = EstateRepository::query();

        $builder->forPage(1, 1000);

        expect($builder->pageSize)->toBe(500)
            ->and($builder->limit)->toBe(500);
    });

    it('should use minimum perPage of 1', function () {
        $builder = EstateRepository::query();

        $builder->forPage(1, 0);

        expect($builder->pageSize)->toBe(1)
            ->and($builder->limit)->toBe(1);
    });

    it('should return builder instance for chaining', function () {
        $builder = EstateRepository::query();

        $result = $builder->forPage(1, 15);

        expect($result)->toBe($builder);
    });
});

describe('paginate on EstateRepository', function () {
    it('should return a LengthAwarePaginator', function () {
        // First response is for count(), second is for getPage()
        EstateRepository::fake([
            EstateRepository::response([
                EstateRepository::page(recordFactories: [], countAbsolute: 50),
            ]),
            EstateRepository::response([
                EstateRepository::page(recordFactories: [
                    EstateFactory::make()->id(1),
                    EstateFactory::make()->id(2),
                ], countAbsolute: 50),
            ]),
        ]);

        $result = EstateRepository::query()
            ->select(['Id'])
            ->paginate(2, 'page', 1);

        expect($result)->toBeInstanceOf(LengthAwarePaginator::class)
            ->and($result->total())->toBe(50)
            ->and($result->perPage())->toBe(2)
            ->and($result->currentPage())->toBe(1)
            ->and($result->count())->toBe(2);

        EstateRepository::assertSentCount(2);
    });

    it('should paginate with ordering preserved', function () {
        // First response is for count(), second is for getPage()
        EstateRepository::fake([
            EstateRepository::response([
                EstateRepository::page(recordFactories: [], countAbsolute: 10),
            ]),
            EstateRepository::response([
                EstateRepository::page(recordFactories: [
                    EstateFactory::make()->id(2),
                    EstateFactory::make()->id(3),
                ], countAbsolute: 10),
            ]),
        ]);

        $result = EstateRepository::query()
            ->select(['Id', 'kaufpreis'])
            ->orderByDesc('kaufpreis')
            ->paginate(2, 'page', 1);

        expect($result)->toBeInstanceOf(LengthAwarePaginator::class)
            ->and($result->total())->toBe(10)
            ->and($result->count())->toBe(2);

        EstateRepository::assertSentCount(2);
    });
});

describe('simplePaginate on EstateRepository', function () {
    it('should return a Paginator with hasMorePages true when more records exist', function () {
        // For simplePaginate, we fetch perPage+1 records to detect more pages
        // So for perPage=2, we need 3 records to show hasMorePages=true
        EstateRepository::fake([
            EstateRepository::response([
                EstateRepository::page(recordFactories: [
                    EstateFactory::make()->id(1),
                    EstateFactory::make()->id(2),
                    EstateFactory::make()->id(3), // Extra record indicates more pages
                ], countAbsolute: 50),
            ]),
        ]);

        $result = EstateRepository::query()
            ->select(['Id'])
            ->simplePaginate(2, 'page', 1);

        expect($result)->toBeInstanceOf(Paginator::class)
            ->and($result->perPage())->toBe(2)
            ->and($result->currentPage())->toBe(1)
            ->and($result->count())->toBe(2)
            ->and($result->hasMorePages())->toBeTrue();

        EstateRepository::assertSentCount(1);
    });

    it('should detect no more pages when fewer results than perPage+1', function () {
        EstateRepository::fake([
            EstateRepository::response([
                EstateRepository::page(recordFactories: [
                    EstateFactory::make()->id(1),
                    EstateFactory::make()->id(2),
                ], countAbsolute: 2),
            ]),
        ]);

        $result = EstateRepository::query()
            ->select(['Id'])
            ->simplePaginate(2, 'page', 1);

        expect($result)->toBeInstanceOf(Paginator::class)
            ->and($result->count())->toBe(2)
            ->and($result->hasMorePages())->toBeFalse();

        EstateRepository::assertSentCount(1);
    });
});

describe('paginate on AddressRepository', function () {
    it('should return a LengthAwarePaginator', function () {
        // First response is for count(), second is for getPage()
        AddressRepository::fake([
            AddressRepository::response([
                AddressRepository::page(recordFactories: [], countAbsolute: 25),
            ]),
            AddressRepository::response([
                AddressRepository::page(recordFactories: [
                    AddressFactory::make()->id(1),
                    AddressFactory::make()->id(2),
                ], countAbsolute: 25),
            ]),
        ]);

        $result = AddressRepository::query()
            ->select(['Id'])
            ->paginate(2, 'page', 1);

        expect($result)->toBeInstanceOf(LengthAwarePaginator::class)
            ->and($result->total())->toBe(25)
            ->and($result->perPage())->toBe(2)
            ->and($result->currentPage())->toBe(1)
            ->and($result->count())->toBe(2);

        AddressRepository::assertSentCount(2);
    });

    it('should simplePaginate correctly', function () {
        AddressRepository::fake([
            AddressRepository::response([
                AddressRepository::page(recordFactories: [
                    AddressFactory::make()->id(1),
                    AddressFactory::make()->id(2),
                    AddressFactory::make()->id(3), // Extra record for hasMorePages
                ], countAbsolute: 25),
            ]),
        ]);

        $result = AddressRepository::query()
            ->select(['Id'])
            ->simplePaginate(2, 'page', 1);

        expect($result)->toBeInstanceOf(Paginator::class)
            ->and($result->perPage())->toBe(2)
            ->and($result->count())->toBe(2)
            ->and($result->hasMorePages())->toBeTrue();

        AddressRepository::assertSentCount(1);
    });
});

describe('paginate on ActivityRepository', function () {
    it('should return a LengthAwarePaginator', function () {
        // First response is for count(), second is for getPage()
        ActivityRepository::fake([
            ActivityRepository::response([
                ActivityRepository::page(recordFactories: [], countAbsolute: 30),
            ]),
            ActivityRepository::response([
                ActivityRepository::page(recordFactories: [
                    ActivityFactory::make()->id(1),
                    ActivityFactory::make()->id(2),
                ], countAbsolute: 30),
            ]),
        ]);

        $result = ActivityRepository::query()
            ->select(['Id'])
            ->paginate(2, 'page', 1);

        expect($result)->toBeInstanceOf(LengthAwarePaginator::class)
            ->and($result->total())->toBe(30)
            ->and($result->perPage())->toBe(2)
            ->and($result->count())->toBe(2);

        ActivityRepository::assertSentCount(2);
    });

    it('should simplePaginate correctly', function () {
        ActivityRepository::fake([
            ActivityRepository::response([
                ActivityRepository::page(recordFactories: [
                    ActivityFactory::make()->id(1),
                    ActivityFactory::make()->id(2),
                    ActivityFactory::make()->id(3), // Extra record for hasMorePages
                ], countAbsolute: 30),
            ]),
        ]);

        $result = ActivityRepository::query()
            ->select(['Id'])
            ->simplePaginate(2, 'page', 1);

        expect($result)->toBeInstanceOf(Paginator::class)
            ->and($result->perPage())->toBe(2)
            ->and($result->count())->toBe(2)
            ->and($result->hasMorePages())->toBeTrue();

        ActivityRepository::assertSentCount(1);
    });
});

describe('paginate with real HTTP responses', function () {
    it('should work with real HTTP responses for estates', function () {
        Http::preventStrayRequests();
        Http::fake([
            'https://api.onoffice.de/api/stable/api.php' => Http::sequence([
                ReadEstateResponse::make(count: 100), // count() response
                ReadEstateResponse::make(count: 100), // getPage() response
            ]),
        ]);

        EstateRepository::record();

        $result = EstateRepository::query()
            ->select(['Id'])
            ->paginate(15, 'page', 1);

        expect($result)->toBeInstanceOf(LengthAwarePaginator::class)
            ->and($result->total())->toBe(100)
            ->and($result->perPage())->toBe(15);

        EstateRepository::assertSentCount(2);
    });
});

describe('pagination parameters', function () {
    it('should cap perPage at 500', function () {
        EstateRepository::fake([
            EstateRepository::response([
                EstateRepository::page(recordFactories: [], countAbsolute: 1000),
            ]),
            EstateRepository::response([
                EstateRepository::page(
                    recordFactories: array_map(
                        fn ($i) => EstateFactory::make()->id($i),
                        range(1, 500)
                    ),
                    countAbsolute: 1000
                ),
            ]),
        ]);

        $result = EstateRepository::query()
            ->select(['Id'])
            ->paginate(1000, 'page', 1);

        expect($result->perPage())->toBe(500);
    });

    it('should use default perPage of 15', function () {
        EstateRepository::fake([
            EstateRepository::response([
                EstateRepository::page(recordFactories: [], countAbsolute: 100),
            ]),
            EstateRepository::response([
                EstateRepository::page(
                    recordFactories: array_map(
                        fn ($i) => EstateFactory::make()->id($i),
                        range(1, 15)
                    ),
                    countAbsolute: 100
                ),
            ]),
        ]);

        $result = EstateRepository::query()
            ->select(['Id'])
            ->paginate();

        expect($result->perPage())->toBe(15);
    });
});
