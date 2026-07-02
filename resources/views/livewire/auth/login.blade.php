<div>
    <h1 class="text-xl font-semibold text-gray-900">Log in</h1>
    <p class="mt-1 text-sm text-gray-500">Access your investor dashboard.</p>

    @if (session('status'))
        <div class="mt-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
            {{ session('status') }}
        </div>
    @endif

    <form wire:submit="authenticate" class="mt-6 space-y-4">
        <x-text-field label="Email or phone" name="login" wire:model="login" autocomplete="username" autofocus />
        <x-text-field label="Password" name="password" type="password" wire:model="password" autocomplete="current-password" />

        <div class="flex items-center justify-between">
            <label class="flex items-center gap-2 text-sm text-gray-600">
                <input type="checkbox" wire:model="remember" class="rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                Remember me
            </label>

            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="text-sm text-brand-600 hover:text-brand-700">
                    Forgot password?
                </a>
            @endif
        </div>

        <x-primary-button target="authenticate" class="w-full">Log in</x-primary-button>
    </form>

    <p class="mt-4 text-center text-sm text-gray-500">
        Don't have an account?
        <a href="{{ route('register') }}" class="font-medium text-brand-600 hover:text-brand-700">Register</a>
    </p>
</div>
