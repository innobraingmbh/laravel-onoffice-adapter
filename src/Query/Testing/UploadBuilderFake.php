<?php

declare(strict_types=1);

namespace Katalam\OnOfficeAdapter\Query\Testing;

use Throwable;

class UploadBuilderFake extends BaseFake
{
    /**
     * @throws Throwable
     */
    public function save(string $fileContent): string
    {
        $record = $this->get()->first();

        return data_get($record, 'elements.tmpUploadId');
    }

    /**
     * @throws Throwable
     */
    public function link(string $tmpUploadId, array $data = []): bool
    {
        $record = $this->get()->first();

        return data_get($record, 'elements.success') === 'success';
    }

    /**
     * @throws Throwable
     */
    public function saveAndLink(string $fileContent, array $data = []): bool
    {
        $tmpUploadId = $this->save($fileContent);

        return $this->link($tmpUploadId, $data);
    }
}
