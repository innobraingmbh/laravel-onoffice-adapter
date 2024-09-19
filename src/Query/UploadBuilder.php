<?php

declare(strict_types=1);

namespace Innobrain\OnOfficeAdapter\Query;

use Innobrain\OnOfficeAdapter\Dtos\OnOfficeRequest;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeAction;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeResourceType;
use Innobrain\OnOfficeAdapter\Exceptions\OnOfficeException;
use Innobrain\OnOfficeAdapter\Query\Concerns\NonFilterable;
use Innobrain\OnOfficeAdapter\Query\Concerns\NonOrderable;
use Innobrain\OnOfficeAdapter\Query\Concerns\NonSelectable;
use Innobrain\OnOfficeAdapter\Query\Concerns\UploadInBlocks;
use Innobrain\OnOfficeAdapter\Services\OnOfficeService;
use Throwable;

class UploadBuilder extends Builder
{
    use NonFilterable;
    use NonFilterable;
    use NonOrderable;
    use NonSelectable;
    use UploadInBlocks;

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
