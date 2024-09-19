<?php

declare(strict_types=1);

namespace Katalam\OnOfficeAdapter\Query;

use Katalam\OnOfficeAdapter\Dtos\OnOfficeRequest;
use Katalam\OnOfficeAdapter\Enums\OnOfficeAction;
use Katalam\OnOfficeAdapter\Enums\OnOfficeResourceType;
use Katalam\OnOfficeAdapter\Exceptions\OnOfficeException;
use Katalam\OnOfficeAdapter\Services\OnOfficeService;
use Throwable;

class MarketplaceBuilder extends Builder
{
    /**
     * @throws Throwable<OnOfficeException>
     */
    public function unlockProvider(
        string $parameterCacheId,
        string $extendedClaim,
    ): bool {
        $request = new OnOfficeRequest(
            OnOfficeAction::Do,
            OnOfficeResourceType::UnlockProvider,
            parameters: [
                OnOfficeService::PARAMETERCACHEID => $parameterCacheId,
                OnOfficeService::EXTENDEDCLAIM => $extendedClaim,
                ...$this->customParameters,
            ]
        );

        return $this->requestApi($request)
            ->json('response.results.0.data.records.0.elements.success') === 'success';
    }
}
