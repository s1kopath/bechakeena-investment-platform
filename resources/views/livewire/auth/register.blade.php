<div>
    <h1 class="text-xl font-semibold text-gray-900">Create your account</h1>
    <p class="mt-1 text-sm text-gray-500">Start investing with Bechakeena.</p>

    <form wire:submit="register" class="mt-6 space-y-4">
        <x-text-field label="Full name" name="name" wire:model="name" autocomplete="name" autofocus />
        <x-text-field label="Email" name="email" type="email" wire:model="email" autocomplete="email" />
        <x-text-field
            label="Phone"
            name="phone"
            type="tel"
            wire:model="phone"
            autocomplete="tel"
            placeholder="+8801712345678"
        />
        <x-text-field label="Password" name="password" type="password" wire:model="password" autocomplete="new-password" />
        <x-text-field
            label="Confirm password"
            name="password_confirmation"
            type="password"
            wire:model="password_confirmation"
            autocomplete="new-password"
        />

        <div>
            <label class="flex items-start gap-2 text-sm text-gray-600">
                <input type="checkbox" wire:model="terms" class="mt-0.5 rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                <span>
                    I agree to the
                    <a href="/terms" class="text-brand-600 hover:text-brand-700">terms &amp; conditions</a>.
                </span>
            </label>
            @error('terms')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <x-primary-button target="register" class="w-full">Create account</x-primary-button>
    </form>

    <p class="mt-4 text-center text-sm text-gray-500">
        Already have an account?
        <a href="{{ route('login') }}" class="font-medium text-brand-600 hover:text-brand-700">Log in</a>
    </p>
</div>
