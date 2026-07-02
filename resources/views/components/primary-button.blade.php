@props(['target' => null])

<button
    {{ $attributes->merge(['type' => 'submit'])->class('inline-flex items-center justify-center gap-2 rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60') }}
    @if ($target) wire:loading.attr="disabled" wire:target="{{ $target }}" @endif
>
    @if ($target)
        <svg wire:loading wire:target="{{ $target }}" class="h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
        </svg>
    @endif

    {{ $slot }}
</button>
