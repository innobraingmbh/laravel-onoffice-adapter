<?php

declare(strict_types=1);

namespace Katalam\OnOfficeAdapter\Query;

use Illuminate\Support\Collection;
use Katalam\OnOfficeAdapter\Dtos\OnOfficeRequest;
use Katalam\OnOfficeAdapter\Enums\OnOfficeAction;
use Katalam\OnOfficeAdapter\Enums\OnOfficeResourceType;
use Katalam\OnOfficeAdapter\Exceptions\OnOfficeException;
use Katalam\OnOfficeAdapter\Query\Concerns\NonFilterable;
use Katalam\OnOfficeAdapter\Query\Concerns\NonOrderable;
use Katalam\OnOfficeAdapter\Query\Concerns\NonSelectable;
use Katalam\OnOfficeAdapter\Query\Concerns\UploadInBlocks;
use Katalam\OnOfficeAdapter\Services\OnOfficeService;
use Throwable;

class UploadBuilder extends Builder
{
    use NonFilterable;
    use NonFilterable;
    use NonOrderable;
    use NonSelectable;
    use UploadInBlocks;

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
    public function first(): ?array
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
     * @throws Throwable<OnOfficeException>
     */
    public function save(string $fileContent): string
    {
        if ($this->uploadInBlocks > 0) {
            $fileContentSplit = str_split($fileContent, $this->uploadInBlocks);
        } else {
            $fileContentSplit = [$fileContent];
        }

        $tmpUploadId = null;

        collect($fileContentSplit)
            ->each(function (string $fileContent) use (&$tmpUploadId) {
                $continueData = [];
                if ($tmpUploadId) {
                    $continueData = ['tmpUploadId' => $tmpUploadId];
                }

                $request = new OnOfficeRequest(
                    OnOfficeAction::Do,
                    OnOfficeResourceType::UploadFile,
                    parameters: [
                        OnOfficeService::DATA => $fileContent,
                        ...$continueData,
                        ...$this->customParameters,
                    ],
                );

                $tmpUploadId = $this->requestApi($request)
                    ->json('response.results.0.data.records.0.elements.tmpUploadId');
            });

        return $tmpUploadId;
    }

    /**
     * Returns the linked file data.
     *
     * @throws Throwable<OnOfficeException>
     */
    public function link(string $tmpUploadId, array $data = []): array
    {
        $data = array_replace($data, [
            'tmpUploadId' => $tmpUploadId,
        ]);

        $request = new OnOfficeRequest(
            OnOfficeAction::Do,
            OnOfficeResourceType::UploadFile,
            parameters: $data,
        );

        return $this->requestApi($request)
            ->json('response.results.0.data.records.0');
    }

    /**
     * File content as base64-encoded binary data.
     * Returns the linked file data.
     *
     * @throws Throwable<OnOfficeException>
     */
    public function saveAndLink(string $fileContent, array $data = []): array
    {
        $tmpUploadId = $this->save($fileContent);

        return $this->link($tmpUploadId, $data);
    }
}
