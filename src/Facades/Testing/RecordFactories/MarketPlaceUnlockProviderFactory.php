<?php

namespace Katalam\OnOfficeAdapter\Facades\Testing\RecordFactories;

class MarketPlaceUnlockProviderFactory extends BaseFactory
{
    public function id(int $id): self
    {
        return $this;
    }

    public function type(string $type): self
    {
        return $this;
    }

    public function elements(): self
    {
        return $this;
    }

    public function success(bool $success): self
    {
        $this->elements['success'] = $success ? 'success' : 'error';

        return $this;
    }

    public function ok(): self
    {
        $this->elements['success'] = 'success';

        return $this;
    }

    public function error(): self
    {
        $this->elements['success'] = 'error';

        return $this;
    }
}
