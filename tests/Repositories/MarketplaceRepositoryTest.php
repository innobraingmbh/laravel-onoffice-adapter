<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Katalam\OnOfficeAdapter\Facades\ActivityRepository;
use Katalam\OnOfficeAdapter\Facades\EstateRepository;
use Katalam\OnOfficeAdapter\Facades\MarketplaceRepository;
use Katalam\OnOfficeAdapter\Facades\Testing\RecordFactories\AddressFactory;
use Katalam\OnOfficeAdapter\Facades\Testing\RecordFactories\EstateFactory;
use Katalam\OnOfficeAdapter\Facades\Testing\RecordFactories\MarketPlaceUnlockProviderFactory;
use Katalam\OnOfficeAdapter\Tests\Stubs\DoUnlockProviderResponse;
use Katalam\OnOfficeAdapter\Tests\Stubs\ReadActivityResponse;
use Katalam\OnOfficeAdapter\Tests\Stubs\ReadAddressResponse;
use Katalam\OnOfficeAdapter\Tests\Stubs\ReadEstateResponse;

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
