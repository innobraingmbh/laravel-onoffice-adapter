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
use Innobrain\OnOfficeAdapter\Services\OnOfficeResponsePath;
use Innobrain\OnOfficeAdapter\Services\OnOfficeService;
use Throwable;

abstract class FileBuilder extends Builder
{
    /**
     * The onOffice resource the files are attached to (e.g. Estate, Address).
     */
    abstract protected function resourceId(): OnOfficeResourceId;

    /**
     * The request parameter name carrying the parent id (e.g. "estateid").
     */
    abstract protected function parentIdParameter(): string;

    /**
     * The relation type used when modifying or deleting file relations (e.g. "estate").
     */
    abstract protected function relationType(): string;

    /**
     * The id of the parent record the files belong to.
     */
    abstract protected function parentId(): int;

    /**
     * @throws OnOfficeException
     */
    public function get(): Collection
    {
        $request = $this->buildReadRequest([
            OnOfficeService::LISTLIMIT => $this->limit,
            OnOfficeService::LISTOFFSET => $this->offset,
        ]);

        return $this->requestAll($request);
    }

    /**
     * @throws Throwable<OnOfficeException>
     */
    public function first(): ?array
    {
        return $this->requestApi($this->buildReadRequest())
            ->json(OnOfficeResponsePath::FIRST_RECORD);
    }

    /**
     * @throws Throwable<OnOfficeException>
     */
    public function find(int $id): ?array
    {
        $request = $this->buildReadRequest([
            OnOfficeService::FILEID => $id,
        ]);

        $response = $this->requestApi($request);

        $result = $response->json(OnOfficeResponsePath::FIRST_RECORD);

        throw_unless($result,
            OnOfficeException::class,
            OnOfficeError::File_Not_Found->toString(),
            OnOfficeError::File_Not_Found->value, // @phpstan-ignore argument.type
            isResponseError: true); // @phpstan-ignore argument.type

        return $result;
    }

    /**
     * @throws OnOfficeException
     */
    public function each(callable $callback): void
    {
        $this->requestAllChunked($this->buildReadRequest(), $callback);
    }

    /**
     * Build the file read request shared by get(), first(), find() and each().
     *
     * @param  array<string, mixed>  $extraParameters
     */
    protected function buildReadRequest(array $extraParameters = []): OnOfficeRequest
    {
        return new OnOfficeRequest(
            OnOfficeAction::Get,
            OnOfficeResourceType::File,
            $this->resourceId(),
            parameters: [
                $this->parentIdParameter() => $this->parentId(),
                ...$extraParameters,
                ...$this->customParameters,
            ],
        );
    }

    /**
     * @throws Throwable<OnOfficeException>
     */
    public function modify(int $id): bool
    {
        $parameters = array_replace($this->modifies, [
            'fileId' => $id,
            OnOfficeService::PARENTID => $this->parentId(),
            OnOfficeService::RELATIONTYPE => $this->relationType(),
        ]);

        $request = new OnOfficeRequest(
            OnOfficeAction::Modify,
            OnOfficeResourceType::FileRelation,
            parameters: $parameters,
        );

        return $this->requestApi($request)
            ->json(OnOfficeResponsePath::FIRST_RECORD_ELEMENTS_SUCCESS) === 'success';
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
                OnOfficeService::PARENTID => $this->parentId(),
                OnOfficeService::RELATIONTYPE => $this->relationType(),
                ...$this->customParameters,
            ],
        );

        return $this->requestApi($request)
            ->json(OnOfficeResponsePath::FIRST_RECORD_ELEMENTS_SUCCESS) === 'success';
    }
}
