<?php

declare(strict_types=1);

namespace Innobrain\OnOfficeAdapter\Query;

use Illuminate\Support\Collection;
use Innobrain\OnOfficeAdapter\Dtos\OnOfficeRequest;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeAction;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeError;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeResourceId;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeResourceType;
use Innobrain\OnOfficeAdapter\Exceptions\OnOfficeException;
use Innobrain\OnOfficeAdapter\Services\OnOfficeService;
use Throwable;

class AddressFileBuilder extends Builder
{
    public function __construct(
        public int $addressId,
    ) {
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
            OnOfficeResourceId::Address,
            parameters: [
                'addressid' => $this->addressId,
                OnOfficeService::LISTLIMIT => $this->limit,
                OnOfficeService::LISTOFFSET => $this->offset,
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
            OnOfficeResourceId::Address,
            parameters: [
                'addressid' => $this->addressId,
                ...$this->customParameters,
            ],
        );

        return $this->requestApi($request)
            ->json('response.results.0.data.records.0');
    }

    /**
     * @throws Throwable<OnOfficeException>
     */
    public function find(int $id): ?array
    {
        $request = new OnOfficeRequest(
            OnOfficeAction::Get,
            OnOfficeResourceType::File,
            OnOfficeResourceId::Address,
            parameters: [
                'addressid' => $this->addressId,
                'fileid' => $id,
                ...$this->customParameters,
            ],
        );

        $response = $this->requestApi($request);

        $result = $response->json('response.results.0.data.records.0');

        throw_unless($result, new OnOfficeException(
            OnOfficeError::File_Not_Found->toString(),
            OnOfficeError::File_Not_Found->value,
            isResponseError: true,
        ));

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
            OnOfficeResourceId::Address,
            parameters: [
                'addressid' => $this->addressId,
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
            'parentid' => $this->addressId,
            'relationtype' => 'address',
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
                'parentid' => $this->addressId,
                'relationtype' => 'address',
                ...$this->customParameters,
            ],
        );

        return $this->requestApi($request)
            ->json('response.results.0.data.records.0.elements.success') === 'success';
    }
}
