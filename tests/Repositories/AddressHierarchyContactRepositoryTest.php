<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Innobrain\OnOfficeAdapter\Dtos\OnOfficeRequest;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeAction;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeResourceType;
use Innobrain\OnOfficeAdapter\Facades\AddressRepository;
use Innobrain\OnOfficeAdapter\Facades\Query;
use Innobrain\OnOfficeAdapter\Facades\Testing\RecordFactories\AddressFactory;
use Innobrain\OnOfficeAdapter\Tests\Stubs\GetAddressHierarchyContactsResponse;

describe('fake responses', function () {
    test('get', function () {
        Http::preventStrayRequests();
        AddressRepository::fake(AddressRepository::response([
            AddressRepository::page(
                actionId: OnOfficeAction::Get,
                resourceType: OnOfficeResourceType::AddressHierarchyContacts,
                recordFactories: [
                    AddressFactory::make()->id(691)->data(['company' => 'Mufflon GmbH']),
                    AddressFactory::make()->id(693)->data(['firstname' => 'Geralinde', 'surname' => 'Mufflon']),
                ],
            ),
        ]));

        $response = AddressRepository::hierarchyContacts(691)->get();

        expect($response->pluck('id')->all())->toBe([691, 693])
            ->and($response->first()['type'])->toBe('address')
            ->and($response->last()['elements']['firstname'])->toBe('Geralinde');

        AddressRepository::assertSentCount(1);
    });

    test('first', function () {
        Http::preventStrayRequests();
        AddressRepository::fake(AddressRepository::response([
            AddressRepository::page(
                actionId: OnOfficeAction::Get,
                resourceType: OnOfficeResourceType::AddressHierarchyContacts,
                recordFactories: [
                    AddressFactory::make()->id(691)->data(['company' => 'Mufflon GmbH']),
                ],
            ),
        ]));

        $record = AddressRepository::hierarchyContacts(691)->first();

        expect($record)->toBeArray()
            ->and($record['id'])->toBe(691)
            ->and($record['elements']['company'])->toBe('Mufflon GmbH');

        AddressRepository::assertSentCount(1);
    });
});

describe('toRequest', function () {
    test('builds the addressHierarchyContacts request without sending it', function () {
        $request = AddressRepository::hierarchyContacts(691)->toRequest();

        expect($request->actionId)->toBe(OnOfficeAction::Get)
            ->and($request->resourceType)->toBe(OnOfficeResourceType::AddressHierarchyContacts)
            ->and($request->resourceId)->toBe(691)
            ->and($request->parameters)->toBe([]);
    });

    test('address hierarchy contact builders can be batched', function () {
        Query::fake(Query::response([
            Query::page(actionId: OnOfficeAction::Get, resourceType: OnOfficeResourceType::AddressHierarchyContacts, recordFactories: [
                AddressFactory::make()->id(691),
            ]),
            Query::page(actionId: OnOfficeAction::Get, resourceType: OnOfficeResourceType::AddressHierarchyContacts, recordFactories: [
                AddressFactory::make()->id(700),
            ]),
        ]));

        $results = Query::batch([
            AddressRepository::hierarchyContacts(691),
            AddressRepository::hierarchyContacts(700),
        ])->once();

        expect($results)->toHaveCount(2)
            ->and(data_get($results[0], 'data.records.0.id'))->toBe(691)
            ->and(data_get($results[1], 'data.records.0.id'))->toBe(700);

        Query::assertSent(fn (OnOfficeRequest $request) => $request->resourceType === OnOfficeResourceType::AddressHierarchyContacts
            && $request->resourceId === 691);
        Query::assertSent(fn (OnOfficeRequest $request) => $request->resourceId === 700);
    });
});

describe('real responses', function () {
    test('get returns the hierarchy contacts from the response', function () {
        Http::preventStrayRequests();
        Http::fake([
            'https://api.onoffice.de/api/stable/api.php' => GetAddressHierarchyContactsResponse::make(),
        ]);

        AddressRepository::record();

        $records = AddressRepository::hierarchyContacts(691)->get();

        expect($records->pluck('id')->all())->toBe([691, 693])
            ->and($records->pluck('elements.primaryEmail')->all())->toBe(['info@my-onoffice.com', 'g.mufflon@my-onoffice.com']);

        AddressRepository::assertSent(fn (OnOfficeRequest $request) => $request->actionId === OnOfficeAction::Get
            && $request->resourceType === OnOfficeResourceType::AddressHierarchyContacts
            && $request->resourceId === 691);
        AddressRepository::assertSentCount(1);
    });

    test('first returns the first hierarchy contact from the response', function () {
        Http::preventStrayRequests();
        Http::fake([
            'https://api.onoffice.de/api/stable/api.php' => GetAddressHierarchyContactsResponse::make(),
        ]);

        AddressRepository::record();

        $record = AddressRepository::hierarchyContacts(691)->first();

        expect($record)->toBeArray()
            ->and($record['id'])->toBe(691);

        AddressRepository::assertSentCount(1);
    });
});
