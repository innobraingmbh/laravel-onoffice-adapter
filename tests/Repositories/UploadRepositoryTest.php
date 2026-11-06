<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Innobrain\OnOfficeAdapter\Dtos\OnOfficeRequest;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeAction;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeResourceType;
use Innobrain\OnOfficeAdapter\Exceptions\IncompleteUploadException;
use Innobrain\OnOfficeAdapter\Facades\FileRepository;
use Innobrain\OnOfficeAdapter\Facades\Testing\RecordFactories\FileFactory;
use Innobrain\OnOfficeAdapter\Facades\Testing\RecordFactories\FileUploadFactory;
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

    test('save checks the size a fake reports', function () {
        FileRepository::fake(FileRepository::sequence(FileRepository::response([
            FileRepository::page(recordFactories: [
                FileUploadFactory::make()->tmpUploadId('abc')->filesize(4),
            ]),
        ]), times: 2));

        expect(fn () => FileRepository::query()->save(base64_encode('hello')))
            ->toThrow(IncompleteUploadException::class, 'onOffice stored 4 bytes of a 5 byte file.');

        FileRepository::assertSentCount(2);
    });
});

describe('real responses', function () {
    test('get', function () {
        Http::preventStrayRequests();
        Http::fake([
            'https://api.onoffice.de/api/stable/api.php' => Http::sequence([
                UploadFileResponse::make(filesize: 5),
            ]),
        ]);

        FileRepository::record();

        FileRepository::query()->save(base64_encode('hello'));

        FileRepository::assertSentCount(1);
    });

    test('appends every block after the first to the same temporary file', function () {
        Http::preventStrayRequests();
        Http::fake([
            'https://api.onoffice.de/api/stable/api.php' => Http::sequence([
                UploadFileResponse::make(tmpUploadId: 'tmp', filesize: 3),
                UploadFileResponse::make(tmpUploadId: 'tmp', filesize: 6),
            ]),
        ]);

        FileRepository::record();

        $tmpUploadId = FileRepository::query()
            ->uploadInBlocks(4)
            ->save(base64_encode('abcdef'));

        expect($tmpUploadId)->toBe('tmp')
            ->and(FileRepository::recorded()->map(fn (array $pair) => $pair[0]->parameters['tmpUploadId'] ?? null)->all())
            ->toBe([null, 'tmp']);
    });

    test('starts over from a new temporary file when onOffice stored a different size', function () {
        Http::preventStrayRequests();
        Http::fake([
            'https://api.onoffice.de/api/stable/api.php' => Http::sequence([
                UploadFileResponse::make(tmpUploadId: 'corrupt', filesize: 3),
                UploadFileResponse::make(tmpUploadId: 'corrupt', filesize: 9),
                UploadFileResponse::make(tmpUploadId: 'fresh', filesize: 3),
                UploadFileResponse::make(tmpUploadId: 'fresh', filesize: 6),
            ]),
        ]);

        FileRepository::record();

        $tmpUploadId = FileRepository::query()
            ->uploadInBlocks(4)
            ->save(base64_encode('abcdef'));

        expect($tmpUploadId)->toBe('fresh')
            ->and(FileRepository::recorded()->map(fn (array $pair) => $pair[0]->parameters['tmpUploadId'] ?? null)->all())
            ->toBe([null, 'corrupt', null, 'fresh']);
    });

    test('never links a file whose size is still wrong after starting over', function () {
        Http::preventStrayRequests();
        Http::fake([
            'https://api.onoffice.de/api/stable/api.php' => Http::sequence([
                UploadFileResponse::make(filesize: 3),
                UploadFileResponse::make(filesize: 9),
                UploadFileResponse::make(filesize: 3),
                UploadFileResponse::make(filesize: 3),
            ]),
        ]);

        FileRepository::record();

        expect(fn () => FileRepository::query()
            ->uploadInBlocks(4)
            ->saveAndLink(base64_encode('abcdef'), [
                'module' => 'estate',
                'relatedRecordId' => 409,
            ]))
            ->toThrow(IncompleteUploadException::class, 'onOffice stored 3 bytes of a 6 byte file.');

        FileRepository::assertSentCount(4);
        FileRepository::assertNotSent(fn (OnOfficeRequest $request) => isset($request->parameters['relatedRecordId']));
    });
});
