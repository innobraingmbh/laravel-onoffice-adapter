<?php

use Illuminate\Support\Facades\Http;
use Katalam\OnOfficeAdapter\Facades\SettingRepository;
use Katalam\OnOfficeAdapter\Tests\Stubs\GetRegionsResponse;
use Katalam\OnOfficeAdapter\Tests\Stubs\ReadUserResponse;

it('works', function () {
    Http::preventStrayRequests();
    Http::fake([
        '*' => GetRegionsResponse::make(),
    ]);

    $regions = SettingRepository::regions()
        ->get();

    expect($regions)
        ->toHaveCount(1)
        ->and(data_get($regions, '0.elements.children'))
        ->toHaveCount(2);
});
