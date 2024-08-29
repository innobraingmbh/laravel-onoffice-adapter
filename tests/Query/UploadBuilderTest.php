<?php

declare(strict_types=1);

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Katalam\OnOfficeAdapter\Query\UploadBuilder;
use Katalam\OnOfficeAdapter\Services\OnOfficeService;
use Katalam\OnOfficeAdapter\Tests\Stubs\LinkDataResponse;
use Katalam\OnOfficeAdapter\Tests\Stubs\UploadFileResponse;

describe('work', function () {
    it('can save', function () {
        Http::preventStrayRequests();

        Http::fake([
            '*' => UploadFileResponse::make(),
        ]);

        $builder = new UploadBuilder(app(OnOfficeService::class));

        $tmpUploadId = $builder->save(base64_encode('test'));

        expect($tmpUploadId)->toBe('a17ebec0-48f9-44cc-8629-f49ccc68f2d2');
    });

    it('can save in chunks', function () {
        Http::preventStrayRequests();

        Http::fake([
            '*' => Http::sequence([
                UploadFileResponse::make(),
                UploadFileResponse::make(),
            ]),
        ]);

        $builder = new UploadBuilder(app(OnOfficeService::class));

        $tmpUploadId = $builder
            ->uploadInBlocks(4) // test as base64 string has 8 characters
            ->save(base64_encode('test'));

        expect($tmpUploadId)->toBe('a17ebec0-48f9-44cc-8629-f49ccc68f2d2');

        Http::assertSequencesAreEmpty();
    });

    it('can link', function () {
        Http::preventStrayRequests();

        Http::fake([
            '*' => LinkDataResponse::make(),
        ]);

        $builder = new UploadBuilder(app(OnOfficeService::class));

        $builder->link('a17ebec0-48f9-44cc-8629-f49ccc68f2d2', [
            'module' => 'estate',
            'file' => 'JPEG_example_JPG.jpg',
            'relatedRecordId' => 409,
        ]);

        Http::assertSent(static function (Request $request) {
            return data_get($request->data(), 'request.actions.0.parameters.tmpUploadId') === 'a17ebec0-48f9-44cc-8629-f49ccc68f2d2'
                && data_get($request->data(), 'request.actions.0.parameters.relatedRecordId') === 409;
        });
    });

    it('can save and link', function () {
        Http::preventStrayRequests();

        Http::fake([
            '*' => Http::sequence([
                UploadFileResponse::make(),
                LinkDataResponse::make(),
            ]),
        ]);

        $builder = new UploadBuilder(app(OnOfficeService::class));

        $builder->saveAndLink(base64_encode('test'), [
            'module' => 'estate',
            'file' => 'JPEG_example_JPG.jpg',
            'relatedRecordId' => 409,
        ]);

        Http::assertSent(static function (Request $request) {
            return data_get($request->data(), 'request.actions.0.parameters.tmpUploadId') === 'a17ebec0-48f9-44cc-8629-f49ccc68f2d2'
                && data_get($request->data(), 'request.actions.0.parameters.relatedRecordId') === 409;
        });
    });
});
