<?php

declare(strict_types=1);

namespace Innobrain\OnOfficeAdapter\Dtos;

use Innobrain\OnOfficeAdapter\Enums\OnOfficeAction;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeResourceId;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeResourceType;

readonly class OnOfficeRequest
{
    public function __construct(
        public OnOfficeAction $actionId,
        public OnOfficeResourceType $resourceType,
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
}
