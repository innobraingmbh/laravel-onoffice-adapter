<?php

declare(strict_types=1);

use Katalam\OnOfficeAdapter\Enums\OnOfficeRelationType;
use Katalam\OnOfficeAdapter\Query\RelationBuilder;
use Katalam\OnOfficeAdapter\Repositories\RelationRepository;

describe('childIds', function () {
    it('should set the childIds property to the given childIds', function () {
        $builder = new RelationBuilder;
        $builder->setRepository(app(RelationRepository::class));

        $builder->childIds([1]);

        expect($builder->childIds)->toBe([1]);
    });

    it('should wrap the given childIds in an array if it is a int', function () {
        $builder = new RelationBuilder;
        $builder->setRepository(app(RelationRepository::class));

        $builder->childIds(1);

        expect($builder->childIds)->toBe([1]);
    });

    it('should return the builder instance', function () {
        $builder = new RelationBuilder;
        $builder->setRepository(app(RelationRepository::class));

        $result = $builder->childIds([1]);

        expect($result)->toBeInstanceOf(RelationBuilder::class);
    });

    it('should add the given childId to the childIds property', function () {
        $builder = new RelationBuilder;
        $builder->setRepository(app(RelationRepository::class));

        $builder->childIds([1]);
        $builder->addChildIds([2]);

        expect($builder->childIds)->toBe([1, 2]);
    });

    it('should wrap the given childId in an array if it is a int', function () {
        $builder = new RelationBuilder;
        $builder->setRepository(app(RelationRepository::class));

        $builder->childIds([1]);
        $builder->addChildIds(2);

        expect($builder->childIds)->toBe([1, 2]);
    });
});

describe('parentIds', function () {
    it('should set the parentIds property to the given parentIds', function () {
        $builder = new RelationBuilder;
        $builder->setRepository(app(RelationRepository::class));

        $builder->parentIds([1]);

        expect($builder->parentIds)->toBe([1]);
    });

    it('should wrap the given parentIds in an array if it is a int', function () {
        $builder = new RelationBuilder;
        $builder->setRepository(app(RelationRepository::class));

        $builder->parentIds(1);

        expect($builder->parentIds)->toBe([1]);
    });

    it('should return the builder instance', function () {
        $builder = new RelationBuilder;
        $builder->setRepository(app(RelationRepository::class));

        $result = $builder->parentIds([1]);

        expect($result)->toBeInstanceOf(RelationBuilder::class);
    });

    it('should add the given parentId to the parentIds property', function () {
        $builder = new RelationBuilder;
        $builder->setRepository(app(RelationRepository::class));

        $builder->parentIds([1]);
        $builder->addParentIds([2]);

        expect($builder->parentIds)->toBe([1, 2]);
    });

    it('should wrap the given parentId in an array if it is a int', function () {
        $builder = new RelationBuilder;
        $builder->setRepository(app(RelationRepository::class));

        $builder->parentIds([1]);
        $builder->addParentIds(2);

        expect($builder->parentIds)->toBe([1, 2]);
    });
});

describe('relationType', function () {
    it('should set the relationType property to the given relationType', function () {
        $builder = new RelationBuilder;
        $builder->setRepository(app(RelationRepository::class));

        $builder->relationType(OnOfficeRelationType::Buyer);

        expect($builder->relationType)->toBe(OnOfficeRelationType::Buyer);
    });

    it('should return the builder instance', function () {
        $builder = new RelationBuilder;
        $builder->setRepository(app(RelationRepository::class));

        $result = $builder->relationType(OnOfficeRelationType::Buyer);

        expect($result)->toBeInstanceOf(RelationBuilder::class);
    });
});
