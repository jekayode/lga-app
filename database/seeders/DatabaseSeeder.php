<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            ProfessionSeeder::class,
            GeographySeeder::class,
            OtpChannelSettingSeeder::class,
            AdminUserSeeder::class,
            // News posts + galleries (R2 paths): php artisan db:seed --class=NewsContentSeeder
        ]);
    }
}
