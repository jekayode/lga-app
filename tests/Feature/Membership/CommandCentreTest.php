<?php

use App\Livewire\Dashboard\CommandCentre;
use App\Models\User;
use Livewire\Livewire;

test('state managers can open the command centre', function () {
    $manager = makeStateManager();

    $this->actingAs($manager)
        ->get(route('command-centre'))
        ->assertOk()
        ->assertSee('Command Centre')
        ->assertSee('Local Government Area breakdown');
});

test('admins can open the command centre', function () {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(CommandCentre::class)
        ->assertSuccessful()
        ->assertSee('Command Centre');
});

test('agents cannot open the command centre', function () {
    $agent = makeAgent();

    $this->actingAs($agent)
        ->get(route('command-centre'))
        ->assertForbidden();
});
