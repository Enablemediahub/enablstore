<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="icon" type="image/png" href="{{ asset('images/Enablstore-cropped.png') }}">
        <link rel="apple-touch-icon" href="{{ asset('images/Enablstore-cropped.png') }}">

        <title inertia>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @routes
        @vite(['resources/js/app.ts', "resources/js/Pages/{$page['component']}.vue"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
        @php($isLoginPage = ($page['component'] ?? null) === 'Auth/Login')
        <footer class="{{ $isLoginPage ? 'border-white/10 bg-[#171717]/95 text-white shadow-[0_-8px_30px_rgba(0,0,0,0.18)] backdrop-blur' : 'border-t border-neutral-200 bg-white text-neutral-700' }} px-5 py-4 text-center text-xs" @if ($isLoginPage) style="position: fixed; inset-inline: 0; bottom: 0; z-index: 9999;" @endif>
            <p>
                Developed and designed by
                @if ($isLoginPage)
                    <a href="{{ route('super-admin.login') }}" class="inline-flex items-center rounded-md bg-[#e21b23] px-3 py-1.5 font-bold text-white transition hover:bg-[#c9151c]">DALE QUIST</a>
                @else
                    <span class="font-bold text-neutral-900">DALE QUIST</span>
                @endif
                <span class="mx-1" aria-hidden="true">[</span><span class="font-medium">Enable Technologies</span><span class="mx-1" aria-hidden="true">]</span>
            </p>
        </footer>
    </body>
</html>
