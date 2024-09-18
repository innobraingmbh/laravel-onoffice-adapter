<?php

declare(strict_types=1);

namespace Katalam\OnOfficeAdapter\Query;

use Illuminate\Support\Collection;
use Katalam\OnOfficeAdapter\Dtos\OnOfficeRequest;
use Katalam\OnOfficeAdapter\Enums\OnOfficeAction;
use Katalam\OnOfficeAdapter\Enums\OnOfficeResourceType;
use Katalam\OnOfficeAdapter\Exceptions\OnOfficeException;
use Katalam\OnOfficeAdapter\Query\Concerns\NonFilterable;
use Katalam\OnOfficeAdapter\Query\Concerns\NonOrderable;
use Katalam\OnOfficeAdapter\Query\Concerns\NonSelectable;
use Katalam\OnOfficeAdapter\Query\Concerns\RelationTypes;
use Katalam\OnOfficeAdapter\Services\OnOfficeService;
use Throwable;

class RelationBuilder extends Builder
{
    use NonFilterable;
    use NonFilterable;
    use NonOrderable;
    use NonSelectable;
    use RelationTypes;

    /**
     * @throws OnOfficeException
     */
    public function get(): Collection
    {
        $request = new OnOfficeRequest(
            OnOfficeAction::Get,
            OnOfficeResourceType::IdsFromRelation,
            parameters: [
                OnOfficeService::RELATIONTYPE => $this->relationType,
                OnOfficeService::PARENTIDS => $this->parentIds,
                OnOfficeService::CHILDIDS => $this->childIds,
                ...$this->customParameters,
            ],
        );

        $records = $this->requestAll($request);

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

    /**
     * @throws OnOfficeException
     */
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
     * @throws Throwable<OnOfficeException>
     */
    public function create(): bool
    {
        $request = new OnOfficeRequest(
            OnOfficeAction::Create,
            OnOfficeResourceType::Relation,
            parameters: [
                OnOfficeService::RELATIONTYPE => $this->relationType,
                OnOfficeService::PARENTID => $this->parentIds,
                OnOfficeService::CHILDID => $this->childIds,
                ...$this->customParameters,
            ],
        );

        $this->requestApi($request);

        return true;
    }
}
