<?php

declare(strict_types=1);

namespace Innobrain\OnOfficeAdapter\Query\Concerns;

trait UploadInBlocks
{
    private int $uploadInBlocks = 0;

    /**
     * Uploads the file in blocks of the given size.
     *
     * @param  int  $blocks  The size of the blocks, measured in UTF-8 characters.
     */
    public function uploadInBlocks(int $blocks = 20480): self
    {
        $this->uploadInBlocks = $blocks;

        return $this;
    }
}
