<?php

declare(strict_types=1);

namespace Innobrain\OnOfficeAdapter\Query\Concerns;

use Illuminate\Support\Collection;
use Innobrain\OnOfficeAdapter\Dtos\OnOfficeRequest;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeAction;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeResourceId;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeResourceType;
use Innobrain\OnOfficeAdapter\Exceptions\OnOfficeException;
use Innobrain\OnOfficeAdapter\Services\OnOfficeService;

trait Searchable
{
    /**
     * @return Collection<int, array<string, mixed>>
     *
     * @throws OnOfficeException
     */
    public function search(): Collection
    {
        $request = new OnOfficeRequest(
            OnOfficeAction::Get,
            OnOfficeResourceType::Search,
            $this->searchResourceId(),
            parameters: [
                OnOfficeService::INPUT => $this->input,
                ...$this->getSplitSortParameters(),
                OnOfficeService::FILTER => $this->getFilters(),
                ...$this->customParameters,
            ],
        );

        return $this->requestAll($request);
    }

    /**
     * The resource whose search index this builder targets.
     */
    abstract protected function searchResourceId(): OnOfficeResourceId;
}
