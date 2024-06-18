<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Katalam\OnOfficeAdapter\Facades\MarketplaceRepository;
use Katalam\OnOfficeAdapter\Tests\Stubs\DoUnlockProviderResponse;

it('works', function () {
    Http::preventStrayRequests();
    Http::fake([
        '*' => DoUnlockProviderResponse::make(),
    ]);

    $success = MarketplaceRepository::query()
        ->unlockProvider('foo', 'bar');

    expect($success)->toBeTrue();
});
