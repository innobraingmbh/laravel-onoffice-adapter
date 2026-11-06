<?php

declare(strict_types=1);

namespace Innobrain\OnOfficeAdapter\Query;

use Innobrain\OnOfficeAdapter\Dtos\OnOfficeRequest;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeAction;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeResourceType;
use Innobrain\OnOfficeAdapter\Exceptions\IncompleteUploadException;
use Innobrain\OnOfficeAdapter\Exceptions\OnOfficeException;
use Innobrain\OnOfficeAdapter\Query\Concerns\NonFilterable;
use Innobrain\OnOfficeAdapter\Query\Concerns\NonOrderable;
use Innobrain\OnOfficeAdapter\Query\Concerns\NonSelectable;
use Innobrain\OnOfficeAdapter\Query\Concerns\UploadInBlocks;
use Innobrain\OnOfficeAdapter\Services\OnOfficeResponsePath;
use Innobrain\OnOfficeAdapter\Services\OnOfficeService;
use Throwable;

class UploadBuilder extends Builder
{
    use NonFilterable;
    use NonOrderable;
    use NonSelectable;
    use UploadInBlocks;

    /**
     * File content as base64-encoded binary data.
     * Returns the temporary upload id.
     *
     * onOffice appends every block to the temporary file, so a block that
     * reaches it twice or not at all corrupts the file. When the size onOffice
     * reports after the last block is not the file's size, the upload starts
     * over once from a new temporary file before giving up.
     *
     * @throws Throwable<OnOfficeException>
     */
    public function save(string $fileContent): string
    {
        return retry(
            2,
            fn (): string => $this->uploadBlocks($fileContent),
            when: fn (Throwable $exception): bool => $exception instanceof IncompleteUploadException,
        );
    }

    /**
     * A response without a size, as most fakes return, counts as complete.
     *
     * @throws Throwable<OnOfficeException>
     */
    private function uploadBlocks(string $fileContent): string
    {
        $blocks = $this->uploadInBlocks > 0 ? str_split($fileContent, $this->uploadInBlocks) : [$fileContent];

        $tmpUploadId = null;
        $uploadedSize = null;

        foreach ($blocks as $block) {
            $continueData = [];
            if ($tmpUploadId) {
                $continueData = ['tmpUploadId' => $tmpUploadId];
            }

            $request = new OnOfficeRequest(
                OnOfficeAction::Do,
                OnOfficeResourceType::UploadFile,
                parameters: [
                    OnOfficeService::DATA => $block,
                    ...$continueData,
                    ...$this->customParameters,
                ],
            );

            $response = $this->requestApi($request);

            $tmpUploadId = $response->json(OnOfficeResponsePath::FIRST_RECORD_ELEMENTS_TMP_UPLOAD_ID);
            $uploadedSize = $response->json(OnOfficeResponsePath::FIRST_RECORD_ELEMENTS_FILESIZE);
        }

        $fileSize = strlen(base64_decode($fileContent));

        if ($uploadedSize !== null && (int) $uploadedSize !== $fileSize) {
            throw new IncompleteUploadException(sprintf(
                'onOffice stored %d bytes of a %d byte file.',
                $uploadedSize,
                $fileSize,
            ));
        }

        return $tmpUploadId;
    }

    /**
     * Returns the linked file data.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
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
            ->json(OnOfficeResponsePath::FIRST_RECORD);
    }

    /**
     * File content as base64-encoded binary data.
     * Returns the linked file data.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     *
     * @throws Throwable<OnOfficeException>
     */
    public function saveAndLink(string $fileContent, array $data = []): array
    {
        $tmpUploadId = $this->save($fileContent);

        return $this->link($tmpUploadId, $data);
    }

    /**
     * Link a URL (e.g. an Ogulo, film or object link) to a record.
     * URLs have no file content, so no upload is needed beforehand.
     * Returns the linked file data.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     *
     * @throws Throwable<OnOfficeException>
     */
    public function linkUrl(string $url, array $data = []): array
    {
        $data = array_replace($data, [
            'url' => $url,
        ]);

        $request = new OnOfficeRequest(
            OnOfficeAction::Do,
            OnOfficeResourceType::UploadFile,
            parameters: $data,
        );

        return $this->requestApi($request)
            ->json(OnOfficeResponsePath::FIRST_RECORD);
    }
}
