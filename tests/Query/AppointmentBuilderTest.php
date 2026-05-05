<?php

declare(strict_types=1);

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeAction;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeResourceType;
use Innobrain\OnOfficeAdapter\Query\AppointmentBuilder;
use Innobrain\OnOfficeAdapter\Repositories\AppointmentRepository;

describe('date methods', function () {
    it('sets startDate and endDate via dateRange', function () {
        $builder = new AppointmentBuilder;
        $builder->dateRange('2026-01-01', '2026-12-31');

        expect($builder->startDate)->toBe('2026-01-01')
            ->and($builder->endDate)->toBe('2026-12-31');
    });

    it('returns builder instance for chaining', function () {
        $builder = new AppointmentBuilder;

        expect($builder->dateRange('2026-01-01', '2026-12-31'))->toBeInstanceOf(AppointmentBuilder::class);
    });
});

describe('get operation', function () {
    beforeEach(function () {
        Http::preventStrayRequests();
        Http::fake([
            'https://api.onoffice.de/api/stable/api.php' => Http::response([
                'status' => ['code' => 200],
                'response' => [
                    'results' => [
                        [
                            'data' => [
                                'meta' => ['cntabsolute' => 1],
                                'records' => [
                                    ['id' => 1, 'type' => 'appointmentList', 'elements' => []],
                                ],
                            ],
                        ],
                    ],
                ],
            ]),
        ]);
    });

    it('sends get request with correct action and resource type', function () {
        $builder = new AppointmentBuilder;
        $builder
            ->setRepository(new AppointmentRepository)
            ->dateRange('2026-01-01', '2026-12-31')
            ->get();

        Http::assertSent(function (Request $request) {
            $body = json_decode($request->body(), true);

            return data_get($body, 'request.actions.0.actionid') === OnOfficeAction::Get->value
                && data_get($body, 'request.actions.0.resourcetype') === OnOfficeResourceType::AppointmentList->value;
        });
    });

    it('includes startDate and endDate in request', function () {
        $builder = new AppointmentBuilder;
        $builder
            ->setRepository(new AppointmentRepository)
            ->dateRange('2026-01-01', '2026-12-31')
            ->get();

        Http::assertSent(function (Request $request) {
            $body = json_decode($request->body(), true);

            return data_get($body, 'request.actions.0.parameters.filter.startDate') === '2026-01-01'
                && data_get($body, 'request.actions.0.parameters.filter.endDate') === '2026-12-31';
        });
    });

    it('includes selected columns in data parameter', function () {
        $builder = new AppointmentBuilder;
        $builder
            ->setRepository(new AppointmentRepository)
            ->dateRange('2026-01-01', '2026-12-31')
            ->select(['subject', 'date'])
            ->get();

        Http::assertSent(function (Request $request) {
            $body = json_decode($request->body(), true);

            return data_get($body, 'request.actions.0.parameters.data') === ['subject', 'date'];
        });
    });

    it('includes filters in request', function () {
        $builder = new AppointmentBuilder;
        $builder
            ->setRepository(new AppointmentRepository)
            ->dateRange('2026-01-01', '2026-12-31')
            ->where('type', 'call')
            ->get();

        Http::assertSent(function (Request $request) {
            $body = json_decode($request->body(), true);
            $filter = data_get($body, 'request.actions.0.parameters.filter', []);

            return array_key_exists('type', $filter);
        });
    });

    it('throws when dateRange is missing on list reads', function () {
        $builder = new AppointmentBuilder;

        expect(fn () => $builder->setRepository(new AppointmentRepository)->get())
            ->toThrow(\Innobrain\OnOfficeAdapter\Exceptions\OnOfficeException::class);
    });
});
