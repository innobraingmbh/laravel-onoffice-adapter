<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeAction;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeResourceType;
use Innobrain\OnOfficeAdapter\Query\MacroBuilder;
use Innobrain\OnOfficeAdapter\Repositories\MacroRepository;

describe('parameter methods', function () {
    it('sets text parameter correctly', function () {
        $builder = new MacroBuilder;
        $builder->text('_Uservorname _Username');

        expect($builder->customParameters)->toBe(['text' => '_Uservorname _Username']);
    });

    it('sets isHtml parameter correctly', function () {
        $builder = new MacroBuilder;
        $builder->isHtml();

        expect($builder->customParameters)->toBe(['ishtml' => true]);
    });

    it('sets estateIds parameter correctly with array', function () {
        $builder = new MacroBuilder;
        $builder->estateIds([1, 2, 3]);

        expect($builder->customParameters)->toBe(['estateids' => [1, 2, 3]]);
    });

    it('sets estateIds parameter correctly with single ID', function () {
        $builder = new MacroBuilder;
        $builder->estateIds(123);

        expect($builder->customParameters)->toBe(['estateids' => [123]]);
    });

    it('sets addressIds parameter correctly', function () {
        $builder = new MacroBuilder;
        $builder->addressIds([1, 2, 3]);

        expect($builder->customParameters)->toBe(['addressids' => [1, 2, 3]]);
    });

    it('sets appointmentIds parameter correctly', function () {
        $builder = new MacroBuilder;
        $builder->appointmentIds([1, 2, 3]);

        expect($builder->customParameters)->toBe(['appointmentids' => [1, 2, 3]]);
    });

    it('sets agentLogIds parameter correctly', function () {
        $builder = new MacroBuilder;
        $builder->agentLogIds([1, 2, 3]);

        expect($builder->customParameters)->toBe(['agentlogids' => [1, 2, 3]]);
    });
});

describe('resolve operation', function () {
    beforeEach(function () {
        Http::preventStrayRequests();
        Http::fake([
            'https://api.onoffice.de/api/stable/api.php/' => Http::response([
                'status' => [
                    'code' => 200,
                ],
                'response' => [
                    'results' => [
                        [
                            'data' => [
                                'records' => [
                                    [
                                        'elements' => [
                                            'resolvedtext' => 'Max Mustermann',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]),
        ]);
    });

    it('resolves macro with text parameter', function () {
        $builder = new MacroBuilder;

        $builder
            ->setRepository(new MacroRepository)
            ->text('_Uservorname _Username')
            ->resolve();

        Http::assertSent(function (Illuminate\Http\Client\Request $request) {
            $body = json_decode($request->body(), true);

            return data_get($body, 'request.actions.0.parameters.text') === '_Uservorname _Username'
                && data_get($body, 'request.actions.0.actionid') === OnOfficeAction::Get->value
                && data_get($body, 'request.actions.0.resourcetype') === OnOfficeResourceType::MacroResolve->value;
        });
    });

    it('resolves macro with all parameters', function () {
        $builder = new MacroBuilder;

        $builder
            ->setRepository(new MacroRepository)
            ->text('_Uservorname _Username')
            ->isHtml()
            ->estateIds([1, 2])
            ->addressIds([3, 4])
            ->appointmentIds([5, 6])
            ->agentLogIds([7, 8])
            ->resolve();

        Http::assertSent(function (Illuminate\Http\Client\Request $request) {
            $body = json_decode($request->body(), true);

            return data_get($body, 'request.actions.0.parameters.text') === '_Uservorname _Username'
                && data_get($body, 'request.actions.0.parameters.ishtml') === true
                && data_get($body, 'request.actions.0.parameters.estateids') === [1, 2]
                && data_get($body, 'request.actions.0.parameters.addressids') === [3, 4]
                && data_get($body, 'request.actions.0.parameters.appointmentids') === [5, 6]
                && data_get($body, 'request.actions.0.parameters.agentlogids') === [7, 8];
        });
    });

    it('returns resolved text', function () {
        $builder = new MacroBuilder;

        $resolvedText = $builder
            ->setRepository(new MacroRepository)
            ->text('_Uservorname _Username')
            ->resolve();

        expect($resolvedText)->toBe('Max Mustermann');
    });
});
