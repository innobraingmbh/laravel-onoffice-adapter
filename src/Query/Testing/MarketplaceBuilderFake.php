<?php

declare(strict_types=1);

namespace Katalam\OnOfficeAdapter\Query\Testing;

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
