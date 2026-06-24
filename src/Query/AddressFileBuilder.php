<?php

declare(strict_types=1);

namespace Innobrain\OnOfficeAdapter\Query;

use Innobrain\OnOfficeAdapter\Enums\OnOfficeResourceId;
use Innobrain\OnOfficeAdapter\Services\OnOfficeService;

class AddressFileBuilder extends FileBuilder
{
    public function __construct(
        public int $addressId,
    ) {
        parent::__construct();
    }

    protected function resourceId(): OnOfficeResourceId
    {
        return OnOfficeResourceId::Address;
    }

    protected function parentIdParameter(): string
    {
        return OnOfficeService::ADDRESSID;
    }

    protected function relationType(): string
    {
        return 'address';
    }

    protected function parentId(): int
    {
        return $this->addressId;
    }
}
