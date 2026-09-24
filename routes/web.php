<?php

use App\Http\Controllers\PasswordResetLinkController;
use App\Http\Middleware\EnsureMembershipReady;
use App\Livewire\Agents\Applications;
use App\Livewire\Agents\Apply;
use App\Livewire\Agents\OnboardMember;
use App\Livewire\Auth\BecomeAgent;
use App\Livewire\Auth\JoinAlliance;
use App\Livewire\Auth\SetupTwoFactor;
use App\Livewire\Auth\VerifyOtp;
use App\Livewire\Dashboard\CommandCentre;
use App\Livewire\Dashboard\Index as Dashboard;
use App\Livewire\Posts\Index as PostsIndex;
use App\Livewire\Posts\Show as PostsShow;
use App\Models\Post;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome', [
        'latestPosts' => Post::query()
            ->published()
            ->with('category')
            ->limit(8)
            ->get(),
    ]);
})->name('home');

Route::middleware('guest')->group(function () {
    Route::get('join', JoinAlliance::class)->name('join');
    Route::get('become-an-agent', BecomeAgent::class)->name('become-agent');
    Route::post('forgot-password', PasswordResetLinkController::class)->name('password.email');
});

Route::get('news', PostsIndex::class)->name('posts.index');
Route::get('news/{post:slug}', PostsShow::class)->name('posts.show');

Route::middleware(['auth', EnsureMembershipReady::class])->group(function () {
    Route::get('otp/verify', VerifyOtp::class)->name('otp.verify');
    Route::get('two-factor/setup', SetupTwoFactor::class)->name('two-factor.setup');
    Route::get('dashboard', Dashboard::class)->name('dashboard');
    Route::get('command-centre', CommandCentre::class)->name('command-centre');
    Route::get('agents/onboard', OnboardMember::class)->name('agents.onboard');
    Route::get('agents/apply', Apply::class)->name('agents.apply');
    Route::get('agents/applications', Applications::class)->name('agents.applications');
});

require __DIR__.'/settings.php';
