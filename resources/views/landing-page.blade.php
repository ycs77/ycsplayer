<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>ycsPlayer</title>

    <meta name="description" content="線上影音點播包廂">
    <meta property="og:title" content="ycsPlayer">
    <meta property="og:description" content="線上影音點播包廂">
    <meta property="og:image" content="{{ config('app.url') }}/og-20231016.png">
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

        <footer class="py-4 text-blue-300/50 text-center flex justify-between items-center flex-col space-y-2 sm:py-6 sm:flex-row sm:space-y-0">
            <div>
                ✨ Created by <a href="https://twitter.com/ycs77_lucas" target="_blank" class="text-blue-300/75 hover:text-white font-bold">Lucas Yang</a>
            </div>

            <div class="flex space-x-4">
                <a class="text-blue-300/75 hover:text-white" href="https://twitter.com/ycs77_lucas" target="_blank" aria-label="Lucas Yang on Twitter">
                    <svg class="h-6 w-6" fill="currentColor" aria-hidden="true">
                        <path d="M8.29 20.251c7.547 0 11.675-6.253 11.675-11.675 0-.178 0-.355-.012-.53A8.348 8.348 0 0 0 22 5.92a8.19 8.19 0 0 1-2.357.646 4.118 4.118 0 0 0 1.804-2.27 8.224 8.224 0 0 1-2.605.996 4.107 4.107 0 0 0-6.993 3.743 11.65 11.65 0 0 1-8.457-4.287 4.106 4.106 0 0 0 1.27 5.477A4.073 4.073 0 0 1 2.8 9.713v.052a4.105 4.105 0 0 0 3.292 4.022 4.093 4.093 0 0 1-1.853.07 4.108 4.108 0 0 0 3.834 2.85A8.233 8.233 0 0 1 2 18.407a11.615 11.615 0 0 0 6.29 1.84"></path>
                    </svg>
                </a>

                <a class="text-blue-300/75 hover:text-white" href="https://github.com/ycs77/ycsplayer" target="_blank" aria-label="Lucas Yang on GitHub">
                    <svg class="h-6 w-6" fill="currentColor" aria-hidden="true">
                        <path d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0 1 12 6.844a9.59 9.59 0 0 1 2.504.337c1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.02 10.02 0 0 0 22 12.017C22 6.484 17.522 2 12 2Z"></path>
                    </svg>
                </a>
            </div>
        </footer>
    </div>

</body>
</html>
