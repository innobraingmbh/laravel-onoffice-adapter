<?php

declare(strict_types=1);

namespace Innobrain\OnOfficeAdapter\Repositories;

use Innobrain\OnOfficeAdapter\Query\ActionBuilder;
use Innobrain\OnOfficeAdapter\Query\ImprintBuilder;
use Innobrain\OnOfficeAdapter\Query\RegionBuilder;
use Innobrain\OnOfficeAdapter\Query\UserBuilder;

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
