<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Actions\ConfirmTwoFactorAuthentication;
use Laravel\Fortify\Actions\EnableTwoFactorAuthentication;
use Laravel\Fortify\Actions\GenerateNewRecoveryCodes;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.auth')]
#[Title('Set up two-factor authentication')]
class SetupTwoFactor extends Component
{
    public string $code = '';

    #[Locked]
    public bool $showingQr = false;

    #[Locked]
    public ?string $qrCodeSvg = null;

    #[Locked]
    public ?string $secret = null;

    /** @var list<string> */
    #[Locked]
    public array $recoveryCodes = [];

    public function mount(EnableTwoFactorAuthentication $enable): void
    {
        $user = Auth::user();
        abort_unless($user, 403);
        abort_unless($user->role->requiresMandatoryEmailOtpAndTwoFactor(), 403);

        if ($user->hasCompletedMandatoryTwoFactor()) {
            $this->redirect(route('dashboard'), navigate: true);

            return;
        }

        if ($user->two_factor_secret === null) {
            $enable($user);
            $user->refresh();
        }

        $this->showingQr = true;
        $this->qrCodeSvg = $user->twoFactorQrCodeSvg();
        $this->secret = decrypt($user->two_factor_secret);
    }

    public function confirm(ConfirmTwoFactorAuthentication $confirm, GenerateNewRecoveryCodes $generateCodes): mixed
    {
        $this->validate([
            'code' => ['required', 'string'],
        ]);

        $user = Auth::user();
        abort_unless($user, 403);

        $confirm($user, $this->code);

        $user->forceFill(['must_setup_two_factor' => false])->save();

        if (empty($user->recoveryCodes())) {
            $generateCodes($user);
            $user->refresh();
        }

        $this->recoveryCodes = $user->recoveryCodes();

        return $this->redirect(route('dashboard'), navigate: true);
    }

    public function render()
    {
        return view('livewire.auth.setup-two-factor');
    }
}
