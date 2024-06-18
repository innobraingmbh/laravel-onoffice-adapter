<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Katalam\OnOfficeAdapter\Facades\SettingRepository;
use Katalam\OnOfficeAdapter\Tests\Stubs\GetImprintResponse;

it('works', function () {
    Http::preventStrayRequests();
    Http::fake([
        '*' => GetImprintResponse::make(),
    ]);

    $imprint = SettingRepository::imprint()
        ->get();

    expect($imprint)
        ->toHaveCount(1)
        ->and(data_get($imprint, '0.elements.firstname'))
        ->toBe('Max');
});
