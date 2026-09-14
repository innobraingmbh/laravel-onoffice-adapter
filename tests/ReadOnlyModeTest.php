<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Innobrain\OnOfficeAdapter\Dtos\OnOfficeApiCredentials;
use Innobrain\OnOfficeAdapter\Dtos\OnOfficeRequest;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeAction;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeResourceType;
use Innobrain\OnOfficeAdapter\Exceptions\OnOfficeException;
use Innobrain\OnOfficeAdapter\Exceptions\ReadOnlyViolationException;
use Innobrain\OnOfficeAdapter\Facades\AppointmentRepository;
use Innobrain\OnOfficeAdapter\Facades\EstateRepository;
use Innobrain\OnOfficeAdapter\Facades\MarketplaceRepository;
use Innobrain\OnOfficeAdapter\Facades\Query;
use Innobrain\OnOfficeAdapter\Facades\Testing\RecordFactories\EstateFactory;
use Innobrain\OnOfficeAdapter\Facades\Testing\RecordFactories\MarketPlaceUnlockProviderFactory;
use Innobrain\OnOfficeAdapter\Services\OnOfficeService;

describe('action classification', function () {
    it('does not consider reading a mutation', function (OnOfficeAction $action) {
        expect($action->mutates())->toBeFalse();
    })->with([
        'read' => OnOfficeAction::Read,
        'get' => OnOfficeAction::Get,
    ]);

    it('considers every writing action a mutation', function (OnOfficeAction $action) {
        expect($action->mutates())->toBeTrue();
    })->with([
        'create' => OnOfficeAction::Create,
        'modify' => OnOfficeAction::Modify,
        'delete' => OnOfficeAction::Delete,
        'do' => OnOfficeAction::Do,
    ]);
});

describe('the config flag', function () {
    it('is off by default', function () {
        expect(Config::get('onoffice.read_only'))->toBeFalse()
            ->and(resolve(OnOfficeService::class)->isReadOnly())->toBeFalse();
    });

    it('blocks a modify before it is recorded', function () {
        Config::set('onoffice.read_only', true);

        EstateRepository::fake(EstateRepository::response([
            EstateRepository::page(actionId: OnOfficeAction::Modify),
        ]));

        expect(fn () => EstateRepository::query()->addModify('kaufpreis', 1)->modify(1))
            ->toThrow(ReadOnlyViolationException::class);

        EstateRepository::assertSentCount(0);
        EstateRepository::assertNotSent();
    });

    it('blocks a create', function () {
        Config::set('onoffice.read_only', true);

        EstateRepository::fake(EstateRepository::response([
            EstateRepository::page(actionId: OnOfficeAction::Create),
        ]));

        expect(fn () => EstateRepository::query()->create(['kaufpreis' => 1]))
            ->toThrow(ReadOnlyViolationException::class);

        EstateRepository::assertSentCount(0);
    });

    it('blocks a delete', function () {
        Config::set('onoffice.read_only', true);

        AppointmentRepository::fake(AppointmentRepository::response([
            AppointmentRepository::page(actionId: OnOfficeAction::Delete),
        ]));

        expect(fn () => AppointmentRepository::query()->delete(42))
            ->toThrow(ReadOnlyViolationException::class);

        AppointmentRepository::assertSentCount(0);
    });

    it('blocks a do action', function () {
        Config::set('onoffice.read_only', true);

        MarketplaceRepository::fake(MarketplaceRepository::response([
            MarketplaceRepository::page(recordFactories: [
                MarketPlaceUnlockProviderFactory::make(),
            ]),
        ]));

        expect(fn () => MarketplaceRepository::query()->unlockProvider('foo', 'bar'))
            ->toThrow(ReadOnlyViolationException::class);

        MarketplaceRepository::assertSentCount(0);
    });

    it('still allows reads', function () {
        Config::set('onoffice.read_only', true);

        EstateRepository::fake(EstateRepository::response([
            EstateRepository::page(recordFactories: [
                EstateFactory::make()->id(1),
            ]),
        ]));

        $estates = EstateRepository::query()->get();

        expect($estates->count())->toBe(1)
            ->and($estates->first()['id'])->toBe(1);

        EstateRepository::assertSentCount(1);
    });

    it('never reaches the network', function () {
        Config::set('onoffice.read_only', true);

        Http::preventStrayRequests();
        Http::fake();

        expect(fn () => EstateRepository::query()->create(['kaufpreis' => 1]))
            ->toThrow(ReadOnlyViolationException::class);

        Http::assertNothingSent();
    });

    it('does not leak into the next test', function () {
        expect(Config::get('onoffice.read_only'))->toBeFalse();

        EstateRepository::fake(EstateRepository::response([
            EstateRepository::page(actionId: OnOfficeAction::Modify),
        ]));

        expect(EstateRepository::query()->addModify('kaufpreis', 1)->modify(1))->toBeTrue();
    });
});

describe('the credentials flag', function () {
    it('blocks a write when the credentials object opts in', function () {
        EstateRepository::fake(EstateRepository::response([
            EstateRepository::page(actionId: OnOfficeAction::Modify),
        ]));

        $credentials = new OnOfficeApiCredentials('token', 'secret', 'claim', readOnly: true);

        expect(fn () => EstateRepository::query()
            ->withCredentials($credentials)
            ->addModify('kaufpreis', 1)
            ->modify(1))
            ->toThrow(ReadOnlyViolationException::class);

        EstateRepository::assertSentCount(0);
    });

    it('blocks a write when withCredentials is given the flag', function () {
        EstateRepository::fake(EstateRepository::response([
            EstateRepository::page(actionId: OnOfficeAction::Modify),
        ]));

        expect(fn () => EstateRepository::query()
            ->withCredentials('token', 'secret', readOnly: true)
            ->addModify('kaufpreis', 1)
            ->modify(1))
            ->toThrow(ReadOnlyViolationException::class);

        EstateRepository::assertSentCount(0);

        expect(EstateRepository::query()->withCredentials('token', 'secret', readOnly: true)->getCredentials()?->readOnly)
            ->toBeTrue();
    });

    it('still allows reads with read-only credentials', function () {
        EstateRepository::fake(EstateRepository::response([
            EstateRepository::page(recordFactories: [
                EstateFactory::make()->id(1),
            ]),
        ]));

        $estates = EstateRepository::query()
            ->withCredentials('token', 'secret', readOnly: true)
            ->get();

        expect($estates->count())->toBe(1);
    });

    it('is part of the credentials identity', function () {
        $writable = new OnOfficeApiCredentials('token', 'secret', 'claim');
        $readOnly = new OnOfficeApiCredentials('token', 'secret', 'claim', readOnly: true);

        expect($writable->equals($readOnly))->toBeFalse()
            ->and($readOnly->equals(new OnOfficeApiCredentials('token', 'secret', 'claim', readOnly: true)))->toBeTrue();
    });
});

describe('the builder flag', function () {
    it('blocks a write on that query', function () {
        EstateRepository::fake(EstateRepository::response([
            EstateRepository::page(actionId: OnOfficeAction::Modify),
        ]));

        expect(fn () => EstateRepository::query()->readOnly()->addModify('kaufpreis', 1)->modify(1))
            ->toThrow(ReadOnlyViolationException::class);

        EstateRepository::assertSentCount(0);
    });

    it('still allows reads on that query', function () {
        EstateRepository::fake(EstateRepository::response([
            EstateRepository::page(recordFactories: [
                EstateFactory::make()->id(1),
            ]),
        ]));

        expect(EstateRepository::query()->readOnly()->get()->count())->toBe(1);
    });

    it('does not bleed into another query through the shared service', function () {
        EstateRepository::fake([
            EstateRepository::response([
                EstateRepository::page(recordFactories: [
                    EstateFactory::make()->id(1),
                ]),
            ]),
            EstateRepository::response([
                EstateRepository::page(actionId: OnOfficeAction::Modify),
            ]),
        ]);

        EstateRepository::query()->readOnly()->get();

        expect(EstateRepository::query()->addModify('kaufpreis', 1)->modify(1))->toBeTrue();

        EstateRepository::assertSentCount(2);
    });

    it('does not leak into rendering a raw request body through the shared service', function () {
        EstateRepository::fake(EstateRepository::response([
            EstateRepository::page(recordFactories: [
                EstateFactory::make()->id(1),
            ]),
        ]));

        EstateRepository::query()->readOnly()->get();

        // raw() renders the body via toRequestArray(), which resolves the
        // shared service the read-only query above just flagged. Rendering is
        // not sending, so it must not throw for a writable request.
        $request = new OnOfficeRequest(OnOfficeAction::Modify, OnOfficeResourceType::Estate, 1);

        expect($request->toRequestArray())->toHaveKey('request.actions.0.actionid', OnOfficeAction::Modify->value);
    });
});

describe('the one-way ratchet', function () {
    it('cannot be undone by a query that does not ask for read-only', function () {
        Config::set('onoffice.read_only', true);

        EstateRepository::fake(EstateRepository::response([
            EstateRepository::page(actionId: OnOfficeAction::Modify),
        ]));

        $builder = EstateRepository::query()->addModify('kaufpreis', 1);

        expect($builder->isReadOnly())->toBeFalse()
            ->and(fn () => $builder->modify(1))->toThrow(ReadOnlyViolationException::class);

        EstateRepository::assertSentCount(0);
    });

    it('cannot be undone by writable credentials', function () {
        Config::set('onoffice.read_only', true);

        EstateRepository::fake(EstateRepository::response([
            EstateRepository::page(actionId: OnOfficeAction::Modify),
        ]));

        expect(fn () => EstateRepository::query()
            ->withCredentials(new OnOfficeApiCredentials('token', 'secret'))
            ->addModify('kaufpreis', 1)
            ->modify(1))
            ->toThrow(ReadOnlyViolationException::class);
    });

    it('keeps the service flag out of the config flag and vice versa', function () {
        $service = resolve(OnOfficeService::class);

        expect($service->isReadOnly())->toBeFalse();

        $service->setReadOnly(true);
        expect($service->isReadOnly())->toBeTrue();

        $service->setReadOnly(false);
        expect($service->isReadOnly())->toBeFalse();

        Config::set('onoffice.read_only', true);
        expect($service->setReadOnly(false)->isReadOnly())->toBeTrue();
    });
});

describe('batches', function () {
    it('refuses a batch with a single mutating request and records nothing', function () {
        Config::set('onoffice.read_only', true);

        Query::fake(Query::response([
            Query::page(),
            Query::page(actionId: OnOfficeAction::Modify),
        ]));

        expect(fn () => Query::batch([
            new OnOfficeRequest(OnOfficeAction::Read, OnOfficeResourceType::Estate),
            new OnOfficeRequest(OnOfficeAction::Modify, OnOfficeResourceType::Estate, 1),
        ])->once())
            ->toThrow(ReadOnlyViolationException::class);

        Query::assertSentCount(0);
        Query::assertNotSent();
    });

    it('still allows a read-only batch', function () {
        Config::set('onoffice.read_only', true);

        Query::fake(Query::response([
            Query::page(recordFactories: [
                EstateFactory::make()->id(1),
            ]),
        ]));

        $results = Query::batch([
            new OnOfficeRequest(OnOfficeAction::Read, OnOfficeResourceType::Estate),
        ])->once();

        expect($results)->toHaveCount(1);
    });

    it('inherits the flag from a builder added to the batch', function () {
        Query::fake(Query::response([
            Query::page(actionId: OnOfficeAction::Modify),
        ]));

        expect(fn () => Query::batch([EstateRepository::query()->readOnly()])
            ->add(new OnOfficeRequest(OnOfficeAction::Modify, OnOfficeResourceType::Estate, 1))
            ->once())
            ->toThrow(ReadOnlyViolationException::class);

        Query::assertSentCount(0);
    });
});

describe('the exception', function () {
    it('exposes the refused request and is an OnOfficeException', function () {
        $request = new OnOfficeRequest(OnOfficeAction::Modify, OnOfficeResourceType::Estate, 7);

        $exception = new ReadOnlyViolationException($request);

        expect($exception)->toBeInstanceOf(OnOfficeException::class)
            ->and($exception->request)->toBe($request)
            ->and($exception->getMessage())->toBe('Read-only mode: refused Modify on estate.');
    });

    it('names the action and resource of the request it refused', function () {
        Config::set('onoffice.read_only', true);

        MarketplaceRepository::fake(MarketplaceRepository::response([
            MarketplaceRepository::page(recordFactories: [
                MarketPlaceUnlockProviderFactory::make(),
            ]),
        ]));

        try {
            MarketplaceRepository::query()->unlockProvider('foo', 'bar');
        } catch (ReadOnlyViolationException $exception) {
            expect($exception->request->actionId)->toBe(OnOfficeAction::Do)
                ->and($exception->request->resourceType)->toBe(OnOfficeResourceType::UnlockProvider)
                ->and($exception->getMessage())->toContain('Do')
                ->and($exception->getMessage())->toContain('unlockProvider');

            return;
        }

        $this->fail('The do action was not refused.');
    });

    it('is not thrown when a request is only rendered to a raw request body', function () {
        Config::set('onoffice.read_only', true);

        $request = new OnOfficeRequest(OnOfficeAction::Delete, OnOfficeResourceType::Calendar, 1);

        // Rendering is not sending: the guard sits in front of the HTTP call,
        // so debugging helpers such as raw() keep working in read-only mode.
        expect($request->toRequestArray())->toHaveKey('request.actions.0.actionid', OnOfficeAction::Delete->value);
    });
});
