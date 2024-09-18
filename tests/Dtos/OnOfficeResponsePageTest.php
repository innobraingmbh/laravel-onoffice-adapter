<?php

declare(strict_types=1);

use Illuminate\Support\Collection;
use Katalam\OnOfficeAdapter\Dtos\OnOfficeResponsePage;
use Katalam\OnOfficeAdapter\Enums\OnOfficeAction;
use Katalam\OnOfficeAdapter\Enums\OnOfficeResourceId;
use Katalam\OnOfficeAdapter\Enums\OnOfficeResourceType;
use Katalam\OnOfficeAdapter\Facades\Testing\RecordFactories\EstateFactory;

it('works', function () {
    $recordFactories = new Collection;

    $recordFactories->push(EstateFactory::make()->id(4));

    $page = new OnOfficeResponsePage(
        OnOfficeAction::Read,
        OnOfficeResourceType::Estate,
        $recordFactories,
        201,
        1,
        'foo',
        OnOfficeResourceId::Estate,
        false,
        '21',
        1,
        2,
        'nar',
    );

    $response = $page->toResponse();

    expect($response)->toBeArray()
        ->toBe([
            'status' => [
                'code' => 201,
                'errorcode' => 1,
                'message' => 'foo',
            ],
            'response' => [
                'results' => [
                    [
                        'actionid' => OnOfficeAction::Read->value,
                        'resourceid' => OnOfficeResourceId::Estate->value,
                        'resourcetype' => OnOfficeResourceType::Estate->value,
                        'cacheable' => false,
                        'identifier' => '21',
                        'data' => [
                            'meta' => [
                                'cntabsolute' => 1,
                            ],
                            'records' => [
                                [
                                    'id' => 4,
                                    'type' => 'estate',
                                    'elements' => [],
                                ],
                            ],
                        ],
                        'status' => [
                            'errorcode' => 2,
                            'message' => 'nar',
                        ],
                    ],
                ],
            ],
        ]);
});
