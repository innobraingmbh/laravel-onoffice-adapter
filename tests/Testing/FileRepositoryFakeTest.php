<?php

declare(strict_types=1);

use Katalam\OnOfficeAdapter\Facades\FileRepository;
use Katalam\OnOfficeAdapter\Facades\Testing\FileRepositoryFake;
use Katalam\OnOfficeAdapter\Facades\Testing\RecordFactories\FileUploadFactory;

it('can be faked', function () {
    $fake = FileRepository::fake();

    expect($fake)->toBeInstanceOf(FileRepositoryFake::class);
});

describe('save', function () {
    it('can get a fake response', function () {
        FileRepository::fake([
            [
                FileUploadFactory::make()
                    ->tmpUploadId('a17ebec0-48f9-44cc-8629-f49ccc68f2d2'),
            ],
        ]);

        $tmpUploadId = FileRepository::upload()->save(base64_encode('test'));

        expect($tmpUploadId)->toBe('a17ebec0-48f9-44cc-8629-f49ccc68f2d2');
    });

    it('can get multiple fake responses', function () {
        FileRepository::fake([
            [
                FileUploadFactory::make()
                    ->tmpUploadId('a17ebec0-48f9-44cc-8629-f49ccc68f2d2'),
            ],
        ], [
            [
                FileUploadFactory::make()
                    ->tmpUploadId('a17ebec0-48f9-44cc-8629-f49ccc68f2d3'),
            ],
        ]);

        $tmpUploadId = FileRepository::upload()->save(base64_encode('test'));
        $tmpUploadId2 = FileRepository::upload()->save(base64_encode('test2'));

        expect($tmpUploadId)->toBe('a17ebec0-48f9-44cc-8629-f49ccc68f2d2')
            ->and($tmpUploadId2)->toBe('a17ebec0-48f9-44cc-8629-f49ccc68f2d3');
    });

    it('throws an exception when no more fake responses are available', function () {
        FileRepository::fake([
            [
                FileUploadFactory::make()
                    ->tmpUploadId('a17ebec0-48f9-44cc-8629-f49ccc68f2d2'),
            ],
        ]);

        FileRepository::upload()->save(base64_encode('test'));

        expect(static fn () => FileRepository::upload()->save(base64_encode('test2')))
            ->toThrow('No more fake responses');
    });
});

describe('link', function () {
    it('can get a fake response', function () {
        FileRepository::fake([
            [
                FileUploadFactory::make()
                    ->ok(),
            ],
        ]);

        $file = FileRepository::upload()->link('a17ebec0-48f9-44cc-8629-f49ccc68f2d2');

        expect($file['elements']['success'])->toBe('success');
    });

    it('can get multiple fake responses', function () {
        FileRepository::fake([
            [
                FileUploadFactory::make()
                    ->ok(),
            ],
        ], [
            [
                FileUploadFactory::make()
                    ->error(),
            ],
        ]);

        $tmpUpload = FileRepository::upload()->link('a17ebec0-48f9-44cc-8629-f49ccc68f2d2');
        $tmpUpload2 = FileRepository::upload()->link('a17ebec0-48f9-44cc-8629-f49ccc68f2d3');

        expect($tmpUpload)->toBeArray()
            ->and($tmpUpload['elements']['success'])->toBe('success')
            ->and($tmpUpload2)->toBeArray()
            ->and($tmpUpload2['elements']['success'])->not->toBe('success');
    });

    it('throws an exception when no more fake responses are available', function () {
        FileRepository::fake([
            [
                FileUploadFactory::make()
                    ->ok(),
            ],
        ]);

        FileRepository::upload()->link('a17ebec0-48f9-44cc-8629-f49ccc68f2d3');

        expect(static fn () => FileRepository::upload()->link('a17ebec0-48f9-44cc-8629-f49ccc68f2d3'))
            ->toThrow('No more fake responses');
    });
});
