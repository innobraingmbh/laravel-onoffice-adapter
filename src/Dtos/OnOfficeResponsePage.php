<?php

declare(strict_types=1);

namespace Innobrain\OnOfficeAdapter\Dtos;

use Illuminate\Support\Collection;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeAction;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeResourceId;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeResourceType;
use Innobrain\OnOfficeAdapter\Facades\Testing\RecordFactories\BaseFactory;

readonly class OnOfficeResponsePage
{
    public function __construct(
        private OnOfficeAction $actionId,
        private OnOfficeResourceType|string $resourceType,
        private Collection $recordFactories = new Collection,
        private int $status = 200,
        private int $errorCode = 0,
        private string $message = 'OK',
        private OnOfficeResourceId|string|int $resourceId = OnOfficeResourceId::None,
        private bool $cacheable = true,
        private string|int $identifier = '',
        private int $countAbsolute = -1,
        private int $errorCodeResult = 0,
        private string $messageResult = 'OK',
    ) {}

    public function toResponse(): array
    {
        $records = $this->recordFactories
            ->map(fn (BaseFactory $recordFactory) => $recordFactory->toArray())
            ->all();

        return [
            'status' => [
                'code' => $this->status,
                'errorcode' => $this->errorCode,
                'message' => $this->message,
            ],
            'response' => [
                'results' => [
                    [
                        'actionid' => $this->actionId->value,
                        'resourceid' => (string) ($this->resourceId instanceof OnOfficeResourceId
                            ? $this->resourceId->value
                            : $this->resourceId),
                        'resourcetype' => (string) ($this->resourceType instanceof OnOfficeResourceType
                            ? $this->resourceType->value
                            : $this->resourceType),
                        'cacheable' => $this->cacheable,
                        'identifier' => $this->identifier,
                        'data' => [
                            'meta' => [
                                'cntabsolute' => $this->countAbsolute === -1 ? count($records) : $this->countAbsolute,
                            ],
                            'records' => $records,
                        ],
                        'status' => [
                            'errorcode' => $this->errorCodeResult,
                            'message' => $this->messageResult,
                        ],
                    ],
                ],
            ],
        ];
    }
}
