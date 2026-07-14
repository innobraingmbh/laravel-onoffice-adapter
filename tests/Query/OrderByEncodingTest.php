<?php

declare(strict_types=1);

use Innobrain\OnOfficeAdapter\Dtos\OnOfficeRequest;
use Innobrain\OnOfficeAdapter\Facades\ActivityRepository;
use Innobrain\OnOfficeAdapter\Facades\AddressRepository;
use Innobrain\OnOfficeAdapter\Facades\EstateRepository;
use Innobrain\OnOfficeAdapter\Facades\UserRepository;

/**
 * Every builder must encode orderBy() the same way: onOffice expects `sortby`
 * as a `{column: direction}` map. Previously Address/Activity reads dropped the
 * direction (sortorder was always null) and the search() methods emitted an
 * integer sortby, so the caller's sort direction was silently lost.
 */
describe('orderBy sortby encoding', function () {
    it('sends the direction as a sortby map for estate reads', function () {
        EstateRepository::fake(EstateRepository::response([
            EstateRepository::page(),
        ]));

        EstateRepository::query()->orderByDesc('kaufpreis')->get();

        EstateRepository::assertSent(function (OnOfficeRequest $request) {
            return $request->parameters['sortby'] === ['kaufpreis' => 'DESC']
                && ! array_key_exists('sortorder', $request->parameters);
        });
    });

    it('sends the direction as a sortby map for address reads', function () {
        AddressRepository::fake(AddressRepository::response([
            AddressRepository::page(),
        ]));

        AddressRepository::query()->orderByDesc('kaufpreis')->get();

        AddressRepository::assertSent(function (OnOfficeRequest $request) {
            return $request->parameters['sortby'] === ['kaufpreis' => 'DESC']
                && ! array_key_exists('sortorder', $request->parameters);
        });
    });

    it('sends the direction as a sortby map for activity reads', function () {
        ActivityRepository::fake(ActivityRepository::response([
            ActivityRepository::page(),
        ]));

        ActivityRepository::query()->orderByDesc('date')->get();

        ActivityRepository::assertSent(function (OnOfficeRequest $request) {
            return $request->parameters['sortby'] === ['date' => 'DESC']
                && ! array_key_exists('sortorder', $request->parameters);
        });
    });

    it('sends the direction as a sortby map for user reads', function () {
        UserRepository::fake(UserRepository::response([
            UserRepository::page(),
        ]));

        UserRepository::query()->orderByDesc('Name')->get();

        UserRepository::assertSent(function (OnOfficeRequest $request) {
            return $request->parameters['sortby'] === ['Name' => 'DESC']
                && ! array_key_exists('sortorder', $request->parameters);
        });
    });

    it('sends the direction as a sortby map for estate search', function () {
        EstateRepository::fake(EstateRepository::response([
            EstateRepository::page(),
        ]));

        EstateRepository::query()->setInput('foo')->orderByDesc('kaufpreis')->search();

        EstateRepository::assertSent(function (OnOfficeRequest $request) {
            return $request->parameters['sortby'] === ['kaufpreis' => 'DESC']
                && ! array_key_exists('sortorder', $request->parameters);
        });
    });

    it('sends the direction as a sortby map for address search', function () {
        AddressRepository::fake(AddressRepository::response([
            AddressRepository::page(),
        ]));

        AddressRepository::query()->setInput('foo')->orderByDesc('kaufpreis')->search();

        AddressRepository::assertSent(function (OnOfficeRequest $request) {
            return $request->parameters['sortby'] === ['kaufpreis' => 'DESC']
                && ! array_key_exists('sortorder', $request->parameters);
        });
    });

    it('preserves every column when ordering by more than one', function () {
        EstateRepository::fake(EstateRepository::response([
            EstateRepository::page(),
        ]));

        EstateRepository::query()
            ->orderByDesc('kaufpreis')
            ->orderBy('ort')
            ->get();

        EstateRepository::assertSent(function (OnOfficeRequest $request) {
            return $request->parameters['sortby'] === [
                'kaufpreis' => 'DESC',
                'ort' => 'ASC',
            ];
        });
    });
});
