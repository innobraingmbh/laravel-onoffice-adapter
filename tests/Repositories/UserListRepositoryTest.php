<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Innobrain\OnOfficeAdapter\Dtos\OnOfficeRequest;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeAction;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeResourceId;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeResourceType;
use Innobrain\OnOfficeAdapter\Facades\Query;
use Innobrain\OnOfficeAdapter\Facades\Testing\RecordFactories\UserFactory;
use Innobrain\OnOfficeAdapter\Facades\UserRepository;
use Innobrain\OnOfficeAdapter\Tests\Stubs\GetUsersResponse;

describe('fake responses', function () {
    test('get', function () {
        Http::preventStrayRequests();
        UserRepository::fake(UserRepository::response([
            UserRepository::page(
                actionId: OnOfficeAction::Get,
                resourceType: OnOfficeResourceType::Users,
                recordFactories: [
                    UserFactory::make()->id(25)->data(['id' => 25, 'firstname' => 'Paul', 'lastname' => 'Probe']),
                    UserFactory::make()->id(17)->data(['id' => 17, 'firstname' => 'Theo', 'lastname' => 'Test']),
                ],
            ),
        ]));

        $response = UserRepository::list()->get();

        expect($response->pluck('id')->all())->toBe([25, 17])
            ->and($response->last()['elements']['firstname'])->toBe('Theo');

        UserRepository::assertSentCount(1);
    });

    test('first', function () {
        Http::preventStrayRequests();
        UserRepository::fake(UserRepository::response([
            UserRepository::page(
                actionId: OnOfficeAction::Get,
                resourceType: OnOfficeResourceType::Users,
                recordFactories: [
                    UserFactory::make()->id(25)->data(['id' => 25, 'firstname' => 'Paul', 'lastname' => 'Probe']),
                ],
            ),
        ]));

        $record = UserRepository::list()->first();

        expect($record)->toBeArray()
            ->and($record['id'])->toBe(25)
            ->and($record['elements']['lastname'])->toBe('Probe');

        UserRepository::assertSentCount(1);
    });
});

describe('toRequest', function () {
    test('builds the users request without sending it', function () {
        $request = UserRepository::list()->toRequest();

        expect($request->actionId)->toBe(OnOfficeAction::Get)
            ->and($request->resourceType)->toBe(OnOfficeResourceType::Users)
            ->and($request->resourceId)->toBe(OnOfficeResourceId::None)
            ->and($request->parameters)->toBe([]);
    });

    test('group sends the group id together with the byGroupId user filter', function () {
        $request = UserRepository::list()->group(225)->toRequest();

        expect($request->parameters)->toBe([
            'userfilter' => 'byGroupId',
            'groupId' => 225,
        ]);
    });

    test('onlyOwn sends the onlyOwn user filter', function () {
        $request = UserRepository::list()->onlyOwn()->toRequest();

        expect($request->parameters)->toBe([
            'userfilter' => 'onlyOwn',
        ]);
    });

    test('the user filter set last replaces the one before', function () {
        expect(UserRepository::list()->group(225)->onlyOwn()->toRequest()->parameters)->toBe([
            'userfilter' => 'onlyOwn',
        ])->and(UserRepository::list()->onlyOwn()->group(225)->toRequest()->parameters)->toBe([
            'userfilter' => 'byGroupId',
            'groupId' => 225,
        ]);
    });

    test('custom parameters are sent and win over the user filter', function () {
        $request = UserRepository::list()
            ->onlyOwn()
            ->parameter('userfilter', 'ownOfficeGroupOrAll')
            ->toRequest();

        expect($request->parameters)->toBe([
            'userfilter' => 'ownOfficeGroupOrAll',
        ]);
    });

    test('select, where and orderBy leave the request untouched', function () {
        $request = UserRepository::list()
            ->select(['Vorname'])
            ->where('Nr', 25)
            ->orderBy('Nachname')
            ->toRequest();

        expect($request->parameters)->toBe([]);
    });

    test('user list builders can be batched', function () {
        Query::fake(Query::response([
            Query::page(actionId: OnOfficeAction::Get, resourceType: OnOfficeResourceType::Users, recordFactories: [
                UserFactory::make()->id(25),
            ]),
            Query::page(actionId: OnOfficeAction::Get, resourceType: OnOfficeResourceType::Users, recordFactories: [
                UserFactory::make()->id(17),
            ]),
        ]));

        $results = Query::batch([
            UserRepository::list()->group(225),
            UserRepository::list()->group(229),
        ])->once();

        expect($results)->toHaveCount(2)
            ->and(data_get($results[0], 'data.records.0.id'))->toBe(25)
            ->and(data_get($results[1], 'data.records.0.id'))->toBe(17);

        Query::assertSent(fn (OnOfficeRequest $request) => $request->resourceType === OnOfficeResourceType::Users
            && $request->parameters['groupId'] === 225);
        Query::assertSent(fn (OnOfficeRequest $request) => $request->parameters['groupId'] === 229);
    });
});

describe('real responses', function () {
    test('get returns the users from the response', function () {
        Http::preventStrayRequests();
        Http::fake([
            'https://api.onoffice.de/api/stable/api.php' => GetUsersResponse::make(),
        ]);

        UserRepository::record();

        $records = UserRepository::list()->group(225)->get();

        expect($records->pluck('id')->all())->toBe([25, 17])
            ->and($records->pluck('elements.firstname')->all())->toBe(['Paul', 'Theo'])
            ->and($records->first()['elements'])->toHaveKeys(['id', 'username', 'firstname', 'lastname', 'email', 'meetingurl', 'appointmentColor', 'code']);

        UserRepository::assertSent(fn (OnOfficeRequest $request) => $request->actionId === OnOfficeAction::Get
            && $request->resourceType === OnOfficeResourceType::Users
            && $request->parameters === ['userfilter' => 'byGroupId', 'groupId' => 225]);
        UserRepository::assertSentCount(1);
    });

    test('get sends a single request although the response reports no absolute count', function () {
        Http::preventStrayRequests();
        Http::fake([
            'https://api.onoffice.de/api/stable/api.php' => GetUsersResponse::make(),
        ]);

        UserRepository::list()->get();

        Http::assertSentCount(1);
    });

    test('first returns the first user from the response', function () {
        Http::preventStrayRequests();
        Http::fake([
            'https://api.onoffice.de/api/stable/api.php' => GetUsersResponse::make(),
        ]);

        UserRepository::record();

        $record = UserRepository::list()->first();

        expect($record)->toBeArray()
            ->and($record['id'])->toBe(25);

        UserRepository::assertSentCount(1);
    });
});
