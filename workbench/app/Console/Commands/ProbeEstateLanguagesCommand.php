<?php

declare(strict_types=1);

namespace Workbench\App\Console\Commands;

use Illuminate\Console\Command;
use Innobrain\OnOfficeAdapter\Exceptions\OnOfficeException;
use Innobrain\OnOfficeAdapter\Facades\EstateRepository;

class ProbeEstateLanguagesCommand extends Command
{
    protected $signature = 'probe:estate-languages
        {--estate-id= : Estate id to read language variants for. Defaults to the first estate in the tenant.}';

    protected $description = 'Probe the estateLanguage endpoint against the live onOffice API.';

    public function handle(): int
    {
        $estateId = $this->resolveEstateId();

        if ($estateId === 0) {
            $this->components->warn('no estates found — skipping');

            return self::SUCCESS;
        }

        $records = collect();

        $this->components->task("languages({$estateId})", function () use ($estateId, &$records): void {
            $records = EstateRepository::languages($estateId)->get();
        });

        $this->components->info("count: {$records->count()}");

        if ($records->isEmpty()) {
            $this->components->warn('no language variants returned');

            return self::SUCCESS;
        }

        $languages = $records->pluck('elements.language')->filter()->values()->all();

        $this->components->info('languages: '.implode(', ', $languages));

        $main = $records->first(fn (array $record): bool => ($record['elements']['isMain'] ?? false) === true);

        if (is_array($main)) {
            $this->components->info('main language: '.($main['elements']['language'] ?? 'n/a'));
        }

        $this->components->task('first() returns the same leading record as get()', function () use ($estateId, $records): bool {
            $first = EstateRepository::languages($estateId)->first();

            return ($first['id'] ?? null) === ($records->first()['id'] ?? null);
        });

        return self::SUCCESS;
    }

    private function resolveEstateId(): int
    {
        $estateId = (int) ($this->option('estate-id') ?? 0);

        if ($estateId > 0) {
            return $estateId;
        }

        try {
            return (int) (EstateRepository::query()->select(['Id'])->limit(1)->get()->first()['id'] ?? 0);
        } catch (OnOfficeException) {
            return 0;
        }
    }
}
