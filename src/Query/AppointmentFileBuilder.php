<?php

declare(strict_types=1);

namespace Innobrain\OnOfficeAdapter\Query;

use Illuminate\Support\Collection;
use Innobrain\OnOfficeAdapter\Dtos\OnOfficeRequest;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeAction;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeResourceType;
use Innobrain\OnOfficeAdapter\Exceptions\OnOfficeException;
use Innobrain\OnOfficeAdapter\Query\Concerns\NonFilterable;
use Innobrain\OnOfficeAdapter\Query\Concerns\NonOrderable;
use Innobrain\OnOfficeAdapter\Query\Concerns\NonSelectable;
use Innobrain\OnOfficeAdapter\Services\OnOfficeService;

class AppointmentFileBuilder extends Builder
{
    use NonFilterable;
    use NonOrderable;
    use NonSelectable;

    public function __construct(
        public int $appointmentId,
    ) {
        parent::__construct();
    }

    /**
     * @throws OnOfficeException
     */
    public function get(): Collection
    {
        $request = new OnOfficeRequest(
            OnOfficeAction::Get,
            OnOfficeResourceType::File,
            'appointment',
            parameters: [
                'appointmentid' => $this->appointmentId,
                OnOfficeService::LISTLIMIT => $this->limit,
                OnOfficeService::LISTOFFSET => $this->offset,
                ...$this->customParameters,
            ],
        );

        return $this->requestAll($request);
    }
}
