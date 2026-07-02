<?php

declare(strict_types=1);

use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Innobrain\OnOfficeAdapter\Dtos\OnOfficeRequest;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeAction;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeResourceType;
use Innobrain\OnOfficeAdapter\Services\OnOfficeService;

use function Pest\Laravel\travelTo;

it('signs the exact timestamp it reports, even when the clock ticks over a second boundary between building the action and the hmac', function () {
    Config::set([
        'onoffice.token' => Str::random(32),
        'onoffice.secret' => Str::random(64),
    ]);

    $onOfficeService = resolve(OnOfficeService::class);
    $request = new OnOfficeRequest(OnOfficeAction::Read, OnOfficeResourceType::Estate);

    $now = Carbon::create(2026, 7, 2, 23, 59, 59);
    travelTo($now);

    $action = $request->toActionArray($onOfficeService);

    travelTo($now->clone()->addSecond());
    $hmacIfClockWereReadAgain = $onOfficeService->getHmac(
        OnOfficeAction::Read,
        OnOfficeResourceType::Estate,
        Carbon::now()->getTimestamp(),
    );

    expect($action['timestamp'])->toBe($now->getTimestamp())
        ->and($action['hmac'])->not->toBe($hmacIfClockWereReadAgain)
        ->and($onOfficeService->getHmac(OnOfficeAction::Read, OnOfficeResourceType::Estate, $action['timestamp']))
        ->toBe($action['hmac']);
});
