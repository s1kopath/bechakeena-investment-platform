{{-- Guest layout for investor auth screens (Blade + Livewire). --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">

    <title>{{ $title ?? 'Bechakeena' }}</title>

    @vite(['resources/css/app.css'])
    @livewireStyles
</head>
<body class="min-h-screen bg-gray-50 font-sans text-gray-900 antialiased">
    <div class="flex min-h-screen flex-col items-center justify-center px-4 py-12">
        <div class="w-full max-w-md">
            <a href="/" class="mb-8 block text-center text-2xl font-bold text-brand-700">Bechakeena</a>

            <div class="rounded-xl border border-gray-200 bg-white p-8 shadow-sm">
                {{ $slot }}
            </div>
        </div>
    </div>

    @livewireScripts
</body>
</html>
