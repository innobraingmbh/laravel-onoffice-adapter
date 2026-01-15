<?php

declare(strict_types=1);

namespace Innobrain\OnOfficeAdapter\Facades;

use Illuminate\Support\Facades\Facade;
use Innobrain\OnOfficeAdapter\Dtos\OnOfficeRequest;
use Innobrain\OnOfficeAdapter\Dtos\OnOfficeResponse;
use Innobrain\OnOfficeAdapter\Dtos\OnOfficeResponsePage;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeAction;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeResourceId;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeResourceType;
use Innobrain\OnOfficeAdapter\Query\Builder;
use Innobrain\OnOfficeAdapter\Repositories\BaseRepository as RootRepository;

/**
 * @see RootRepository
 *
 * @method static Builder query()
 */
class BaseRepository extends Facade
{
    /**
     * @param  OnOfficeResponsePage|OnOfficeResponse|array<int, OnOfficeResponsePage|OnOfficeResponse|array<int, OnOfficeResponsePage>>|null  $stubCallables
     */
    public static function fake(OnOfficeResponsePage|OnOfficeResponse|array|null $stubCallables): RootRepository
    {
        return tap(static::getFacadeRoot(), static function (RootRepository $fake) use ($stubCallables) {
            $fake->fake($stubCallables);
        });
    }

    protected static function getFacadeAccessor(): string
    {
        return RootRepository::class;
    }

    /**
     * @return array<int, OnOfficeResponse>
     */
    public static function sequence(OnOfficeResponse $response, int $times = 1): array
    {
        return static::getFacadeRoot()->sequence($response, $times);
    }

    /**
     * @param  array<int, OnOfficeResponsePage>  $pages
     */
    public static function response(array $pages = []): OnOfficeResponse
    {
        return static::getFacadeRoot()->response($pages);
    }

    /**
     * @param  array<int, \Innobrain\OnOfficeAdapter\Facades\Testing\RecordFactories\BaseFactory>  $recordFactories
     */
    public static function page(
        OnOfficeAction $actionId = OnOfficeAction::Read,
        OnOfficeResourceType|string $resourceType = OnOfficeResourceType::Estate,
        array $recordFactories = [],
        int $status = 200,
        int $errorCode = 0,
        string $message = 'OK',
        OnOfficeResourceId|string|int $resourceId = OnOfficeResourceId::None,
        bool $cacheable = true,
        string|int $identifier = '',
        int $countAbsolute = -1,
        int $errorCodeResult = 0,
        string $messageResult = 'OK',
    ): OnOfficeResponsePage {
        return static::getFacadeRoot()->page(
            $actionId,
            $resourceType,
            $recordFactories,
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

    public static function preventStrayRequests(bool $value = true): RootRepository
    {
        return static::getFacadeRoot()->preventStrayRequests($value);
    }

    public static function record(bool $recording = true): RootRepository
    {
        return static::getFacadeRoot()->record($recording);
    }

    public static function stopRecording(): RootRepository
    {
        return static::getFacadeRoot()->stopRecording();
    }

    /**
     * @return array{0: OnOfficeRequest, 1: array<string, mixed>}|null
     */
    public static function lastRecorded(): ?array
    {
        return static::getFacadeRoot()->lastRecorded();
    }

    public static function lastRecordedRequest(): ?OnOfficeRequest
    {
        return static::getFacadeRoot()->lastRecordedRequest();
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function lastRecordedResponse(): ?array
    {
        return static::getFacadeRoot()->lastRecordedResponse();
    }

    public static function assertSent(?callable $callback = null): void
    {
        static::getFacadeRoot()->assertSent($callback);
    }

    public static function assertNotSent(?callable $callback = null): void
    {
        static::getFacadeRoot()->assertNotSent($callback);
    }

    public static function assertSentCount(int $count): void
    {
        static::getFacadeRoot()->assertSentCount($count);
    }
}
