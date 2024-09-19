<?php

declare(strict_types=1);

namespace Innobrain\OnOfficeAdapter\Query;

use Illuminate\Support\Arr;
use Innobrain\OnOfficeAdapter\Dtos\OnOfficeRequest;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeAction;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeResourceType;
use Innobrain\OnOfficeAdapter\Exceptions\OnOfficeException;
use Innobrain\OnOfficeAdapter\Services\OnOfficeService;
use Throwable;

class SearchCriteriaBuilder extends Builder
{
    private string $mode = 'internal';

    /**
     * @throws Throwable<OnOfficeException>
     */
    public function find(int|array $id): array
    {
        $request = new OnOfficeRequest(
            OnOfficeAction::Get,
            OnOfficeResourceType::SearchCriteria,
            parameters: [
                OnOfficeService::MODE => $this->mode,
                OnOfficeService::IDS => Arr::wrap($id),
                ...$this->customParameters,
            ],
        );

        return $this->requestApi($request)
            ->json('response.results.0.data.records.0', []);
    }

    public function mode(string $mode): self
    {
        $this->mode = $mode;

        return $this;
    }
}
