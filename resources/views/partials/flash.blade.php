{{-- Shared flash messages (Livewire/Blade). Classes are static so Tailwind can see them. --}}
@if (session('success'))
    <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800" role="alert">
        {{ session('success') }}
    </div>
@endif
@if (session('error'))
    <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert">
        {{ session('error') }}
    </div>
@endif
@if (session('warning'))
    <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800" role="alert">
        {{ session('warning') }}
    </div>
@endif
@if (session('info'))
    <div class="rounded-lg border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-800" role="alert">
        {{ session('info') }}
    </div>
@endif
