<?php

namespace Tests\Feature\Auth;

use App\Livewire\Auth\Login;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $this->get('/login')->assertOk()->assertSeeLivewire(Login::class);
    }

    public function test_user_can_login_with_email(): void
    {
        $user = User::factory()->create(['password' => Hash::make('Password123!')]);

        Livewire::test(Login::class)
            ->set('login', $user->email)
            ->set('password', 'Password123!')
            ->call('authenticate')
            ->assertRedirect(route('dashboard.index'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_user_can_login_with_phone(): void
    {
        $user = User::factory()->create([
            'phone' => '01755555555',
            'password' => Hash::make('Password123!'),
        ]);

        Livewire::test(Login::class)
            ->set('login', '01755555555')
            ->set('password', 'Password123!')
            ->call('authenticate')
            ->assertRedirect(route('dashboard.index'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_user_cannot_login_with_wrong_password(): void
    {
        $user = User::factory()->create(['password' => Hash::make('Password123!')]);

        Livewire::test(Login::class)
            ->set('login', $user->email)
            ->set('password', 'wrong-password')
            ->call('authenticate')
            ->assertHasErrors('login');

        $this->assertGuest();
    }

    public function test_deactivated_user_cannot_login(): void
    {
        $user = User::factory()->deactivated()->create(['password' => Hash::make('Password123!')]);

        Livewire::test(Login::class)
            ->set('login', $user->email)
            ->set('password', 'Password123!')
            ->call('authenticate')
            ->assertHasErrors('login');

        $this->assertGuest();
    }

    public function test_login_is_throttled_after_too_many_attempts(): void
    {
        $user = User::factory()->create(['password' => Hash::make('Password123!')]);

        $component = Livewire::test(Login::class)->set('login', $user->email);

        foreach (range(1, 5) as $ignored) {
            $component->set('password', 'wrong-password')->call('authenticate');
        }

        // 6th attempt with the CORRECT password is still rejected — proving throttle, not creds.
        $component->set('password', 'Password123!')->call('authenticate')->assertHasErrors('login');
        $this->assertGuest();
    }

    public function test_authenticated_user_can_logout(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/logout')->assertRedirect('/');
        $this->assertGuest();
    }

    public function test_guest_is_redirected_from_dashboard_to_login(): void
    {
        $this->get('/dashboard')->assertRedirect(route('login'));
    }

    public function test_authenticated_user_is_redirected_away_from_login(): void
    {
        $this->actingAs(User::factory()->create())->get('/login')->assertRedirect('/dashboard');
    }
}
