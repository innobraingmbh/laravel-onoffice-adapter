<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Innobrain\OnOfficeAdapter\Facades\MarketplaceRepository;
use Innobrain\OnOfficeAdapter\Facades\Testing\RecordFactories\MarketPlaceUnlockProviderFactory;
use Innobrain\OnOfficeAdapter\Tests\Stubs\DoUnlockProviderResponse;

describe('fake responses', function () {
    test('get', function () {
        MarketplaceRepository::fake(MarketplaceRepository::response([
            MarketplaceRepository::page(recordFactories: [
                MarketPlaceUnlockProviderFactory::make(),
            ]),
        ]));

        MarketplaceRepository::query()->unlockProvider('foo', 'bar');

        MarketplaceRepository::assertSentCount(1);
    });
});

describe('real responses', function () {
    test('get', function () {
        Http::preventStrayRequests();
        Http::fake([
            'https://api.onoffice.de/api/stable/api.php/' => Http::sequence([
                DoUnlockProviderResponse::make(),
            ]),
        ]);

        MarketplaceRepository::record();

        MarketplaceRepository::query()->unlockProvider('foo', 'bar');

        MarketplaceRepository::assertSentCount(1);
    });
});
