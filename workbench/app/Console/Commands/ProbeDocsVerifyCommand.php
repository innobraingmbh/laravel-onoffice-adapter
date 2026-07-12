<?php

declare(strict_types=1);

namespace Workbench\App\Console\Commands;

use Illuminate\Console\Command;
use Innobrain\OnOfficeAdapter\Facades\ActionRepository;
use Innobrain\OnOfficeAdapter\Facades\AddressRepository;
use Innobrain\OnOfficeAdapter\Facades\AppointmentRepository;
use Innobrain\OnOfficeAdapter\Facades\EstateRepository;
use Innobrain\OnOfficeAdapter\Facades\FieldRepository;
use Innobrain\OnOfficeAdapter\Facades\FilterRepository;
use Innobrain\OnOfficeAdapter\Facades\LogRepository;
use Innobrain\OnOfficeAdapter\Facades\MacroRepository;
use Throwable;

class ProbeDocsVerifyCommand extends Command
{
    protected $signature = 'probe:docs-verify';

    protected $description = 'Read-only probe verifying field names and response shapes claimed in docs/ against the live API.';

    public function handle(): int
    {
        $this->probeFieldModules();
        $this->probeFieldNames();
        $this->probePermittedValues();
        $this->probeActionTypes();
        $this->probeFilterShape();
        $this->probeLogShape();
        $this->probeAppointmentShape();
        $this->probeMacros();

        return self::SUCCESS;
    }

    private function probeFieldModules(): void
    {
        $this->components->info('== Field modules (field-repository.md) ==');

        foreach (['address', 'estate', 'agentsLog', 'agentslog', 'calendar', 'email', 'file', 'task', 'user'] as $module) {
            try {
                $fields = FieldRepository::query()->withModules($module)->get();
                $first = $fields->first();
                $elementKeys = $first ? implode(', ', array_keys($first['elements'] ?? [])) : '(empty)';
                $this->line(sprintf('  %-10s -> %d record(s), id=%s, element keys: %s',
                    $module, $fields->count(), $first['id'] ?? '—', $elementKeys));
            } catch (Throwable $e) {
                $this->line(sprintf('  %-10s -> ERROR: %s', $module, $e->getMessage()));
            }
        }
    }

    private function probeFieldNames(): void
    {
        $this->components->info('== Field name claims (estate/address/activity docs) ==');

        $claims = [
            'estate' => [
                'status', 'objektart', 'nutzungsart', 'vermarktungsart', 'kaufpreis', 'kaltmiete',
                'warmmiete', 'wohnflaeche', 'grundstuecksflaeche', 'anzahl_zimmer', 'verkauft',
                'reserviert', 'geaendert_am', 'objekttitel', 'lage',
            ],
            'address' => [
                'Status', 'Anrede', 'Vorname', 'Name', 'Strasse', 'Plz', 'Ort', 'Land', 'Benutzer',
                'newsletter_aktiv', 'KdNr',
                'phone', 'phone_private', 'phone_business', 'mobile',
                'fax', 'fax_private', 'fax_business',
                'email', 'email_private', 'email_business',
                'default_phone', 'default_email', 'defaultphone', 'defaultemail',
                'Briefanrede',
            ],
            'agentsLog' => [
                'Aktionsart', 'Aktionstyp', 'advisorylevel', 'Benutzer', 'Adress_nr', 'Objekt_nr',
                'dauer', 'created', 'Datum', 'note', 'cost', 'reasoncancellation',
            ],
        ];

        foreach ($claims as $module => $names) {
            try {
                $record = FieldRepository::query()
                    ->withModules($module)
                    ->parameters(['labels' => true, 'language' => 'DEU', 'showFieldMeasureFormat' => true])
                    ->get()
                    ->firstWhere('id', $module) ?? FieldRepository::query()->withModules($module)->get()->first();

                $actual = array_keys($record['elements'] ?? []);
                $present = array_values(array_intersect($names, $actual));
                $missing = array_values(array_diff($names, $actual));

                $this->line("  [{$module}] present: ".implode(', ', $present));
                $this->line("  [{$module}] MISSING: ".($missing === [] ? '(none)' : implode(', ', $missing)));

                if ($record !== null) {
                    $sample = collect($record['elements'])->first(fn ($f) => is_array($f));
                    $this->line("  [{$module}] per-field keys: ".implode(', ', array_keys(is_array($sample) ? $sample : [])));
                }
            } catch (Throwable $e) {
                $this->line("  [{$module}] ERROR: {$e->getMessage()}");
            }
        }
    }

    private function probePermittedValues(): void
    {
        $this->components->info('== Permitted values (status/objektart/vermarktungsart/advisorylevel) ==');

        $checks = [
            ['estate', ['status', 'objektart', 'nutzungsart', 'vermarktungsart']],
            ['agentsLog', ['advisorylevel']],
            ['address', ['newsletter_aktiv']],
        ];

        foreach ($checks as [$module, $fields]) {
            try {
                $record = FieldRepository::query()
                    ->withModules($module)
                    ->parameters(['labels' => true, 'language' => 'DEU'])
                    ->get()
                    ->first();

                foreach ($fields as $field) {
                    $def = data_get($record, "elements.{$field}");
                    $permitted = data_get($def, 'permittedvalues');
                    $type = data_get($def, 'type');
                    $this->line(sprintf('  %s.%s: type=%s permittedvalues=%s',
                        $module, $field, var_export($type, true),
                        json_encode($permitted, JSON_UNESCAPED_UNICODE)));
                }
            } catch (Throwable $e) {
                $this->line("  [{$module}] ERROR: {$e->getMessage()}");
            }
        }
    }

    private function probeActionTypes(): void
    {
        $this->components->info('== Action types (action-repository.md) ==');

        try {
            $actions = ActionRepository::query()->get();
            $first = $actions->first();
            $this->line('  records: '.$actions->count());
            $this->line('  first record: '.json_encode($first, JSON_UNESCAPED_UNICODE));
        } catch (Throwable $e) {
            $this->line("  ERROR: {$e->getMessage()}");
        }
    }

    private function probeFilterShape(): void
    {
        $this->components->info('== Filter record shape (filter-repository.md) ==');

        try {
            $filter = FilterRepository::query()->estate()->first();
            $this->line('  first estate filter: '.json_encode($filter, JSON_UNESCAPED_UNICODE));
        } catch (Throwable $e) {
            $this->line("  ERROR: {$e->getMessage()}");
        }
    }

    private function probeLogShape(): void
    {
        $this->components->info('== Log record shape (log-repository.md) ==');

        try {
            $log = LogRepository::query()->first();
            $this->line('  first log: '.json_encode($log, JSON_UNESCAPED_UNICODE));
        } catch (Throwable $e) {
            $this->line("  ERROR: {$e->getMessage()}");
        }
    }

    private function probeAppointmentShape(): void
    {
        $this->components->info('== Appointment element value types (appointment-repository.md tip) ==');

        try {
            $appointment = AppointmentRepository::query()
                ->dateRange('2020-01-01', '2026-12-31')
                ->first();

            if ($appointment === null) {
                $this->line('  no appointments found in range');

                return;
            }

            foreach (['type', 'status', 'date', 'location'] as $field) {
                $value = data_get($appointment, "elements.{$field}");
                $this->line(sprintf('  %s: %s = %s', $field, get_debug_type($value), json_encode($value, JSON_UNESCAPED_UNICODE)));
            }
        } catch (Throwable $e) {
            $this->line("  ERROR: {$e->getMessage()}");
        }
    }

    private function probeMacros(): void
    {
        $this->components->info('== Macro resolution (macro-repository.md common macros) ==');

        try {
            $address = AddressRepository::query()->select(['KdNr'])->first();
            $estate = EstateRepository::query()->select(['Id'])->first();

            if ($address === null || $estate === null) {
                $this->line('  no address/estate available to resolve against');

                return;
            }

            $resolved = MacroRepository::query()
                ->text('[_Anrede|_Vorname|_Name|_Briefanrede|_Strasse|_Plz|_Ort] [_objekttitel|_kaufpreis|_warmmiete|_wohnflaeche|_ort]')
                ->addressIds((int) $address['id'])
                ->estateIds((int) $estate['id'])
                ->resolve();

            $this->line("  address id {$address['id']}, estate id {$estate['id']}");
            $this->line('  resolved: '.$resolved);
        } catch (Throwable $e) {
            $this->line("  ERROR: {$e->getMessage()}");
        }
    }
}
