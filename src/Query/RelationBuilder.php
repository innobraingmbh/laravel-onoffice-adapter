<?php

declare(strict_types=1);

namespace Katalam\OnOfficeAdapter\Query;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Katalam\OnOfficeAdapter\Enums\OnOfficeAction;
use Katalam\OnOfficeAdapter\Enums\OnOfficeRelationType;
use Katalam\OnOfficeAdapter\Enums\OnOfficeResourceType;
use Katalam\OnOfficeAdapter\Exceptions\OnOfficeException;
use Katalam\OnOfficeAdapter\Query\Concerns\NonFilterable;
use Katalam\OnOfficeAdapter\Query\Concerns\NonOrderable;
use Katalam\OnOfficeAdapter\Query\Concerns\NonSelectable;
use Katalam\OnOfficeAdapter\Services\OnOfficeService;

class RelationBuilder extends Builder
{
    use NonFilterable;
    use NonFilterable;
    use NonOrderable;
    use NonSelectable;

    public array $parentIds = [];

    public array $childIds = [];

    public OnOfficeRelationType $relationType;

    public function __construct(
        private readonly OnOfficeService $onOfficeService,
    ) {
    }

    public function get(): Collection
    {
        $records = $this->onOfficeService->requestAll(/**
         * @throws OnOfficeException
         */ function () {
            return $this->onOfficeService->requestApi(
                OnOfficeAction::Get,
                OnOfficeResourceType::IdsFromRelation,
                parameters: [
                    OnOfficeService::RELATIONTYPE => $this->relationType,
                    OnOfficeService::PARENTIDS => $this->parentIds,
                    OnOfficeService::CHILDIDS => $this->childIds,
                ],
            );
        }, pageSize: $this->limit, offset: $this->offset);

        // $records is always an array containing a single element
        return collect(data_get($records->first(), 'elements'));
    }

    /**
     * @throws OnOfficeException
     */
    public function first(): array
    {
        throw new OnOfficeException('Not implemented in onOffice');
    }

    /**
     * @throws OnOfficeException
     */
    public function find(int $id): array
    {
        throw new OnOfficeException('Not implemented in onOffice');
    }

    public function each(callable $callback): void
    {
        $records = $this->get();

        $callback($records);
    }

    public function parentIds(int|array $parentIds): static
    {
        $this->parentIds = Arr::wrap($parentIds);

        return $this;
    }

    public function addParentIds(int|array $parentIds): static
    {
        $this->parentIds = array_merge($this->parentIds, Arr::wrap($parentIds));

        return $this;
    }

    public function childIds(int|array $childIds): static
    {
        $this->childIds = Arr::wrap($childIds);

        return $this;
    }

    public function addChildIds(int|array $childIds): static
    {
        $this->childIds = array_merge($this->childIds, Arr::wrap($childIds));

        return $this;
    }

    public function relationType(OnOfficeRelationType $relationType): static
    {
        $this->relationType = $relationType;

        return $this;
    }

    /**
     * @throws OnOfficeException
     */
    public function modify(int $id): bool
    {
        throw new OnOfficeException('Not implemented');
    }
}
