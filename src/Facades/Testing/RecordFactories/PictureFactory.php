<?php

declare(strict_types=1);

namespace Innobrain\OnOfficeAdapter\Facades\Testing\RecordFactories;

use Innobrain\OnOfficeAdapter\Facades\Testing\RecordFactories\Concerns\SuccessTrait;

class PictureFactory extends BaseFactory
{
    use SuccessTrait;

    public string $type = 'files';

    public array $elements = [
        'url' => 'https://via.placeholder.com/150',
        'title' => 'Test Picture',
        'text' => 'Test Picture Description',
        'originalname' => 'test.jpg',
        'modified' => 1540301235,
        'estateMainId' => 1,
    ];

    public function id(int $id): static
    {
        $this->id = $id;
        $this->elements['estateId'] = $id;

        return $this;
    }

    public function data(array $data): static
    {
        $this->elements = array_merge($this->elements, ['estateId' => $this->id, ...$data]);

        return $this;
    }
}
