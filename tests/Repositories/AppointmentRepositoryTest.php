<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Innobrain\OnOfficeAdapter\Facades\AppointmentRepository;
use Innobrain\OnOfficeAdapter\Facades\Testing\RecordFactories\AppointmentFactory;
use Innobrain\OnOfficeAdapter\Tests\Stubs\GetAppointmentListResponse;

describe('fake responses', function () {
    test('get', function () {
        AppointmentRepository::fake(AppointmentRepository::response([
            AppointmentRepository::page(recordFactories: [
                AppointmentFactory::make()->id(1),
            ]),
        ]));

        $response = AppointmentRepository::query()->get();

        expect($response->count())->toBe(1)
            ->and($response->first()['id'])->toBe(1);

        AppointmentRepository::assertSentCount(1);
    });

    test('get with date range', function () {
        AppointmentRepository::fake(AppointmentRepository::response([
            AppointmentRepository::page(recordFactories: [
                AppointmentFactory::make()->id(1),
                AppointmentFactory::make()->id(2),
            ]),
        ]));

        $response = AppointmentRepository::query()
            ->startDate('2026-01-01')
            ->endDate('2026-12-31')
            ->get();

        expect($response->count())->toBe(2);

        AppointmentRepository::assertSentCount(1);
    });
});

describe('real responses', function () {
    test('get', function () {
        Http::preventStrayRequests();
        Http::fake([
            'https://api.onoffice.de/api/stable/api.php' => Http::sequence([
                GetAppointmentListResponse::make(count: 1),
            ]),
        ]);

        AppointmentRepository::record();

        $response = AppointmentRepository::query()->get();

        expect($response->count())->toBe(1);

        AppointmentRepository::assertSentCount(1);
    });
});
