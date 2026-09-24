<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\OtpChannelSetting;
use Illuminate\Database\Seeder;

class OtpChannelSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (Role::cases() as $role) {
            OtpChannelSetting::query()->updateOrCreate(
                ['role' => $role->value],
                [
                    'sms_enabled' => false,
                    'email_enabled' => $role->requiresMandatoryEmailOtpAndTwoFactor(),
                    'whatsapp_enabled' => false,
                ]
            );
        }
    }
}
