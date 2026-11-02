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
use Override;
use Throwable;

/**
 * The endpoint takes no parameters and reports no absolute count, so the
 * contacts are read with a single request rather than through requestAll().
 */
class AddressHierarchyContactBuilder extends Builder
{
    use NonFilterable;
    use NonOrderable;
    use NonSelectable;

    public function __construct(
        public int $addressId,
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
        return $this->requestFirstRecord($this->toRequest());
    }

    /**
     * Build the addressHierarchyContacts request this builder would send, without sending it.
     * Useful for reading the hierarchy contacts of many addresses in one batched API call.
     */
    #[Override]
    public function toRequest(): OnOfficeRequest
    {
        return new OnOfficeRequest(
            OnOfficeAction::Get,
            OnOfficeResourceType::AddressHierarchyContacts,
            $this->addressId,
            parameters: $this->customParameters,
        );
    }
}
