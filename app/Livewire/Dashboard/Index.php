<?php

namespace App\Livewire\Dashboard;

use App\Services\Dashboard\MembershipMetrics;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Dashboard')]
class Index extends Component
{
    public function render(MembershipMetrics $metrics)
    {
        $user = Auth::user();
        abort_unless($user, 403);

        return view('livewire.dashboard.index', [
            'user' => $user,
            'metrics' => $metrics->forUser($user),
        ]);
    }
}
