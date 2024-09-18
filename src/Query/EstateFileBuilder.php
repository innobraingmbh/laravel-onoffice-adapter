<?php

declare(strict_types=1);

namespace Katalam\OnOfficeAdapter\Query;

use Illuminate\Support\Collection;
use Katalam\OnOfficeAdapter\Dtos\OnOfficeRequest;
use Katalam\OnOfficeAdapter\Enums\OnOfficeAction;
use Katalam\OnOfficeAdapter\Enums\OnOfficeResourceId;
use Katalam\OnOfficeAdapter\Enums\OnOfficeResourceType;
use Katalam\OnOfficeAdapter\Exceptions\OnOfficeException;
use Katalam\OnOfficeAdapter\Services\OnOfficeService;
use Throwable;

class EstateFileBuilder extends Builder
{
    public int $estateId;

    public function __construct(
        int $estateId,
    ) {
        $this->estateId = $estateId;

        parent::__construct();
    }

    /**
     * @throws OnOfficeException
     */
    public function get(): Collection
    {
        $request = new OnOfficeRequest(
            OnOfficeAction::Get,
            OnOfficeResourceType::File,
            OnOfficeResourceId::Estate,
            parameters: [
                'estateid' => $this->estateId,
                ...$this->customParameters,
            ],
        );

        return $this->requestAll($request);
    }

    /**
     * @throws Throwable<OnOfficeException>
     */
    public function first(): ?array
    {
        $request = new OnOfficeRequest(
            OnOfficeAction::Get,
            OnOfficeResourceType::File,
            OnOfficeResourceId::Estate,
            parameters: [
                'estateid' => $this->estateId,
                ...$this->customParameters,
            ],
        );

        return $this->requestApi($request)
            ->json('response.results.0.data.records.0');
    }

    /**
     * @throws Throwable<OnOfficeException>
     */
    public function find(int $id): array
    {
        $request = new OnOfficeRequest(
            OnOfficeAction::Get,
            OnOfficeResourceType::File,
            OnOfficeResourceId::Estate,
            parameters: [
                'estateid' => $this->estateId,
                'fileid' => $id,
                ...$this->customParameters,
            ],
        );

        $response = $this->requestApi($request);

        $result = $response->json('response.results.0.data.records.0');

        if (! $result) {
            throw new OnOfficeException('File not found');
        }

        return $result;
    }

    /**
     * @throws OnOfficeException
     */
    public function each(callable $callback): void
    {
        $request = new OnOfficeRequest(
            OnOfficeAction::Get,
            OnOfficeResourceType::File,
            OnOfficeResourceId::Estate,
            parameters: [
                'estateid' => $this->estateId,
                ...$this->customParameters,
            ],
        );

        $this->requestAllChunked($request, $callback);
    }

    /**
     * @throws Throwable<OnOfficeException>
     */
    public function modify(int $id): bool
    {
        $parameters = array_replace($this->modifies, [
            'fileId' => $id,
            'parentid' => $this->estateId,
            'relationtype' => 'estate',
        ]);

        $request = new OnOfficeRequest(
            OnOfficeAction::Modify,
            OnOfficeResourceType::FileRelation,
            parameters: $parameters,
        );

        return $this->requestApi($request)
            ->json('response.results.0.data.records.0.elements.success') === 'success';
    }

    /**
     * @throws Throwable<OnOfficeException>
     */
    public function delete(int $id): bool
    {
        $request = new OnOfficeRequest(
            OnOfficeAction::Delete,
            OnOfficeResourceType::FileRelation,
            parameters: [
                'fileId' => $id,
                'parentid' => $this->estateId,
                'relationtype' => 'estate',
                ...$this->customParameters,
            ],
        );

        return $this->requestApi($request)
                ->json('response.results.0.data.records.0.elements.success') === 'success';
    }
}
