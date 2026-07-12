<?php

declare(strict_types=1);

namespace Workbench\App\Console\Commands;

use Illuminate\Console\Command;
use Innobrain\OnOfficeAdapter\Facades\AddressRepository;
use Innobrain\OnOfficeAdapter\Facades\AppointmentRepository;
use Innobrain\OnOfficeAdapter\Facades\EstateRepository;
use Innobrain\OnOfficeAdapter\Facades\LogRepository;
use Throwable;

class ProbeDocsVerify2Command extends Command
{
    protected $signature = 'probe:docs-verify2';

    protected $description = 'Read-only probe round 2: log first()/find(), appointment shape, per-field select acceptance.';

    public function handle(): int
    {
        $this->probeLogs();
        $this->probeAppointments();
        $this->probeSelectAcceptance();

        return self::SUCCESS;
    }

    private function probeLogs(): void
    {
        $this->components->info('== Logs ==');

        try {
            $log = LogRepository::query()->limit(1)->first();
            $this->line('  first() with limit(1): '.json_encode($log, JSON_UNESCAPED_UNICODE));

            if ($log !== null) {
                $found = LogRepository::query()->find((int) $log['id']);
                $this->line('  find('.$log['id'].'): '.json_encode($found, JSON_UNESCAPED_UNICODE));
            }
        } catch (Throwable $e) {
            $this->line('  ERROR: '.$e->getMessage());
        }

        try {
            $count = LogRepository::query()->get()->count();
            $this->line("  bare get() works, records: {$count}");
        } catch (Throwable $e) {
            $this->line('  bare get() ERROR: '.$e->getMessage());
        }

        try {
            $count = LogRepository::query()->withModule('estate')->count();
            $this->line("  count() works: {$count}");
        } catch (Throwable $e) {
            $this->line('  count() ERROR: '.$e->getMessage());
        }

        try {
            $count = LogRepository::query()->withModule('estate')->limit(1)->count();
            $this->line("  count() with limit(1) works: {$count}");
        } catch (Throwable $e) {
            $this->line('  count() with limit(1) ERROR: '.$e->getMessage());
        }
    }

    private function probeAppointments(): void
    {
        $this->components->info('== Appointment element value types (with select) ==');

        try {
            $appointment = AppointmentRepository::query()
                ->select(['type', 'status', 'date', 'location', 'subject', 'notes'])
                ->dateRange('2020-01-01', '2026-12-31')
                ->first();

            if ($appointment === null) {
                $this->line('  no appointments found');

                return;
            }

            foreach (['type', 'status', 'date', 'location'] as $field) {
                $value = data_get($appointment, "elements.{$field}");
                $this->line(sprintf('  %s: %s = %s', $field, get_debug_type($value), json_encode($value, JSON_UNESCAPED_UNICODE)));
            }
        } catch (Throwable $e) {
            $this->line('  ERROR: '.$e->getMessage());
        }
    }

    private function probeSelectAcceptance(): void
    {
        $this->components->info('== Per-field select acceptance ==');

        $estateFields = ['status', 'status2', 'verkauft', 'reserviert', 'geaendert_am'];
        foreach ($estateFields as $field) {
            $this->trySelect('estate', fn () => EstateRepository::query()->select([$field])->first(), $field);
        }

        $addressFields = [
            'Status', 'phone', 'phone_private', 'phone_business', 'mobile',
            'fax', 'fax_private', 'fax_business',
            'email', 'email_private', 'email_business',
            'default_phone', 'default_email', 'defaultphone', 'defaultemail',
        ];
        foreach ($addressFields as $field) {
            $this->trySelect('address', fn () => AddressRepository::query()->select([$field])->first(), $field);
        }
    }

    private function trySelect(string $module, callable $query, string $field): void
    {
        try {
            $record = $query();
            $value = data_get($record, "elements.{$field}");
            $this->line(sprintf('  %s.%-16s OK, value: %s', $module, $field, json_encode($value, JSON_UNESCAPED_UNICODE)));
        } catch (Throwable $e) {
            $this->line(sprintf('  %s.%-16s REJECTED: %s', $module, $field, str_replace("\n", ' ', $e->getMessage())));
        }
    }
}
