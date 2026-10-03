<?php

use App\Livewire\Auth\JoinAlliance;
use App\Models\User;
use App\Services\Membership\RegistersMember;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;

beforeEach(function () {
    config([
        'services.turnstile.secret_key' => 'test-secret',
        'services.turnstile.allowed_hostnames' => ['lagosgrassrootsalliance.com'],
    ]);
});

function fakeSiteverify(array $overrides = []): void
{
    Http::fake([
        'challenges.cloudflare.com/*' => Http::response([
            'success' => true,
            'action' => 'join',
            'hostname' => 'lagosgrassrootsalliance.com',
            ...$overrides,
        ]),
    ]);
}

test('community members register when turnstile verifies the join action and hostname', function () {
    fakeSiteverify();

    $user = app(RegistersMember::class)->registerCommunityMember(registrationPayload(makeAgent(), [
        'skip_turnstile' => false,
        'turnstileToken' => 'valid-token',
    ]));

    expect($user->exists)->toBeTrue();

    Http::assertSent(fn ($request) => $request['response'] === 'valid-token' && $request['secret'] === 'test-secret');
});

test('registration is rejected when turnstile fails', function (array $siteverifyResponse) {
    fakeSiteverify($siteverifyResponse);

    $payload = registrationPayload(makeAgent(), [
        'skip_turnstile' => false,
        'turnstileToken' => 'some-token',
    ]);

    expect(fn () => app(RegistersMember::class)->registerCommunityMember($payload))
        ->toThrow(ValidationException::class, 'Security check failed');

    expect(User::query()->where('email', $payload['email'])->exists())->toBeFalse();
})->with([
    'unsuccessful token' => [['success' => false]],
    'action from another form' => [['action' => 'become_agent']],
    'foreign hostname' => [['hostname' => 'evil.example.com']],
]);

test('turnstile token is not spent when other fields are invalid', function () {
    fakeSiteverify();

    expect(fn () => app(RegistersMember::class)->registerCommunityMember(registrationPayload(makeAgent(), [
        'skip_turnstile' => false,
        'turnstileToken' => 'valid-token',
        'email' => 'not-an-email',
    ])))->toThrow(ValidationException::class);

    Http::assertNothingSent();
});

test('join form resets the turnstile widget after a failed submit', function () {
    fakeSiteverify(['success' => false]);
    $payload = registrationPayload(makeAgent());

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
        ->set('turnstileToken', 'used-token')
        ->call('register')
        ->assertHasErrors('turnstileToken')
        ->assertSet('turnstileToken', '')
        ->assertDispatched('turnstile-reset');
});
