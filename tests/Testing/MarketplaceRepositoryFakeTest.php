<?php

use Katalam\OnOfficeAdapter\Facades\MarketplaceRepository;
use Katalam\OnOfficeAdapter\Facades\Testing\MarketplaceRepositoryFake;
use Katalam\OnOfficeAdapter\Facades\Testing\RecordFactories\MarketPlaceUnlockProviderFactory;

describe('get', function () {
    it('can be faked', function () {
        $fake = MarketplaceRepository::fake();

        expect($fake)->toBeInstanceOf(MarketplaceRepositoryFake::class);
    });

    it('can get a fake response', function () {
        MarketplaceRepository::fake([
            [
                MarketPlaceUnlockProviderFactory::make()->ok(),
            ],
        ]);

        $success = MarketplaceRepository::query()->unlockProvider('foo', 'bar');

        expect($success)->toBeTrue();
    });

    it('can get an error fake response', function () {
        MarketplaceRepository::fake([
            [
                MarketPlaceUnlockProviderFactory::make()->error(),
            ],
        ]);

        $success = MarketplaceRepository::query()->unlockProvider('foo', 'bar');

        expect($success)->toBeFalse();
    });
});
