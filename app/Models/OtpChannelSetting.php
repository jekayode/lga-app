<?php

namespace App\Models;

use App\Enums\Role;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

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

    protected static function booted(): void
    {
        static::saved(fn (OtpChannelSetting $setting) => static::forgetCachedSettings($setting->role));
        static::deleted(fn (OtpChannelSetting $setting) => static::forgetCachedSettings($setting->role));
    }

    /**
     * Resolve the settings for a role, cached across requests and memoized within one.
     *
     * Attributes are cached as an array because cache unserialization of objects is disabled.
     */
    public static function forRole(Role $role): self
    {
        /** @var array<string, mixed> $attributes */
        $attributes = Cache::memo()->rememberForever(
            static::cacheKey($role),
            fn (): array => static::query()->firstOrCreate(
                ['role' => $role->value],
                [
                    'sms_enabled' => false,
                    'email_enabled' => $role->requiresMandatoryEmailOtpAndTwoFactor(),
                    'whatsapp_enabled' => false,
                ]
            )->getAttributes(),
        );

        return (new static)->newFromBuilder($attributes);
    }

    public static function forgetCachedSettings(Role $role): void
    {
        Cache::memo()->forget(static::cacheKey($role));
    }

    protected static function cacheKey(Role $role): string
    {
        return "otp_channel_settings.{$role->value}";
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
