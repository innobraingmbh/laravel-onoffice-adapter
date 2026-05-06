<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Innobrain\OnOfficeAdapter\Dtos\OnOfficeRequest;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeAction;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeResourceType;
use Innobrain\OnOfficeAdapter\Facades\AppointmentRepository;
use Innobrain\OnOfficeAdapter\Facades\Testing\RecordFactories\AppointmentFactory;
use Innobrain\OnOfficeAdapter\Tests\Stubs\GetAppointmentResponse;

describe('fake responses', function () {
    test('get', function () {
        AppointmentRepository::fake(AppointmentRepository::response([
            AppointmentRepository::page(recordFactories: [
                AppointmentFactory::make()
                    ->id(1),
            ]),
        ]));

        $response = AppointmentRepository::query()
            ->dateRange('2025-01-01', '2025-01-31')
            ->get();

        expect($response->count())->toBe(1)
            ->and($response->first()['id'])->toBe(1);

        AppointmentRepository::assertSentCount(1);
    });

    test('first', function () {
        AppointmentRepository::fake(AppointmentRepository::response([
            AppointmentRepository::page(recordFactories: [
                AppointmentFactory::make()
                    ->id(1),
                AppointmentFactory::make()
                    ->id(2),
            ]),
        ]));

        $response = AppointmentRepository::query()
            ->dateRange('2025-01-01', '2025-01-31')
            ->first();

        expect($response)->toBeArray()
            ->and($response['id'])->toBe(1);
    });

    test('find', function () {
        AppointmentRepository::fake(AppointmentRepository::response([
            AppointmentRepository::page(recordFactories: [
                AppointmentFactory::make()
                    ->id(42),
            ]),
        ]));

        $response = AppointmentRepository::query()->find(42);

        expect($response)->toBeArray()
            ->and($response['id'])->toBe(42);

        AppointmentRepository::assertSent(fn (OnOfficeRequest $request) => $request->actionId === OnOfficeAction::Get
            && $request->resourceType === OnOfficeResourceType::AppointmentList
            && $request->resourceId === 42
        );
    });

    test('create', function () {
        AppointmentRepository::fake(AppointmentRepository::response([
            AppointmentRepository::page(recordFactories: [
                AppointmentFactory::make()
                    ->id(1),
            ]),
        ]));

        $response = AppointmentRepository::query()->create([
            'subject' => 'Test Appointment',
            'start_dt' => '2025-01-01 10:00:00',
            'end_dt' => '2025-01-01 11:00:00',
        ]);

        expect($response)->toBeArray()
            ->and($response['id'])->toBe(1);

        AppointmentRepository::assertSent(fn (OnOfficeRequest $request) => $request->actionId === OnOfficeAction::Create
            && $request->resourceType === OnOfficeResourceType::Calendar
        );
    });

    test('modify', function () {
        AppointmentRepository::fake(AppointmentRepository::response([
            AppointmentRepository::page(recordFactories: [
                AppointmentFactory::make()
                    ->set('success', 'success'),
            ]),
        ]));

        $result = AppointmentRepository::query()
            ->addModify('subject', 'Updated Subject')
            ->modify(42);

        expect($result)->toBeTrue();

        AppointmentRepository::assertSent(fn (OnOfficeRequest $request) => $request->actionId === OnOfficeAction::Modify
            && $request->resourceType === OnOfficeResourceType::Calendar
            && $request->resourceId === 42
            && $request->parameters['data']['subject'] === 'Updated Subject'
        );
    });

    test('delete', function () {
        AppointmentRepository::fake(AppointmentRepository::response([
            AppointmentRepository::page(recordFactories: [
                AppointmentFactory::make()
                    ->set('success', 'success'),
            ]),
        ]));

        $result = AppointmentRepository::query()->delete(42);

        expect($result)->toBeTrue();

        AppointmentRepository::assertSent(fn (OnOfficeRequest $request) => $request->actionId === OnOfficeAction::Delete
            && $request->resourceType === OnOfficeResourceType::Calendar
            && $request->resourceId === 42
        );
    });

    test('conflicts', function () {
        AppointmentRepository::fake(AppointmentRepository::response([
            AppointmentRepository::page(recordFactories: [
                AppointmentFactory::make()->id(1),
            ]),
        ]));

        $response = AppointmentRepository::query()->conflicts([
            'start_dt' => '2025-01-01 10:00:00',
            'end_dt' => '2025-01-01 11:00:00',
            'subscribers' => [1, 2],
        ]);

        expect($response)->toBeArray();

        AppointmentRepository::assertSent(fn (OnOfficeRequest $request) => $request->actionId === OnOfficeAction::Get
            && $request->resourceType === OnOfficeResourceType::AppointmentConflicts
        );
    });

    test('sendConfirmation', function () {
        AppointmentRepository::fake(AppointmentRepository::response([
            AppointmentRepository::page(recordFactories: [
                AppointmentFactory::make()->id(1),
            ]),
        ]));

        $response = AppointmentRepository::query()->sendConfirmation(42);

        expect($response)->toBeArray();

        AppointmentRepository::assertSent(fn (OnOfficeRequest $request) => $request->actionId === OnOfficeAction::Do
            && $request->resourceType === OnOfficeResourceType::AppointmentAffirmation
            && $request->parameters['calendarId'] === 42
        );
    });

    test('resources', function () {
        AppointmentRepository::fake(AppointmentRepository::response([
            AppointmentRepository::page(recordFactories: [
                AppointmentFactory::make()->id(1)->set('name', 'Room A'),
            ]),
        ]));

        $response = AppointmentRepository::query()->resources();

        expect($response->count())->toBe(1);

        AppointmentRepository::assertSent(fn (OnOfficeRequest $request) => $request->actionId === OnOfficeAction::Get
            && $request->resourceType === OnOfficeResourceType::CalendarResources
        );
    });

    test('files', function () {
        AppointmentRepository::fake(AppointmentRepository::response([
            AppointmentRepository::page(recordFactories: [
                AppointmentFactory::make()->id(1),
            ]),
        ]));

        $response = AppointmentRepository::files(42)->get();

        expect($response->count())->toBe(1);

        AppointmentRepository::assertSent(fn (OnOfficeRequest $request) => $request->actionId === OnOfficeAction::Get
            && $request->resourceType === OnOfficeResourceType::File
            && $request->resourceId === 'appointment'
            && $request->parameters['appointmentid'] === 42
        );
    });
});

describe('real responses', function () {
    test('get', function () {
        Http::preventStrayRequests();
        Http::fake([
            'https://api.onoffice.de/api/stable/api.php' => Http::sequence([
                GetAppointmentResponse::make(count: 1500),
                GetAppointmentResponse::make(count: 1500),
                GetAppointmentResponse::make(count: 1500),
            ]),
        ]);

        AppointmentRepository::record();

        $response = AppointmentRepository::query()
            ->dateRange('2025-01-01', '2025-12-31')
            ->get();

        expect($response->count())->toBe(3);

        AppointmentRepository::assertSentCount(3);
    });
});

describe('filter methods', function () {
    test('dateRange', function () {
        AppointmentRepository::fake(AppointmentRepository::response([
            AppointmentRepository::page(recordFactories: [
                AppointmentFactory::make()->id(1),
            ]),
        ]));

        AppointmentRepository::query()
            ->dateRange('2025-01-01', '2025-01-31')
            ->get();

        AppointmentRepository::assertSent(fn (OnOfficeRequest $request) => $request->parameters['filter']['startDate'] === '2025-01-01'
            && $request->parameters['filter']['endDate'] === '2025-01-31'
        );
    });

    test('users', function () {
        AppointmentRepository::fake(AppointmentRepository::response([
            AppointmentRepository::page(recordFactories: [
                AppointmentFactory::make()->id(1),
            ]),
        ]));

        AppointmentRepository::query()
            ->dateRange('2025-01-01', '2025-01-31')
            ->users([1, 2])
            ->get();

        AppointmentRepository::assertSent(fn (OnOfficeRequest $request) => $request->parameters['filter']['userIds'] === [1, 2]
        );
    });

    test('groups', function () {
        AppointmentRepository::fake(AppointmentRepository::response([
            AppointmentRepository::page(recordFactories: [
                AppointmentFactory::make()->id(1),
            ]),
        ]));

        AppointmentRepository::query()
            ->dateRange('2025-01-01', '2025-01-31')
            ->groups([3, 4])
            ->get();

        AppointmentRepository::assertSent(fn (OnOfficeRequest $request) => $request->parameters['filter']['groupIds'] === [3, 4]
        );
    });

    test('cancelled', function () {
        AppointmentRepository::fake(AppointmentRepository::response([
            AppointmentRepository::page(recordFactories: [
                AppointmentFactory::make()->id(1),
            ]),
        ]));

        AppointmentRepository::query()
            ->dateRange('2025-01-01', '2025-01-31')
            ->cancelled()
            ->get();

        AppointmentRepository::assertSent(fn (OnOfficeRequest $request) => $request->parameters['filter']['isCancelled'] === true
        );
    });

    test('done', function () {
        AppointmentRepository::fake(AppointmentRepository::response([
            AppointmentRepository::page(recordFactories: [
                AppointmentFactory::make()->id(1),
            ]),
        ]));

        AppointmentRepository::query()
            ->dateRange('2025-01-01', '2025-01-31')
            ->done()
            ->get();

        AppointmentRepository::assertSent(fn (OnOfficeRequest $request) => $request->parameters['filter']['isDone'] === true
        );
    });

    test('recurrent', function () {
        AppointmentRepository::fake(AppointmentRepository::response([
            AppointmentRepository::page(recordFactories: [
                AppointmentFactory::make()->id(1),
            ]),
        ]));

        AppointmentRepository::query()
            ->dateRange('2025-01-01', '2025-01-31')
            ->recurrent()
            ->get();

        AppointmentRepository::assertSent(fn (OnOfficeRequest $request) => $request->parameters['filter']['isRecurrent'] === true
        );
    });
});
