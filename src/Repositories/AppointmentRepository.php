<?php

declare(strict_types=1);

namespace Innobrain\OnOfficeAdapter\Repositories;

use Innobrain\OnOfficeAdapter\Query\AppointmentBuilder;
use Innobrain\OnOfficeAdapter\Query\AppointmentFileBuilder;

class AppointmentRepository extends BaseRepository
{
    protected function createBuilder(): AppointmentBuilder
    {
        return new AppointmentBuilder;
    }

    public function files(int $appointmentId): AppointmentFileBuilder
    {
        /** @var AppointmentFileBuilder */
        return $this->createBuilderFromClass(AppointmentFileBuilder::class, $appointmentId);
    }
}
