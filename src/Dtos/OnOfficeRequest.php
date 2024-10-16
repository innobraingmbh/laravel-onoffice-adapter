<?php

declare(strict_types=1);

namespace Innobrain\OnOfficeAdapter\Dtos;

use Illuminate\Support\Carbon;
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
        public array $parameters = []
    ) {}

    public function toArray(): array
    {
        return [
            'actionid' => $this->actionId->value,
            'resourceType' => $this->resourceType->value,
            'resourceId' => $this->resourceId,
            'identifier' => $this->identifier,
            'parameters' => $this->parameters,
        ];
    }

    public function toRequestArray(): array
    {
        /** @var OnOfficeService $onOfficeService */
        $onOfficeService = app(OnOfficeService::class);

        if (! empty($onOfficeService->getApiClaim())) {
            $this->parameters = array_replace([OnOfficeService::EXTENDEDCLAIM => $onOfficeService->getApiClaim()], $this->parameters);
        }

        return [
            'token' => $onOfficeService->getToken(),
            'request' => [
                'actions' => [
                    [
                        'actionid' => $this->actionId->value,
                        'resourceid' => $this->resourceId instanceof OnOfficeResourceId
                            ? $this->resourceId->value
                            : $this->resourceId,
                        'resourcetype' => $this->resourceType instanceof OnOfficeResourceType
                            ? $this->resourceType->value
                            : $this->resourceType,
                        'identifier' => $this->identifier,
                        'timestamp' => Carbon::now()->timestamp,
                        'hmac' => $onOfficeService->getHmac($this->actionId, $this->resourceType),
                        'hmac_version' => 2,
                        'parameters' => $this->parameters,
                    ],
                ],
            ],
        ];
    }
}
