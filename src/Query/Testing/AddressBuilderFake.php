<?php

declare(strict_types=1);

namespace Katalam\OnOfficeAdapter\Query\Testing;

use Exception;
use Katalam\OnOfficeAdapter\Query\Concerns\RecordIds;
use Throwable;

class AddressBuilderFake extends BaseFake
{
    use RecordIds;

    /**
     * @throws Throwable
     */
    public function count(): int
    {
        $nextRequest = $this->fakeResponses->shift();
        throw_if($nextRequest === null, new Exception('No more fake responses'));

        return collect($nextRequest)->flatten()->count();
    }

    public function addCountryIsoCodeType(string $countryIsoCodeType): self
    {
        return $this;
    }
}
