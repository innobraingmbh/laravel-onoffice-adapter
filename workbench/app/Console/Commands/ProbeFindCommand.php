<?php

declare(strict_types=1);

namespace Workbench\App\Console\Commands;

use Closure;
use Illuminate\Console\Command;
use Innobrain\OnOfficeAdapter\Exceptions\OnOfficeException;
use Innobrain\OnOfficeAdapter\Facades\ActivityRepository;
use Innobrain\OnOfficeAdapter\Facades\AddressRepository;
use Innobrain\OnOfficeAdapter\Facades\AppointmentRepository;
use Innobrain\OnOfficeAdapter\Facades\EstateRepository;
use Innobrain\OnOfficeAdapter\Facades\LastSeenRepository;
use Innobrain\OnOfficeAdapter\Facades\Query;
use Innobrain\OnOfficeAdapter\Facades\SettingRepository;
use Innobrain\OnOfficeAdapter\Facades\TaskRepository;
use Innobrain\OnOfficeAdapter\Facades\UserRepository;

class ProbeFindCommand extends Command
{
    protected $signature = 'probe:find';

    protected $description = 'Probe single-record reads for every withId() builder against the live onOffice API: find() (eager) vs withId() (lazy/batch).';

    public function handle(): int
    {
        $probes = [
            // Builders whose list read needs no extra context.
            fn () => $this->probePair('Estate', fn () => EstateRepository::query()->select(['Id'])),
            fn () => $this->probePair('Address', fn () => AddressRepository::query()->select(['KdNr'])),
            fn () => $this->probePair('Task', fn () => TaskRepository::query()),
            // The user read returns nothing on an empty data set, so select real columns.
            fn () => $this->probePair('User', fn () => UserRepository::query()->select(['Vorname', 'Nachname', 'Emailname'])),
            fn () => $this->probeActivity(),
            fn () => $this->probeAppointment(),
            fn () => $this->probeLastSeen(),
            fn () => $this->probeIdScopedShapes(),
            fn () => $this->probeTaskCount(),
            fn () => $this->probeImprint(),
        ];

        // A live failure in one builder must not hide the others.
        foreach ($probes as $probe) {
            try {
                $probe();
            } catch (OnOfficeException $e) {
                $this->components->error($e->getMessage());
            }
        }

        return self::SUCCESS;
    }

    /**
     * Confirm find() (eager) and withId() (batch) read the same record.
     *
     * @param  Closure(): \Innobrain\OnOfficeAdapter\Query\Builder  $factory
     */
    private function probePair(string $label, Closure $factory): void
    {
        $id = $this->firstId(fn () => ($factory())->limit(1)->get()->first()['id'] ?? 0);

        if ($id === 0) {
            $this->components->warn("{$label}: no records — skipping");

            return;
        }

        $this->components->task("{$label}  find({$id})  [eager]", fn (): bool => (int) (($factory())->find($id)['id'] ?? 0) === $id);

        $this->components->task("{$label}  withId({$id})  [batch]", function () use ($id, $factory): bool {
            $results = Query::batch([($factory())->withId($id)])->once();

            return (int) data_get($results[0], 'data.records.0.id') === $id
                && (int) data_get($results[0], 'data.meta.cntabsolute') === 1;
        });
    }

    /**
     * Activities are listed within an estate, but read by id without that context.
     *
     * Known limitation (pre-existing, not from the find()/withId() unification):
     * the live API rejects a single-activity read with `missing configuration for
     * resourceType "agentslog"`, because the read carries no estate/address
     * context. find() and withId() now fail identically here, which is the point —
     * the underlying single read is just unsupported as implemented.
     */
    private function probeActivity(): void
    {
        $estateId = $this->firstId(fn () => EstateRepository::query()->select(['Id'])->limit(1)->get()->first()['id'] ?? 0);

        $id = $estateId === 0 ? 0 : $this->firstId(fn () => ActivityRepository::query()->estateId($estateId)->limit(1)->get()->first()['id'] ?? 0);

        if ($id === 0) {
            $this->components->warn('Activity: no activities found — skipping');

            return;
        }

        $this->components->task("Activity  find({$id})  [eager, Get]", fn (): bool => (int) (ActivityRepository::query()->find($id)['id'] ?? 0) === $id);

        $this->components->task("Activity  withId({$id})  [batch, Get]", function () use ($id): bool {
            $results = Query::batch([ActivityRepository::query()->withId($id)])->once();

            return (int) data_get($results[0], 'data.records.0.id') === $id;
        });
    }

    /**
     * Appointments list with a date range, but a single read needs none — the
     * case the refactor fixed.
     */
    private function probeAppointment(): void
    {
        $start = date('Y-m-d', strtotime('-3 years'));
        $end = date('Y-m-d', strtotime('+1 year'));

        $id = $this->firstId(fn () => AppointmentRepository::query()
            ->dateRange($start, $end)
            ->select(['id'])
            ->get()
            ->first()['id'] ?? 0);

        if ($id === 0) {
            $this->components->warn('Appointment: no appointments in window — skipping');

            return;
        }

        $this->components->task("Appointment  find({$id})  [eager]", fn (): bool => (int) (AppointmentRepository::query()->select(['id'])->find($id)['id'] ?? 0) === $id);

        $this->components->task("Appointment  withId({$id})  [batch, no dateRange]", function () use ($id): bool {
            $results = Query::batch([AppointmentRepository::query()->select(['id'])->withId($id)])->once();

            return (int) data_get($results[0], 'data.records.0.id') === $id;
        });
    }

    /**
     * LastSeen does not support reading by id; both faces must reject consistently.
     */
    private function probeLastSeen(): void
    {
        $this->components->task('LastSeen  find()  rejects  [guard]', fn (): bool => $this->rejects(fn () => LastSeenRepository::query()->find(1)));

        $this->components->task('LastSeen  withId()  rejects  [guard]', fn (): bool => $this->rejects(fn () => LastSeenRepository::query()->withId(1)->get()));
    }

    /**
     * Every terminal an id-scoped builder can reach. The list window is
     * skipped for these reads, so verify the API accepts the bare id read
     * everywhere and reports usable cntabsolute meta for count()/paginate().
     */
    private function probeIdScopedShapes(): void
    {
        $id = $this->firstId(fn () => EstateRepository::query()->select(['Id'])->limit(1)->get()->first()['id'] ?? 0);

        if ($id === 0) {
            $this->components->warn('Estate: no records — skipping id-scoped shapes');

            return;
        }

        $this->components->task("Estate  withId({$id})->first()  [shape]", fn (): bool => (int) (EstateRepository::query()->withId($id)->first()['id'] ?? 0) === $id);

        $this->components->task("Estate  withId({$id})->get()  [shape]", function () use ($id): bool {
            $records = EstateRepository::query()->withId($id)->get();

            return $records->count() === 1 && (int) $records->first()['id'] === $id;
        });

        $this->components->task("Estate  withId({$id})->count()  [shape]", fn (): bool => EstateRepository::query()->withId($id)->count() === 1);

        $this->components->task("Estate  withId({$id})->paginate()  [shape]", function () use ($id): bool {
            $paginator = EstateRepository::query()->withId($id)->paginate();

            return $paginator->total() === 1 && (int) data_get($paginator->items(), '0.id') === $id;
        });

        $this->components->task("Estate  find('{$id}')  [string id]", fn (): bool => (int) (EstateRepository::query()->find((string) $id)['id'] ?? 0) === $id);
    }

    /**
     * The task endpoint's count() quirk (cntabsolute mirrors listlimit) and
     * its id-scoped form both go through readRequest() now — confirm both
     * still answer sensibly.
     */
    private function probeTaskCount(): void
    {
        $id = $this->firstId(fn () => TaskRepository::query()->limit(1)->get()->first()['id'] ?? 0);

        if ($id === 0) {
            $this->components->warn('Task: no records — skipping count shapes');

            return;
        }

        $this->components->task('Task  count()  [quirk, listlimit=500]', fn (): bool => TaskRepository::query()->count() >= 1);

        $this->components->task("Task  withId({$id})->count()  [shape]", fn (): bool => TaskRepository::query()->withId($id)->count() === 1);
    }

    /**
     * The imprint builder is the one non-Paginate consumer of the shared
     * single-record combinator (requestFirstRecord), so its first()/find()
     * need their own live check. The impressum is a singleton settings
     * record whose id is the string "impressum" — there is nothing to find
     * by numeric id, so this also records what the API does with one.
     */
    private function probeImprint(): void
    {
        $record = null;

        $this->components->task('Imprint  first()  [combinator]', function () use (&$record): bool {
            $record = SettingRepository::imprint()->first();

            return $record !== null;
        });

        $this->components->task('Imprint  record id is the string "impressum"', fn (): bool => ($record['id'] ?? null) === 'impressum');

        $this->components->task('Imprint  find(1)  [API ignores the id, returns the singleton]', fn (): bool => (SettingRepository::imprint()->find(1)['id'] ?? null) === 'impressum');
    }

    private function firstId(Closure $list): int
    {
        try {
            return (int) ($list() ?? 0);
        } catch (OnOfficeException) {
            return 0;
        }
    }

    private function rejects(Closure $call): bool
    {
        try {
            $call();

            return false;
        } catch (OnOfficeException) {
            return true;
        }
    }
}
