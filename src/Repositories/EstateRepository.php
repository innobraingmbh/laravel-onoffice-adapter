<?php

namespace Katalam\OnOfficeAdapter\Repositories;

use Illuminate\Support\Collection;
use Katalam\OnOfficeAdapter\Enums\OnOfficeAction;
use Katalam\OnOfficeAdapter\Enums\OnOfficeResourceType;
use Katalam\OnOfficeAdapter\Exceptions\OnOfficeException;
use Katalam\OnOfficeAdapter\Services\OnOfficeService;

readonly class EstateRepository
{
    public function __construct(
        private OnOfficeService $onOfficeService,
    ) {
    }

    /**
     * Requests all estates from the onOffice API with a paginated request.
     */
    public function all(): Collection
    {
        return $this->onOfficeService->requestAll(/**
         * @throws OnOfficeException
         */ function (int $pageSize, int $offset) {
            return $this->onOfficeService->requestApi(
                OnOfficeAction::Read,
                OnOfficeResourceType::Estate,
                parameters: [
                    OnOfficeService::DATA => ['Id'],
                    OnOfficeService::LISTLIMIT => $pageSize,
                    OnOfficeService::LISTOFFSET => $offset,
                ]
            );
        });
    }
}
