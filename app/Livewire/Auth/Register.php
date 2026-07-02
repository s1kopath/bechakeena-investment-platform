<?php

namespace App\Livewire\Auth;

use App\Actions\RegisterUser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.guest')]
#[Title('Create your account')]
class Register extends Component
{
    public string $name = '';

    public string $email = '';

    public string $phone = '';

    public string $password = '';

    public string $password_confirmation = '';

    public bool $terms = false;

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            // Global, country-agnostic: optional leading "+" then 7–15 digits (E.164 range).
            'phone' => ['required', 'string', 'regex:/^\+?\d{7,15}$/', 'unique:users,phone'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'terms' => ['accepted'],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return [
            'phone.regex' => 'Enter a valid phone number with country code (e.g. +8801712345678).',
            'terms.accepted' => 'You must accept the terms to continue.',
        ];
    }

    public function register(RegisterUser $registerUser)
    {
        // Normalize before validating: lowercase email, strip phone separators.
        $this->email = strtolower(trim($this->email));
        $this->phone = preg_replace('/[^\d+]/', '', $this->phone) ?? '';

        $validated = $this->validate();

        $user = $registerUser->handle([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => $validated['password'],
        ]);

        Auth::login($user);
        session()->regenerate();

        return redirect()->intended(route('dashboard.index'));
    }

    public function render()
    {
        return view('livewire.auth.register');
    }
}
