<?php

declare(strict_types=1);

use Illuminate\Support\Collection;
use Katalam\OnOfficeAdapter\Dtos\OnOfficeRequest;
use Katalam\OnOfficeAdapter\Dtos\OnOfficeResponse;
use Katalam\OnOfficeAdapter\Enums\OnOfficeAction;
use Katalam\OnOfficeAdapter\Enums\OnOfficeResourceType;
use Katalam\OnOfficeAdapter\Repositories\BaseRepository;

describe('stray requests', function () {
    it('will set the preventStrayRequests property when calling preventStrayRequests default', function () {
        $builder = new BaseRepository;

        $builder->preventStrayRequests();

        $m = new ReflectionProperty($builder, 'preventStrayRequests');
        $m->setAccessible(true);

        expect($m->getValue($builder))->toBe(true);
    });

    it('will set the preventStrayRequests property when calling preventStrayRequests with values', function ($value) {
        $builder = new BaseRepository;

        $builder->preventStrayRequests($value);

        $m = new ReflectionProperty($builder, 'preventStrayRequests');
        $m->setAccessible(true);

        expect($m->getValue($builder))->toBe($value);
    })->with([true, false]);

    it('will set the preventStrayRequest property when calling allowStrayRequests', function () {
        $builder = new BaseRepository;

        $builder->preventStrayRequests();
        $builder->preventStrayRequests(false);

        $m = new ReflectionProperty($builder, 'preventStrayRequests');
        $m->setAccessible(true);

        expect($m->getValue($builder))->toBe(false);
    });
});

describe('record', function () {
    it('will set the recording property when calling record default', function () {
        $builder = new BaseRepository;

        $builder->record();

        $m = new ReflectionProperty($builder, 'recording');
        $m->setAccessible(true);

        expect($m->getValue($builder))->toBe(true);
    });

    it('will set the recording property when calling stop recording', function () {
        $builder = new BaseRepository;

        $builder->record();
        $builder->stopRecording();

        $m = new ReflectionProperty($builder, 'recording');
        $m->setAccessible(true);

        expect($m->getValue($builder))->toBe(false);
    });

    it('will set the recording property when calling record with values', function ($value) {
        $builder = new BaseRepository;

        $builder->record($value);

        $m = new ReflectionProperty($builder, 'recording');
        $m->setAccessible(true);

        expect($m->getValue($builder))->toBe($value);
    })->with([true, false]);

    it('will add the request and response to the recorded property', function () {
        $builder = new BaseRepository;

        $builder->record();
        $builder->recordRequestResponsePair(new OnOfficeRequest(OnOfficeAction::Read, OnOfficeResourceType::Estate), ['response']);

        $m = new ReflectionProperty($builder, 'recorded');
        $m->setAccessible(true);

        expect($m->getValue($builder))->toBeArray()
            ->and($m->getValue($builder)[0][0]->toArray())->toBe((new OnOfficeRequest(OnOfficeAction::Read, OnOfficeResourceType::Estate))->toArray())
            ->and($m->getValue($builder)[0][1])->toBe(['response']);
    });

    it('will not add the request and response to the recorded property when recording is off', function () {
        $builder = new BaseRepository;

        $builder->recordRequestResponsePair(new OnOfficeRequest(OnOfficeAction::Read, OnOfficeResourceType::Estate), ['response']);

        $m = new ReflectionProperty($builder, 'recorded');
        $m->setAccessible(true);

        expect($m->getValue($builder))->toBe([]);
    });
});

describe('fake', function () {
    it('will add the response to the stubCallables property', function () {
        $builder = new BaseRepository;

        $builder->fake(new OnOfficeResponse(collect()));

        $m = new ReflectionProperty($builder, 'stubCallables');
        $m->setAccessible(true);

        expect($m->getValue($builder)->toArray()[0])->toBeInstanceOf(OnOfficeResponse::class);
    });

    it('will add the response to the stubCallables property when calling fake with an array', function () {
        $builder = new BaseRepository;

        $builder->fake([new OnOfficeResponse(collect())]);

        $m = new ReflectionProperty($builder, 'stubCallables');
        $m->setAccessible(true);

        expect($m->getValue($builder)->toArray()[0])->toBeInstanceOf(OnOfficeResponse::class);
    });
});

describe('assert', function () {
    it('can find a record by callable', function () {
        $builder = new BaseRepository;

        $builder->record();
        $builder->recordRequestResponsePair(new OnOfficeRequest(OnOfficeAction::Read, OnOfficeResourceType::Estate), ['response']);

        $filteredRecordings = $builder->recorded(function (OnOfficeRequest $request, array $response) {
            return $request->actionId === OnOfficeAction::Read;
        });

        expect($filteredRecordings)->toBeInstanceOf(Collection::class)
            ->and($filteredRecordings[0][0]->toArray())->toBe((new OnOfficeRequest(OnOfficeAction::Read, OnOfficeResourceType::Estate))->toArray())
            ->and($filteredRecordings[0][1])->toBe(['response']);
    });

    it('can assert sent', function () {
        $builder = new BaseRepository;

        $builder->record();
        $builder->recordRequestResponsePair(new OnOfficeRequest(OnOfficeAction::Read, OnOfficeResourceType::Estate), ['response']);

        $builder->assertSent(function (OnOfficeRequest $request) {
            return $request->actionId === OnOfficeAction::Read;
        });
    });

    it('can assert sent with a response', function () {
        $builder = new BaseRepository;

        $builder->record();
        $builder->recordRequestResponsePair(new OnOfficeRequest(OnOfficeAction::Read, OnOfficeResourceType::Estate), ['response']);

        $builder->assertSent(function (OnOfficeRequest $request, array $response) {
            return $request->actionId === OnOfficeAction::Read && $response === ['response'];
        });
    });

    it('can assert not sent', function () {
        $builder = new BaseRepository;

        $builder->record();
        $builder->recordRequestResponsePair(new OnOfficeRequest(OnOfficeAction::Read, OnOfficeResourceType::Estate), ['response']);

        $builder->assertNotSent(function (OnOfficeRequest $request) {
            return $request->actionId === OnOfficeAction::Create;
        });
    });

    it('can assert not sent with a response', function () {
        $builder = new BaseRepository;

        $builder->record();
        $builder->recordRequestResponsePair(new OnOfficeRequest(OnOfficeAction::Read, OnOfficeResourceType::Estate), ['response']);

        $builder->assertNotSent(function (OnOfficeRequest $request, array $response) {
            return $request->actionId === OnOfficeAction::Create && $response === ['response'];
        });
    });

    it('can assert sent count', function () {
        $builder = new BaseRepository;

        $builder->record();
        $builder->recordRequestResponsePair(new OnOfficeRequest(OnOfficeAction::Read, OnOfficeResourceType::Estate), ['response']);
        $builder->recordRequestResponsePair(new OnOfficeRequest(OnOfficeAction::Read, OnOfficeResourceType::Estate), ['response']);

        $builder->assertSentCount(2);
    });
});
