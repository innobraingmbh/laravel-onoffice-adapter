<?php

declare(strict_types=1);

use Illuminate\Support\Collection;
use Katalam\OnOfficeAdapter\Dtos\OnOfficeResponse;
use Katalam\OnOfficeAdapter\Dtos\OnOfficeResponsePage;
use Katalam\OnOfficeAdapter\Enums\OnOfficeAction;
use Katalam\OnOfficeAdapter\Enums\OnOfficeResourceType;
use Katalam\OnOfficeAdapter\Facades\Testing\RecordFactories\EstateFactory;

it('works', function () {
    $recordFactories = new Collection;

    $recordFactories->push(EstateFactory::make()->id(4));

    $page = new OnOfficeResponsePage(
        OnOfficeAction::Read,
        OnOfficeResourceType::Estate,
        $recordFactories,
    );

    $response = new OnOfficeResponse(collect([$page]));

    expect($response->shift())->toBeInstanceOf(OnOfficeResponsePage::class)
        ->and($response->isEmpty())->toBeTrue();
});
