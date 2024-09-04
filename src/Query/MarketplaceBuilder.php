<?php

declare(strict_types=1);

namespace Katalam\OnOfficeAdapter\Query;

use Illuminate\Support\Collection;
use Katalam\OnOfficeAdapter\Enums\OnOfficeAction;
use Katalam\OnOfficeAdapter\Enums\OnOfficeResourceType;
use Katalam\OnOfficeAdapter\Exceptions\OnOfficeException;
use Katalam\OnOfficeAdapter\Services\OnOfficeService;

class MarketplaceBuilder extends Builder
{
    public function __construct(
        private readonly OnOfficeService $onOfficeService,
    ) {}

    /**
     * @throws OnOfficeException
     */
    public function get(): Collection
    {
        throw new OnOfficeException('Method not implemented yet');
    }

    public function unlockProvider(
        string $parameterCacheId,
        string $extendedClaim,
    ): bool {
        $response = $this->onOfficeService->requestApi(
            OnOfficeAction::Do,
            OnOfficeResourceType::UnlockProvider,
            parameters: [
                OnOfficeService::PARAMETERCACHEID => $parameterCacheId,
                OnOfficeService::EXTENDEDCLAIM => $extendedClaim,
                ...$this->customParameters,
            ]
        );

        return $response->json('response.results.0.data.records.0.elements.success') === 'success';
    }

    /**
     * @throws OnOfficeException
     */
    public function first(): ?array
    {
        throw new OnOfficeException('Method not implemented yet');
    }

    /**
     * @throws OnOfficeException
     */
    public function find(int $id): array
    {
        throw new OnOfficeException('Method not implemented yet');
    }

    /**
     * @throws OnOfficeException
     */
    public function each(callable $callback): void
    {
        throw new OnOfficeException('Method not implemented yet');
    }

    /**
     * @throws OnOfficeException
     */
    public function modify(int $id): bool
    {
        throw new OnOfficeException('Not implemented');
    }
}
