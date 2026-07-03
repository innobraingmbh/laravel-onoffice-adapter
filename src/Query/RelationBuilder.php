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
use Override;
use Throwable;

class RelationBuilder extends Builder
{
    use NonFilterable;
    use NonOrderable;
    use NonSelectable;
    use RelationTypes;

    /**
     * Returns relation mappings (parent ID => child ID pairs).
     * Note: This returns a different shape than the parent interface declares,
     * as relations return ID mappings rather than entity records.
     *
     * @throws OnOfficeException
     */
    #[Override]
    public function get(): Collection
    {
        $records = $this->requestAll($this->toRequest());

        // $records is always an array containing a single element
        /** @var array<string, mixed> $elements */
        $elements = data_get($records->first(), 'elements', []);

        /** @var Collection<int, array<string, mixed>> */
        return collect($elements);
    }

    /**
     * Build the ids-from-relation request this builder would send, without
     * sending it. Useful for resolving several relation lookups in one batched
     * API call. Unlike get(), a batch result keeps the raw record shape: the
     * parent ID => child ID map sits at data.records.0.elements.
     */
    #[Override]
    public function toRequest(): OnOfficeRequest
    {
        return new OnOfficeRequest(
            OnOfficeAction::Get,
            OnOfficeResourceType::IdsFromRelation,
            parameters: [
                OnOfficeService::RELATIONTYPE => $this->relationType,
                OnOfficeService::PARENTIDS => $this->parentIds,
                OnOfficeService::CHILDIDS => $this->childIds,
                ...$this->customParameters,
            ],
        );
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
