<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Innobrain\OnOfficeAdapter\Dtos\OnOfficeRequest;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeAction;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeResourceId;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeResourceType;
use Innobrain\OnOfficeAdapter\Facades\LinkRepository;
use Innobrain\OnOfficeAdapter\Facades\Query;
use Innobrain\OnOfficeAdapter\Facades\Testing\RecordFactories\LinkFactory;
use Innobrain\OnOfficeAdapter\Services\OnOfficeService;
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

describe('toRequest', function () {
    test('builds the get-link request without sending it', function () {
        $request = LinkRepository::query()
            ->module(OnOfficeResourceId::Estate)
            ->recordId(5)
            ->toRequest();

        expect($request->actionId)->toBe(OnOfficeAction::Get)
            ->and($request->resourceType)->toBe(OnOfficeResourceType::GetLink)
            ->and($request->resourceId)->toBe(OnOfficeResourceId::Estate)
            ->and($request->parameters)->toBe([OnOfficeService::RECORDID => 5]);
    });

    test('includes the type parameter for agentslog links', function () {
        $request = LinkRepository::query()
            ->module(OnOfficeResourceId::AgentsLog)
            ->recordId(7)
            ->type(OnOfficeResourceId::Address)
            ->toRequest();

        expect($request->parameters)->toBe([OnOfficeService::RECORDID => 7, 'type' => 'address']);
    });

    test('link builders can be batched', function () {
        Query::fake(Query::response([
            Query::page(resourceType: OnOfficeResourceType::GetLink, recordFactories: [
                LinkFactory::make()->id(1)->data(['url' => 'https://smart.onoffice.de/link-1']),
            ]),
            Query::page(resourceType: OnOfficeResourceType::GetLink, recordFactories: [
                LinkFactory::make()->id(2)->data(['url' => 'https://smart.onoffice.de/link-2']),
            ]),
        ]));

        $results = Query::batch([
            LinkRepository::query()->module(OnOfficeResourceId::Estate)->recordId(1),
            LinkRepository::query()->module(OnOfficeResourceId::Estate)->recordId(2),
        ])->once();

        expect($results)->toHaveCount(2)
            ->and(data_get($results[0], 'data.records.0.elements.url'))->toBe('https://smart.onoffice.de/link-1')
            ->and(data_get($results[1], 'data.records.0.elements.url'))->toBe('https://smart.onoffice.de/link-2');

        Query::assertSent(fn (OnOfficeRequest $request) => $request->resourceType === OnOfficeResourceType::GetLink
            && ($request->parameters[OnOfficeService::RECORDID] ?? null) === 1);
        Query::assertSent(fn (OnOfficeRequest $request) => ($request->parameters[OnOfficeService::RECORDID] ?? null) === 2);
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
