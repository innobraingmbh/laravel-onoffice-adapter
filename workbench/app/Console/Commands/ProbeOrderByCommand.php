<?php

declare(strict_types=1);

namespace Workbench\App\Console\Commands;

use Closure;
use Illuminate\Console\Command;
use Innobrain\OnOfficeAdapter\Exceptions\OnOfficeException;
use Innobrain\OnOfficeAdapter\Facades\ActivityRepository;
use Innobrain\OnOfficeAdapter\Facades\AddressRepository;
use Innobrain\OnOfficeAdapter\Facades\EstateRepository;
use Innobrain\OnOfficeAdapter\Facades\UserRepository;
use Innobrain\OnOfficeAdapter\Query\Builder;

/**
 * Which sortby encoding does the live API actually accept, per endpoint?
 *
 * builder default      - whatever the builder emits for orderByDesc().
 * `sortby` map         - {column: direction}, forced via parameter().
 * `sortby` + sortorder - column name as a string, direction alongside it.
 *
 * Estate, user and activity reads honour only the map; the address read and
 * both search endpoints honour only the split form (the searches reject the
 * map outright). The builder default row must read DESC OK on every endpoint.
 */
class ProbeOrderByCommand extends Command
{
    protected $signature = 'probe:order-by {--limit=6}';

    protected $description = 'Probe which sortby encoding the live onOffice API honours, per endpoint.';

    public function handle(): int
    {
        $estateId = (int) (EstateRepository::query()->select(['Id'])->limit(1)->get()->first()['id'] ?? 0);

        $endpoints = [
            'Estate read' => fn () => EstateRepository::query()->select(['Id']),
            'Address read' => fn () => AddressRepository::query()->select(['KdNr']),
            'User read' => fn () => UserRepository::query()->select(['Vorname', 'Nachname', 'Emailname']),
            'Activity read' => fn () => ActivityRepository::query()->estateId($estateId),
            'Estate search' => fn () => EstateRepository::query()->setInput('a'),
            'Address search' => fn () => AddressRepository::query()->setInput('a'),
        ];

        // Each endpoint's column must be one its API actually sorts by:
        // activities only know fields like Datum, and the address search
        // silently ignores Id but honours KdNr.
        $columns = [
            'Estate read' => 'Id',
            'Address read' => 'KdNr',
            'User read' => 'Nachname',
            'Activity read' => 'Datum',
            'Estate search' => 'Id',
            'Address search' => 'KdNr',
        ];

        foreach ($endpoints as $label => $factory) {
            $this->newLine();
            $this->components->info($label);

            $column = $columns[$label];
            $search = str_contains($label, 'search');

            $this->report('  builder default', $factory, $column, $search, fn (Builder $q) => $q);

            $this->report('  sortby map', $factory, $column, $search, fn (Builder $q) => $q
                ->parameter('sortby', [$column => 'DESC'])
                ->parameter('sortorder', null));

            $this->report('  sortby + sortorder', $factory, $column, $search, fn (Builder $q) => $q
                ->parameter('sortby', $column)
                ->parameter('sortorder', 'DESC'));
        }

        $this->newLine();

        return self::SUCCESS;
    }

    /**
     * Ask for DESC and report what actually came back.
     *
     * @param  Closure(): Builder  $factory
     * @param  Closure(Builder): Builder  $encode
     */
    private function report(string $label, Closure $factory, string $column, bool $search, Closure $encode): void
    {
        $limit = (int) $this->option('limit');

        try {
            $query = $encode($factory()->orderByDesc($column)->limit($limit));

            $records = ($search ? $query->search() : $query->get())->take($limit);
        } catch (OnOfficeException $e) {
            $this->line(sprintf('%s  <fg=red>ERROR</>  %s', $label, $e->getMessage()));

            return;
        }

        $values = $records->map(fn (array $record) => $column === 'Nachname'
            ? (string) data_get($record, 'elements.Nachname', '')
            : (int) $record['id'])->values();

        if ($values->count() < 2) {
            $this->line(sprintf('%s  <fg=yellow>SKIP</>   fewer than 2 records', $label));

            return;
        }

        $sorted = $values->all() === $values->sortDesc()->values()->all();

        $this->line(sprintf(
            '%s  %s  %s',
            $label,
            $sorted ? '<fg=green>DESC OK  </>' : '<fg=red>NOT SORTED</>',
            $values->implode(', '),
        ));
    }
}
