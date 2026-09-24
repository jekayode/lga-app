<?php

namespace Database\Seeders;

use App\Models\LocalGovernment;
use App\Models\PollingUnit;
use App\Models\Ward;
use Illuminate\Database\Seeder;

class GeographySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        /** @var list<array{name: string, code: string, wards: list<array{name: string, code: string, polling_units: list<array{name: string, code: string, location?: string}>}>}> $geography */
        $geography = require __DIR__.'/data/lagos_geography.php';

        foreach ($geography as $lgaData) {
            $lga = LocalGovernment::query()->updateOrCreate(
                [
                    'state' => 'Lagos',
                    'name' => $lgaData['name'],
                ],
                [
                    'code' => $lgaData['code'],
                    'is_active' => true,
                ]
            );

            foreach ($lgaData['wards'] as $wardData) {
                $ward = Ward::query()->updateOrCreate(
                    [
                        'local_government_id' => $lga->id,
                        'name' => $wardData['name'],
                    ],
                    [
                        'code' => $wardData['code'],
                        'is_active' => true,
                    ]
                );

                foreach ($wardData['polling_units'] as $puData) {
                    $fullCode = sprintf(
                        '24/%s/%s/%s',
                        $lgaData['code'],
                        $wardData['code'],
                        $puData['code']
                    );

                    PollingUnit::query()->updateOrCreate(
                        [
                            'ward_id' => $ward->id,
                            'name' => $puData['name'],
                        ],
                        [
                            'code' => $fullCode,
                            'location' => $puData['location'] ?? null,
                            'is_active' => true,
                        ]
                    );
                }
            }
        }
    }
}
