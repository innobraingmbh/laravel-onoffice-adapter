<?php

use Katalam\OnOfficeAdapter\Facades\EstateRepository;
use Katalam\OnOfficeAdapter\Facades\Testing\EstateRepositoryFake;
use Katalam\OnOfficeAdapter\Facades\Testing\RecordFactories\EstateFactory;
use Katalam\OnOfficeAdapter\Facades\Testing\RecordFactories\FileFactory;

describe('get', function () {
    it('can be faked', function () {
        $fake = EstateRepository::fake();

        expect($fake)->toBeInstanceOf(EstateRepositoryFake::class);
    });

    it('can get a fake response', function () {
        EstateRepository::fake([
            [
                EstateFactory::make()
                    ->id(1)
                    ->set('foo', 'bar'),
            ],
        ]);

        $estates = EstateRepository::query()->get();

        expect($estates)->toHaveCount(1)
            ->and($estates->first()['id'])->toBe(1);
    });

    it('can get fake responses in pages', function () {
        EstateRepository::fake([
            [
                EstateFactory::make()->id(1),
            ],
            [
                EstateFactory::make()->id(2),
                EstateFactory::make()->id(3),
            ],
        ]);

        $estates = EstateRepository::query()->get();

        expect($estates)->toHaveCount(3)
            ->and($estates->first()['id'])->toBe(1)
            ->and($estates->last()['id'])->toBe(3);
    });

    it('can get multiple fake responses', function () {
        EstateRepository::fake([
            [
                EstateFactory::make()->id(1),
            ],
        ], [
            [
                EstateFactory::make()->id(2),
                EstateFactory::make()->id(3),
            ],
        ]);

        $estates = EstateRepository::query()->get();

        expect($estates)->toHaveCount(1)
            ->and($estates->first()['id'])->toBe(1);

        $estates = EstateRepository::query()->get();

        expect($estates)->toHaveCount(2)
            ->and($estates->first()['id'])->toBe(2)
            ->and($estates->last()['id'])->toBe(3);
    });

    it('throws an exception when no more fake responses are available', function () {
        EstateRepository::fake([
            [
                EstateFactory::make()->id(1),
            ],
        ]);

        EstateRepository::query()->get();

        expect(fn () => EstateRepository::query()->get())
            ->toThrow('No more fake responses');
    });
});

describe('find', function () {
    it('can find a fake response', function () {
        EstateRepository::fake([
            [
                EstateFactory::make()->id(1),
            ],
        ]);

        $estate = EstateRepository::query()->find(1);

        expect($estate['id'])->toBe(1);
    });

    it('can find a fake response in pages', function () {
        EstateRepository::fake([
            [
                EstateFactory::make()->id(1),
            ],
            [
                EstateFactory::make()->id(2),
                EstateFactory::make()->id(3),
            ],
        ]);

        $estate = EstateRepository::query()->find(3);

        expect($estate['id'])->toBe(3);
    });

    it('throws an exception when no more fake responses are available', function () {
        EstateRepository::fake([
            [
                EstateFactory::make()->id(1),
            ],
        ]);

        EstateRepository::query()->find(1);

        expect(static fn () => EstateRepository::query()->find(1))
            ->toThrow('No more fake responses');
    });
});

describe('modify', function () {
    it('can modify', function () {
        EstateRepository::fake([
            [
                true,
            ],
        ]);

        $result = EstateRepository::query()->modify(1);

        expect($result)->toBeTrue();
    });

    it('can modify multiple times', function () {
        EstateRepository::fake([
            [
                true,
            ],
        ], [
            [
                true,
            ],
        ]);

        $result = EstateRepository::query()->modify(1);

        expect($result)->toBeTrue();

        $result = EstateRepository::query()->modify(2);

        expect($result)->toBeTrue();
    });

    it('throws an exception when no more fake responses are available', function () {
        EstateRepository::fake();

        EstateRepository::query()->modify(1);
    })->throws(Exception::class, 'No more fake responses');
});

describe('delete', function () {
    it('can delete', function () {
        EstateRepository::fake([
            [
                FileFactory::make()
                    ->ok(),
            ],
        ]);

        $result = EstateRepository::files()->delete(1);

        expect($result)->toBeTrue();
    });

    it('can delete multiple times', function () {
        EstateRepository::fake([
            [
                FileFactory::make()
                    ->ok(),
            ],
        ], [
            [
                FileFactory::make()
                    ->ok(),
            ],
        ]);

        $result = EstateRepository::files()->delete(1);

        expect($result)->toBeTrue();

        $result = EstateRepository::files()->delete(2);

        expect($result)->toBeTrue();
    });

    it('throws an exception when no more fake responses are available', function () {
        EstateRepository::fake();

        EstateRepository::files()->delete(1);
    })->throws(Exception::class, 'No more fake responses');
});
