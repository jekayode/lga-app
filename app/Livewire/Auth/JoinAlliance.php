<?php

namespace App\Livewire\Auth;

use App\Enums\Role;
use App\Models\LocalGovernment;
use App\Models\PollingUnit;
use App\Models\Profession;
use App\Models\Ward;
use App\Services\Membership\RegistersMember;
use App\Services\Otp\OtpBroker;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.auth', ['maxWidth' => 'max-w-2xl'])]
#[Title('Join the Alliance')]
class JoinAlliance extends Component
{
    public string $name = '';

    public string $email = '';

    public string $phone = '';

    public string $password = '';

    public string $password_confirmation = '';

    public string $referral_code = '';

    public ?int $profession_id = null;

    public bool $has_disability = false;

    public string $disability_notes = '';

    public ?int $local_government_id = null;

    public ?int $ward_id = null;

    public ?int $polling_unit_id = null;

    public ?string $otp_channel = null;

    public string $turnstileToken = '';

    public function mount(): void
    {
        $this->referral_code = strtoupper((string) request()->query('ref', ''));
    }

    public function updatedLocalGovernmentId(): void
    {
        $this->ward_id = null;
        $this->polling_unit_id = null;
    }

    public function updatedWardId(): void
    {
        $this->polling_unit_id = null;
    }

    #[Computed]
    public function professions()
    {
        return Profession::query()->active()->get();
    }

    #[Computed]
    public function localGovernments()
    {
        return LocalGovernment::query()->active()->get();
    }

    #[Computed]
    public function wards()
    {
        if (! $this->local_government_id) {
            return collect();
        }

        return Ward::query()->active()->where('local_government_id', $this->local_government_id)->get();
    }

    #[Computed]
    public function pollingUnits()
    {
        if (! $this->ward_id) {
            return collect();
        }

        return PollingUnit::query()->active()->where('ward_id', $this->ward_id)->get();
    }

    #[Computed]
    public function otpChannels(): array
    {
        return app(OtpBroker::class)->enabledChannelsFor(Role::CommunityMember);
    }

    public function register(RegistersMember $registrar): mixed
    {
        try {
            $user = $registrar->registerCommunityMember([
                'name' => $this->name,
                'email' => $this->email,
                'phone' => $this->phone,
                'password' => $this->password,
                'password_confirmation' => $this->password_confirmation,
                'referral_code' => strtoupper(trim($this->referral_code)),
                'profession_id' => $this->profession_id,
                'has_disability' => $this->has_disability,
                'disability_notes' => $this->disability_notes,
                'local_government_id' => $this->local_government_id,
                'ward_id' => $this->ward_id,
                'polling_unit_id' => $this->polling_unit_id,
                'otp_channel' => $this->otp_channel,
                'turnstileToken' => $this->turnstileToken,
            ]);
        } catch (ValidationException $exception) {
            $this->setErrorBag($exception->validator->errors());
            $this->resetTurnstile();

            return null;
        }

        Auth::login($user);

        if (app(OtpBroker::class)->requiresOtp(Role::CommunityMember)) {
            return $this->redirect(route('otp.verify'), navigate: true);
        }

        return $this->redirect(route('dashboard'), navigate: true);
    }

    /**
     * Turnstile tokens are single-use, so issue a fresh challenge after a failed submit.
     */
    protected function resetTurnstile(): void
    {
        $this->turnstileToken = '';
        $this->dispatch('turnstile-reset');
    }

    public function render()
    {
        return view('livewire.auth.join-alliance');
    }
}
