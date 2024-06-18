<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Katalam\OnOfficeAdapter\Enums\OnOfficeRelationType;
use Katalam\OnOfficeAdapter\Facades\RelationRepository;
use Katalam\OnOfficeAdapter\Query\RelationBuilder;
use Katalam\OnOfficeAdapter\Services\OnOfficeService;
use Katalam\OnOfficeAdapter\Tests\Stubs\GetEstateAgentsResponse;

it('works', function () {
    Http::preventStrayRequests();
    Http::fake([
        '*' => GetEstateAgentsResponse::make(),
    ]);

    $agents = RelationRepository::query()
        ->relationType(OnOfficeRelationType::ContactPersonBroker)
        ->get();

    expect($agents)
        ->toHaveCount(6)
        ->and($agents->first()[0])->toBe('2169')
        ->and($agents->first()[1])->toBe('2205');
});

describe('childIds', function () {
    it('should set the childIds property to the given childIds', function () {
        $builder = new RelationBuilder(app(OnOfficeService::class));

        $builder->childIds([1]);

        expect($builder->childIds)->toBe([1]);
    });

    it('should wrap the given childIds in an array if it is a int', function () {
        $builder = new RelationBuilder(app(OnOfficeService::class));

        $builder->childIds(1);

        expect($builder->childIds)->toBe([1]);
    });

    it('should return the builder instance', function () {
        $builder = new RelationBuilder(app(OnOfficeService::class));

        $result = $builder->childIds([1]);

        expect($result)->toBeInstanceOf(RelationBuilder::class);
    });

    it('should add the given childId to the childIds property', function () {
        $builder = new RelationBuilder(app(OnOfficeService::class));

        $builder->childIds([1]);
        $builder->addChildIds([2]);

        expect($builder->childIds)->toBe([1, 2]);
    });

    it('should wrap the given childId in an array if it is a int', function () {
        $builder = new RelationBuilder(app(OnOfficeService::class));

        $builder->childIds([1]);
        $builder->addChildIds(2);

        expect($builder->childIds)->toBe([1, 2]);
    });
});

describe('parentIds', function () {
    it('should set the parentIds property to the given parentIds', function () {
        $builder = new RelationBuilder(app(OnOfficeService::class));

        $builder->parentIds([1]);

        expect($builder->parentIds)->toBe([1]);
    });

    it('should wrap the given parentIds in an array if it is a int', function () {
        $builder = new RelationBuilder(app(OnOfficeService::class));

        $builder->parentIds(1);

        expect($builder->parentIds)->toBe([1]);
    });

    it('should return the builder instance', function () {
        $builder = new RelationBuilder(app(OnOfficeService::class));

        $result = $builder->parentIds([1]);

        expect($result)->toBeInstanceOf(RelationBuilder::class);
    });

    it('should add the given parentId to the parentIds property', function () {
        $builder = new RelationBuilder(app(OnOfficeService::class));

        $builder->parentIds([1]);
        $builder->addParentIds([2]);

        expect($builder->parentIds)->toBe([1, 2]);
    });

    it('should wrap the given parentId in an array if it is a int', function () {
        $builder = new RelationBuilder(app(OnOfficeService::class));

        $builder->parentIds([1]);
        $builder->addParentIds(2);

        expect($builder->parentIds)->toBe([1, 2]);
    });
});

describe('relationType', function () {
    it('should set the relationType property to the given relationType', function () {
        $builder = new RelationBuilder(app(OnOfficeService::class));

        $builder->relationType(OnOfficeRelationType::Buyer);

        expect($builder->relationType)->toBe(OnOfficeRelationType::Buyer);
    });

    it('should return the builder instance', function () {
        $builder = new RelationBuilder(app(OnOfficeService::class));

        $result = $builder->relationType(OnOfficeRelationType::Buyer);

        expect($result)->toBeInstanceOf(RelationBuilder::class);
    });
});
