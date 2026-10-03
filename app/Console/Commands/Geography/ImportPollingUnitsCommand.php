<?php

namespace App\Console\Commands\Geography;

use App\Models\LocalGovernment;
use App\Models\PollingUnit;
use App\Models\Ward;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * Imports INEC polling units for Lagos into wards that already exist.
 *
 * Data: database/seeders/data/lagos_polling_units.csv, the Lagos rows of
 * github.com/mykeels/inec-polling-units (MIT), scraped from inecnigeria.org.
 * It carries no official PU codes, so `code` is left empty for imported units.
 */
class ImportPollingUnitsCommand extends Command
{
    /**
     * INEC spells these LGAs differently from the app's list.
     *
     * @var array<string, string>
     */
    protected const array LGA_ALIASES = [
        'IFAKO IJAYE' => 'IFAKO IJAIYE',
        'SOMOLU' => 'SHOMOLU',
    ];

    protected $signature = 'geography:import-polling-units
                            {--file= : CSV path (defaults to database/seeders/data/lagos_polling_units.csv)}
                            {--report= : Where to write the CSV of unmatched rows}
                            {--dry-run : Match and report without writing to the database}';

    protected $description = 'Import INEC Lagos polling units into matching wards and report wards that do not match';

    public function handle(): int
    {
        $file = $this->option('file') ?: database_path('seeders/data/lagos_polling_units.csv');

        if (! File::exists($file)) {
            $this->error("File not found: {$file}");

            return self::FAILURE;
        }

        $wardIndex = $this->wardIndex();
        $matched = [];
        $unmatched = [];

        foreach ($this->readRows($file) as $row) {
            $lgaKey = $this->lgaKey($row['local_government_name']);
            $ward = $wardIndex[$lgaKey][static::normalize($row['ward_name'])] ?? null;

            if ($ward === null) {
                $unmatched[] = $row;

                continue;
            }

            $matched[$ward->id][] = trim($row['name']);
        }

        $this->summarise($matched, $unmatched);
        $this->writeReport($unmatched);

        if ($this->option('dry-run')) {
            $this->warn('Dry run: nothing was written.');

            return self::SUCCESS;
        }

        $this->import($matched);

        return self::SUCCESS;
    }

    /**
     * @param  array<int, list<string>>  $matched
     */
    protected function import(array $matched): void
    {
        $deactivated = 0;

        DB::transaction(function () use ($matched, &$deactivated): void {
            foreach ($matched as $wardId => $names) {
                $uniqueNames = static::disambiguate($names);
                $now = now();

                foreach (array_chunk($uniqueNames, 500) as $chunk) {
                    PollingUnit::query()->upsert(
                        array_map(fn (string $name): array => [
                            'ward_id' => $wardId,
                            'name' => $name,
                            'is_active' => true,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ], $chunk),
                        ['ward_id', 'name'],
                        ['is_active', 'updated_at'],
                    );
                }

                // Placeholder units from the original seed stay for existing members but are hidden from new sign-ups.
                $deactivated += PollingUnit::query()
                    ->where('ward_id', $wardId)
                    ->whereNotIn('name', $uniqueNames)
                    ->where('is_active', true)
                    ->update(['is_active' => false]);
            }
        });

        $this->info('Imported '.array_sum(array_map('count', $matched)).' polling units into '.count($matched).' wards.');
        $this->info("Deactivated {$deactivated} placeholder unit(s) in those wards.");
    }

    /**
     * @return array<string, array<string, Ward>>
     */
    protected function wardIndex(): array
    {
        $index = [];

        foreach (LocalGovernment::query()->with('wards')->get() as $lga) {
            foreach ($lga->wards as $ward) {
                $index[static::normalize($lga->name)][static::normalize($ward->name)] = $ward;
            }
        }

        return $index;
    }

    /**
     * @return Collection<int, array<string, string>>
     */
    protected function readRows(string $file): Collection
    {
        $handle = fopen($file, 'r');
        $header = fgetcsv($handle, escape: '\\');
        $rows = collect();

        while (($values = fgetcsv($handle, escape: '\\')) !== false) {
            $row = array_combine($header, $values);

            if (Str::upper(trim($row['state_name'])) === 'LAGOS') {
                $rows->push($row);
            }
        }

        fclose($handle);

        return $rows;
    }

    protected function lgaKey(string $name): string
    {
        $key = static::normalize($name);

        return self::LGA_ALIASES[$key] ?? $key;
    }

    /**
     * @param  array<int, list<string>>  $matched
     * @param  list<array<string, string>>  $unmatched
     */
    protected function summarise(array $matched, array $unmatched): void
    {
        $this->info(sprintf(
            'Matched %d units in %d wards. Unmatched: %d units in %d INEC wards.',
            array_sum(array_map('count', $matched)),
            count($matched),
            count($unmatched),
            collect($unmatched)->unique(fn (array $row) => $row['local_government_name'].'|'.$row['ward_name'])->count(),
        ));

        $this->table(
            ['LGA', 'INEC ward (not in app)', 'Units'],
            collect($unmatched)
                ->groupBy(fn (array $row) => $row['local_government_name'].'|'.$row['ward_name'])
                ->map(fn (Collection $rows, string $key) => [...explode('|', $key), $rows->count()])
                ->sortBy(fn (array $row) => $row[0].$row[1])
                ->values()
                ->all(),
        );
    }

    /**
     * @param  list<array<string, string>>  $unmatched
     */
    protected function writeReport(array $unmatched): void
    {
        $path = $this->option('report') ?: storage_path('app/reports/unmatched-polling-units.csv');
        File::ensureDirectoryExists(dirname($path));

        $handle = fopen($path, 'w');
        fputcsv($handle, ['local_government_name', 'ward_name', 'name'], escape: '\\');

        foreach ($unmatched as $row) {
            fputcsv($handle, [$row['local_government_name'], $row['ward_name'], $row['name']], escape: '\\');
        }

        fclose($handle);

        $this->line("Unmatched rows written to {$path}");
    }

    /**
     * Upper-case, strip punctuation and treat 1/2/3… and I/II/III… as the same.
     */
    public static function normalize(string $name): string
    {
        $words = preg_split('/\s+/', trim((string) preg_replace('/[^A-Z0-9]+/', ' ', Str::upper(str_replace('&', ' AND ', $name)))));
        $numerals = ['1' => 'I', '2' => 'II', '3' => 'III', '4' => 'IV', '5' => 'V', '6' => 'VI'];

        return implode(' ', array_map(fn (string $word): string => $numerals[$word] ?? $word, $words));
    }

    /**
     * INEC lists several units at the same venue; number repeats so each stays selectable.
     *
     * @param  list<string>  $names
     * @return list<string>
     */
    public static function disambiguate(array $names): array
    {
        $seen = [];

        return array_map(function (string $name) use (&$seen): string {
            $key = Str::upper($name);
            $seen[$key] = ($seen[$key] ?? 0) + 1;

            return $seen[$key] === 1 ? $name : "{$name} ({$seen[$key]})";
        }, $names);
    }
}
