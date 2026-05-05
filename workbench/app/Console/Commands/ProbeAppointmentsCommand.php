<?php

declare(strict_types=1);

namespace Workbench\App\Console\Commands;

use Illuminate\Console\Command;
use Innobrain\OnOfficeAdapter\Exceptions\OnOfficeException;
use Innobrain\OnOfficeAdapter\Facades\AppointmentRepository;

class ProbeAppointmentsCommand extends Command
{
    protected $signature = 'probe:appointments
        {--start= : Start date (Y-m-d). Defaults to 3 years ago.}
        {--end= : End date (Y-m-d). Defaults to 1 year ahead.}';

    protected $description = 'Probe the appointmentList endpoint against the live onOffice API.';

    public function handle(): int
    {
        $start = $this->option('start') ?? date('Y-m-d', strtotime('-3 years'));
        $end = $this->option('end') ?? date('Y-m-d', strtotime('+1 year'));

        $this->components->task("list {$start}..{$end}", function () use ($start, $end, &$list) {
            $list = AppointmentRepository::query()
                ->dateRange($start, $end)
                ->select(['id', 'subject'])
                ->get();
        });

        $this->components->info("count: {$list->count()}");

        if ($list->isEmpty()) {
            $this->components->warn('no appointments in window — skipping find()');

            return self::SUCCESS;
        }

        $id = (int) $list->first()['id'];

        $this->components->task("find({$id})", function () use ($id, &$record) {
            $record = AppointmentRepository::query()
                ->select(['id', 'subject'])
                ->find($id);
        });

        $this->components->info('got id: '.($record['id'] ?? 'n/a'));

        $this->components->task('guard rejects list reads without dateRange', function () {
            try {
                AppointmentRepository::query()->get();

                return false;
            } catch (OnOfficeException) {
                return true;
            }
        });

        return self::SUCCESS;
    }
}
