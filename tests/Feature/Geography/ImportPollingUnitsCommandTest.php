<?php

use App\Models\LocalGovernment;
use App\Models\PollingUnit;
use App\Models\Ward;

beforeEach(function () {
    $this->csv = tempnam(sys_get_temp_dir(), 'pu').'.csv';
    $this->report = tempnam(sys_get_temp_dir(), 'report').'.csv';

    file_put_contents($this->csv, implode("\n", [
        'name,ward_name,local_government_name,state_name,location.latitude,location.longitude',
        '"CPS PRY. SCHOOL",ISALE ODO,AGEGE,LAGOS,,',
        '"CPS PRY. SCHOOL",ISALE ODO,AGEGE,LAGOS,,',
        'OPEN SPACE KOLLINGTON,FESTAC 1,IFAKO-IJAYE,LAGOS,,',
        'KEKE MARKET,KEKE,AGEGE,LAGOS,,',
        'LGEA SCHOOL INABE,OLAMABORO V,OLAMABORO,KOGI,,',
    ]));

    $agege = LocalGovernment::factory()->create(['name' => 'Agege']);
    $ifako = LocalGovernment::factory()->create(['name' => 'Ifako-Ijaiye']);
    $this->isaleOdo = Ward::factory()->create(['local_government_id' => $agege->id, 'name' => 'Isale Odo']);
    $this->festac = Ward::factory()->create(['local_government_id' => $ifako->id, 'name' => 'Festac I']);
    $this->placeholder = PollingUnit::factory()->create(['ward_id' => $this->isaleOdo->id, 'name' => 'Isale Odo Primary School', 'is_active' => true]);
});

test('imports units into matching wards, numbering repeated venues', function () {
    $this->artisan('geography:import-polling-units', ['--file' => $this->csv, '--report' => $this->report])
        ->assertSuccessful();

    expect(PollingUnit::query()->where('ward_id', $this->isaleOdo->id)->where('is_active', true)->pluck('name')->sort()->values()->all())
        ->toBe(['CPS PRY. SCHOOL', 'CPS PRY. SCHOOL (2)'])
        ->and(PollingUnit::query()->where('ward_id', $this->festac->id)->pluck('name')->all())
        ->toBe(['OPEN SPACE KOLLINGTON']);
});

test('keeps placeholder units for existing members but hides them from selection', function () {
    $this->artisan('geography:import-polling-units', ['--file' => $this->csv, '--report' => $this->report]);

    expect($this->placeholder->fresh())->not->toBeNull()
        ->and($this->placeholder->fresh()->is_active)->toBeFalse();
});

test('reports Lagos units whose ward is not in the app and ignores other states', function () {
    $this->artisan('geography:import-polling-units', ['--file' => $this->csv, '--report' => $this->report])
        ->assertSuccessful();

    $report = file_get_contents($this->report);

    expect($report)->toContain('KEKE MARKET')
        ->and($report)->not->toContain('INABE')
        ->and(PollingUnit::query()->where('name', 'KEKE MARKET')->exists())->toBeFalse();
});

test('dry run writes nothing', function () {
    $this->artisan('geography:import-polling-units', ['--file' => $this->csv, '--report' => $this->report, '--dry-run' => true])
        ->assertSuccessful();

    expect(PollingUnit::query()->count())->toBe(1)
        ->and($this->placeholder->fresh()->is_active)->toBeTrue();
});

test('re-running the import is idempotent', function () {
    $this->artisan('geography:import-polling-units', ['--file' => $this->csv, '--report' => $this->report]);
    $this->artisan('geography:import-polling-units', ['--file' => $this->csv, '--report' => $this->report]);

    expect(PollingUnit::query()->where('ward_id', $this->isaleOdo->id)->count())->toBe(3);
});

test('can create missing INEC wards and import their units', function () {
    $this->artisan('geography:import-polling-units', ['--file' => $this->csv, '--report' => $this->report, '--create-missing-wards' => true])
        ->assertSuccessful();

    $keke = Ward::query()->where('name', 'Keke')->first();

    expect($keke)->not->toBeNull()
        ->and($keke->local_government_id)->toBe($this->isaleOdo->local_government_id)
        ->and($keke->pollingUnits()->pluck('name')->all())->toBe(['KEKE MARKET'])
        ->and(Ward::query()->where('name', 'like', '%Olamaboro%')->exists())->toBeFalse()
        ->and(Ward::query()->where('local_government_id', $this->festac->local_government_id)->count())->toBe(1);
});
