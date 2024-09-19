<?php

declare(strict_types=1);

namespace Innobrain\OnOfficeAdapter\Facades;

use Illuminate\Support\Facades\Facade;
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

    public static function response(array $pages = []): OnOfficeResponse
    {
        return static::getFacadeRoot()->response($pages);
    }

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
        int $countAbsolute = 0,
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
