<?php

namespace Tests\Feature\Auth;

use App\Livewire\Auth\VerifyEmail;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Auth\Notifications\VerifyEmail as VerifyEmailNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Livewire\Livewire;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_verification_notice_is_shown_to_unverified_user(): void
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)
            ->get('/verify-email')
            ->assertOk()
            ->assertSeeLivewire(VerifyEmail::class);
    }

    public function test_unverified_user_is_redirected_from_dashboard_to_notice(): void
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)->get('/dashboard')->assertRedirect(route('verification.notice'));
    }

    public function test_verified_user_can_access_the_dashboard(): void
    {
        $this->actingAs(User::factory()->create())->get('/dashboard')->assertOk();
    }

    public function test_email_can_be_verified_via_signed_link(): void
    {
        Event::fake();
        $user = User::factory()->unverified()->create();

        $url = URL::temporarySignedRoute('verification.verify', now()->addMinutes(60), [
            'id' => $user->id,
            'hash' => sha1($user->email),
        ]);

        $this->actingAs($user)->get($url)->assertRedirect(route('dashboard.index').'?verified=1');

        Event::assertDispatched(Verified::class);
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
    }

    public function test_email_is_not_verified_with_an_invalid_hash(): void
    {
        $user = User::factory()->unverified()->create();

        $url = URL::temporarySignedRoute('verification.verify', now()->addMinutes(60), [
            'id' => $user->id,
            'hash' => sha1('wrong-email'),
        ]);

        $this->actingAs($user)->get($url)->assertForbidden();
        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }

    public function test_verification_link_can_be_resent(): void
    {
        Notification::fake();
        $user = User::factory()->unverified()->create();

        Livewire::actingAs($user)
            ->test(VerifyEmail::class)
            ->call('resend');

        Notification::assertSentTo($user, VerifyEmailNotification::class);
    }

    public function test_verify_email_component_redirects_already_verified_user(): void
    {
        $user = User::factory()->create(); // verified by default

        Livewire::actingAs($user)
            ->test(VerifyEmail::class)
            ->assertRedirect(route('dashboard.index'));
    }
}
