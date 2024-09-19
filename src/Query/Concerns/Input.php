<?php

declare(strict_types=1);

namespace Innobrain\OnOfficeAdapter\Query\Concerns;

trait Input
{
    private string $input = '';

    public function setInput(string $input): self
    {
        $this->input = $input;

        return $this;
    }
}
