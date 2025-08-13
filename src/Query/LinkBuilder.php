<?php

declare(strict_types=1);

namespace Innobrain\OnOfficeAdapter\Query;

use Illuminate\Support\Collection;
use Innobrain\OnOfficeAdapter\Dtos\OnOfficeRequest;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeAction;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeResourceId;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeResourceType;
use Innobrain\OnOfficeAdapter\Exceptions\OnOfficeException;
use Innobrain\OnOfficeAdapter\Services\OnOfficeService;
use Throwable;

class LinkBuilder extends Builder
{
    public OnOfficeResourceId $resourceId;

    public int $recordId;

    public OnOfficeResourceId $type = OnOfficeResourceId::Estate;

    /**
     * @throws OnOfficeException
     */
    public function get(): Collection
    {
        $parameters = [
            OnOfficeService::RECORDID => $this->recordId,
            ...$this->customParameters,
        ];

        if ($this->resourceId === OnOfficeResourceId::AgentsLog) {
            $parameters['type'] = $this->type->value;
        }

        $request = new OnOfficeRequest(
            OnOfficeAction::Get,
            OnOfficeResourceType::GetLink,
            $this->resourceId,
            parameters: $parameters,
        );

        return $this->requestAll($request);
    }

    /**
     * @throws OnOfficeException
     * @throws Throwable
     */
    public function first(): ?array
    {
        $parameters = [
            OnOfficeService::RECORDID => $this->recordId,
            ...$this->customParameters,
        ];

        if ($this->resourceId === OnOfficeResourceId::AgentsLog) {
            $parameters['type'] = $this->type->value;
        }

        $request = new OnOfficeRequest(
            OnOfficeAction::Get,
            OnOfficeResourceType::GetLink,
            $this->resourceId,
            parameters: $parameters,
        );

        return $this->requestApi($request)
            ->json('response.results.0.data.records.0');
    }

    /**
     * @throws Throwable
     * @throws OnOfficeException
     */
    public function find(int $id): ?array
    {
        $parameters = [
            OnOfficeService::RECORDID => $id,
            ...$this->customParameters,
        ];

        if ($this->resourceId === OnOfficeResourceId::AgentsLog) {
            $parameters['type'] = $this->type->value;
        }

        $request = new OnOfficeRequest(
            OnOfficeAction::Get,
            OnOfficeResourceType::GetLink,
            $this->resourceId,
            parameters: $parameters,
        );

        return $this->requestApi($request)
            ->json('response.results.0.data.records.0');
    }

    public function withResourceId(OnOfficeResourceId $resourceId): self
    {
        $this->resourceId = $resourceId;

        return $this;
    }

    public function module(OnOfficeResourceId $resourceId): self
    {
        return $this->withResourceId($resourceId);
    }

    public function recordId(int $recordId): static
    {
        $this->recordId = $recordId;

        return $this;
    }

    public function type(OnOfficeResourceId $type): static
    {
        $this->type = $type;

        return $this;
    }
}
