<?php

declare(strict_types=1);

namespace Innobrain\OnOfficeAdapter\Query;

use Illuminate\Support\Collection;
use Innobrain\OnOfficeAdapter\Dtos\OnOfficeRequest;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeAction;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeResourceType;
use Innobrain\OnOfficeAdapter\Exceptions\OnOfficeException;
use Innobrain\OnOfficeAdapter\Query\Concerns\NonFilterable;
use Innobrain\OnOfficeAdapter\Query\Concerns\NonOrderable;
use Innobrain\OnOfficeAdapter\Query\Concerns\NonSelectable;
use Innobrain\OnOfficeAdapter\Query\Concerns\RelationTypes;
use Innobrain\OnOfficeAdapter\Services\OnOfficeService;
use Throwable;

class RelationBuilder extends Builder
{
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
    public function each(callable $callback): void
    {
        $records = $this->get();

        $callback($records);
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
