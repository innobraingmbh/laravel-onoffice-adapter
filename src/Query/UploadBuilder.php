<?php

declare(strict_types=1);

namespace Katalam\OnOfficeAdapter\Query;

use Illuminate\Support\Collection;
use Katalam\OnOfficeAdapter\Enums\OnOfficeAction;
use Katalam\OnOfficeAdapter\Enums\OnOfficeResourceType;
use Katalam\OnOfficeAdapter\Exceptions\OnOfficeException;
use Katalam\OnOfficeAdapter\Query\Concerns\NonFilterable;
use Katalam\OnOfficeAdapter\Query\Concerns\NonOrderable;
use Katalam\OnOfficeAdapter\Query\Concerns\NonSelectable;
use Katalam\OnOfficeAdapter\Services\OnOfficeService;

class UploadBuilder extends Builder
{
    use NonFilterable;
    use NonFilterable;
    use NonOrderable;
    use NonSelectable;

    public function __construct(
        private readonly OnOfficeService $onOfficeService,
    ) {
    }

    /**
     * @throws OnOfficeException
     */
    public function get(): Collection
    {
        throw new OnOfficeException('Method not implemented');
    }

    /**
     * @throws OnOfficeException
     */
    public function first(): array
    {
        throw new OnOfficeException('Method not implemented');
    }

    /**
     * @throws OnOfficeException
     */
    public function find(int $id): array
    {
        throw new OnOfficeException('Method not implemented');
    }

    /**
     * @throws OnOfficeException
     */
    public function each(callable $callback): void
    {
        throw new OnOfficeException('Method not implemented');
    }

    /**
     * @throws OnOfficeException
     */
    public function modify(int $id): bool
    {
        throw new OnOfficeException('Not implemented');
    }

    /**
     * File content as base64-encoded binary data.
     * Returns the temporary upload id.
     *
     * @throws OnOfficeException
     */
    public function save(string $fileContent): string
    {
        $response = $this->onOfficeService->requestApi(
            OnOfficeAction::Do,
            OnOfficeResourceType::UploadFile,
            parameters: [
                OnOfficeService::DATA => $fileContent,
            ]
        );

        return $response->json('response.results.0.data.records.0.elements.tmpUploadId');
    }

    /**
     * Returns the linked file data.
     *
     * @throws OnOfficeException
     */
    public function link(string $tmpUploadId, array $data = []): array
    {
        $data = array_replace($data, [
            'tmpUploadId' => $tmpUploadId,
        ]);

        $response = $this->onOfficeService->requestApi(
            OnOfficeAction::Do,
            OnOfficeResourceType::UploadFile,
            parameters: $data,
        );

        return $response->json('response.results.0.data.records.0');
    }

    /**
     * File content as base64-encoded binary data.
     * Returns the linked file data.
     *
     * @throws OnOfficeException
     */
    public function saveAndLink(string $fileContent, array $data = []): array
    {
        $tmpUploadId = $this->save($fileContent);

        return $this->link($tmpUploadId, $data);
    }
}
