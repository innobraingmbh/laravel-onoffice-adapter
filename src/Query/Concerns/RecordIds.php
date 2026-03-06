<?php

declare(strict_types=1);

namespace Innobrain\OnOfficeAdapter\Query\Concerns;

use Illuminate\Support\Arr;

trait RecordIds
{
    /**
     * @var array<int, int>
     */
    public array $recordIds = [];

    /**
     * @param  array<int, int>|int  $recordIds
     */
    public function recordIds(array|int $recordIds): static
    {
        $this->recordIds = Arr::wrap($recordIds);

        return $this;
    }

    /**
     * @param  int|array<int, int>  $recordId
     */
    public function addRecordIds(int|array $recordId): static
    {
        $this->recordIds = array_merge($this->recordIds, Arr::wrap($recordId));

        return $this;
    }
}
