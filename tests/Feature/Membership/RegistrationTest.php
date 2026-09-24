<?php

use App\Enums\Role;
use App\Livewire\Auth\BecomeAgent;
use App\Livewire\Auth\JoinAlliance;
use App\Models\User;
use App\Services\Membership\RegistersMember;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;

test('join alliance screen can be rendered', function () {
    $this->get(route('join'))->assertOk();
});

test('become an agent screen can be rendered', function () {
    $this->get(route('become-agent'))->assertOk();
});

test('community members can register with a valid agent referral code', function () {
    $agent = makeAgent();

    $user = app(RegistersMember::class)->registerCommunityMember(registrationPayload($agent));

    expect($user->role)->toBe(Role::CommunityMember)
        ->and($user->referred_by_id)->toBe($agent->id)
        ->and($user->otp_verified_at)->toBeNull();
});

test('community members cannot register with an invalid agent referral code', function () {
    $agent = makeAgent();
    $payload = registrationPayload($agent, ['referral_code' => 'INVALID1']);

    app(RegistersMember::class)->registerCommunityMember($payload);
})->throws(ValidationException::class);

test('agents can register with a coordinator referral code', function () {
    $coordinator = makeWardCoordinator();

    $user = app(RegistersMember::class)->registerAgent(registrationPayload($coordinator, [
        'email' => 'agent'.uniqid().'@example.com',
        'phone' => '081'.fake()->unique()->numerify('########'),
    ]));

    expect($user->role)->toBe(Role::Agent)
        ->and($user->referral_code)->not->toBeNull()
        ->and($user->must_setup_two_factor)->toBeTrue()
        ->and($user->referred_by_id)->toBe($coordinator->id);
});

test('agents cannot register without a coordinator referral code', function () {
    $agent = makeAgent();

    app(RegistersMember::class)->registerAgent(registrationPayload($agent, [
        'email' => 'bad-agent@example.com',
        'phone' => '08212345678',
    ]));
})->throws(ValidationException::class);

test('join alliance livewire form creates a community member', function () {
    $agent = makeAgent();
    $payload = registrationPayload($agent);

    Livewire::test(JoinAlliance::class)
        ->set('name', $payload['name'])
        ->set('email', $payload['email'])
        ->set('phone', $payload['phone'])
        ->set('password', $payload['password'])
        ->set('password_confirmation', $payload['password_confirmation'])
        ->set('referral_code', $payload['referral_code'])
        ->set('profession_id', $payload['profession_id'])
        ->set('local_government_id', $payload['local_government_id'])
        ->set('ward_id', $payload['ward_id'])
        ->set('polling_unit_id', $payload['polling_unit_id'])
        ->call('register')
        ->assertHasNoErrors()
        ->assertRedirect(route('dashboard'));

    $this->assertAuthenticated();
    expect(User::query()->where('email', $payload['email'])->exists())->toBeTrue();
});

test('become agent livewire form redirects to otp verification', function () {
    $coordinator = makeWardCoordinator();
    $payload = registrationPayload($coordinator, [
        'email' => 'newagent'.uniqid().'@example.com',
        'phone' => '083'.fake()->unique()->numerify('########'),
    ]);

    Livewire::test(BecomeAgent::class)
        ->set('name', $payload['name'])
        ->set('email', $payload['email'])
        ->set('phone', $payload['phone'])
        ->set('password', $payload['password'])
        ->set('password_confirmation', $payload['password_confirmation'])
        ->set('referral_code', $payload['referral_code'])
        ->set('profession_id', $payload['profession_id'])
        ->set('local_government_id', $payload['local_government_id'])
        ->set('ward_id', $payload['ward_id'])
        ->set('polling_unit_id', $payload['polling_unit_id'])
        ->call('register')
        ->assertHasNoErrors()
        ->assertRedirect(route('otp.verify'));
});
