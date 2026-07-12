<?php

declare(strict_types=1);

namespace Workbench\App\Console\Commands;

use Illuminate\Console\Command;
use Innobrain\OnOfficeAdapter\Facades\ActionRepository;
use Innobrain\OnOfficeAdapter\Facades\LogRepository;
use Throwable;

class ProbeDocsVerify3Command extends Command
{
    protected $signature = 'probe:docs-verify3';

    protected $description = 'Read-only probe round 3: action kind record shape + allowAutomaticTypesForActionKind, log count via limit(0).';

    public function handle(): int
    {
        $this->probeActionKinds();
        $this->probeLogCount();

        return self::SUCCESS;
    }

    private function probeActionKinds(): void
    {
        $this->components->info('== Action kinds ==');

        try {
            $kinds = ActionRepository::query()->get();
            $first = $kinds->first();
            $this->line('  records: '.$kinds->count());
            $this->line('  first record: '.json_encode($first, JSON_UNESCAPED_UNICODE));

            $emptyDefault = $kinds->filter(fn (array $kind) => count(data_get($kind, 'elements.types', [])) === 0);
            $this->line('  kinds with zero types by default: '.$emptyDefault->map(fn (array $kind) => data_get($kind, 'elements.key'))->implode(', '));

            $full = ActionRepository::query()
                ->parameters(['allowAutomaticTypesForActionKind' => $kinds->pluck('elements.key')->all()])
                ->get();

            foreach ($kinds as $kind) {
                $key = data_get($kind, 'elements.key');
                $before = count(data_get($kind, 'elements.types', []));
                $after = count(data_get($full->first(fn (array $k) => data_get($k, 'elements.key') === $key), 'elements.types', []));

                if ($before !== $after) {
                    $this->line(sprintf('  %s: %d types by default, %d with allowAutomaticTypesForActionKind', $key, $before, $after));
                }
            }
        } catch (Throwable $e) {
            $this->line('  ERROR: '.$e->getMessage());
        }
    }

    private function probeLogCount(): void
    {
        $this->components->info('== Log count via limit(0) ==');

        try {
            $count = LogRepository::query()->limit(0)->count();
            $this->line("  limit(0)->count(): {$count}");
        } catch (Throwable $e) {
            $this->line('  limit(0)->count() ERROR: '.$e->getMessage());
        }
    }
}
