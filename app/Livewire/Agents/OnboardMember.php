<?php

namespace App\Livewire\Agents;

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

#[Layout('layouts.app')]
#[Title('Onboard community member')]
class OnboardMember extends Component
{
    public string $name = '';

    public string $email = '';

    public string $phone = '';

    public string $password = '';

    public string $password_confirmation = '';

    public ?int $profession_id = null;

    public bool $has_disability = false;

    public string $disability_notes = '';

    public ?int $local_government_id = null;

    public ?int $ward_id = null;

    public ?int $polling_unit_id = null;

    public ?string $otp_channel = null;

    public function mount(): void
    {
        $user = Auth::user();
        abort_unless($user?->isAgent(), 403);

        $this->local_government_id = $user->local_government_id;
        $this->ward_id = $user->ward_id;
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

    public function save(RegistersMember $registrar): mixed
    {
        $agent = Auth::user();
        abort_unless($agent?->isAgent(), 403);

        try {
            $registrar->registerCommunityMember([
                'name' => $this->name,
                'email' => $this->email,
                'phone' => $this->phone,
                'password' => $this->password,
                'password_confirmation' => $this->password_confirmation,
                'referral_code' => $agent->referral_code,
                'profession_id' => $this->profession_id,
                'has_disability' => $this->has_disability,
                'disability_notes' => $this->disability_notes,
                'local_government_id' => $this->local_government_id,
                'ward_id' => $this->ward_id,
                'polling_unit_id' => $this->polling_unit_id,
                'otp_channel' => $this->otp_channel,
                'skip_turnstile' => true,
            ], $agent);
        } catch (ValidationException $exception) {
            $this->setErrorBag($exception->validator->errors());

            return null;
        }

        session()->flash('status', 'Community member onboarded successfully.');

        return $this->redirect(route('dashboard'), navigate: true);
    }

    public function render()
    {
        return view('livewire.agents.onboard-member');
    }
}
