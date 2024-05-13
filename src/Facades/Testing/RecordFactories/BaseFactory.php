<?php

namespace Katalam\OnOfficeAdapter\Facades\Testing\RecordFactories;

class BaseFactory
{
    public int $id = 0;

    public string $type = '';

    public array $elements = [];

    public function id(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function type(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function set(string $key, mixed $value): self
    {
        $this->elements[$key] = $value;

        return $this;
    }

    public static function make(): self
    {
        return new static();
    }

    public static function times(int $times): array
    {
        return array_fill(0, $times, static::make());
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'elements' => $this->elements,
        ];
    }
}
