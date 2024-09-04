<?php

declare(strict_types=1);

namespace Katalam\OnOfficeAdapter\Query;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Katalam\OnOfficeAdapter\Enums\OnOfficeAction;
use Katalam\OnOfficeAdapter\Enums\OnOfficeResourceType;
use Katalam\OnOfficeAdapter\Exceptions\OnOfficeException;
use Katalam\OnOfficeAdapter\Services\OnOfficeService;

class SearchCriteriaBuilder extends Builder
{
    private string $mode = 'internal';

    public function __construct(
        private readonly OnOfficeService $onOfficeService,
    ) {}

    /**
     * @throws OnOfficeException
     */
    public function get(): Collection
    {
        throw new OnOfficeException('Not implemented');
    }

    /**
     * @throws OnOfficeException
     */
    public function first(): ?array
    {
        throw new OnOfficeException('Not implemented');
    }

    /**
     * @throws OnOfficeException
     */
    public function find(int|array $id): array
    {
        $response = $this->onOfficeService->requestApi(
            OnOfficeAction::Get,
            OnOfficeResourceType::SearchCriteria,
            parameters: [
                OnOfficeService::MODE => $this->mode,
                OnOfficeService::IDS => Arr::wrap($id),
                ...$this->customParameters,
            ],
        );

        return $response->json('response.results.0.data.records.0', []);
    }

    /**
     * @throws OnOfficeException
     */
    public function each(callable $callback): void
    {
        throw new OnOfficeException('Not implemented');
    }

    /**
     * @throws OnOfficeException
     */
    public function modify(int $id): bool
    {
        throw new OnOfficeException('Not implemented');
    }

    public function mode(string $mode): self
    {
        $this->mode = $mode;

        return $this;
    }
}
