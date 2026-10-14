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
use Innobrain\OnOfficeAdapter\Services\OnOfficeService;
use Throwable;

/**
 * The impressum is a singleton settings record (its id is the literal
 * string "impressum"), so first() is the accessor. There is nothing to
 * find by id — the API ignores a resource id on this endpoint.
 */
class ImprintBuilder extends Builder
{
    use NonFilterable;
    use NonOrderable;

    /**
     * @throws OnOfficeException
     */
    public function get(): Collection
    {
        return $this->requestAll($this->buildReadRequest());
    }

    /**
     * @throws Throwable<OnOfficeException>
     */
    public function first(): ?array
    {
        return $this->requestFirstRecord($this->buildReadRequest());
    }

    /**
     * Build the impressum read request shared by get() and first().
     */
    protected function buildReadRequest(): OnOfficeRequest
    {
        return new OnOfficeRequest(
            OnOfficeAction::Read,
            OnOfficeResourceType::Impressum,
            parameters: [
                OnOfficeService::DATA => $this->columns,
                ...$this->customParameters,
            ]
        );
    }
}
