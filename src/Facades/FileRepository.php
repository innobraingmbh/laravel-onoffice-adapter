<?php

declare(strict_types=1);

namespace Katalam\OnOfficeAdapter\Facades;

use Illuminate\Support\Facades\Facade;
use Katalam\OnOfficeAdapter\Facades\Testing\FileRepositoryFake;
use Katalam\OnOfficeAdapter\Query\UploadBuilder;

/**
 * @see \Katalam\OnOfficeAdapter\Repositories\SettingRepository
 *
 * @method static UploadBuilder upload()
 */
class FileRepository extends Facade
{
    public static function fake(array ...$fakeResponses): FileRepositoryFake
    {
        static::swap($fake = new FileRepositoryFake(...$fakeResponses));

        return $fake;
    }

    protected static function getFacadeAccessor(): string
    {
        return \Katalam\OnOfficeAdapter\Repositories\FileRepository::class;
    }
}
