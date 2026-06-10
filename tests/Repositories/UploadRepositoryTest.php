<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Innobrain\OnOfficeAdapter\Dtos\OnOfficeRequest;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeAction;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeResourceType;
use Innobrain\OnOfficeAdapter\Facades\FileRepository;
use Innobrain\OnOfficeAdapter\Facades\Testing\RecordFactories\FileFactory;
use Innobrain\OnOfficeAdapter\Tests\Stubs\UploadFileResponse;

describe('fake responses', function () {
    test('get', function () {
        FileRepository::fake(FileRepository::response([
            FileRepository::page(recordFactories: [
                FileFactory::make()
                    ->data([
                        'tmpUploadId' => 'abc',
                    ]),
            ]),
        ]));

        FileRepository::query()->save('abc');

        FileRepository::assertSentCount(1);
    });

    test('linkUrl', function () {
        FileRepository::fake(FileRepository::response([
            FileRepository::page(recordFactories: [
                FileFactory::make()
                    ->data([
                        'url' => 'https://example.com/tour',
                    ]),
            ]),
        ]));

        $result = FileRepository::query()->linkUrl('https://example.com/tour', [
            'module' => 'estate',
            'relatedRecordId' => 2651,
            'Art' => 'Ogulo-Link',
        ]);

        expect($result['elements']['url'])->toBe('https://example.com/tour');

        FileRepository::assertSentCount(1);
        FileRepository::assertSent(fn (OnOfficeRequest $request) => $request->actionId === OnOfficeAction::Do
            && $request->resourceType === OnOfficeResourceType::UploadFile
            && $request->parameters['url'] === 'https://example.com/tour'
            && $request->parameters['module'] === 'estate'
        );
    });
});

describe('real responses', function () {
    test('get', function () {
        Http::preventStrayRequests();
        Http::fake([
            'https://api.onoffice.de/api/stable/api.php' => Http::sequence([
                UploadFileResponse::make(),
            ]),
        ]);

        FileRepository::record();

        FileRepository::query()->save('abc');

        FileRepository::assertSentCount(1);
    });
});
