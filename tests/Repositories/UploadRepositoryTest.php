<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Katalam\OnOfficeAdapter\Facades\FileRepository;
use Katalam\OnOfficeAdapter\Facades\Testing\RecordFactories\FileFactory;
use Katalam\OnOfficeAdapter\Tests\Stubs\UploadFileResponse;

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
});

describe('real responses', function () {
    test('get', function () {
        Http::preventStrayRequests();
        Http::fake([
            'https://api.onoffice.de/api/stable/api.php/' => Http::sequence([
                UploadFileResponse::make(),
            ]),
        ]);

        FileRepository::record();

        FileRepository::query()->save('abc');

        FileRepository::assertSentCount(1);
    });
});
