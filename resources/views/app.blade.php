<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1">

    @inertiaHead

    <meta name="description" content="線上影音點播包廂">
    <meta property="og:title" content="ycsPlayer">
    <meta property="og:description" content="線上影音點播包廂">
    <meta property="og:image" content="{{ config('app.url') }}/og-20230910.png">
    <meta name="twitter:card" content="summary_large_image">

    @vite('resources/js/app.ts')
</head>

<body class="font-sans antialiased">
    @inertia
</body>
</html>
