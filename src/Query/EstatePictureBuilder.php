<?php

declare(strict_types=1);

namespace Innobrain\OnOfficeAdapter\Query;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Innobrain\OnOfficeAdapter\Dtos\OnOfficeRequest;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeAction;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeResourceId;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeResourceType;
use Innobrain\OnOfficeAdapter\Exceptions\OnOfficeException;
use Throwable;

class EstatePictureBuilder extends Builder
{
    private const DEFAULT_CATEGORIES = [
        'Titelbild',
        'Foto',
        'Foto_gross',
        'Grundriss',
        'Lageplan',
        'Epass_Skala',
        'Panorama',
        'Link',
        'Film-Link',
        'Ogulo-Link',
        'Objekt-Link',
        'Expose',
    ];

    public array $estateIds;

    public array $categories = [];

    public function __construct(
        int|array $estateId,
    ) {
        $this->estateIds = Arr::wrap($estateId);

        parent::__construct();
    }

    /**
     * @throws OnOfficeException
     * @throws Throwable
     */
    public function get(bool $concurrently = false): Collection
    {
        if ($this->categories === []) {
            $this->categories = self::DEFAULT_CATEGORIES;
        }

        $request = new OnOfficeRequest(
            OnOfficeAction::Get,
            OnOfficeResourceType::EstatePictures,
            OnOfficeResourceId::None,
            parameters: [
                'estateids' => $this->estateIds,
                'categories' => $this->categories,
                ...$this->customParameters,
            ],
        );

        if ($concurrently) {
            return $this->requestConcurrently($request);
        }

        return $this->requestAll($request);
    }

    /**
     * @throws OnOfficeException
     */
    public function each(callable $callback): void
    {
        if ($this->categories === []) {
            $this->categories = self::DEFAULT_CATEGORIES;
        }

        $request = new OnOfficeRequest(
            OnOfficeAction::Get,
            OnOfficeResourceType::EstatePictures,
            OnOfficeResourceId::None,
            parameters: [
                'estateids' => $this->estateIds,
                'categories' => $this->categories,
                ...$this->customParameters,
            ],
        );

        $this->requestAllChunked($request, $callback);
    }

    public function category(string|array $category): self
    {
        $this->categories = Arr::wrap($category);

        return $this;
    }

    public function size(int $width, int $height): self
    {
        $this->customParameters['size'] = "{$width}x{$height}";

        return $this;
    }

    /**
     * @param  string  $language  ISO 639-1 language code
     * @return $this
     */
    public function language(string $language): self
    {
        $this->customParameters['language'] = $language;

        return $this;
    }
}
