<?php

declare(strict_types=1);

namespace Innobrain\OnOfficeAdapter\Dtos;

use Illuminate\Support\Facades\Date;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeAction;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeResourceId;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeResourceType;
use Innobrain\OnOfficeAdapter\Services\OnOfficeService;

class OnOfficeRequest
{
    public function __construct(
        public OnOfficeAction $actionId,
        public OnOfficeResourceType|string $resourceType,
        public OnOfficeResourceId|string|int $resourceId = OnOfficeResourceId::None,
        public string|int $identifier = '',
        /** @var array<string, mixed> */
        public array $parameters = []
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'actionid' => $this->actionId->value,
            'resourceType' => $this->resourceType instanceof OnOfficeResourceType
                ? $this->resourceType->value
                : $this->resourceType,
            'resourceId' => $this->resourceId,
            'identifier' => $this->identifier,
            'parameters' => $this->parameters,
        ];
    }

    /**
     * Returns the action element of the request body,
     * including the HMAC and timestamp.
     *
     * @return array<string, mixed>
     */
    public function toActionArray(OnOfficeService $onOfficeService): array
    {
        if (! empty($onOfficeService->getApiClaim())) {
            $this->parameters = array_replace([OnOfficeService::EXTENDEDCLAIM => $onOfficeService->getApiClaim()], $this->parameters);
        }

        return [
            'actionid' => $this->actionId->value,
            'resourceid' => $this->resourceId instanceof OnOfficeResourceId
                ? $this->resourceId->value
                : $this->resourceId,
            'resourcetype' => $this->resourceType instanceof OnOfficeResourceType
                ? $this->resourceType->value
                : $this->resourceType,
            'identifier' => $this->identifier,
            'timestamp' => Date::now()->timestamp,
            'hmac' => $onOfficeService->getHmac($this->actionId, $this->resourceType),
            'hmac_version' => 2,
            'parameters' => $this->parameters,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function toRequestArray(): array
    {
        return resolve(OnOfficeService::class)->requestBody([$this]);
    }
}
