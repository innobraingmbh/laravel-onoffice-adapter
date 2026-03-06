<?php

declare(strict_types=1);

namespace Innobrain\OnOfficeAdapter\Query\Concerns;

use Illuminate\Support\Arr;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeRelationType;

trait RelationTypes
{
    /**
     * @var array<int, int>
     */
    public array $parentIds = [];

    /**
     * @var array<int, int>
     */
    public array $childIds = [];

    public OnOfficeRelationType|string $relationType;

    /**
     * @param  int|array<int, int>  $parentIds
     */
    public function parentIds(int|array $parentIds): static
    {
        $this->parentIds = Arr::wrap($parentIds);

        return $this;
    }

    /**
     * @param  int|array<int, int>  $parentIds
     */
    public function addParentIds(int|array $parentIds): static
    {
        $this->parentIds = array_merge($this->parentIds, Arr::wrap($parentIds));

        return $this;
    }

    /**
     * @param  int|array<int, int>  $childIds
     */
    public function childIds(int|array $childIds): static
    {
        $this->childIds = Arr::wrap($childIds);

        return $this;
    }

    /**
     * @param  int|array<int, int>  $childIds
     */
    public function addChildIds(int|array $childIds): static
    {
        $this->childIds = array_merge($this->childIds, Arr::wrap($childIds));

        return $this;
    }

    public function relationType(OnOfficeRelationType|string $relationType): static
    {
        $this->relationType = $relationType;

        return $this;
    }
}
