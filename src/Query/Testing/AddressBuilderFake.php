<?php

namespace Katalam\OnOfficeAdapter\Query\Testing;

use Exception;
use Throwable;

class AddressBuilderFake extends BaseFake
{
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
