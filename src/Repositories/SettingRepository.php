<?php

declare(strict_types=1);

namespace Katalam\OnOfficeAdapter\Repositories;

use Katalam\OnOfficeAdapter\Query\ActionBuilder;
use Katalam\OnOfficeAdapter\Query\Builder;
use Katalam\OnOfficeAdapter\Query\ImprintBuilder;
use Katalam\OnOfficeAdapter\Query\RegionBuilder;
use Katalam\OnOfficeAdapter\Query\UserBuilder;

class SettingRepository extends BaseRepository
{
    /**
     * Returns a new user builder instance.
     */
    public function users(): UserBuilder
    {
        /** @var UserBuilder */
        return $this->createBuilderFromClass(UserBuilder::class);
    }

    /**
     * Returns a new regions builder instance.
     */
    public function regions(): RegionBuilder
    {
        /** @var RegionBuilder */
        return $this->createBuilderFromClass(RegionBuilder::class);
    }

    /*
     * Returns a new imprint builder instance.
     */
    public function imprint(): ImprintBuilder
    {
        /** @var ImprintBuilder */
        return $this->createBuilderFromClass(ImprintBuilder::class);
    }

    /*
     * Returns a new actions builder instance.
     */
    public function actions(): ActionBuilder
    {
        /** @var ActionBuilder */
        return $this->createBuilderFromClass(ActionBuilder::class);
    }
}
