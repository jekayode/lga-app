<?php

namespace App\Livewire\Agents;

use App\Actions\ApproveAgentApplication;
use App\Enums\AgentApplicationStatus;
use App\Models\AgentApplication;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Agent applications')]
class Applications extends Component
{
    public function mount(): void
    {
        $user = Auth::user();
        abort_unless($user && ($user->isWardCoordinator() || $user->isAdmin() || $user->isStateManager()), 403);
    }

    public function approve(int $applicationId, ApproveAgentApplication $action): void
    {
        $application = AgentApplication::query()->with('user')->findOrFail($applicationId);
        $this->authorizeReview($application);
        $action->approve($application, Auth::user());
        session()->flash('status', 'Application approved.');
    }

    public function reject(int $applicationId): void
    {
        $application = AgentApplication::query()->with('user')->findOrFail($applicationId);
        $this->authorizeReview($application);

        $application->update([
            'status' => AgentApplicationStatus::Rejected,
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        session()->flash('status', 'Application rejected.');
    }

    protected function authorizeReview(AgentApplication $application): void
    {
        $reviewer = Auth::user();
        abort_unless($reviewer, 403);

        if ($reviewer->isAdmin() || $reviewer->isStateManager()) {
            return;
        }

        abort_unless(
            $reviewer->isWardCoordinator()
            && $application->user->ward_id === $reviewer->ward_id,
            403
        );
    }

    public function render()
    {
        $user = Auth::user();

        $query = AgentApplication::query()->pending()->with('user')->latest();

        if ($user->isWardCoordinator()) {
            $query->whereHas('user', fn ($q) => $q->where('ward_id', $user->ward_id));
        }

        return view('livewire.agents.applications', [
            'applications' => $query->get(),
        ]);
    }
}
