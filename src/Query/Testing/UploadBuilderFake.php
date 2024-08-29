<?php

declare(strict_types=1);

namespace Katalam\OnOfficeAdapter\Query\Testing;

use Katalam\OnOfficeAdapter\Query\Concerns\UploadInBlocks;
use Throwable;

class UploadBuilderFake extends BaseFake
{
    use UploadInBlocks;

    /**
     * @throws Throwable
     */
    public function save(string $fileContent): string
    {
        $record = null;

        if ($this->uploadInBlocks > 0) {
            $blocks = str_split($fileContent, $this->uploadInBlocks);

            $iMax = count($blocks);
            for ($i = 0; $i < $iMax; $i++) {
                $record = $this->get()->first();
            }
        } else {
            $record = $this->get()->first();
        }

        return data_get($record, 'elements.tmpUploadId');
    }

    /**
     * @throws Throwable
     */
    public function link(string $tmpUploadId, array $data = []): array
    {
        return $this->get()->first();
    }

    /**
     * @throws Throwable
     */
    public function saveAndLink(string $fileContent, array $data = []): array
    {
        $tmpUploadId = $this->save($fileContent);

        return $this->link($tmpUploadId, $data);
    }
}
