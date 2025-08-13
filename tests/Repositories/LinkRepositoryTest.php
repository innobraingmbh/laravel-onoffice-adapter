<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeResourceId;
use Innobrain\OnOfficeAdapter\Facades\LinkRepository;
use Innobrain\OnOfficeAdapter\Facades\Testing\RecordFactories\LinkFactory;
use Innobrain\OnOfficeAdapter\Tests\Stubs\GetLinkResponse;

describe('fake responses', function () {
    test('get', function () {
        LinkRepository::fake(LinkRepository::response([
            LinkRepository::page(recordFactories: [
                LinkFactory::make()
                    ->id(1),
            ]),
        ]));

        $response = LinkRepository::query()
            ->withResourceId(OnOfficeResourceId::Estate)
            ->recordId(1)
            ->get();

        expect($response->count())->toBe(1)
            ->and($response->first()['id'])->toBe(1);

        LinkRepository::assertSentCount(1);
    });
});

describe('real responses', function () {
    test('get', function () {
        Http::preventStrayRequests();
        Http::fake([
            'https://api.onoffice.de/api/stable/api.php' => Http::sequence([
                GetLinkResponse::make(),
            ]),
        ]);

        LinkRepository::record();

        $response = LinkRepository::query()
            ->withResourceId(OnOfficeResourceId::Estate)
            ->recordId(1)
            ->get();

        expect($response->count())->toBe(1);

        LinkRepository::assertSentCount(1);
    });
});
