<?php

declare(strict_types=1);

namespace Innobrain\OnOfficeAdapter\Query;

use Innobrain\OnOfficeAdapter\Dtos\OnOfficeRequest;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeAction;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeResourceType;
use Innobrain\OnOfficeAdapter\Exceptions\OnOfficeException;
use Innobrain\OnOfficeAdapter\Services\OnOfficeService;
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
