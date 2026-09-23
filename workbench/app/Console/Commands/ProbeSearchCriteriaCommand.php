<?php

declare(strict_types=1);

namespace Workbench\App\Console\Commands;

use Closure;
use Illuminate\Console\Command;
use Innobrain\OnOfficeAdapter\Exceptions\OnOfficeException;
use Innobrain\OnOfficeAdapter\Exceptions\OnOfficeQueryException;
use Innobrain\OnOfficeAdapter\Facades\SearchCriteriaRepository;
use Innobrain\OnOfficeAdapter\Facades\SettingRepository;

class ProbeSearchCriteriaCommand extends Command
{
    protected $signature = 'probe:search-criteria
        {--search-data= : Comma-separated field=value pairs to match (default: vermarktungsart=kauf)}
        {--modify= : Search criteria id to round-trip a public note on (writes to the tenant)}';

    protected $description = 'Probe the search criteria endpoints against the live onOffice API: fields, reads, matching and modify.';

    public function handle(): int
    {
        $this->components->info('Tenant: '.(SettingRepository::imprint()->first()['elements']['firma'] ?? 'unknown'));

        $probes = [
            $this->probeFields(...),
            $this->probeMatching(...),
            $this->probeReads(...),
            $this->probeModify(...),
        ];

        // A live failure in one probe must not hide the others.
        foreach ($probes as $probe) {
            try {
                $probe();
            } catch (OnOfficeException $e) {
                $this->components->error($e->getMessage());
            }
        }

        return self::SUCCESS;
    }

    private function probeFields(): void
    {
        $this->components->task('fields()  returns categories with fields', function (): bool {
            $categories = SearchCriteriaRepository::fields()->parameter('language', 'ENG')->get();

            return $categories->isNotEmpty()
                && $categories->every(fn (array $category): bool => is_array($category['elements']['fields'] ?? null));
        });
    }

    private function probeMatching(): void
    {
        $searchData = $this->searchData();
        $this->components->info('Matching '.json_encode($searchData));

        $count = SearchCriteriaRepository::matching($searchData)->count();
        $this->components->info("count(): {$count}");

        if ($count === 0) {
            $this->components->warn('No matching search criteria — pass --search-data with values this tenant has');

            return;
        }

        $this->components->task('get()  returns every match across pages', fn (): bool => SearchCriteriaRepository::matching($searchData)->pageSize(2)->get()->count() === $count);

        $this->components->task('get()  pages without duplicates  [ungrouped, pageSize 2 vs 500]', function () use ($searchData): bool {
            $paged = SearchCriteriaRepository::matching($searchData)->groupByAddress(false)->pageSize(2)->get()->pluck('id');
            $single = SearchCriteriaRepository::matching($searchData)->groupByAddress(false)->get()->pluck('id');

            return $paged->duplicates()->isEmpty() && $paged->all() === $single->all();
        });

        $this->components->task('groupByAddress()  yields one match per address', function () use ($searchData): bool {
            $addresses = SearchCriteriaRepository::matching($searchData)->get()->pluck('elements.adresse');

            return $addresses->duplicates()->isEmpty();
        });

        $this->components->task('first()  returns the id and address', function () use ($searchData): bool {
            $elements = SearchCriteriaRepository::matching($searchData)->first()['elements'] ?? [];

            return isset($elements['Id'], $elements['adresse']);
        });

        $this->components->task('paginate()  reports the total and a later page', function () use ($searchData, $count): bool {
            $paginator = SearchCriteriaRepository::matching($searchData)->paginate(perPage: 1, page: min(2, $count));

            return $paginator->total() === $count && count($paginator->items()) === 1;
        });

        $this->components->task('select()  outputs the selected fields', function () use ($searchData): bool {
            $elements = SearchCriteriaRepository::matching($searchData)->select(['Id', 'adresse', 'vermarktungsart'])->first()['elements'] ?? [];

            return array_keys($elements) === ['Id', 'adresse', 'vermarktungsart'];
        });

        $this->components->task('outputAll()  outputs more than id and address', fn (): bool => count(SearchCriteriaRepository::matching($searchData)->outputAll()->first()['elements'] ?? []) > 2);

        $this->components->task('orderByDesc()  sorts by a search criteria field', function () use ($searchData): bool {
            $prices = SearchCriteriaRepository::matching($searchData)
                ->groupByAddress(false)
                ->select(['kaufpreis__bis'])
                ->orderByDesc('kaufpreis__bis')
                ->get()
                ->map(fn (array $record): float => (float) ($record['elements']['kaufpreis__bis'] ?? 0));

            return $prices->all() === $prices->sortDesc()->values()->all();
        });

        $this->components->task('find()  rejects  [guard]', fn (): bool => $this->rejects(fn () => SearchCriteriaRepository::matching()->find(1)));
    }

    private function probeReads(): void
    {
        $match = SearchCriteriaRepository::matching($this->searchData())->first()['elements'] ?? null;

        if ($match === null) {
            $this->components->warn('Reads: no search criteria to read — skipping');

            return;
        }

        $id = (int) $match['Id'];
        $addressId = (int) $match['adresse'];

        $this->components->task("query()  find({$id})  [mode searchcriteria]", fn (): bool => (int) (SearchCriteriaRepository::query()->mode('searchcriteria')->find($id)['id'] ?? 0) === $id);

        $this->components->task("query()  recordIds([{$addressId}])->get()  [mode internal] includes {$id}", fn (): bool => SearchCriteriaRepository::query()->mode('internal')->recordIds([$addressId])->get()->pluck('id')->contains($id));
    }

    private function probeModify(): void
    {
        $id = (int) $this->option('modify');

        if ($id === 0) {
            $this->components->warn('Modify: skipped — pass --modify=<search criteria id> to round-trip a public note');

            return;
        }

        if (! $this->confirm("This writes to search criteria {$id} on the tenant above and restores it. Continue?")) {
            return;
        }

        $original = $this->publicNote($id);
        $probe = 'adapter probe '.now()->toDateTimeString();

        $this->components->task("modify({$id})  sets the public note", function () use ($id, $probe): bool {
            SearchCriteriaRepository::query()->addModify('krit_bemerkung_oeffentlich', $probe)->modify($id);

            return $this->publicNote($id) === $probe;
        });

        $this->components->task("modify({$id})  restores the public note", function () use ($id, $original): bool {
            SearchCriteriaRepository::query()->addModify('krit_bemerkung_oeffentlich', $original)->modify($id);

            return $this->publicNote($id) === $original;
        });
    }

    /**
     * @return array<string, string>
     */
    private function searchData(): array
    {
        return collect(explode(',', $this->option('search-data') ?? 'vermarktungsart=kauf'))
            ->mapWithKeys(function (string $pair): array {
                [$field, $value] = array_pad(explode('=', $pair, 2), 2, '');

                return [trim($field) => trim($value)];
            })
            ->all();
    }

    private function publicNote(int $id): string
    {
        return (string) (SearchCriteriaRepository::query()->mode('searchcriteria')->find($id)['elements']['_meta']['publicnote'] ?? '');
    }

    private function rejects(Closure $call): bool
    {
        try {
            $call();

            return false;
        } catch (OnOfficeException|OnOfficeQueryException) {
            return true;
        }
    }
}
