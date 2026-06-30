<?php

declare(strict_types=1);

namespace Workbench\App\Console\Commands;

use Illuminate\Console\Command;
use Innobrain\OnOfficeAdapter\Dtos\OnOfficeRequest;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeAction;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeResourceType;
use Innobrain\OnOfficeAdapter\Exceptions\OnOfficeException;
use Innobrain\OnOfficeAdapter\Facades\AddressRepository;
use Innobrain\OnOfficeAdapter\Facades\EstateRepository;
use Innobrain\OnOfficeAdapter\Facades\Query;
use Innobrain\OnOfficeAdapter\Facades\TaskRepository;
use Innobrain\OnOfficeAdapter\Services\OnOfficeService;

class ProbeBatchCommand extends Command
{
    protected $signature = 'probe:batch {--limit=2 : Records per action.}';

    protected $description = 'Probe Query::batch() against the live onOffice API: many actions, one HTTP call.';

    public function handle(): int
    {
        $limit = (int) $this->option('limit');

        // Builders are converted to read requests; each becomes one signed action.
        $this->components->task("batch estate+address ({$limit} each) in one call", function () use ($limit, &$results) {
            $results = Query::batch([
                EstateRepository::query()->select(['Id', 'kaufpreis'])->limit($limit),
                AddressRepository::query()->select(['Vorname', 'Name'])->limit($limit),
            ])->once();
        });

        $estate = $results[0] ?? [];
        $address = $results[1] ?? [];

        $this->components->info('results: '.$results->count());
        $this->line(sprintf('  [0] resourcetype=%s  cntabsolute=%s  records=%d',
            data_get($estate, 'resourcetype'),
            data_get($estate, 'data.meta.cntabsolute'),
            count(data_get($estate, 'data.records', [])),
        ));
        $this->line(sprintf('  [1] resourcetype=%s  cntabsolute=%s  records=%d',
            data_get($address, 'resourcetype'),
            data_get($address, 'data.meta.cntabsolute'),
            count(data_get($address, 'data.records', [])),
        ));

        $this->components->task('one result per action, in request order', fn (): bool => $results->count() === 2
            && data_get($estate, 'resourcetype') === 'estate'
            && data_get($address, 'resourcetype') === 'address');

        $this->components->task('records per action honour the builder limit', fn (): bool => count(data_get($estate, 'data.records', [])) <= $limit
            && count(data_get($address, 'data.records', [])) <= $limit);

        // Mix a raw OnOfficeRequest with a builder added after construction.
        $this->components->task('raw request + add() after batch() dispatch together', function () use ($limit, &$mixed): bool {
            $mixed = Query::batch([
                new OnOfficeRequest(OnOfficeAction::Read, OnOfficeResourceType::Estate, parameters: [
                    OnOfficeService::DATA => ['Id'],
                    OnOfficeService::LISTLIMIT => $limit,
                ]),
            ])
                ->add(AddressRepository::query()->select(['Name'])->limit($limit))
                ->once();

            return $mixed->count() === 2
                && data_get($mixed[0], 'resourcetype') === 'estate'
                && data_get($mixed[1], 'resourcetype') === 'address';
        });

        // A single-action batch must still loop the failure check (min one result).
        $this->components->task('single-action batch dispatches', function () use ($limit, &$single): bool {
            $single = Query::batch([
                EstateRepository::query()->select(['Id'])->limit($limit),
            ])->once();

            return $single->count() === 1 && data_get($single[0], 'resourcetype') === 'estate';
        });

        // Order must hold beyond two actions.
        $this->components->task('three actions keep request order', function () use ($limit): bool {
            $three = Query::batch([
                EstateRepository::query()->select(['Id'])->limit($limit),
                AddressRepository::query()->select(['Name'])->limit($limit),
                EstateRepository::query()->select(['Id'])->limit($limit),
            ])->once();

            return $three->pluck('resourcetype')->all() === ['estate', 'address', 'estate'];
        });

        // Filters set on the builder must survive the toRequest() conversion.
        $this->components->task('where() filter survives toRequest()', function () use ($limit, $estate, &$filtered): bool {
            $filtered = Query::batch([
                EstateRepository::query()->whereBetween('kaufpreis', 100000, 200000)->select(['Id'])->limit($limit),
            ])->once();

            $filteredCount = (int) data_get($filtered[0], 'data.meta.cntabsolute');

            return $filteredCount <= (int) data_get($estate, 'data.meta.cntabsolute');
        });

        // The batch path and the normal count() path must agree on the same query.
        $this->components->task('batch cntabsolute matches EstateRepository::count()', function () use ($estate): bool {
            return (int) data_get($estate, 'data.meta.cntabsolute') === EstateRepository::query()->count();
        });

        // Guard: a non-zero offset on a resource without offset support throws (client-side, no HTTP).
        $this->components->task('offset guard rejects non-offset resource in batch', function (): bool {
            try {
                Query::batch([TaskRepository::query()->offset(50)]);

                return false;
            } catch (OnOfficeException) {
                return true;
            }
        });

        // Guard: an empty batch throws on dispatch (client-side, no HTTP).
        $this->components->task('empty batch throws on once()', function (): bool {
            try {
                Query::batch()->once();

                return false;
            } catch (OnOfficeException) {
                return true;
            }
        });

        return self::SUCCESS;
    }
}
