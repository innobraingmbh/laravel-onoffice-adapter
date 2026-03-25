<?php

declare(strict_types=1);

namespace Innobrain\OnOfficeAdapter\Repositories;

use Innobrain\OnOfficeAdapter\Query\AppointmentBuilder;

class AppointmentRepository extends BaseRepository
{
    protected function createBuilder(): AppointmentBuilder
    {
        return new AppointmentBuilder;
    }
}
