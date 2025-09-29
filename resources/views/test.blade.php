@php
   
    $dbTheme = \App\Models\Setting::query()
        ->where('key', 'theme')
        ->value('value') ?? 'light';

    $appTheme = ($appTheme ?? null) ?: $dbTheme;
@endphp

<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Theme Test Page</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css','resources/js/app.js'])
</head>

<body class="min-h-screen antialiased theme-{{ $appTheme }} bg-surface text-foreground">
    <header class="p-6 border-b border-divider/40 bg-layer/40 backdrop-blur-md">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <h1 class="text-xl font-bold">Test Page</h1>

        
        </div>
    </header>

    <main class="max-w-4xl mx-auto py-12 px-6">
        <div class="p-6 rounded-2xl shadow-sm bg-card border border-divider/40">
            <h2 class="text-2xl font-bold mb-2">Welcome!</h2>
            <p class="text-foreground/80">
                This page follows the global theme:
                <b>{{ $appTheme }}</b>.
            </p>

            <div class="mt-6 flex gap-4">
                <button class="px-4 py-2 rounded-lg bg-priSmary text-primary-foreground hover:opacity-90">
                    Primary Action
                </button>
                <button class="px-4 py-2 rounded-lg border border-divider/60 hover:bg-layer/40">
                    Secondary Action
                </button>
            </div>
        </div>
    </main>

    <footer class="p-6 border-t border-divider/40 text-center text-foreground/60">
        &copy; {{ date('Y') }} Groove — Test Theme Page
    </footer>
</body>
</html>
