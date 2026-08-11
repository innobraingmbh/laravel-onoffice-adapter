<?php

declare(strict_types=1);

namespace Innobrain\OnOfficeAdapter\Query;

use Illuminate\Support\Collection;
use Innobrain\OnOfficeAdapter\Dtos\OnOfficeRequest;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeAction;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeResourceType;
use Innobrain\OnOfficeAdapter\Exceptions\OnOfficeException;
use Innobrain\OnOfficeAdapter\Exceptions\OnOfficeQueryException;
use Innobrain\OnOfficeAdapter\Services\OnOfficeResponsePath;
use Innobrain\OnOfficeAdapter\Services\OnOfficeService;
use Throwable;

class FilterBuilder extends Builder
{
    public const MODULE_ESTATE = 'estate';

    public const MODULE_ADDRESS = 'address';

    public string $module;

    /**
     * @throws OnOfficeException
     * @throws Throwable<OnOfficeQueryException>
     */
    public function get(): Collection
    {
        return $this->requestAll($this->buildRequest());
    }

    /**
     * @throws Throwable<OnOfficeException>
     * @throws Throwable<OnOfficeQueryException>
     */
    public function first(): ?array
    {
        return $this->requestApi($this->buildRequest())
            ->json(OnOfficeResponsePath::FIRST_RECORD);
    }

    /**
     * @throws OnOfficeException
     * @throws Throwable<OnOfficeQueryException>
     */
    public function each(callable $callback): void
    {
        $this->requestAllChunked($this->buildRequest(), $callback);
    }

    public function estate(): static
    {
        $this->module = self::MODULE_ESTATE;

        return $this;
    }

    public function address(): static
    {
        $this->module = self::MODULE_ADDRESS;

        return $this;
    }

    /**
     * @throws Throwable<OnOfficeQueryException>
     */
    private function buildRequest(): OnOfficeRequest
    {
        throw_unless(isset($this->module), OnOfficeQueryException::class, 'Filter Builder module is not set');

        return new OnOfficeRequest(
            OnOfficeAction::Get,
            OnOfficeResourceType::Filters,
            parameters: [
                OnOfficeService::MODULE => $this->module,
            ],
        );
    }
}
