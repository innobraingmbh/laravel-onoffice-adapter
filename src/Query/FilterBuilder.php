<?php

declare(strict_types=1);

namespace Innobrain\OnOfficeAdapter\Query;

use Illuminate\Support\Collection;
use Innobrain\OnOfficeAdapter\Dtos\OnOfficeRequest;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeAction;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeResourceType;
use Innobrain\OnOfficeAdapter\Exceptions\OnOfficeException;
use Innobrain\OnOfficeAdapter\Services\OnOfficeService;
use Throwable;

class FilterBuilder extends Builder
{
    public string $module;

    /**
     * @throws OnOfficeException
     */
    public function get(): Collection
    {
        $request = new OnOfficeRequest(
            OnOfficeAction::Get,
            OnOfficeResourceType::Filters,
            parameters: [
                OnOfficeService::MODULE => $this->module,
            ]
        );

        return $this->requestAll($request);
    }

    /**
     * @throws Throwable<OnOfficeException>
     */
    public function first(): ?array
    {
        $request = new OnOfficeRequest(
            OnOfficeAction::Get,
            OnOfficeResourceType::Filters,
            parameters: [
                'module' => $this->module,
            ]
        );

        return $this->requestApi($request)
            ->json('response.results.0.data.records.0');

    }

    /**
     * @throws OnOfficeException
     */
    public function each(callable $callback): void
    {
        $request = new OnOfficeRequest(
            OnOfficeAction::Get,
            OnOfficeResourceType::Filters,
            parameters: [
                OnOfficeService::MODULE => $this->module,
            ],
        );

        $this->requestAllChunked($request, $callback);
    }

    public function estate(): static
    {
        $this->module = 'estate';

        return $this;
    }

    public function address(): static
    {
        $this->module = 'address';

        return $this;
    }
}
