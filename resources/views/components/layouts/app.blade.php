{{-- Public site layout (Blade + Livewire, server-rendered for SEO). --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ $title ?? 'Bechakeena Investment Platform' }}</title>
    <meta name="description" content="{{ $description ?? 'Invest in Bechakeena bulk laptop procurement cycles and earn fixed, tenure-based rebates.' }}">

    @vite(['resources/css/app.css'])
    @livewireStyles
</head>
<body class="min-h-screen bg-white font-sans text-gray-900 antialiased">
    {{ $slot }}

    @livewireScripts
</body>
</html>
