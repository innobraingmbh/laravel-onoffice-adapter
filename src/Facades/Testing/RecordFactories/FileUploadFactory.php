<?php

namespace Katalam\OnOfficeAdapter\Facades\Testing\RecordFactories;

use Illuminate\Support\Str;

class FileUploadFactory extends BaseFactory
{
    public function id(int $id): static
    {
        return $this;
    }

    public function type(string $type): static
    {
        return $this;
    }

    public function elements(): static
    {
        return $this;
    }

    public function tmpUploadId(string $tmpUploadId = ''): static
    {
        if ($tmpUploadId === '') {
            $tmpUploadId = Str::uuid()->toString();
        }

        $this->elements['tmpUploadId'] = $tmpUploadId;

        return $this;
    }

    public function success(bool $success): static
    {
        $this->elements['success'] = $success ? 'success' : 'error';

        return $this;
    }

    public function ok(): static
    {
        $this->elements['success'] = 'success';

        return $this;
    }

    public function error(): static
    {
        $this->elements['success'] = 'error';

        return $this;
    }
}
