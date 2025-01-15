<?php

declare(strict_types=1);

namespace Innobrain\OnOfficeAdapter\Repositories;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Innobrain\OnOfficeAdapter\Dtos\OnOfficeRequest;
use Innobrain\OnOfficeAdapter\Dtos\OnOfficeResponse;
use Innobrain\OnOfficeAdapter\Dtos\OnOfficeResponsePage;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeAction;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeResourceId;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeResourceType;
use Innobrain\OnOfficeAdapter\Query\Builder;
use PHPUnit\Framework\Assert as PHPUnit;

class BaseRepository
{
    /**
     * The stub callables that will be used to fake the responses.
     */
    protected Collection $stubCallables;

    /**
     * Indicates that an exception should be thrown
     * if a request is made without a stub callable.
     */
    protected bool $preventStrayRequests = false;

    /**
     * Indicates that the requests should be recorded.
     */
    protected bool $recording = false;

    /**
     * The recorded requests.
     */
    protected array $recorded = [];

    public function __construct()
    {
        $this->stubCallables = new Collection;
    }

    public function fake(OnOfficeResponsePage|OnOfficeResponse|array|null $responses): static
    {
        $this->record();

        if (is_null($responses)) {
            $responses = $this->response();
        }

        if (is_array($responses)) {
            foreach ($responses as $fake) {
                if ($fake instanceof OnOfficeResponse) {
                    $this->stubCallables->push($fake);
                } elseif ($fake instanceof OnOfficeResponsePage || is_array($fake)) {
                    $this->stubCallables->push(new OnOfficeResponse(collect(Arr::wrap($fake))));
                }
            }

            return $this;
        }

        if ($responses instanceof OnOfficeResponse) {
            $this->stubCallables->push($responses);
        }

        if ($responses instanceof OnOfficeResponsePage) {
            $this->stubCallables->push(new OnOfficeResponse(collect([$responses])));
        }

        return $this;
    }

    public function sequence(OnOfficeResponse $response, int $times = 1): array
    {
        return collect()
            ->range(1, $times)
            ->map(fn () => clone $response)
            ->values()
            ->toArray();
    }

    public function response(array $pages = []): OnOfficeResponse
    {
        if ($pages === []) {
            $pages = [$this->page()];
        }

        return new OnOfficeResponse(collect($pages));
    }

    public function page(
        OnOfficeAction $actionId = OnOfficeAction::Read,
        OnOfficeResourceType|string $resourceType = OnOfficeResourceType::Estate,
        array $recordFactories = [],
        int $status = 200,
        int $errorCode = 0,
        string $message = 'OK',
        OnOfficeResourceId|string|int $resourceId = OnOfficeResourceId::None,
        bool $cacheable = true,
        string|int $identifier = '',
        int $countAbsolute = 0,
        int $errorCodeResult = 0,
        string $messageResult = 'OK',
    ): OnOfficeResponsePage {
        return new OnOfficeResponsePage(
            $actionId,
            $resourceType,
            collect($recordFactories),
            $status,
            $errorCode,
            $message,
            $resourceId,
            $cacheable,
            $identifier,
            $countAbsolute,
            $errorCodeResult,
            $messageResult,
        );
    }

    public function query(): Builder
    {
        return tap($this->createBuilder(), function (Builder $builder) {
            $builder
                ->stub($this->stubCallables)
                ->preventStrayRequests($this->preventStrayRequests)
                ->setRepository($this);
        });
    }

    protected function createBuilder(): Builder
    {
        return new Builder;
    }

    /**
     * Create a new builder instance from the given class.
     * The parameters will be passed to the builder's constructor.
     */
    protected function createBuilderFromClass(string $class, ...$parameter): Builder
    {
        return tap(new $class(...$parameter), function (Builder $builder) {
            $builder
                ->stub($this->stubCallables)
                ->preventStrayRequests($this->preventStrayRequests)
                ->setRepository($this);
        });
    }

    public function preventStrayRequests(bool $value = true): static
    {
        $this->preventStrayRequests = $value;

        return $this;
    }

    public function allowStrayRequests(): static
    {
        return $this->preventStrayRequests(false);
    }

    public function record(bool $recording = true): static
    {
        $this->recording = $recording;

        return $this;
    }

    public function stopRecording(): static
    {
        return $this->record(false);
    }

    public function recordRequestResponsePair(OnOfficeRequest $request, array $response): static
    {
        if ($this->recording) {
            $this->recorded[] = [$request, $response];
        }

        return $this;
    }

    public function lastRecorded(): ?array
    {
        return collect($this->recorded)->last();
    }

    public function lastRecordedRequest(): ?OnOfficeRequest
    {
        return collect($this->recorded)->last()[0] ?? null;
    }

    public function lastRecordedResponse(): ?array
    {
        return collect($this->recorded)->last()[1] ?? null;
    }

    /**
     * Get a collection of the request / response pairs matching the given truth test.
     */
    public function recorded(?callable $callback = null): Collection
    {
        if ($this->recorded === []) {
            return collect();
        }

        $callback = $callback ?: static fn () => true;

        return collect($this->recorded)->filter(fn (array $pair) => $callback($pair[0], $pair[1]));
    }

    public function assertSent(?callable $callback = null): void
    {
        PHPUnit::assertTrue(
            $this->recorded($callback)->isNotEmpty(),
            'An expected request was not recorded.'
        );
    }

    public function assertNotSent(?callable $callback = null): void
    {
        // @phpstan-ignore-next-line
        PHPUnit::assertTrue(
            $this->recorded($callback)->isEmpty(),
            'An unexpected request was recorded.'
        );
    }

    public function assertSentCount(int $count): void
    {
        PHPUnit::assertCount($count, $this->recorded());
    }
}
