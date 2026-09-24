<?php

namespace App\Models;

use App\Enums\Role;
use Illuminate\Database\Eloquent\Model;

class OtpChannelSetting extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'role',
        'sms_enabled',
        'email_enabled',
        'whatsapp_enabled',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'role' => Role::class,
            'sms_enabled' => 'boolean',
            'email_enabled' => 'boolean',
            'whatsapp_enabled' => 'boolean',
        ];
    }

    public static function forRole(Role $role): self
    {
        return static::query()->firstOrCreate(
            ['role' => $role->value],
            [
                'sms_enabled' => false,
                'email_enabled' => $role->requiresMandatoryEmailOtpAndTwoFactor(),
                'whatsapp_enabled' => false,
            ]
        );
    }

    /**
     * @return list<string>
     */
    public function enabledChannels(): array
    {
        $channels = [];

        if ($this->sms_enabled) {
            $channels[] = 'sms';
        }

        if ($this->email_enabled) {
            $channels[] = 'email';
        }

        if ($this->whatsapp_enabled) {
            $channels[] = 'whatsapp';
        }

        return $channels;
    }

    public function requiresOtp(): bool
    {
        return $this->sms_enabled || $this->email_enabled || $this->whatsapp_enabled;
    }
}
