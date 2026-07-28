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
use Innobrain\OnOfficeAdapter\Services\OnOfficeResponsePath;
use Innobrain\OnOfficeAdapter\Services\OnOfficeService;
use Override;
use Throwable;

/**
 * An estate has a handful of language variants at most, so this endpoint is read
 * with a single request rather than through requestAll(): no listlimit is sent and
 * the API's default page size covers every variant. There is no each() for the same
 * reason — there is nothing to chunk.
 */
class EstateLanguageBuilder extends Builder
{
    use NonFilterable;
    use NonOrderable;
    use NonSelectable;

    public function __construct(
        public int $estateId,
    ) {
        parent::__construct();
    }

    /**
     * @throws OnOfficeException
     */
    public function get(): Collection
    {
        /** @var array<int, array<string, mixed>> $records */
        $records = $this->requestApi($this->toRequest())
            ->json(OnOfficeResponsePath::RECORDS, []);

        return collect($records);
    }

    /**
     * @throws Throwable<OnOfficeException>
     */
    public function first(): ?array
    {
        return $this->requestApi($this->toRequest())
            ->json(OnOfficeResponsePath::FIRST_RECORD);
    }

    /**
     * Build the estateLanguage request this builder would send, without sending it.
     * Useful for reading the language variants of many estates in one batched API call.
     */
    #[Override]
    public function toRequest(): OnOfficeRequest
    {
        return new OnOfficeRequest(
            OnOfficeAction::Get,
            OnOfficeResourceType::EstateLanguage,
            parameters: [
                OnOfficeService::ESTATEID => $this->estateId,
                ...$this->customParameters,
            ],
        );
    }
}
