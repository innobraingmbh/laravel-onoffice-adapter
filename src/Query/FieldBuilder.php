<?php

declare(strict_types=1);

namespace Innobrain\OnOfficeAdapter\Query;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Innobrain\OnOfficeAdapter\Dtos\OnOfficeRequest;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeAction;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeResourceType;
use Innobrain\OnOfficeAdapter\Exceptions\OnOfficeException;

class FieldBuilder extends Builder
{
    public array $modules = [];

    /**
     * @throws OnOfficeException
     */
    public function get(): Collection
    {
        $request = new OnOfficeRequest(
            OnOfficeAction::Get,
            OnOfficeResourceType::Fields,
            parameters: [
                'modules' => $this->modules,
                ...$this->customParameters,
            ],
        );

        return $this->requestAll($request);
    }

    /**
     * @throws OnOfficeException
     */
    public function first(): ?array
    {
        $response = $this->onOfficeService->requestApi(
            OnOfficeAction::Get,
            OnOfficeResourceType::Fields,
            parameters: [
                'modules' => $this->modules,
                ...$this->customParameters,
            ],
        );

        return $response->json('response.results.0.data.records.0');
    }

    /**
     * @throws OnOfficeException
     */
    public function each(callable $callback): void
    {
        $request = new OnOfficeRequest(
            OnOfficeAction::Get,
            OnOfficeResourceType::Fields,
            parameters: [
                'modules' => $this->modules,
                ...$this->customParameters,
            ],
        );

        $this->requestAllChunked($request, $callback);
    }

    public function withModules(array|string $modules): static
    {
        $this->modules = array_merge($this->modules, Arr::wrap($modules));

        return $this;
    }
}
