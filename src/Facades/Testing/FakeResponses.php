<?php

declare(strict_types=1);

namespace Katalam\OnOfficeAdapter\Facades\Testing;

use Illuminate\Support\Collection;

trait FakeResponses
{
    public Collection $fakeResponses;

    public function __construct(array ...$fakeResponses)
    {
        $this->fakeResponses = collect($fakeResponses);
    }
}
