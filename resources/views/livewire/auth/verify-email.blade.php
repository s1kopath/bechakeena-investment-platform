<div>
    <h1 class="text-xl font-semibold text-gray-900">Verify your email</h1>
    <p class="mt-1 text-sm text-gray-500">We sent a verification link to your email address.</p>

    @if (session('status') === 'verification-link-sent')
        <div class="mt-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
            A new verification link has been sent to your email address.
        </div>
    @endif

    <p class="mt-4 text-sm text-gray-600">
        Please click the link in that email to activate your account. If you didn't receive it,
        request another below.
    </p>

    <div class="mt-6 flex items-center justify-between gap-4">
        <x-primary-button type="button" wire:click="resend" target="resend">
            Resend verification email
        </x-primary-button>

        <button type="button" wire:click="logout" class="text-sm text-gray-500 hover:text-brand-700">
            Log out
        </button>
    </div>
</div>
