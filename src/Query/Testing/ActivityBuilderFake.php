<?php

declare(strict_types=1);

namespace Katalam\OnOfficeAdapter\Query\Testing;

use Katalam\OnOfficeAdapter\Query\Concerns\RecordIds;
use Throwable;

class ActivityBuilderFake extends BaseFake
{
    use RecordIds;

    public string $estateOrAddress = 'estate';

    /**
     * @throws Throwable
     */
    public function create(array $data): array
    {
        return $this->get()->first();
    }

    public function estate(): static
    {
        $this->estateOrAddress = 'estateid';

        return $this;
    }

    public function address(): static
    {
        $this->estateOrAddress = 'addressid';

        return $this;
    }

    public function recordIdsAsEstate(): static
    {
        $this->estate();

        return $this;
    }

    public function recordIdsAsAddress(): static
    {
        $this->address();

        return $this;
    }
}
