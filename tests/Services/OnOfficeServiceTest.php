<?php

use Illuminate\Support\Str;
use Katalam\OnOfficeAdapter\Services\OnOfficeService;

it('can use the config token and secret', function () {
    $token = Str::random();
    $secret = Str::random();

    config([
        'onoffice.token' => $token,
        'onoffice.secret' => $secret,
    ]);

    $onOfficeService = app(OnOfficeService::class);

    expect($onOfficeService->getToken())->toBe($token)
        ->and($onOfficeService->getSecret())->toBe($secret);
});
