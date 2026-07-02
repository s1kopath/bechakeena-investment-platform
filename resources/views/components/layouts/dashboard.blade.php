{{-- Authenticated investor layout (Blade + Livewire). --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">

    <title>{{ $title ?? 'Dashboard' }} — Bechakeena</title>

    @vite(['resources/css/app.css'])
    @livewireStyles
</head>
<body class="min-h-screen bg-gray-50 font-sans text-gray-900 antialiased">
    <header class="border-b border-gray-200 bg-white">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-4">
            <a href="/" class="text-lg font-semibold text-brand-700">Bechakeena</a>
            <nav class="flex items-center gap-6 text-sm font-medium text-gray-600">
                <a href="{{ route('dashboard.index') }}" class="hover:text-brand-700">Dashboard</a>
                <span class="text-gray-400">{{ auth()->user()?->name }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-gray-600 transition hover:text-brand-700">
                        Log out
                    </button>
                </form>
            </nav>
        </div>
    </header>

    <main class="mx-auto max-w-7xl space-y-6 px-6 py-8">
        @include('partials.flash')
        {{ $slot }}
    </main>

    @livewireScripts
</body>
</html>
