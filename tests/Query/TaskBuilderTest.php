<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeAction;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeResourceType;
use Innobrain\OnOfficeAdapter\Query\TaskBuilder;
use Innobrain\OnOfficeAdapter\Repositories\TaskRepository;

describe('relation filter methods', function () {
    it('sets relatedAddressId', function () {
        $builder = new TaskBuilder;
        $builder->relatedAddress(42);

        expect($builder->relatedAddressId)->toBe(42);
    });

    it('sets relatedEstateId', function () {
        $builder = new TaskBuilder;
        $builder->relatedEstate(99);

        expect($builder->relatedEstateId)->toBe(99);
    });

    it('sets relatedProjectId', function () {
        $builder = new TaskBuilder;
        $builder->relatedProject(7);

        expect($builder->relatedProjectId)->toBe(7);
    });

    it('returns builder instance for chaining', function () {
        $builder = new TaskBuilder;

        expect($builder->relatedAddress(1))->toBeInstanceOf(TaskBuilder::class)
            ->and($builder->relatedEstate(2))->toBeInstanceOf(TaskBuilder::class)
            ->and($builder->relatedProject(3))->toBeInstanceOf(TaskBuilder::class);
    });
});

describe('CRUD operations', function () {
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
                                    ['id' => 1, 'type' => 'task', 'elements' => []],
                                ],
                            ],
                        ],
                    ],
                ],
            ]),
        ]);
    });

    it('sends read request with correct action and resource type', function () {
        $builder = new TaskBuilder;
        $builder->setRepository(new TaskRepository)->get();

        Http::assertSent(function (Illuminate\Http\Client\Request $request) {
            $body = json_decode($request->body(), true);

            return data_get($body, 'request.actions.0.actionid') === OnOfficeAction::Read->value
                && data_get($body, 'request.actions.0.resourcetype') === OnOfficeResourceType::Task->value;
        });
    });

    it('includes relatedAddressId in request when set', function () {
        $builder = new TaskBuilder;
        $builder->setRepository(new TaskRepository)->relatedAddress(42)->get();

        Http::assertSent(function (Illuminate\Http\Client\Request $request) {
            $body = json_decode($request->body(), true);

            return data_get($body, 'request.actions.0.parameters.relatedAddressId') === 42;
        });
    });

    it('includes relatedEstateId in request when set', function () {
        $builder = new TaskBuilder;
        $builder->setRepository(new TaskRepository)->relatedEstate(99)->get();

        Http::assertSent(function (Illuminate\Http\Client\Request $request) {
            $body = json_decode($request->body(), true);

            return data_get($body, 'request.actions.0.parameters.relatedEstateId') === 99;
        });
    });

    it('omits null relation parameters from request', function () {
        $builder = new TaskBuilder;
        $builder->setRepository(new TaskRepository)->get();

        Http::assertSent(function (Illuminate\Http\Client\Request $request) {
            $body = json_decode($request->body(), true);
            $params = data_get($body, 'request.actions.0.parameters', []);

            return ! array_key_exists('relatedAddressId', $params)
                && ! array_key_exists('relatedEstateId', $params)
                && ! array_key_exists('relatedProjectIds', $params);
        });
    });

    it('sends create request with correct action and data', function () {
        $builder = new TaskBuilder;
        $builder->setRepository(new TaskRepository)->create([
            'Betreff' => 'Call back',
            'Prio' => 3,
        ]);

        Http::assertSent(function (Illuminate\Http\Client\Request $request) {
            $body = json_decode($request->body(), true);

            return data_get($body, 'request.actions.0.actionid') === OnOfficeAction::Create->value
                && data_get($body, 'request.actions.0.resourcetype') === OnOfficeResourceType::Task->value
                && data_get($body, 'request.actions.0.parameters.data.Betreff') === 'Call back'
                && data_get($body, 'request.actions.0.parameters.data.Prio') === 3;
        });
    });

    it('forwards related ids at top level on create', function () {
        $builder = new TaskBuilder;
        $builder->setRepository(new TaskRepository)
            ->relatedEstate(42)
            ->relatedAddress(7)
            ->relatedProject(11)
            ->create(['Betreff' => 'Linked task']);

        Http::assertSent(function (Illuminate\Http\Client\Request $request) {
            $body = json_decode($request->body(), true);
            $params = data_get($body, 'request.actions.0.parameters', []);

            return data_get($params, 'relatedEstateId') === 42
                && data_get($params, 'relatedAddressId') === 7
                && data_get($params, 'relatedProjectIds') === 11
                && ! array_key_exists('relatedEstateId', data_get($params, 'data', []))
                && ! array_key_exists('relatedAddressId', data_get($params, 'data', []));
        });
    });

    it('returns the created record from create()', function () {
        $builder = new TaskBuilder;
        $result = $builder->setRepository(new TaskRepository)->create(['Betreff' => 'New']);

        expect($result)->toBe(['id' => 1, 'type' => 'task', 'elements' => []]);
    });

    it('sends modify request with resourceid and data', function () {
        $builder = new TaskBuilder;
        $result = $builder->setRepository(new TaskRepository)
            ->addModify('Status', 4)
            ->modify(99);

        expect($result)->toBeTrue();

        Http::assertSent(function (Illuminate\Http\Client\Request $request) {
            $body = json_decode($request->body(), true);

            return data_get($body, 'request.actions.0.actionid') === OnOfficeAction::Modify->value
                && data_get($body, 'request.actions.0.resourcetype') === OnOfficeResourceType::Task->value
                && data_get($body, 'request.actions.0.resourceid') === 99
                && data_get($body, 'request.actions.0.parameters.data.Status') === 4;
        });
    });

    it('forwards related ids at top level on modify', function () {
        $builder = new TaskBuilder;
        $builder->setRepository(new TaskRepository)
            ->relatedEstate(42)
            ->addModify('Status', 4)
            ->modify(99);

        Http::assertSent(function (Illuminate\Http\Client\Request $request) {
            $body = json_decode($request->body(), true);
            $params = data_get($body, 'request.actions.0.parameters', []);

            return data_get($params, 'relatedEstateId') === 42
                && ! array_key_exists('relatedEstateId', data_get($params, 'data', []));
        });
    });

    it('sends read request for find()', function () {
        $builder = new TaskBuilder;
        $builder->setRepository(new TaskRepository)->find(5);

        Http::assertSent(function (Illuminate\Http\Client\Request $request) {
            $body = json_decode($request->body(), true);

            return data_get($body, 'request.actions.0.actionid') === OnOfficeAction::Read->value
                && data_get($body, 'request.actions.0.resourcetype') === OnOfficeResourceType::Task->value
                && data_get($body, 'request.actions.0.resourceid') === 5;
        });
    });
});
