<?php

declare(strict_types=1);

namespace Katalam\OnOfficeAdapter\Query\Testing;

use Exception;
use Illuminate\Support\Arr;
use Katalam\OnOfficeAdapter\Query\Concerns\RecordIds;
use Throwable;

class FieldBuilderFake extends BaseFake
{
    use RecordIds;

    public function withModules(array|string $modules): static
    {
        return $this;
    }

    /**
     * @throws Throwable
     */
    public function create(array $data): array
    {
        return $this->get()->first();
    }
}
