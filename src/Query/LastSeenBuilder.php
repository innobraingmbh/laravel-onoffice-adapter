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

class LastSeenBuilder extends Builder
{
    use Paginate;

    public string $module = '';

    public int $userId = -1;

    protected function buildReadRequest(): OnOfficeRequest
    {
        $parameters = [
            OnOfficeService::MODULE => $this->module,
            OnOfficeService::FILTER => $this->getFilters(),
            ...$this->customParameters,
        ];

        if ($this->userId > 0) {
            $parameters['user'] = $this->userId;
        }

        return new OnOfficeRequest(
            OnOfficeAction::Read,
            OnOfficeResourceType::RecordsLastSeen,
            parameters: $parameters,
        );
    }

    /**
     * @throws Throwable<OnOfficeException>
     */
    public function find(int $id): ?array
    {
        throw new OnOfficeException('Find by ID is not supported for LastSeen records.');
    }

    /**
     * Count is not supported for LastSeen records.
     *
     * @throws OnOfficeException
     */
    public function count(): int
    {
        throw new OnOfficeException('Count is not supported for LastSeen records.');
    }

    public function withModule(string $module): static
    {
        $this->module = $module;

        return $this;
    }

    public function withUserId(int $userId): static
    {
        $this->userId = $userId;

        return $this;
    }
}
