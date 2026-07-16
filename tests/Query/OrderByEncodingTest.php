<?php

declare(strict_types=1);

use Innobrain\OnOfficeAdapter\Dtos\OnOfficeRequest;
use Innobrain\OnOfficeAdapter\Facades\ActivityRepository;
use Innobrain\OnOfficeAdapter\Facades\AddressRepository;
use Innobrain\OnOfficeAdapter\Facades\EstateRepository;
use Innobrain\OnOfficeAdapter\Facades\UserRepository;

/**
 * onOffice's sort encoding differs per endpoint (verified against the live
 * API): estate, user and activity reads take `sortby` as a `{column: direction}`
 * map, while the address read silently ignores that map and the search
 * endpoints reject it outright — those need `sortby` as a column name with the
 * direction in a separate `sortorder`. Previously Address/Activity reads
 * dropped the direction and the search() methods emitted an integer sortby,
 * so the caller's sort direction was silently lost.
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

    it('sends the direction as a sortby map for activity reads', function () {
        ActivityRepository::fake(ActivityRepository::response([
            ActivityRepository::page(),
        ]));

        ActivityRepository::query()->orderByDesc('Datum')->get();

        ActivityRepository::assertSent(function (OnOfficeRequest $request) {
            return $request->parameters['sortby'] === ['Datum' => 'DESC']
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

    it('sends the column and direction separately for address reads', function () {
        AddressRepository::fake(AddressRepository::response([
            AddressRepository::page(),
        ]));

        AddressRepository::query()->orderByDesc('KdNr')->get();

        AddressRepository::assertSent(function (OnOfficeRequest $request) {
            return $request->parameters['sortby'] === 'KdNr'
                && $request->parameters['sortorder'] === 'DESC';
        });
    });

    it('sends the column and direction separately for estate search', function () {
        EstateRepository::fake(EstateRepository::response([
            EstateRepository::page(),
        ]));

        EstateRepository::query()->setInput('foo')->orderByDesc('kaufpreis')->search();

        EstateRepository::assertSent(function (OnOfficeRequest $request) {
            return $request->parameters['sortby'] === 'kaufpreis'
                && $request->parameters['sortorder'] === 'DESC';
        });
    });

    it('sends the column and direction separately for address search', function () {
        AddressRepository::fake(AddressRepository::response([
            AddressRepository::page(),
        ]));

        AddressRepository::query()->setInput('foo')->orderByDesc('KdNr')->search();

        AddressRepository::assertSent(function (OnOfficeRequest $request) {
            return $request->parameters['sortby'] === 'KdNr'
                && $request->parameters['sortorder'] === 'DESC';
        });
    });

    it('sends null sort parameters for an unordered address read', function () {
        AddressRepository::fake(AddressRepository::response([
            AddressRepository::page(),
        ]));

        AddressRepository::query()->get();

        AddressRepository::assertSent(function (OnOfficeRequest $request) {
            return $request->parameters['sortby'] === null
                && $request->parameters['sortorder'] === null;
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
