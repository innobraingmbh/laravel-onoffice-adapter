<?php

declare(strict_types=1);

namespace Innobrain\OnOfficeAdapter\Dtos;

use Illuminate\Support\Collection;

readonly class OnOfficeResponse
{
    /**
     * @param  Collection<int, OnOfficeResponsePage>  $pages
     */
    public function __construct(
        protected Collection $pages,
    ) {}

    /**
     * A copy with its own page queue. The pages are shifted off as the fake
     * serves them, so a plain clone, which shares the queue, would be spent
     * by the time it is reached.
     */
    public function copy(): self
    {
        return new self(collect($this->pages->all()));
    }

    public function shift(): OnOfficeResponsePage
    {
        /** @var OnOfficeResponsePage */
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
