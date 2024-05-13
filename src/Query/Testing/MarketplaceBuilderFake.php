<?php

namespace Katalam\OnOfficeAdapter\Query\Testing;

use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Katalam\OnOfficeAdapter\Facades\Testing\RecordFactories\BaseFactory;
use Katalam\OnOfficeAdapter\Query\Builder;
use Throwable;

class MarketplaceBuilderFake extends BaseFake
{
    /**
     * @throws Throwable
     */
    public function unlockProvider(
        string $parameterCacheId,
        string $extendedClaim,
    ): bool {
        $record = $this->get()->first();

        return data_get($record, 'elements.success') === 'success';
    }
}
