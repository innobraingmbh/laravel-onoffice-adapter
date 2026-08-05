<?php

declare(strict_types=1);

namespace Innobrain\OnOfficeAdapter\Query;

use Innobrain\OnOfficeAdapter\Dtos\OnOfficeRequest;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeAction;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeResourceType;
use Innobrain\OnOfficeAdapter\Exceptions\OnOfficeException;
use Innobrain\OnOfficeAdapter\Query\Concerns\Paginate;
use Innobrain\OnOfficeAdapter\Services\OnOfficeService;
use Throwable;

class LogBuilder extends Builder
{
    use Paginate;

    public string $module = '';

    public string $action = '';

    public int $userId = -1;

    protected function buildReadRequest(): OnOfficeRequest
    {
        $parameters = [
            OnOfficeService::MODULE => $this->module,
            OnOfficeService::ACTION => $this->action,
            OnOfficeService::FILTER => $this->getFilters(),
            ...$this->customParameters,
        ];

        if ($this->userId > 0) {
            $parameters['user'] = $this->userId;
        }

        return new OnOfficeRequest(
            OnOfficeAction::Read,
            OnOfficeResourceType::Log,
            parameters: $parameters,
        );
    }

    /**
     * @throws Throwable<OnOfficeException>
     */
    public function find(int $id): ?array
    {
        return $this->requestFind(OnOfficeAction::Read, OnOfficeResourceType::Log, $id);
    }

    public function withModule(string $module): static
    {
        $this->module = $module;

        return $this;
    }

    public function withAction(string $action): static
    {
        $this->action = $action;

        return $this;
    }

    public function withUserId(int $userId): static
    {
        $this->userId = $userId;

        return $this;
    }
}
