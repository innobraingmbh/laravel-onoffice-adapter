<?php

declare(strict_types=1);

namespace Katalam\OnOfficeAdapter\Repositories;

use Katalam\OnOfficeAdapter\Query\ImprintBuilder;
use Katalam\OnOfficeAdapter\Query\RegionBuilder;
use Katalam\OnOfficeAdapter\Query\UserBuilder;
use Katalam\OnOfficeAdapter\Services\OnOfficeService;

class SettingRepository
{
    public function __construct(
        private readonly OnOfficeService $onOfficeService,
    ) {}

    /**
     * Returns a new user builder instance.
     */
    public function users(): UserBuilder
    {
        return new UserBuilder($this->onOfficeService);
    }

    /**
     * Returns a new regions builder instance.
     */
    public function regions(): RegionBuilder
    {
        return new RegionBuilder($this->onOfficeService);
    }

    /*
     * Returns a new imprint builder instance.
     */
    public function imprint(): ImprintBuilder
    {
        return new ImprintBuilder($this->onOfficeService);
    }
}
