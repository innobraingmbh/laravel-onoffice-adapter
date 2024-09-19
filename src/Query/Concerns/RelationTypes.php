<?php

declare(strict_types=1);

namespace Innobrain\OnOfficeAdapter\Query\Concerns;

use Illuminate\Support\Arr;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeRelationType;

trait RelationTypes
{
    public array $parentIds = [];

    public array $childIds = [];

    public OnOfficeRelationType $relationType;

    public function parentIds(int|array $parentIds): static
    {
        $this->parentIds = Arr::wrap($parentIds);

        return $this;
    }

    public function addParentIds(int|array $parentIds): static
    {
        $this->parentIds = array_merge($this->parentIds, Arr::wrap($parentIds));

        return $this;
    }

    public function childIds(int|array $childIds): static
    {
        $this->childIds = Arr::wrap($childIds);

        return $this;
    }

    public function addChildIds(int|array $childIds): static
    {
        $this->childIds = array_merge($this->childIds, Arr::wrap($childIds));

        return $this;
    }

    public function relationType(OnOfficeRelationType $relationType): static
    {
        $this->relationType = $relationType;

        return $this;
    }
}
