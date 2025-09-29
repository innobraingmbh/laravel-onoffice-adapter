<?php

declare(strict_types=1);

namespace Innobrain\OnOfficeAdapter\Query;

use Illuminate\Support\Collection;
use Innobrain\OnOfficeAdapter\Dtos\OnOfficeRequest;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeAction;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeResourceType;
use Innobrain\OnOfficeAdapter\Exceptions\OnOfficeException;
use Innobrain\OnOfficeAdapter\Exceptions\OnOfficeQueryException;
use Innobrain\OnOfficeAdapter\Services\OnOfficeService;
use Throwable;

class FilterBuilder extends Builder
{
    public string $module;

    /**
     * @throws OnOfficeException
     * @throws Throwable<OnOfficeQueryException>
     */
    public function get(bool $concurrently = false): Collection
    {
        throw_unless(isset($this->module), new OnOfficeQueryException('Filter Builder module is not set'));

        $request = new OnOfficeRequest(
            OnOfficeAction::Get,
            OnOfficeResourceType::Filters,
            parameters: [
                OnOfficeService::MODULE => $this->module,
            ]
        );

        if ($concurrently) {
            return $this->requestConcurrently($request);
        }

        return $this->requestAll($request);
    }

    /**
     * @throws Throwable<OnOfficeException>
     * @throws Throwable<OnOfficeQueryException>
     */
    public function first(): ?array
    {
        throw_unless(isset($this->module), new OnOfficeQueryException('Filter Builder module is not set'));

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
     * @throws Throwable<OnOfficeQueryException>
     */
    public function each(callable $callback): void
    {
        throw_unless(isset($this->module), new OnOfficeQueryException('Filter Builder module is not set'));

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
