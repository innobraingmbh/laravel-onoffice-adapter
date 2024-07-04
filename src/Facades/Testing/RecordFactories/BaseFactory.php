<?php

declare(strict_types=1);

namespace Katalam\OnOfficeAdapter\Facades\Testing\RecordFactories;

abstract class BaseFactory
{
    public int|string $id = 0;

    public string $type = '';

    public array $elements = [];

    /**
     * Final to solve Unsafe usage of new static()
     */
    final public function __construct() {}

    public function id(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function type(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function set(string $key, mixed $value): static
    {
        $this->elements[$key] = $value;

        return $this;
    }

    public function data(array $data): static
    {
        $this->elements = array_merge($this->elements, $data);

        return $this;
    }

    public static function make(): static
    {
        return new static();
    }

    public static function times(int $times = 1): array
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

    public function __call(string $name, array $arguments): static
    {
        if (str_starts_with($name, 'set')) {
            $key = lcfirst(substr($name, 3));
            $value = data_get($arguments, 0);

            return $this->set($key, $value);
        }

        return $this;
    }
}
