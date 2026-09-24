<?php

namespace App\Livewire\Dashboard;

use App\Services\Dashboard\MembershipMetrics;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Command Centre')]
class CommandCentre extends Component
{
    public function mount(): void
    {
        $user = Auth::user();
        abort_unless($user && ($user->isStateManager() || $user->isAdmin()), 403);
    }

    public function render(MembershipMetrics $metrics)
    {
        $user = Auth::user();
        abort_unless($user, 403);

        return view('livewire.dashboard.command-centre', [
            'user' => $user,
            'metrics' => $metrics->forCommandCentre($user),
        ]);
    }
}
