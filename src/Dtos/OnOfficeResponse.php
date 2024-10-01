<?php

declare(strict_types=1);

namespace Innobrain\OnOfficeAdapter\Dtos;

use Illuminate\Support\Collection;

readonly class OnOfficeResponse
{
    public function __construct(
        protected Collection $pages,
    ) {}

    public function shift(): OnOfficeResponsePage
    {
        return $this->pages->shift();
    }

    public function isEmpty(): bool
    {
        return $this->pages->isEmpty();
    }

    public function count(): int
    {
        return $this->pages->count();
    }
}
