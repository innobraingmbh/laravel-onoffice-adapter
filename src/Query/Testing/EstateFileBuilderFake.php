<?php

namespace Katalam\OnOfficeAdapter\Query\Testing;

use Throwable;

class EstateFileBuilderFake extends BaseFake
{
    /**
     * @throws Throwable
     */
    public function delete(int $id): bool
    {
        $record = $this->get()->first();

        return data_get($record, 'elements.success') === 'success';
    }
}
