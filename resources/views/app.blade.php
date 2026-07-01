<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title inertia>{{ config('app.name', 'Bechakeena') }}</title>

    @vite(['resources/js/app.jsx'])
    @inertiaHead
</head>
<body class="min-h-screen bg-gray-50 font-sans text-gray-900 antialiased">
    @inertia
</body>
</html>
