<?php

declare(strict_types=1);

namespace Workbench\App\Console\Commands;

use GuzzleHttp\TransferStats;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Innobrain\OnOfficeAdapter\Dtos\OnOfficeRequest;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeAction;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeResourceType;
use Innobrain\OnOfficeAdapter\Exceptions\OnOfficeException;
use Innobrain\OnOfficeAdapter\Services\OnOfficeService;

class ProbeConnectionReuseCommand extends Command
{
    protected $signature = 'probe:connection-reuse
        {--calls=5 : Sequential calls per mode.}';

    protected $description = 'Compare per-call connect/TLS cost with the HTTP connection reused vs. opened fresh each time.';

    public function handle(): int
    {
        $calls = max(1, (int) $this->option('calls'));

        /** @var array<int, array<string, mixed>> $stats */
        $stats = [];
        Http::globalOptions([
            'on_stats' => function (TransferStats $transfer) use (&$stats): void {
                $stats[] = $transfer->getHandlerStats();
            },
        ]);

        if (config('onoffice.token') === null) {
            $this->components->warn('no credentials in .env — calls will fail with an API error, but the transport timing is still valid');
        }

        foreach ([true, false] as $reuse) {
            Config::set('onoffice.reuse_connection', $reuse);
            $stats = [];

            $this->components->info(($reuse ? 'reuse_connection=true' : 'reuse_connection=false')." — {$calls} sequential calls");

            $service = resolve(OnOfficeService::class);
            $request = new OnOfficeRequest(OnOfficeAction::Read, OnOfficeResourceType::User, parameters: [
                OnOfficeService::DATA => ['Anrede'],
                OnOfficeService::LISTLIMIT => 1,
            ]);

            for ($i = 0; $i < $calls; $i++) {
                try {
                    $service->requestApi($request);
                } catch (OnOfficeException) {
                    // An error response still travelled over the wire; that is all we measure here.
                }
            }

            $this->table(
                ['#', 'total ms', 'tcp connect ms', 'tls done ms', 'local port'],
                collect($stats)->map(fn (array $stat, int $index): array => [
                    $index + 1,
                    number_format($stat['total_time'] * 1000, 0),
                    number_format($stat['connect_time'] * 1000, 0),
                    number_format($stat['appconnect_time'] * 1000, 0),
                    $stat['local_port'],
                ])->all(),
            );

            $this->components->info(sprintf('mean total: %d ms', collect($stats)->avg('total_time') * 1000));
        }

        $this->components->info('A "tcp connect" and "tls done" of 0 and a repeated local port mean the call reused the previous connection.');

        return self::SUCCESS;
    }
}
