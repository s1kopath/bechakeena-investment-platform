<?php

namespace Tests\Feature\Auth;

use App\Enums\KycStatus;
use App\Livewire\Auth\Register;
use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $this->get('/register')
            ->assertOk()
            ->assertSeeLivewire(Register::class);
    }

    public function test_new_user_can_register_and_is_sent_a_verification_email(): void
    {
        Notification::fake();

        Livewire::test(Register::class)
            ->set('name', 'Asad Rahman')
            ->set('email', 'asad@example.com')
            ->set('phone', '01712345678')
            ->set('password', 'Password123!')
            ->set('password_confirmation', 'Password123!')
            ->set('terms', true)
            ->call('register')
            ->assertRedirect(route('dashboard.index'));

        $this->assertAuthenticated();

        $user = User::where('email', 'asad@example.com')->first();
        $this->assertNotNull($user);
        $this->assertSame('01712345678', $user->phone);
        $this->assertSame(KycStatus::Pending, $user->kyc_status);
        $this->assertNull($user->email_verified_at);

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_registration_requires_unique_email_and_phone(): void
    {
        User::factory()->create(['email' => 'taken@example.com', 'phone' => '01799999999']);

        Livewire::test(Register::class)
            ->set('name', 'Dupe')
            ->set('email', 'taken@example.com')
            ->set('phone', '01799999999')
            ->set('password', 'Password123!')
            ->set('password_confirmation', 'Password123!')
            ->set('terms', true)
            ->call('register')
            ->assertHasErrors(['email', 'phone']);

        $this->assertGuest();
    }

    public function test_registration_requires_accepted_terms(): void
    {
        Livewire::test(Register::class)
            ->set('name', 'No Terms')
            ->set('email', 'noterms@example.com')
            ->set('phone', '01712345670')
            ->set('password', 'Password123!')
            ->set('password_confirmation', 'Password123!')
            ->set('terms', false)
            ->call('register')
            ->assertHasErrors('terms');

        $this->assertGuest();
    }

    public function test_registration_rejects_invalid_phone(): void
    {
        Livewire::test(Register::class)
            ->set('name', 'Bad Phone')
            ->set('email', 'badphone@example.com')
            ->set('phone', '12345')
            ->set('password', 'Password123!')
            ->set('password_confirmation', 'Password123!')
            ->set('terms', true)
            ->call('register')
            ->assertHasErrors('phone');
    }

    public function test_registration_renders_verification_email_without_route_errors(): void
    {
        // No Notification::fake(): the real VerifyEmail notification renders and builds the
        // signed `verification.verify` URL. Guards against the missing-route 500 regression.
        Livewire::test(Register::class)
            ->set('name', 'Real Send')
            ->set('email', 'realsend@example.com')
            ->set('phone', '+8801712345699')
            ->set('password', 'Password123!')
            ->set('password_confirmation', 'Password123!')
            ->set('terms', true)
            ->call('register')
            ->assertRedirect(route('dashboard.index'));

        $this->assertAuthenticated();
    }
}
