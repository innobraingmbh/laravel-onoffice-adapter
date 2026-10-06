<?php

declare(strict_types=1);

namespace Innobrain\OnOfficeAdapter\Query\Concerns;

use Innobrain\OnOfficeAdapter\Dtos\OnOfficeRequest;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeAction;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeResourceId;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeResourceType;
use Innobrain\OnOfficeAdapter\Exceptions\OnOfficeException;
use Innobrain\OnOfficeAdapter\Services\OnOfficeResponsePath;
use Throwable;

/**
 * The write twin of the Paginate trait: it hoists the create/modify/delete
 * terminals out of the individual builders, which otherwise copy the same
 * OnOfficeRequest-build-dispatch-unwrap skeleton verbatim. Each builder keeps
 * ownership of the one thing that genuinely differs — the parameter payload —
 * and only has to say which resource and action the write targets.
 */
trait WritesRecords
{
    /**
     * Send a create request and return the created record.
     *
     * @param  array<string, mixed>  $parameters
     * @return array<string, mixed>
     *
     * @throws OnOfficeException
     * @throws Throwable
     */
    protected function createRecord(
        OnOfficeResourceType|string $resourceType,
        array $parameters,
        OnOfficeResourceId|string|int $resourceId = OnOfficeResourceId::None,
    ): array {
        $request = new OnOfficeRequest(
            OnOfficeAction::Create,
            $resourceType,
            $resourceId,
            parameters: $parameters,
        );

        return $this->requestApi($request)
            ->json(OnOfficeResponsePath::FIRST_RECORD);
    }

    /**
     * Send a modify request. The API throws on failure, so reaching the return
     * means the change was accepted.
     *
     * @param  array<string, mixed>  $parameters
     *
     * @throws OnOfficeException
     * @throws Throwable
     */
    protected function modifyRecord(
        OnOfficeResourceType|string $resourceType,
        array $parameters,
        OnOfficeResourceId|string|int $resourceId = OnOfficeResourceId::None,
    ): bool {
        $request = new OnOfficeRequest(
            OnOfficeAction::Modify,
            $resourceType,
            $resourceId,
            parameters: $parameters,
        );

        $this->requestApi($request);

        return true;
    }

    /**
     * Send a delete request, returning whether onOffice reported success.
     *
     * @param  array<string, mixed>  $parameters
     *
     * @throws OnOfficeException
     * @throws Throwable
     */
    protected function deleteRecord(
        OnOfficeResourceType|string $resourceType,
        array $parameters = [],
        OnOfficeResourceId|string|int $resourceId = OnOfficeResourceId::None,
    ): bool {
        $request = new OnOfficeRequest(
            OnOfficeAction::Delete,
            $resourceType,
            $resourceId,
            parameters: $parameters,
        );

        return $this->requestApi($request)
            ->json(OnOfficeResponsePath::FIRST_RECORD_ELEMENTS_SUCCESS) === 'success';
    }
}
