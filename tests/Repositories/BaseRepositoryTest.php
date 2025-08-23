<?php

declare(strict_types=1);

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Innobrain\OnOfficeAdapter\Dtos\OnOfficeRequest;
use Innobrain\OnOfficeAdapter\Dtos\OnOfficeResponse;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeAction;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeError;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeResourceType;
use Innobrain\OnOfficeAdapter\Exceptions\OnOfficeException;
use Innobrain\OnOfficeAdapter\Facades\BaseRepository as BaseRepositoryFacade;
use Innobrain\OnOfficeAdapter\Facades\Testing\RecordFactories\BaseFactory;
use Innobrain\OnOfficeAdapter\Facades\Testing\RecordFactories\EstateFactory;
use Innobrain\OnOfficeAdapter\Repositories\BaseRepository;
use Innobrain\OnOfficeAdapter\Services\OnOfficeService;
use Mockery\MockInterface;
use Symfony\Component\VarDumper\VarDumper;

use function Pest\Laravel\mock;

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

    it('will dump the last record', function () {
        $builder = new BaseRepository;

        $builder->record();

        $request = new OnOfficeRequest(OnOfficeAction::Read, OnOfficeResourceType::Estate);

        $builder->recordRequestResponsePair($request, ['response']);

        $response = $builder->lastRecorded();

        expect($response)->toBe([$request, ['response']]);
    });

    it('will dump the last record request', function () {
        $builder = new BaseRepository;

        $builder->record();

        $request = new OnOfficeRequest(OnOfficeAction::Read, OnOfficeResourceType::Estate);

        $builder->recordRequestResponsePair($request, ['response']);

        $response = $builder->lastRecordedRequest();

        expect($response)->toBe($request);
    });

    it('will dump the last record response', function () {
        $builder = new BaseRepository;

        $builder->record();

        $request = new OnOfficeRequest(OnOfficeAction::Read, OnOfficeResourceType::Estate);

        $builder->recordRequestResponsePair($request, ['response']);

        $response = $builder->lastRecordedResponse();

        expect($response)->toBe(['response']);
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

    it('can fake a sequence', function () {
        $builder = new BaseRepository;

        $builder->fake($builder->sequence(
            new OnOfficeResponse(collect()),
            20,
        ));

        $m = new ReflectionProperty($builder, 'stubCallables');
        $m->setAccessible(true);

        expect($m->getValue($builder)->toArray())->toHaveCount(20);
    });

    it('can fake a response with more than one page', function () {
        $builder = new BaseRepository;

        $builder->fake([
            $builder->response([
                $builder->page(recordFactories: [
                    BaseFactory::make(),
                ]),
                $builder->page(recordFactories: [
                    BaseFactory::make(),
                ]),
            ]),
        ]);

        $result = $builder->query()->call(new OnOfficeRequest(
            OnOfficeAction::Read,
            OnOfficeResourceType::Estate,
        ));

        expect($result->count())->toBe(2);
    });

    it('can fake a response with more than one page and another response', function () {
        $builder = new BaseRepository;

        $builder->fake([
            $builder->response([
                $builder->page(recordFactories: [
                    BaseFactory::make(),
                ]),
                $builder->page(recordFactories: [
                    BaseFactory::make(),
                ]),
            ]),
            $builder->response([
                $builder->page(recordFactories: [
                    BaseFactory::make(),
                ]),
            ]),
        ]);

        $result = $builder->query()->call(new OnOfficeRequest(
            OnOfficeAction::Read,
            OnOfficeResourceType::Estate,
        ));

        expect($result->count())->toBe(2);

        $result = $builder->query()->call(new OnOfficeRequest(
            OnOfficeAction::Read,
            OnOfficeResourceType::Estate,
        ));

        expect($result->count())->toBe(1);
    });

    it('can fake a response with more than one page and another response on each page', function () {
        $builder = new BaseRepository;

        $builder->fake([
            $builder->response([
                $builder->page(recordFactories: [
                    BaseFactory::make(),
                ]),
                $builder->page(recordFactories: [
                    BaseFactory::make(),
                ]),
            ]),
            $builder->response([
                $builder->page(recordFactories: [
                    BaseFactory::make(),
                ]),
            ]),
        ]);

        $result = [];
        $builder->query()->chunked(new OnOfficeRequest(
            OnOfficeAction::Read,
            OnOfficeResourceType::Estate,
        ), function (array $records) use (&$result) {
            $result[] = count($records);
        });

        expect($result[0])->toBe(1)
            ->and($result[1])->toBe(1);

        $builder->query()->chunked(new OnOfficeRequest(
            OnOfficeAction::Read,
            OnOfficeResourceType::Estate,
        ), function (array $records) use (&$result) {
            $result[] = count($records);
        });

        expect($result[2])->toBe(1);
    });

    it('can throw stub responses', function () {
        $builder = new BaseRepository;

        $builder->fake([
            $builder->response([
                $builder->page(
                    errorCodeResult: OnOfficeError::Unknown_Error_Occurred->value,
                    messageResult: OnOfficeError::Unknown_Error_Occurred->toString(),
                ),
            ]),
        ]);

        $builder->query()->call(new OnOfficeRequest(
            OnOfficeAction::Read,
            OnOfficeResourceType::Estate,
        ));
    })->throws(OnOfficeException::class, OnOfficeError::Unknown_Error_Occurred->toString());
});

describe('assert', function () {
    it('can find a record by callable', function () {
        $builder = new BaseRepository;

        $builder->record();
        $builder->recordRequestResponsePair(new OnOfficeRequest(OnOfficeAction::Read, OnOfficeResourceType::Estate), ['response']);

        $filteredRecordings = $builder->recorded(fn (OnOfficeRequest $request, array $response) => $request->actionId === OnOfficeAction::Read);

        expect($filteredRecordings)->toBeInstanceOf(Collection::class)
            ->and($filteredRecordings[0][0]->toArray())->toBe((new OnOfficeRequest(OnOfficeAction::Read, OnOfficeResourceType::Estate))->toArray())
            ->and($filteredRecordings[0][1])->toBe(['response']);
    });

    it('can assert sent', function () {
        $builder = new BaseRepository;

        $builder->record();
        $builder->recordRequestResponsePair(new OnOfficeRequest(OnOfficeAction::Read, OnOfficeResourceType::Estate), ['response']);

        $builder->assertSent(fn (OnOfficeRequest $request) => $request->actionId === OnOfficeAction::Read);
    });

    it('can assert sent with a response', function () {
        $builder = new BaseRepository;

        $builder->record();
        $builder->recordRequestResponsePair(new OnOfficeRequest(OnOfficeAction::Read, OnOfficeResourceType::Estate), ['response']);

        $builder->assertSent(fn (OnOfficeRequest $request, array $response) => $request->actionId === OnOfficeAction::Read && $response === ['response']);
    });

    it('can assert not sent', function () {
        $builder = new BaseRepository;

        $builder->record();
        $builder->recordRequestResponsePair(new OnOfficeRequest(OnOfficeAction::Read, OnOfficeResourceType::Estate), ['response']);

        $builder->assertNotSent(fn (OnOfficeRequest $request) => $request->actionId === OnOfficeAction::Create);
    });

    it('can assert not sent with a response', function () {
        $builder = new BaseRepository;

        $builder->record();
        $builder->recordRequestResponsePair(new OnOfficeRequest(OnOfficeAction::Read, OnOfficeResourceType::Estate), ['response']);

        $builder->assertNotSent(fn (OnOfficeRequest $request, array $response) => $request->actionId === OnOfficeAction::Create && $response === ['response']);
    });

    it('can assert sent count', function () {
        $builder = new BaseRepository;

        $builder->record();
        $builder->recordRequestResponsePair(new OnOfficeRequest(OnOfficeAction::Read, OnOfficeResourceType::Estate), ['response']);
        $builder->recordRequestResponsePair(new OnOfficeRequest(OnOfficeAction::Read, OnOfficeResourceType::Estate), ['response']);

        $builder->assertSentCount(2);
    });
});

describe('request', function () {
    it('will call once', function () {
        Http::preventStrayRequests();
        Http::fake([
            'https://api.onoffice.de/api/stable/api.php' => Http::response([
                'status' => [
                    'code' => 200,
                ],
            ]),
        ]);

        $builder = new BaseRepository;

        $request = new OnOfficeRequest(OnOfficeAction::Read, OnOfficeResourceType::Estate);

        $builder->query()->once($request);

        Http::assertSentCount(1);
    });

    it('will call call', function () {
        Http::preventStrayRequests();
        Http::fake([
            'https://api.onoffice.de/api/stable/api.php' => Http::response([
                'status' => [
                    'code' => 200,
                ],
            ]),
        ]);

        $builder = new BaseRepository;

        $request = new OnOfficeRequest(OnOfficeAction::Read, OnOfficeResourceType::Estate);

        $builder->query()->call($request);

        Http::assertSentCount(1);
    });

    it('will call chunked', function () {
        Http::preventStrayRequests();
        Http::fake([
            'https://api.onoffice.de/api/stable/api.php' => Http::response([
                'status' => [
                    'code' => 200,
                ],
            ]),
        ]);

        $builder = new BaseRepository;

        $request = new OnOfficeRequest(OnOfficeAction::Read, OnOfficeResourceType::Estate);

        $builder->query()->chunked($request, fn () => true);

        Http::assertSentCount(1);
    });

    it('will call with string as resource type', function () {
        Http::preventStrayRequests();
        Http::fake([
            'https://api.onoffice.de/api/stable/api.php' => Http::response([
                'status' => [
                    'code' => 200,
                ],
            ]),
        ]);

        $builder = new BaseRepository;

        $request = new OnOfficeRequest(OnOfficeAction::Read, 'estate');

        $builder->query()->call($request);

        Http::assertSentCount(1);
    });
});

describe('middlewares', function () {
    it('will call the before callbacks', function () {
        $builder = new BaseRepository;

        Http::preventStrayRequests();
        Http::fake([
            'https://api.onoffice.de/api/stable/api.php' => Http::response([
                'status' => [
                    'code' => 200,
                ],
            ]),
        ]);

        $builder->record();

        $request = new OnOfficeRequest(OnOfficeAction::Read, OnOfficeResourceType::Estate);

        $builder->query()
            ->before(function (OnOfficeRequest $request) {
                $request->identifier = 'before';
            })
            ->call($request);

        $builder->assertSent(fn (OnOfficeRequest $request) => $request->identifier === 'before');
    });

    it('will call the before callbacks in the order they are added', function () {
        $builder = new BaseRepository;

        Http::preventStrayRequests();
        Http::fake([
            'https://api.onoffice.de/api/stable/api.php' => Http::response([
                'status' => [
                    'code' => 200,
                ],
            ]),
        ]);

        $builder->record();

        $request = new OnOfficeRequest(OnOfficeAction::Read, OnOfficeResourceType::Estate);

        $builder->query()
            ->before(function (OnOfficeRequest $request) {
                $request->identifier = 'before';
            })
            ->before(function (OnOfficeRequest $request) {
                $request->identifier = 'after_the_first_before';
            })
            ->call($request);

        $builder->assertSent(fn (OnOfficeRequest $request) => $request->identifier === 'after_the_first_before');
    });

    it('can dump the request', function () {
        $builder = new BaseRepository;

        Http::preventStrayRequests();
        Http::fake([
            'https://api.onoffice.de/api/stable/api.php' => Http::response([
                'status' => [
                    'code' => 200,
                ],
            ]),
        ]);

        $dumped = [];

        VarDumper::setHandler(static function (mixed $value) use (&$dumped) {
            $dumped[] = $value;
        });

        $builder->record();

        $request = new OnOfficeRequest(OnOfficeAction::Read, OnOfficeResourceType::Estate);

        $builder->query()
            ->dump()
            ->call($request);

        expect($dumped)->toHaveCount(1)
            ->and($dumped[0])->toBeInstanceOf(OnOfficeRequest::class)
            ->and($dumped[0]->actionId)->toBe(OnOfficeAction::Read)
            ->and($dumped[0]->resourceType)->toBe(OnOfficeResourceType::Estate);

        VarDumper::setHandler(null);
    });

    it('can raw the request', function () {
        $builder = new BaseRepository;

        Http::preventStrayRequests();
        Http::fake([
            'https://api.onoffice.de/api/stable/api.php' => Http::response([
                'status' => [
                    'code' => 200,
                ],
            ]),
        ]);

        $dumped = [];

        VarDumper::setHandler(static function (mixed $value) use (&$dumped) {
            $dumped[] = $value;
        });

        $builder->record();

        $request = new OnOfficeRequest(OnOfficeAction::Read, OnOfficeResourceType::Estate);

        $builder->query()
            ->dump()
            ->call($request);

        expect($dumped)->toHaveCount(1)
            ->and($dumped[0])->toBeInstanceOf(OnOfficeRequest::class)
            ->and($dumped[0]->actionId)->toBe(OnOfficeAction::Read)
            ->and($dumped[0]->resourceType)->toBe(OnOfficeResourceType::Estate);

        VarDumper::setHandler(null);
    });
});

describe('custom credentials', function () {
    it('can set custom credentials', function () {
        $builder = new BaseRepository;

        $builder = $builder
            ->query()
            ->withCredentials('token', 'secret', 'claim');

        $m = new ReflectionMethod($builder, 'getOnOfficeService');
        $m->setAccessible(true);
        /* @var OnOfficeService $onOfficeService */
        $onOfficeService = $m->invoke($builder);

        expect($onOfficeService->getToken())->toBe('token')
            ->and($onOfficeService->getSecret())->toBe('secret')
            ->and($onOfficeService->getApiClaim())->toBe('claim');
    });

    it('can set custom credentials twice', function () {
        $builder = new BaseRepository;
        $builder
            ->query()
            ->withCredentials('token', 'secret', 'claim');

        $builder = new BaseRepository;
        $builder = $builder
            ->query()
            ->withCredentials('token2', 'secret2', 'claim2');

        $m = new ReflectionMethod($builder, 'getOnOfficeService');
        $m->setAccessible(true);
        /* @var OnOfficeService $onOfficeService */
        $onOfficeService = $m->invoke($builder);

        expect($onOfficeService->getToken())->toBe('token2')
            ->and($onOfficeService->getSecret())->toBe('secret2')
            ->and($onOfficeService->getApiClaim())->toBe('claim2');
    });

    it('will reset to default', function () {
        $builder = new BaseRepository;
        $builder
            ->query()
            ->withCredentials('token', 'secret', 'claim');

        $builder = new BaseRepository;
        $builder = $builder
            ->query();

        $m = new ReflectionMethod($builder, 'getOnOfficeService');
        $m->setAccessible(true);

        /* @var OnOfficeService $onOfficeService */
        $onOfficeService = $m->invoke($builder);
        expect($onOfficeService->getToken())->toBe(Config::get('onoffice.token'))
            ->and($onOfficeService->getSecret())->toBe(Config::get('onoffice.secret'))
            ->and($onOfficeService->getApiClaim())->toBe('');
    });
});

describe('check user rights', function () {
    test('user rights callback', function () {
        BaseRepositoryFacade::preventStrayRequests();

        BaseRepositoryFacade::fake([
            BaseRepositoryFacade::response([
                BaseRepositoryFacade::page(recordFactories: [
                    EstateFactory::make()
                        ->id(2),
                    EstateFactory::make()
                        ->id(3),
                ]),
            ]),
            BaseRepositoryFacade::response([
                BaseRepositoryFacade::page(recordFactories: [
                    BaseFactory::make()
                        ->data([
                            '3',
                        ]),
                ]),
            ]),
        ]);

        $response = BaseRepositoryFacade::query()
            ->checkUserRecordsRight('read', 'address', 17)
            ->requestApi(new OnOfficeRequest(OnOfficeAction::Read, OnOfficeResourceType::Estate));

        expect($response->json('response.results.0.data.records.*.id'))
            ->toHaveCount(1)
            ->toBe([3]);
    });

    test('will use same credentials as the repository', function () {
        BaseRepositoryFacade::preventStrayRequests();

        BaseRepositoryFacade::fake([
            BaseRepositoryFacade::response([
                BaseRepositoryFacade::page(recordFactories: [
                    EstateFactory::make()
                        ->id(2),
                    EstateFactory::make()
                        ->id(3),
                ]),
            ]),
            BaseRepositoryFacade::response([
                BaseRepositoryFacade::page(recordFactories: [
                    BaseFactory::make()
                        ->data([
                            '3',
                        ]),
                ]),
            ]),
        ]);

        mock(OnOfficeService::class, function (MockInterface $mock) {
            $mock->makePartial()
                ->shouldReceive('setCredentials')
                ->twice()
                ->andReturnSelf();
        });

        BaseRepositoryFacade::query()
            ->withCredentials('token', 'secret', 'claim')
            ->checkUserRecordsRight('read', 'address', 17)
            ->requestApi(new OnOfficeRequest(OnOfficeAction::Read, OnOfficeResourceType::Estate));
    });
});
