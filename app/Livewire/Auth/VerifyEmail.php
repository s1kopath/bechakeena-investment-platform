<?php

namespace App\Livewire\Auth;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.guest')]
#[Title('Verify your email')]
class VerifyEmail extends Component
{
    public function mount(): void
    {
        if (Auth::user()?->hasVerifiedEmail()) {
            $this->redirect(route('dashboard.index'), navigate: true);
        }
    }

    /**
     * Resend the verification link.
     */
    public function resend(): void
    {
        if (Auth::user()->hasVerifiedEmail()) {
            $this->redirect(route('dashboard.index'), navigate: true);

            return;
        }

        Auth::user()->sendEmailVerificationNotification();

        session()->flash('status', 'verification-link-sent');
    }

    public function logout(): RedirectResponse
    {
        Auth::guard('web')->logout();

        session()->invalidate();
        session()->regenerateToken();

        return redirect('/');
    }

    public function render()
    {
        return view('livewire.auth.verify-email');
    }
}
