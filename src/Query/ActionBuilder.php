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
use Throwable;

class ActionBuilder extends Builder
{
    use NonFilterable;
    use NonOrderable;

    /**
     * @throws OnOfficeException
     * @throws Throwable
     */
    public function get(bool $concurrently = false): Collection
    {
        $request = new OnOfficeRequest(
            OnOfficeAction::Get,
            OnOfficeResourceType::ActionTypes,
            parameters: [
                ...$this->customParameters,
            ]
        );

        if ($concurrently) {
            return $this->requestConcurrently($request);
        }

        return $this->requestAll($request);
    }
}
