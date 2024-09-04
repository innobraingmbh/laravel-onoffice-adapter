<?php

declare(strict_types=1);

namespace Katalam\OnOfficeAdapter\Query;

use Illuminate\Support\Collection;
use Katalam\OnOfficeAdapter\Enums\OnOfficeAction;
use Katalam\OnOfficeAdapter\Enums\OnOfficeResourceType;
use Katalam\OnOfficeAdapter\Exceptions\OnOfficeException;
use Katalam\OnOfficeAdapter\Query\Concerns\NonFilterable;
use Katalam\OnOfficeAdapter\Query\Concerns\NonOrderable;
use Katalam\OnOfficeAdapter\Query\Concerns\NonSelectable;
use Katalam\OnOfficeAdapter\Query\Concerns\RelationTypes;
use Katalam\OnOfficeAdapter\Services\OnOfficeService;

class RelationBuilder extends Builder
{
    use NonFilterable;
    use NonFilterable;
    use NonOrderable;
    use NonSelectable;
    use RelationTypes;

    public function __construct(
        private readonly OnOfficeService $onOfficeService,
    ) {}

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
                    ...$this->customParameters,
                ],
            );
        }, pageSize: $this->limit, offset: $this->offset);

        // $records is always an array containing a single element
        return collect(data_get($records->first(), 'elements'));
    }

    /**
     * @throws OnOfficeException
     */
    public function first(): ?array
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

    /**
     * @throws OnOfficeException
     */
    public function modify(int $id): bool
    {
        throw new OnOfficeException('Not implemented');
    }

    /**
     * @throws OnOfficeException
     */
    public function create(): array
    {
        $response = $this->onOfficeService->requestApi(
            OnOfficeAction::Create,
            OnOfficeResourceType::Relation,
            parameters: [
                OnOfficeService::RELATIONTYPE => $this->relationType,
                OnOfficeService::PARENTIDS => $this->parentIds,
                OnOfficeService::CHILDIDS => $this->childIds,
                ...$this->customParameters,
            ],
        );

        return $response->json('response.results.0.data.records.0');
    }
}
