<?php

declare(strict_types=1);

use Illuminate\Support\Collection;
use Innobrain\OnOfficeAdapter\Dtos\OnOfficeResponse;
use Innobrain\OnOfficeAdapter\Dtos\OnOfficeResponsePage;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeAction;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeResourceType;
use Innobrain\OnOfficeAdapter\Facades\Testing\RecordFactories\EstateFactory;

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
