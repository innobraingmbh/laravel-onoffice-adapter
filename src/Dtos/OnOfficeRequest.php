<?php

declare(strict_types=1);

namespace Katalam\OnOfficeAdapter\Dtos;

use Katalam\OnOfficeAdapter\Enums\OnOfficeAction;
use Katalam\OnOfficeAdapter\Enums\OnOfficeResourceId;
use Katalam\OnOfficeAdapter\Enums\OnOfficeResourceType;

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
