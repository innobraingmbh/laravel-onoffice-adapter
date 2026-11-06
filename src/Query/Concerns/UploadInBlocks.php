<?php

declare(strict_types=1);

namespace Innobrain\OnOfficeAdapter\Query\Concerns;

trait UploadInBlocks
{
    private int $uploadInBlocks = 0;

    /**
     * Uploads the file in blocks of the given size.
     *
     * @param  int  $blocks  The size of the blocks, measured in base64 characters.
     */
    public function uploadInBlocks(int $blocks = 1048576): self
    {
        $this->uploadInBlocks = $blocks;

        return $this;
    }
}
