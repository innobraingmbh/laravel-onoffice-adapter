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
 * The regions endpoint is not paginated: it returns the full tree in one response
 * and reports `meta.cntabsolute` as the total node count. Reads issue a single
 * request rather than looping through requestAll(), which would re-fetch and
 * duplicate the tree once per `ceil(cntabsolute / pageSize)` page.
 */
class RegionBuilder extends Builder
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

    /**
     * @deprecated Not paginated; use get(). Only ever invokes the callback once.
     *
     * @throws OnOfficeException
     */
    public function each(callable $callback): void
    {
        $callback($this->requestApi($this->buildReadRequest())->json(OnOfficeResponsePath::RECORDS, []));
    }

    private function buildReadRequest(): OnOfficeRequest
    {
        return new OnOfficeRequest(
            OnOfficeAction::Get,
            OnOfficeResourceType::Regions,
            ...$this->customParameters,
        );
    }
}
