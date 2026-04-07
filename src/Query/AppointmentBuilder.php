<?php

declare(strict_types=1);

namespace Innobrain\OnOfficeAdapter\Query;

use Illuminate\Support\Collection;
use Innobrain\OnOfficeAdapter\Dtos\OnOfficeRequest;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeAction;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeResourceType;
use Innobrain\OnOfficeAdapter\Exceptions\OnOfficeException;
use Innobrain\OnOfficeAdapter\Services\OnOfficeService;

class AppointmentBuilder extends Builder
{
    public ?string $startDate = null;

    public ?string $endDate = null;

    /**
     * @throws OnOfficeException
     */
    public function get(): Collection
    {
        $request = new OnOfficeRequest(
            OnOfficeAction::Get,
            OnOfficeResourceType::AppointmentList,
            parameters: [
                'startDate' => $this->startDate,
                'endDate' => $this->endDate,
                OnOfficeService::DATA => $this->columns,
                OnOfficeService::FILTER => $this->getFilters(),
                ...$this->customParameters,
            ],
        );

        return $this->requestAll($request);
    }

    public function startDate(string $date): static
    {
        $this->startDate = $date;

        return $this;
    }

    public function endDate(string $date): static
    {
        $this->endDate = $date;

        return $this;
    }
}
