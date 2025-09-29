@php
    use Illuminate\Support\Str;

    // The profile being viewed
    $photoUrl  = $user->photo ? asset('storage/' . ltrim($user->photo, '/')) : null;
    $initials  = strtoupper(substr($user->firstname ?? '', 0, 1) . substr($user->lastname ?? '', 0, 1));

    // Role flags for the viewed profile
    $isCoach   = isset($user->coach_id);
    $isClient  = isset($user->client_id);
    $role      = $isCoach ? 'coach' : ($isClient ? 'client' : 'guest');

    // Authenticated viewer (coach area)
    $authCoach = auth('coach')->check() ? auth('coach')->user() : null;
    $client    = auth('client')->check() ? auth('client')->user() : null;

    // Notifications (for the viewed profile)
    $unreadNotifications = $user?->unreadNotifications ?? collect();

    // Address (coach profile may only have partials)
    $addressDisplay = collect([
        $user->street ?? null,
        $user->barangay_name ?? $user->barangay ?? null,
        $user->city_name ?? null,
        $user->province_name ?? null,
        $user->region_name ?? null,
    ])->filter()->implode(', ');

    // ---------- Portfolio helpers (added) ----------
    $fileUrl = fn ($path) => $path ? asset('storage/' . ltrim($path, '/')) : null;
    $isPdf   = fn ($path) => is_string($path) && Str::endsWith(Str::lower($path), '.pdf');

    // Build a flexible list of portfolio items. We keep public-facing items visible and
    // can optionally include IDs/selfies if you decide to show them (now hidden by default).
    $portfolioItems = [];

    // 1) Legacy single-image field from your original Coach UI
    if (!empty($user->portfolio)) {
        $portfolioItems[] = [
            'label' => 'Portfolio',
            'path'  => $user->portfolio,
            'url'   => $fileUrl($user->portfolio),
            'isPdf' => $isPdf($user->portfolio),
            'public'=> true,
        ];
    }

    // 2) Newer fields aligned with Client UI (optional if present in schema)
    if (!empty($user->portfolio_path)) {
        $portfolioItems[] = [
            'label' => 'Portfolio',
            'path'  => $user->portfolio_path,
            'url'   => $fileUrl($user->portfolio_path),
            'isPdf' => $isPdf($user->portfolio_path),
            'public'=> true,
        ];
    }
    if (!empty($user->valid_id_path)) {
        $portfolioItems[] = [
            'label' => 'Valid ID',
            'path'  => $user->valid_id_path,
            'url'   => $fileUrl($user->valid_id_path),
            'isPdf' => $isPdf($user->valid_id_path),
            // hide in public grid by default; flip to true if you want to show
            'public'=> false,
        ];
    }
    if (!empty($user->id_selfie_path)) {
        $portfolioItems[] = [
            'label' => 'Selfie with ID',
            'path'  => $user->id_selfie_path,
            'url'   => $fileUrl($user->id_selfie_path),
            'isPdf' => $isPdf($user->id_selfie_path),
            'public'=> false,
        ];
    }

    // Filter to what's visible in the grid
    $visiblePortfolio = array_values(array_filter($portfolioItems, fn($i) => $i['public'] && $i['url']));
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Groove – Coach Profile</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
    <link rel="icon" href="/image/bg/LOG.png" type="image/png" sizes="512x512">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen antialiased theme-{{ $appTheme }} bg-surface text-foreground">

<!-- HEADER -->
<header
    x-data="{ scrolled:false }"
    x-init="scrolled = window.scrollY > 10; window.addEventListener('scroll', () => scrolled = window.scrollY > 10)"
    :class="scrolled ? 'bg-card/80 backdrop-blur-sm shadow-lg border-b border-divider/40' : 'bg-transparent'"
    class="w-full py-4 px-8 fixed top-0 left-0 z-50 transition-all duration-300 ease-in-out"
>
    <div class="flex justify-between items-center max-w-7xl mx-auto">
        <div class="flex items-center gap-3">
            <img src="/image/wc/logo.png" alt="Logo" class="h-12 w-auto object-contain select-none"/>
        </div>

        <nav class="hidden md:flex space-x-4 text-sm font-medium">
            <a href="/coach/home"
               class="relative px-4 py-2 rounded-xl text-fg/70 hover:text-fg hover:bg-layer hover:border hover:border-divider/40 hover:shadow-md transition-all duration-300">Home</a>
            <a href="{{ route('talents') }}"
               class="relative px-4 py-2 rounded-xl text-fg/70 hover:text-fg hover:bg-layer hover:border hover:border-divider/40 hover:shadow-md transition-all duration-300">Talents</a>
            <a href="{{ route('about') }}"
               class="relative px-4 py-2 rounded-xl text-fg/70 hover:text-fg hover:bg-layer hover:border hover:border-divider/40 hover:shadow-md transition-all duration-300">About</a>
            <a href="{{ route('messages.index') }}"
               class="relative px-4 py-2 rounded-xl text-fg/70 hover:text-fg hover:bg-layer hover:border hover:border-divider/40 hover:shadow-md transition-all duration-300">Messages</a>
        </nav>

        <div class="flex items-center gap-4 text-fg/70">
            <!-- Notifications -->
            <div x-data="{ openNotif:false }" class="relative">
                <button @click="openNotif = !openNotif"
                        class="w-10 h-10 flex items-center justify-center rounded-full hover:bg-primary/15 transition relative"
                        aria-label="Notifications">
                    <i class="fa-regular fa-bell text-lg"></i>
                    @if ($unreadNotifications->count())
                        <span x-show="!openNotif"
                              class="absolute -top-1 -right-1 w-5 h-5 text-[10px] font-bold bg-primary text-fg rounded-full flex items-center justify-center">
                          {{ $unreadNotifications->count() }}
                        </span>
                    @endif
                </button>

                <div x-show="openNotif" @click.away="openNotif=false"
                     class="absolute right-0 mt-3 w-[33vh] max-h-96 bg-card border border-divider/40 rounded-xl shadow-xl p-4 space-y-3 z-50 overflow-y-auto"
                     x-transition>
                    <h4 class="text-fg text-base font-semibold border-b border-divider/40 pb-2">Notifications</h4>

                    @forelse ($unreadNotifications as $notif)
                        <div wire:click="$emit('markAsRead', '{{ $notif->id }}')"
                             class="bg-layer hover:bg-layer/80 rounded-lg p-3 text-sm cursor-pointer transition border border-divider/40">
                            <p class="font-medium text-fg">{{ $notif->data['title'] }}</p>
                            <p class="text-fg/80 text-xs mt-1">{{ $notif->data['message'] }}</p>
                            <p class="text-fg/60 text-xs mt-2">{{ $notif->created_at->diffForHumans() }}</p>
                        </div>
                    @empty
                        <div class="text-center text-fg/60 italic py-6 text-sm">You're all caught up</div>
                    @endforelse
                </div>
            </div>

            <!-- Profile dropdown (Auth Coach) -->
            <div x-data="{ open:false }" class="relative" x-cloak>
                <button @click="open = !open"
                        class="flex items-center gap-x-3 px-3 py-2 bg-card rounded-full hover:shadow-md active:scale-95 transition duration-200 focus:outline-none border border-divider/40"
                        aria-label="User Profile Menu"
                >
                    <div x-data="coachAvatarUpdater()" x-init="init()">
                        <template x-if="photoUrl">
                            <img :src="photoUrl" alt="Coach Avatar" class="w-8 h-8 rounded-full object-cover border-2 border-primary shadow-md">
                        </template>
                        <template x-if="!photoUrl">
                            <div class="w-8 h-8 flex items-center justify-center bg-layer rounded-full text-sm font-bold uppercase border border-divider/40 shadow-inner">
                                {{ strtoupper(substr($authCoach->firstname ?? 'C', 0, 1)) }}{{ strtoupper(substr($authCoach->lastname ?? 'C', 0, 1)) }}
                            </div>
                        </template>
                    </div>
                    <div class="flex items-center space-x-2 text-xs leading-none">
                        <span class="capitalize text-fg/80">
                            {{ strtolower($authCoach->firstname ?? 'coach') }} {{ $authCoach->lastname ?? '' }}
                        </span>
                        <i class="fa-solid fa-caret-down text-fg/60"></i>
                    </div>
                </button>

                <div x-show="open" @click.away="open=false"
                     class="absolute mt-2 w-60 bg-card border border-divider/40 rounded-2xl shadow-2xl z-50 overflow-hidden transition-all duration-300 ease-out origin-top"
                     x-transition role="menu" aria-label="Profile">
                    <div class="px-4 py-3 bg-layer backdrop-blur-sm border-b border-divider/40 text-center">
                        <p class="text-sm font-semibold text-fg">{{ $authCoach->firstname ?? 'Coach' }} {{ $authCoach->middlename ?? '' }}</p>
                        <p class="text-xs text-fg/60 mt-0.5">#{{ $authCoach->coach_id ?? '0000' }} &bullet; {{ ucfirst($authCoach->role ?? 'coach') }}</p>
                    </div>

                    <div class="flex flex-col px-3 py-2 space-y-1">
                        <a href="{{ route('coach.profile') }}"
                           class="flex items-center gap-2 text-fg/80 hover:text-fg hover:bg-layer px-3 py-1.5 rounded-xl transition shadow-sm">
                            <i class="fa-regular fa-user text-primary"></i>
                            <span class="text-sm">Profile</span>
                        </a>
                        <a href="{{ route('coach.profile.edit') }}"
                           class="flex items-center gap-2 text-fg/80 hover:text-fg hover:bg-layer px-3 py-1.5 rounded-xl transition shadow-sm">
                            <i class="fa-solid fa-gear text-primary"></i>
                            <span class="text-sm">Settings</span>
                        </a>
                    </div>

                    <div class="border-t border-divider/40 px-3 py-2">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                    class="w-full flex items-center gap-2 text-rose-500 hover:text-rose-600 hover:bg-rose-500/10 px-3 py-1.5 rounded-xl text-sm transition shadow-sm">
                                <i class="fa-solid fa-arrow-right-from-bracket text-sm"></i>
                                <span>Logout</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Mobile -->
            <div class="md:hidden">
                <details class="group">
                    <summary class="list-none w-10 h-10 grid place-items-center rounded-full border border-divider/40 bg-card hover:bg-layer cursor-pointer">
                        <i class="fa-solid fa-bars"></i>
                    </summary>
                    <div class="absolute right-8 mt-2 w-60 rounded-2xl border border-divider/40 bg-card/95 backdrop-blur-md shadow-xl p-2 z-50">
                        <a href="/coach/home" class="flex items-center gap-2 rounded-xl px-3 py-2 text-sm text-fg/80 hover:bg-layer">Home</a>
                        <a href="{{ route('talents') }}" class="flex items-center gap-2 rounded-xl px-3 py-2 text-sm text-fg/80 hover:bg-layer">Talents</a>
                        <a href="{{ route('about') }}" class="flex items-center gap-2 rounded-xl px-3 py-2 text-sm text-fg/80 hover:bg-layer">About</a>
                        <a href="{{ route('messages.index') }}" class="flex items-center gap-2 rounded-xl px-3 py-2 text-sm text-fg/80 hover:bg-layer">Messages</a>
                    </div>
                </details>
            </div>
        </div>
    </div>
</header>

<main class="max-w-7xl mx-auto px-6 md:px-10 pt-24 pb-16"
      x-data="{ tab: 'info', showPortfolio:false, lightbox:{open:false, type:'image', src:null, title:null} }">
    <!-- HERO -->
    <section class="bg-card border border-divider/40 p-6 rounded-3xl text-center shadow-lg">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 md:gap-0">
            <div class="flex items-center gap-6">
                <div class="w-24 h-24 rounded-full overflow-hidden shadow-lg bg-layer">
                    @if ($photoUrl)
                        <img src="{{ $photoUrl }}" alt="Photo" class="w-full h-full object-cover">
                    @else
                        <div class="flex items-center justify-center h-full w-full text-3xl font-bold text-fg/60">{{ $initials }}</div>
                    @endif
                </div>

                <div class="text-left space-y-1">
                    <h1 class="text-3xl font-extrabold tracking-tight leading-snug">
                        {{ $user->firstname }} {{ $user->middlename }} {{ $user->lastname }}
                    </h1>

                    <div class="flex items-center flex-wrap gap-2 text-sm">
                        <div class="bg-layer border border-divider/40 px-3 py-1.5 rounded-xl shadow-md">
                            <span class="text-sm uppercase font-semibold tracking-wider">
                                @if(isset($user->id)) ID {{ $user->id }}
                                @elseif(isset($user->coach_id)) ID {{ $user->coach_id }}
                                @elseif(isset($user->client_id)) ID {{ $user->client_id }}
                                @else ID N/A @endif
                            </span>
                        </div>

                          @php $verifiedLabel = $isCoach ? 'Verified Coach' : ($isClient ? 'Verified Client' : 'Verified'); @endphp
@if (($isCoach || $isClient) && !empty($user->account_verified))
    <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-green-100 text-green-800 text-sm font-semibold shadow-md"
          title="{{ $user->approved_at ? 'Approved: ' . \Carbon\Carbon::parse($user->approved_at)->toDayDateTimeString() : 'Verified account' }}">
        <i class="fa-solid fa-circle-check text-green-600"></i>
        {{ $verifiedLabel }}
    </span>
@endif

        
                    </div>
                </div>
            </div>

            <div class="mt-2 md:mt-0 flex items-center gap-3">
                <a href="{{ route('messages.index') }}"
                   class="px-4 py-2 bg-layer border border-divider/40 rounded-xl hover:opacity-90 shadow text-fg text-sm">
                    Message
                </a>

                <p class="px-4 py-2 border border-divider/40 rounded-full text-center w-fit mx-auto md:mx-0 text-sm">
                    {{ ucfirst($user->role) }}
                </p>
            </div>
        </div>
    </section>

    <!-- CONTENT GRID -->
    <div class="max-w-7xl mx-auto py-6 lg:grid lg:grid-cols-4 lg:gap-4">
        <div class="lg:col-span-3">
            <!-- Tabs -->
            <nav class="flex justify-center space-x-2 text-sm font-medium mt-2">
                <button @click="tab='info'"
                        :class="tab==='info' ? 'bg-card text-fg shadow-sm' : 'bg-layer text-fg/70 hover:text-fg'"
                        class="px-4 py-1.5 rounded-lg border border-divider/40">Info</button>

                <button @click="tab='posts'"
                        :class="tab==='posts' ? 'bg-card text-fg shadow-sm' : 'bg-layer text-fg/70 hover:text-fg'"
                        class="px-4 py-1.5 rounded-lg border border-divider/40">Posts</button>

                @if($isCoach)
                    <button @click="tab='appointment'"
                            :class="tab==='appointment' ? 'bg-card text-fg shadow-sm' : 'bg-layer text-fg/70 hover:text-fg'"
                            class="px-4 py-1.5 rounded-lg border border-divider/40">Appointment</button>

                    <button @click="tab='feedback'"
                            :class="tab==='feedback' ? 'bg-card text-fg shadow-sm' : 'bg-layer text-fg/70 hover:text-fg'"
                            class="px-4 py-1.5 rounded-lg border border-divider/40">Ratings & Feedback</button>
                @endif
            </nav>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
                <!-- INFO -->
                <section x-show="tab==='info'" x-transition class="lg:col-span-2 space-y-6">
                    @if($isCoach && !empty($user->about))
                        <div class="bg-card border border-divider/40 p-6 rounded-3xl shadow">
                            <h3 class="text-xl font-semibold text-primary mb-4 border-b border-divider/40 pb-2 text-center">ABOUT / BIO</h3>
                            <p class="text-sm text-fg/80 text-center">{{ $user->about }}</p>
                        </div>
                    @endif

                    <div class="bg-card border border-divider/40 p-6 rounded-3xl shadow">
                        <h3 class="text-xl font-semibold text-primary mb-4 border-b border-divider/40 pb-2">Talent & Personal Info</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                            <div class="flex justify-between"><span class="text-fg/60">Talent:</span><span class="ml-2 bg-layer px-2 py-1 rounded-md">{{ $user->talent ?? $user->talents ?? 'N/A' }}</span></div>
                            <div class="flex justify-between"><span class="text-fg/60">Genre:</span><span class="ml-2 bg-layer px-2 py-1 rounded-md">{{ $user->genre ?? $user->genres ?? 'N/A' }}</span></div>
                            <div class="flex justify-between"><span class="text-fg/60">Birthday:</span>
                                <span class="ml-2 bg-layer px-2 py-1 rounded-md">
                                    {{ !empty($user->birthdate) ? \Carbon\Carbon::parse($user->birthdate)->format('F d, Y') : 'N/A' }}
                                </span>
                            </div>
                            <div class="flex justify-between"><span class="text-fg/60">Address:</span>
                                <span class="ml-2 bg-layer px-2 py-1 rounded-md">
                                    {{ $addressDisplay ?: (($user->address ?? null) ? $user->address.(!empty($user->barangay) ? ', '.$user->barangay : '') : 'N/A') }}
                                </span>
                            </div>
                            <div class="flex justify-between"><span class="text-fg/60">Contact:</span><span class="ml-2 bg-layer px-2 py-1 rounded-md">{{ $user->contact ?? 'N/A' }}</span></div>
                            <div class="flex justify-between"><span class="text-fg/60">Email:</span><span class="ml-2 bg-layer px-2 py-1 rounded-md">{{ $user->email ?? 'N/A' }}</span></div>
                        </div>
                    </div>

                    <div class="bg-card border border-divider/40 p-6 rounded-3xl shadow">
                        <h3 class="text-xl font-semibold text-primary mb-4 border-b border-divider/40 pb-2">Additional Info</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                            <div class="flex justify-between"><span class="text-fg/60">Appointment Price:</span>
                                <span class="ml-2 bg-layer px-2 py-1 rounded-md">{{ isset($user->appointment_price) ? '₱'.number_format($user->appointment_price,2) : 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between"><span class="text-fg/60">Session Duration:</span>
                                <span class="ml-2 bg-layer px-2 py-1 rounded-md">{{ $user->session_duration ?? 'N/A' }}{{ $user->session_duration ? ' mins' : '' }}</span>
                            </div>
                            <div class="flex justify-between"><span class="text-fg/60">Payment Method:</span>
                                <span class="ml-2 bg-layer px-2 py-1 rounded-md">{{ $user->payment_method ?? 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between"><span class="text-fg/60">Notice Hours:</span>
                                <span class="ml-2 bg-layer px-2 py-1 rounded-md">{{ $user->notice_hours ?? 'N/A' }}{{ $user->notice_hours ? ' hrs' : '' }}</span>
                            </div>
                            <div class="flex justify-between"><span class="text-fg/60">Notice Days:</span>
                                <span class="ml-2 bg-layer px-2 py-1 rounded-md">{{ $user->notice_days ?? 'N/A' }}{{ $user->notice_days ? ' days' : '' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- ====== PORTFOLIO (ADDED FUNCTION) ====== -->
                    @if($isCoach)
                        <div id="portfolio-section" class="w-full">
                            @if (count($visiblePortfolio) > 0)
                                <div x-show="showPortfolio" x-transition class="grid grid-cols-1 sm:grid-cols-1 lg:grid-cols-1  w-full">
                                    @foreach ($visiblePortfolio as $item)
                                        <div class="relative rounded-2xl overflow-hidden border border-divider/40 bg-card group">
                                            @if ($item['isPdf'])
                                                <a href="{{ $item['url'] }}" target="_blank" class="flex items-center justify-center aspect-[4/3] w-full">
                                                    <div class="text-center">
                                                        <i class="fa-regular fa-file-pdf text-5xl mb-2"></i>
                                                        <div class="text-xs opacity-80">Open PDF</div>
                                                    </div>
                                                </a>
                                            @else
                                                <img src="{{ $item['url'] }}" alt="{{ $item['label'] }}" class="w-full h-auto object-cover aspect-[4/3]">
                                                <button
                                                    class="absolute inset-0 opacity-0 group-hover:opacity-100 transition bg-card grid place-items-center"
                                                    @click="lightbox={open:true,type:'image',src:'{{ $item['url'] }}',title:'{{ $item['label'] }}'}"
                                                    title="View">
                                                    <i class="fa-solid fa-magnifying-glass-plus text-2xl text-fg"></i>
                                                </button>
                                            @endif
                                            <span class="absolute left-2 top-2 text-[11px] font-semibold bg-layer/90 border border-divider/40 rounded-full px-2 py-1">
                                                {{ $item['label'] }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="flex flex-col items-center justify-center text-fg/60 gap-2 p-6 rounded-lg border border-divider/40 bg-layer h-60 w-full">
                                    <i class="fa-regular fa-image text-3xl"></i>
                                    <p class="italic">No portfolio files added yet.</p>
                                </div>
                            @endif
                        </div>
                    @endif
                    <!-- ====== /PORTFOLIO ====== -->
                </section>

                <!-- POSTS -->
                <section x-show="tab==='posts'" x-transition class="bg-card p-6 rounded-xl border border-divider/40 shadow lg:col-span-2">
                    <h2 class="text-lg font-bold mb-4 text-fg">Posts</h2>

                    @if (!isset($posts) || $posts->isEmpty())
                        <div class="text-center text-fg/60">No posts yet.</div>
                    @else
                        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6">
                            @foreach ($posts as $post)
                                @php
                                    $isVideo = Str::endsWith($post->media_path, ['.mp4','.webm','.ogg']);
                                @endphp

                                <div class="rounded-2xl overflow-hidden border border-divider/40 bg-card shadow h-60 relative group">
                                    <div class="w-full h-full relative overflow-hidden">
                                        @if ($isVideo)
                                            <video controls class="w-full h-full object-cover">
                                                <source src="{{ $post->media_url }}" type="video/mp4">
                                            </video>
                                        @else
                                            <img src="{{ $post->media_url }}"
                                                 class="w-full h-full object-cover transition duration-500 group-hover:scale-105"
                                                 alt="Post Media">
                                        @endif

                                        <div class="absolute bottom-0 left-0 w-full bg-gradient-to-t from-[rgba(var(--surface-rgb),.9)] to-transparent px-4 py-3">
                                            <p class="text-xs text-fg/70">
                                                {{ ucfirst($post->role) }}: {{ $post->user_name }}
                                            </p>
                                            <p class="text-sm text-fg font-medium line-clamp-2">{{ $post->caption }}</p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </section>

                <!-- APPOINTMENT (kept) -->
                @if($isCoach)
                <section id="appointment-section"
                         x-show="tab==='appointment'"
                         x-transition
                         class="lg:col-span-2 bg-card p-6 rounded-xl border border-divider/40 shadow text-fg">
                    <h2 class="text-lg sm:text-xl font-bold mb-6">
                        Book a Coach {{ $user->firstname }} Appointment
                    </h2>

                    <div class="mb-4 p-4 bg-rose-600 text-white rounded shadow">
                        You must be logged in as a client to book an appointment.
                    </div>
                </section>
                @endif

                <!-- FEEDBACK -->
                <section x-show="tab==='feedback'" x-transition class="lg:col-span-2 bg-card border border-divider/40 p-6 rounded-3xl shadow">
                    <h2 class="text-base font-bold text-primary mb-2 border-b border-divider/40 pb-2">Ratings & Feedback</h2>
                    <div class="text-center text-fg/60">No reviews yet.</div>
                </section>
            </div>
        </div>

        <!-- ASIDE -->
        <aside class="w-full h-[500px] bg-card border border-divider/40 p-4 rounded-xl flex flex-col gap-4">
            @if ($isCoach && (count($visiblePortfolio) > 0))
                <button
                    @click="
                      tab = 'info'; showPortfolio = true;
                      $nextTick(() => { const el = document.getElementById('portfolio-section'); if (el) el.scrollIntoView({ behavior:'smooth', block:'start' }); });
                    "
                    class="flex items-center justify-center gap-2 px-6 py-3 bg-layer text-fg font-medium shadow hover:opacity-90 rounded-xl w-full">
                    <i class="fa-solid fa-bars"></i> View Portfolio
                </button>
            @endif

            @if ($isCoach)
                <button
                    @click="tab='appointment'; $nextTick(() => { const el = document.getElementById('appointment-section'); if (el) el.scrollIntoView({ behavior:'smooth', block:'start' }); })"
                    class="flex items-center justify-center gap-2 px-6 py-3 bg-layer text-fg font-medium shadow hover:opacity-90 rounded-xl w-full">
                    <i class="fa-regular fa-calendar"></i> Appointment
                </button>
            @endif
        </aside>
    </div>

    <!-- Lightbox -->
    <div
        x-show="lightbox.open"
        x-transition.opacity
        class="fixed inset-0 bg-black/70 backdrop-blur-sm z-[100]"
        @click.self="lightbox.open=false"
        x-cloak
    >
        <div class="max-w-5xl w-[95%] mx-auto mt-16 bg-card rounded-2xl border border-divider/40 shadow-2xl overflow-hidden">
            <div class="flex items-center justify-between px-4 py-3 border-b border-divider/40">
                <h4 class="text-sm font-semibold" x-text="lightbox.title || 'Preview'"></h4>
                <button class="text-fg/70 hover:text-fg" @click="lightbox.open=false"><i class="fa-solid fa-xmark text-lg"></i></button>
            </div>
            <div class="p-2">
                <template x-if="lightbox.type==='image'">
                    <img :src="lightbox.src" alt="" class="w-full h-auto rounded-lg">
                </template>
            </div>
        </div>
    </div>
</main>

<!-- Helpers -->
<script>
    function coachAvatarUpdater(){
        return {
            photoUrl: '{{ ($authCoach && $authCoach->photo) ? asset('storage/' . ltrim($authCoach->photo, '/')) : '' }}',
            fetchPhoto(){
                fetch("{{ route('coach.profile.photo') }}")
                    .then(r => r.json())
                    .then(d => { if(d.photo_url) this.photoUrl = d.photo_url; })
                    .catch(()=>{});
            },
            init(){
                this.fetchPhoto();
                setInterval(()=>this.fetchPhoto(), 5000);
            }
        }
    }
</script>
</body>
</html>
