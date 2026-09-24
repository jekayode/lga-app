<?php

namespace App\Livewire\Agents;

use App\Enums\AgentApplicationStatus;
use App\Models\AgentApplication;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Become an Agent')]
class Apply extends Component
{
    public string $motivation = '';

    public function mount(): void
    {
        $user = Auth::user();
        abort_unless($user?->isCommunityMember(), 403);
    }

    public function submit(): mixed
    {
        $user = Auth::user();
        abort_unless($user?->isCommunityMember(), 403);

        $this->validate([
            'motivation' => ['required', 'string', 'min:20', 'max:2000'],
        ]);

        if ($user->agentApplications()->where('status', AgentApplicationStatus::Pending)->exists()) {
            $this->addError('motivation', 'You already have a pending application.');

            return null;
        }

        AgentApplication::query()->create([
            'user_id' => $user->id,
            'status' => AgentApplicationStatus::Pending,
            'motivation' => $this->motivation,
        ]);

        session()->flash('status', 'Your agent application has been submitted.');

        return $this->redirect(route('dashboard'), navigate: true);
    }

    public function render()
    {
        return view('livewire.agents.apply');
    }
}
