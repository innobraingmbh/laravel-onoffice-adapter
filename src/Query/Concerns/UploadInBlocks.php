<?php

declare(strict_types=1);

namespace Innobrain\OnOfficeAdapter\Query\Concerns;

trait UploadInBlocks
{
    private int $uploadInBlocks = 0;

    public function uploadInBlocks(int $blocks = 5120): self
    {
        $this->uploadInBlocks = $blocks;

        return $this;
    }
}
