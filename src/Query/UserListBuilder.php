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
 * The endpoint reports no absolute count, so the list is read with a single
 * request rather than through requestAll().
 */
class UserListBuilder extends Builder
{
    use NonFilterable;
    use NonOrderable;
    use NonSelectable;

    /**
     * @var array<string, string|int>
     */
    protected array $userFilter = [];

    /**
     * onOffice ignores a groupId that comes without the byGroupId user filter,
     * so both are always sent together.
     */
    public function group(int $groupId): static
    {
        $this->userFilter = [
            'userfilter' => 'byGroupId',
            'groupId' => $groupId,
        ];

        return $this;
    }

    public function onlyOwn(): static
    {
        $this->userFilter = [
            'userfilter' => 'onlyOwn',
        ];

        return $this;
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
     * Build the users request this builder would send, without sending it.
     * Useful for reading the user lists of many groups in one batched API call.
     */
    #[Override]
    public function toRequest(): OnOfficeRequest
    {
        return new OnOfficeRequest(
            OnOfficeAction::Get,
            OnOfficeResourceType::Users,
            parameters: [
                ...$this->userFilter,
                ...$this->customParameters,
            ],
        );
    }
}
