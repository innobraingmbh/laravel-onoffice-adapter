<?php

declare(strict_types=1);

namespace Katalam\OnOfficeAdapter\Query\Concerns;

use Illuminate\Support\Arr;

trait RecordIds
{
    public array $recordIds = [];

    public function recordIds(array|int $recordIds): static
    {
        $this->recordIds = Arr::wrap($recordIds);

        return $this;
    }

    public function addRecordIds(int|array $recordId): static
    {
        $this->recordIds = array_merge($this->recordIds, Arr::wrap($recordId));

        return $this;
    }
}
