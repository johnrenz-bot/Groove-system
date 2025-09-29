<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? config('app.name') }}</title>
@vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen antialiased theme-{{ $appTheme }} bg-surface text-foreground">
    <div class="min-h-screen flex flex-col">
        {{-- Global header (optional) --}}
        <header class="border-b border-divider/50 bg-layer/40 backdrop-blur-md">
            <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between">
                <div class="font-semibold">My App</div>
                <nav class="flex gap-2 text-sm">
                    <a href="{{ route('Admincontrol') }}"
                       class="flex items-center px-4 py-2 rounded-lg text-foreground/80 hover:text-foreground hover:bg-layer/60 transition duration-200 shadow-sm hover:shadow-md border border-transparent hover:border-divider/60">
                        <span class="mr-2">⚙️</span> Control
                    </a>
                </nav>
            </div>
        </header>

        {{-- Page content --}}
        <main class="max-w-7xl mx-auto w-full p-4 flex-1">
            @yield('content')
        </main>

        {{-- Footer (optional) --}}
        <footer class="p-4 border-t border-divider/40 text-center text-foreground/60 text-sm">
            &copy; {{ date('Y') }} Groove
        </footer>
    </div>

    @stack('scripts')
</body>
</html>
