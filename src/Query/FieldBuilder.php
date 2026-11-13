<?php

declare(strict_types=1);

namespace Innobrain\OnOfficeAdapter\Query;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Innobrain\OnOfficeAdapter\Dtos\OnOfficeRequest;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeAction;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeResourceType;
use Innobrain\OnOfficeAdapter\Exceptions\OnOfficeException;
use Innobrain\OnOfficeAdapter\Services\OnOfficeResponsePath;
use Throwable;

class FieldBuilder extends Builder
{
    /**
     * @var array<int, string>
     */
    public array $modules = [];

    /**
     * @throws OnOfficeException
     */
    public function get(): Collection
    {
        return $this->requestAll($this->buildReadRequest());
    }

    /**
     * @throws Throwable<OnOfficeException>
     */
    public function first(): ?array
    {
        return $this->requestApi($this->buildReadRequest())
            ->json(OnOfficeResponsePath::FIRST_RECORD);
    }

    /**
     * @throws OnOfficeException
     */
    public function each(callable $callback): void
    {
        $this->requestAllChunked($this->buildReadRequest(), $callback);
    }

    /**
     * Build the fields read request shared by get(), first() and each().
     */
    protected function buildReadRequest(): OnOfficeRequest
    {
        return new OnOfficeRequest(
            OnOfficeAction::Get,
            OnOfficeResourceType::Fields,
            parameters: [
                'modules' => $this->modules,
                ...$this->customParameters,
            ],
        );
    }

    /**
     * @param  array<int, string>|string  $modules
     */
    public function withModules(array|string $modules): static
    {
        $this->modules = array_merge($this->modules, Arr::wrap($modules));

        return $this;
    }
}
