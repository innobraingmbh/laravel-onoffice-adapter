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
use Innobrain\OnOfficeAdapter\Services\OnOfficeResponsePath;
use Throwable;

/**
 * The fields configured as search criteria, one record per category. Each
 * record's elements hold the category `name` and its `fields`. The endpoint
 * is not paginated.
 */
class SearchCriteriaFieldBuilder extends Builder
{
    use NonFilterable;
    use NonOrderable;
    use NonSelectable;

    /**
     * @throws OnOfficeException
     */
    public function get(): Collection
    {
        /** @var array<int, array<string, mixed>> $records */
        $records = $this->requestApi($this->buildReadRequest())->json(OnOfficeResponsePath::RECORDS, []);

        return collect($records);
    }

    /**
     * @throws Throwable<OnOfficeException>
     */
    public function first(): ?array
    {
        return $this->requestApi($this->buildReadRequest())
            ->json(OnOfficeResponsePath::FIRST_RECORD);
    }

    private function buildReadRequest(): OnOfficeRequest
    {
        return new OnOfficeRequest(
            OnOfficeAction::Get,
            OnOfficeResourceType::SearchCriteriaFields,
            parameters: $this->customParameters,
        );
    }
}
