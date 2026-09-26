<?php

namespace App\Livewire\Auth;

use App\Models\LocalGovernment;
use App\Models\PollingUnit;
use App\Models\Profession;
use App\Models\Ward;
use App\Services\Membership\RegistersMember;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.auth', ['maxWidth' => 'max-w-2xl'])]
#[Title('Coordinators Corner')]
class BecomeAgent extends Component
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

    public function register(RegistersMember $registrar): mixed
    {
        try {
            $user = $registrar->registerAgent([
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
                'turnstileToken' => $this->turnstileToken,
                'cf-turnstile-response' => $this->turnstileToken,
                'skip_turnstile' => app()->environment('testing') || blank(config('services.turnstile.secret_key')),
            ]);
        } catch (ValidationException $exception) {
            $this->setErrorBag($exception->validator->errors());

            return null;
        }

        Auth::login($user);

        return $this->redirect(route('otp.verify'), navigate: true);
    }

    public function render()
    {
        return view('livewire.auth.become-agent');
    }
}
