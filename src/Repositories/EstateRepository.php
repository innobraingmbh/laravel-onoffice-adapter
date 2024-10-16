<?php

declare(strict_types=1);

namespace Innobrain\OnOfficeAdapter\Repositories;

use Innobrain\OnOfficeAdapter\Query\EstateBuilder;
use Innobrain\OnOfficeAdapter\Query\EstateFileBuilder;
use Innobrain\OnOfficeAdapter\Query\EstatePictureBuilder;

class EstateRepository extends BaseRepository
{
    protected function createBuilder(): EstateBuilder
    {
        return new EstateBuilder;
    }

    /**
     * Returns a new estate file builder instance.
     */
    public function files(int $estateId): EstateFileBuilder
    {
        /** @var EstateFileBuilder */
        return $this->createBuilderFromClass(EstateFileBuilder::class, $estateId);
    }

    /**
     * Returns a new estate picture builder instance.
     */
    public function pictures(int|array $estateId): EstatePictureBuilder
    {
        /** @var EstatePictureBuilder */
        return $this->createBuilderFromClass(EstatePictureBuilder::class, $estateId);
    }
}
