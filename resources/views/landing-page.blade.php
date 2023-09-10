<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>ycsPlayer</title>

    <meta name="description" content="線上影音點播包廂">
    <meta property="og:title" content="ycsPlayer">
    <meta property="og:description" content="線上影音點播包廂">
    <meta property="og:image" content="{{ config('app.url') }}/og-20230910.png">
    <meta name="twitter:card" content="summary_large_image">

    @vite('resources/css/landing-page.css')
</head>

<body class="font-sans antialiased h-full">

    <div class="container px-4 mx-auto h-full flex flex-col">
        <header class="mt-3 px-4 py-2.5 flex justify-between lg:px-6 lg:py-3">
            <a href="/" class="font-bold tracking-wide">
                ycsPlayer
            </a>

            <div class="flex items-center gap-4">
                <a href="/login" class="btn btn-primary btn-sm sm:btn-base">
                    登入
                </a>
                <a href="/register" class="btn btn-primary btn-sm sm:btn-base">
                    註冊
                </a>
            </div>
        </header>

        <div class="flex justify-center items-center grow min-h-0 text-center">
            <div>
                <svg class="w-20 h-20 mx-auto sm:w-24 sm:h-24" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24">
                    <g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"><path d="M21 12a9 9 0 1 1-18 0a9 9 0 0 1 18 0Z"/><path d="M15.91 11.672a.375.375 0 0 1 0 .656l-5.603 3.113a.375.375 0 0 1-.557-.328V8.887c0-.286.307-.466.557-.327l5.603 3.112Z"/></g>
                </svg>

                <h1 class="mt-4 text-6xl font-bold tracking-wide sm:text-7xl">ycsPlayer</h1>
                <h2 class="text-2xl text-blue-200/75 tracking-wide mt-4 sm:text-3xl sm:mt-8">線上影音點播包廂</h2>

                <div class="mt-4 flex justify-center items-center gap-4 sm:mt-8">
                    <a href="/login" class="btn btn-primary btn-lg">
                        登入
                    </a>
                    <a href="/register" class="btn btn-primary btn-lg">
                        註冊
                    </a>
                </div>
            </div>
        </div>

        <footer class="py-6 text-blue-300/50">
            ✨ Made by <a href="https://star-note-lucas.vercel.app/" target="_blank" class="text-blue-500">Lucas</a>
        </footer>
    </div>

</body>
</html>
