<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeAction;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeResourceType;
use Innobrain\OnOfficeAdapter\Query\TaskBuilder;
use Innobrain\OnOfficeAdapter\Repositories\TaskRepository;

describe('related parameter helpers', function () {
    it('emits no related parameters when none are set', function () {
        $builder = new TaskBuilder;

        $m = new ReflectionMethod($builder, 'prepareRelatedParameters');
        $m->setAccessible(true);

        expect($m->invoke($builder))->toBe([]);
    });

    it('combines all related parameters correctly', function () {
        $builder = new TaskBuilder;

        $builder
            ->relatedEstateId(123)
            ->relatedAddressId(456)
            ->relatedProjectIds([7, 8]);

        $m = new ReflectionMethod($builder, 'prepareRelatedParameters');
        $m->setAccessible(true);

        expect($m->invoke($builder))->toBe([
            'relatedEstateId' => 123,
            'relatedAddressId' => 456,
            'relatedProjectIds' => [7, 8],
        ]);
    });

    it('passes through a scalar relatedProjectIds value unchanged', function () {
        $builder = new TaskBuilder;

        $builder->relatedProjectIds(9);

        $m = new ReflectionMethod($builder, 'prepareRelatedParameters');
        $m->setAccessible(true);

        expect($m->invoke($builder))->toBe(['relatedProjectIds' => 9]);
    });
});

describe('CRUD operations', function () {
    beforeEach(function () {
        Http::preventStrayRequests();
        Http::fake([
            'https://api.onoffice.de/api/stable/api.php' => Http::response([
                'status' => [
                    'code' => 200,
                ],
                'response' => [
                    'results' => [
                        [
                            'data' => [
                                'meta' => [
                                    'cntabsolute' => 1,
                                ],
                                'records' => [
                                    ['id' => 1, 'type' => 'task'],
                                ],
                            ],
                        ],
                    ],
                ],
            ]),
        ]);
    });

    it('reads tasks with related parameters and selected columns', function () {
        $builder = new TaskBuilder;

        $builder
            ->setRepository(new TaskRepository)
            ->select(['Betreff', 'Status'])
            ->relatedEstateId(123)
            ->get();

        Http::assertSent(function (Illuminate\Http\Client\Request $request) {
            $body = json_decode($request->body(), true);

            return data_get($body, 'request.actions.0.actionid') === OnOfficeAction::Read->value
                && data_get($body, 'request.actions.0.resourcetype') === OnOfficeResourceType::Task->value
                && data_get($body, 'request.actions.0.parameters.relatedEstateId') === 123
                && data_get($body, 'request.actions.0.parameters.data') === ['Betreff', 'Status'];
        });
    });

    it('finds a task by id using the read action with resourceid', function () {
        $builder = new TaskBuilder;

        $builder
            ->setRepository(new TaskRepository)
            ->find(42);

        Http::assertSent(function (Illuminate\Http\Client\Request $request) {
            $body = json_decode($request->body(), true);

            return data_get($body, 'request.actions.0.actionid') === OnOfficeAction::Read->value
                && data_get($body, 'request.actions.0.resourcetype') === OnOfficeResourceType::Task->value
                && (int) data_get($body, 'request.actions.0.resourceid') === 42;
        });
    });

    it('creates a task with the payload nested in data and related ids at the top level', function () {
        $builder = new TaskBuilder;

        $builder
            ->setRepository(new TaskRepository)
            ->relatedEstateId(459)
            ->relatedAddressId(247)
            ->create([
                'Betreff' => 'Besichtigungstermin',
                'Verantwortung' => 'robert',
            ]);

        Http::assertSent(function (Illuminate\Http\Client\Request $request) {
            $body = json_decode($request->body(), true);

            return data_get($body, 'request.actions.0.actionid') === OnOfficeAction::Create->value
                && data_get($body, 'request.actions.0.resourcetype') === OnOfficeResourceType::Task->value
                && data_get($body, 'request.actions.0.parameters.relatedEstateId') === 459
                && data_get($body, 'request.actions.0.parameters.relatedAddressId') === 247
                && data_get($body, 'request.actions.0.parameters.data.Betreff') === 'Besichtigungstermin'
                && data_get($body, 'request.actions.0.parameters.data.Verantwortung') === 'robert';
        });
    });

    it('modifies a task with addModify and forwards related parameters', function () {
        $builder = new TaskBuilder;

        $result = $builder
            ->setRepository(new TaskRepository)
            ->relatedEstateId(459)
            ->addModify('Status', 2)
            ->modify(7);

        expect($result)->toBeTrue();

        Http::assertSent(function (Illuminate\Http\Client\Request $request) {
            $body = json_decode($request->body(), true);

            return data_get($body, 'request.actions.0.actionid') === OnOfficeAction::Modify->value
                && data_get($body, 'request.actions.0.resourcetype') === OnOfficeResourceType::Task->value
                && (int) data_get($body, 'request.actions.0.resourceid') === 7
                && data_get($body, 'request.actions.0.parameters.relatedEstateId') === 459
                && data_get($body, 'request.actions.0.parameters.data.Status') === 2;
        });
    });
});
