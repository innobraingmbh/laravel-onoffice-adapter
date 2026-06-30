<?php

declare(strict_types=1);

namespace Workbench\App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\Client\Response;
use Innobrain\OnOfficeAdapter\Dtos\OnOfficeRequest;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeAction;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeResourceType;
use Innobrain\OnOfficeAdapter\Facades\SettingRepository;
use Innobrain\OnOfficeAdapter\Services\OnOfficeService;

class ProbeRegionsCommand extends Command
{
    protected $signature = 'probe:regions';

    protected $description = 'Probe the regions endpoint against the live onOffice API and report its pagination shape.';

    public function handle(): int
    {
        $baseline = $this->fetch('baseline (no list params)', []);

        $cntabsolute = data_get($baseline, 'response.results.0.data.meta.cntabsolute');
        $records = data_get($baseline, 'response.results.0.data.records', []);
        $topLevel = is_array($records) ? count($records) : 0;
        $totalNodes = is_array($records)
            ? array_sum(array_map(fn (array $r): int => $this->countNodes($r['elements'] ?? $r), $records))
            : 0;

        $this->components->info('meta.cntabsolute: '.var_export($cntabsolute, true).' ('.gettype($cntabsolute).')');
        $this->components->info("top-level records: {$topLevel}");
        $this->components->info("total nodes (incl. nested children): {$totalNodes}");
        $this->components->info('pages the generic reader would request (ceil(cntabsolute/500)): '
            .(is_numeric($cntabsolute) ? (int) ceil(((int) $cntabsolute) / 500) : 'n/a'));

        $rows = [];
        foreach ([[1, 0], [1, 1], [1, 2], [2, 0], [2, 2], [500, 0], [500, 500]] as [$limit, $offset]) {
            $rows[] = $this->describePage($limit, $offset);
        }

        $this->table(['listlimit', 'listoffset', 'cntabsolute', 'records', 'first id'], $rows);

        $page1 = $this->describePage(1, 0);
        $page2 = $this->describePage(1, 1);

        $this->components->info('listlimit honored (small page returns fewer records than baseline): '
            .(($topLevel > 1 && (int) $page1[3] < $topLevel) ? 'YES' : 'no / inconclusive'));
        $this->components->info('listoffset honored (page 2 differs from page 1): '
            .(($page1[4] !== $page2[4] || $page1[3] !== $page2[3]) ? 'YES' : 'NO — offset ignored, full tree re-served → duplication'));

        $fixed = 0;
        $this->components->task('fixed get() (single request)', function () use (&$fixed): void {
            $fixed = SettingRepository::regions()->get()->count();
        });

        $old = 0;
        $this->components->task('old paginated reader (requestAll, pageSize 500)', function () use (&$old): void {
            $builder = SettingRepository::regions();
            $old = app(OnOfficeService::class)->requestAll(
                fn (int $pageSize, int $offset) => $builder->requestApi(new OnOfficeRequest(
                    OnOfficeAction::Get,
                    OnOfficeResourceType::Regions,
                    parameters: ['listlimit' => $pageSize, 'listoffset' => $offset],
                )),
            )->count();
        });

        $this->components->info("fixed get() top-level records: {$fixed}");
        $this->components->info("old reader top-level records: {$old}"
            .($topLevel > 0 ? ' (×'.round($old / $topLevel, 1).' duplication)' : ''));

        return self::SUCCESS;
    }

    private function fetch(string $label, array $parameters): array
    {
        $raw = [];

        $this->components->task($label, function () use ($parameters, &$raw): void {
            SettingRepository::regions()
                ->when($parameters !== [], fn ($q) => $q->parameter('parameters', $parameters))
                ->after(function (Response $response) use (&$raw): void {
                    $raw = $response->json() ?? [];
                })
                ->get();
        });

        return $raw;
    }

    private function describePage(int $limit, int $offset): array
    {
        $raw = $this->fetch("listlimit={$limit} listoffset={$offset}", ['listlimit' => $limit, 'listoffset' => $offset]);

        $records = data_get($raw, 'response.results.0.data.records', []);

        return [
            $limit,
            $offset,
            (string) var_export(data_get($raw, 'response.results.0.data.meta.cntabsolute'), true),
            is_array($records) ? count($records) : 0,
            (string) (data_get($records, '0.elements.id') ?? data_get($records, '0.id') ?? '—'),
        ];
    }

    private function countNodes(array $node): int
    {
        $count = 1;
        $children = $node['children'] ?? [];

        if (is_array($children)) {
            foreach ($children as $child) {
                if (is_array($child)) {
                    $count += $this->countNodes($child);
                }
            }
        }

        return $count;
    }
}
