<?php

use App\Actions\ApproveAgentApplication;
use App\Enums\AgentApplicationStatus;
use App\Enums\Role;
use App\Livewire\Agents\Applications;
use App\Models\AgentApplication;
use App\Models\User;
use Livewire\Livewire;

test('ward coordinators only see applications from their ward', function () {
    $coordinator = makeWardCoordinator();
    $other = makeWardCoordinator();

    $memberInWard = User::factory()->create([
        'ward_id' => $coordinator->ward_id,
        'local_government_id' => $coordinator->local_government_id,
    ]);
    $memberOtherWard = User::factory()->create([
        'ward_id' => $other->ward_id,
        'local_government_id' => $other->local_government_id,
    ]);

    AgentApplication::factory()->create(['user_id' => $memberInWard->id]);
    AgentApplication::factory()->create(['user_id' => $memberOtherWard->id]);

    $this->actingAs($coordinator);

    Livewire::test(Applications::class)
        ->assertSee($memberInWard->name)
        ->assertDontSee($memberOtherWard->name);
});

test('approving an agent application promotes the member', function () {
    $coordinator = makeWardCoordinator();
    $member = User::factory()->create([
        'role' => Role::CommunityMember,
        'ward_id' => $coordinator->ward_id,
        'local_government_id' => $coordinator->local_government_id,
    ]);

    $application = AgentApplication::factory()->create([
        'user_id' => $member->id,
        'status' => AgentApplicationStatus::Pending,
    ]);

    app(ApproveAgentApplication::class)->approve($application, $coordinator);

    $member->refresh();

    expect($member->role)->toBe(Role::Agent)
        ->and($member->referral_code)->not->toBeNull()
        ->and($member->must_setup_two_factor)->toBeTrue()
        ->and($application->fresh()->status)->toBe(AgentApplicationStatus::Approved);
});

test('agents can visit the onboard page', function () {
    $agent = makeAgent();

    $this->actingAs($agent)
        ->get(route('agents.onboard'))
        ->assertOk();
});

test('community members cannot visit the onboard page', function () {
    $member = User::factory()->create();

    $this->actingAs($member)
        ->get(route('agents.onboard'))
        ->assertForbidden();
});
