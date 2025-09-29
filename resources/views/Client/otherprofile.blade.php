@php
    use Illuminate\Support\Str;

    $photoUrl  = $user->photo ? asset('storage/' . $user->photo) : null;
    $initials  = strtoupper(substr($user->firstname ?? '', 0, 1) . substr($user->lastname ?? '', 0, 1));

    $isCoach   = isset($user->coach_id);
    $isClient  = isset($user->client_id);
    $role      = $isCoach ? 'coach' : ($isClient ? 'client' : 'guest');

    $client    = auth('client')->user();

    $unreadNotifications = $user?->unreadNotifications ?? collect();

    $feedbacks = $isCoach
        ? ($user->relationLoaded('feedbacks') ? $user->feedbacks : ($user->feedbacks()->with('user')->get() ?? collect()))
        : collect();

    $addressDisplay = collect([
        $user->street ?? null,
        $user->barangay_name ?? $user->barangay ?? null,
        $user->city_name ?? null,
        $user->province_name ?? null,
        $user->region_name ?? null,
    ])->filter()->implode(', ');

    $fileUrl = fn ($path) => $path ? asset('storage/' . ltrim($path, '/')) : null;
    $isPdf   = fn ($path) => is_string($path) && Str::endsWith(Str::lower($path), '.pdf');

    $portfolioItems = [];
    if (!empty($user->portfolio_path)) {
        $portfolioItems[] = [
            'label' => 'Portfolio',
            'path'  => $user->portfolio_path,
            'url'   => $fileUrl($user->portfolio_path),
            'isPdf' => $isPdf($user->portfolio_path),
        ];
    }
    if (!empty($user->valid_id_path)) {
        $portfolioItems[] = [
            'label' => 'Valid ID',
            'path'  => $user->valid_id_path,
            'url'   => $fileUrl($user->valid_id_path),
            'isPdf' => $isPdf($user->valid_id_path),
        ];
    }
    if (!empty($user->id_selfie_path)) {
        $portfolioItems[] = [
            'label' => 'Selfie with ID',
            'path'  => $user->id_selfie_path,
            'url'   => $fileUrl($user->id_selfie_path),
            'isPdf' => $isPdf($user->id_selfie_path),
        ];
    }
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Groove – Profile</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>

    <link rel="icon" href="/image/bg/LOG.png" type="image/png" sizes="512x512">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Optional: if your Tailwind build doesn't include animate-spin -->
    <style>
      .animate-spin { animation: spin 1s linear infinite; }
      @keyframes spin { to { transform: rotate(360deg); } }
    </style>
</head>

<body class="font-sans antialiased min-h-screen theme-{{ $appTheme }} bg-surface text-fg">

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

        <nav class="flex space-x-4 text-sm font-medium">
            <a href="/client/home"
               class="relative px-4 py-2 rounded-xl text-fg/70 hover:text-fg hover:bg-layer hover:border hover:border-divider/40 hover:shadow-md transition-all duration-300">Home</a>

            <a href="{{ route('talent') }}"
               class="relative px-4 py-2 rounded-xl text-fg/70 hover:text-fg hover:bg-layer hover:border hover:border-divider/40 hover:shadow-md transition-all duration-300">Talents</a>

            <a href="{{ route('about') }}"
               class="relative px-4 py-2 rounded-xl text-fg/70 hover:text-fg hover:bg-layer hover:border hover:border-divider/40 hover:shadow-md transition-all duration-300">About</a>

            <a href="{{ route('messages.index') }}"
               class="relative px-4 py-2 rounded-xl text-fg/70 hover:text-fg hover:bg-layer hover:border hover:border-divider/40 hover:shadow-md transition-all duration-300">Messages</a>
        </nav>

        <div class="flex items-center gap-4 text-fg/70">
            <div x-data="{ openNotif:false }" class="relative">
                <button @click="openNotif = !openNotif"
                        class="w-10 h-10 flex items-center justify-center rounded-full hover:bg-primary/15 transition relative">
                    <i class="fa-regular fa-bell text-lg"></i>
                    @if ($unreadNotifications->count())
                        <span x-show="!openNotif"
                              class="absolute -top-1 -right-1 w-5 h-5 text-[10px] font-bold bg-primary rounded-full flex items-center justify-center">
                          {{ $unreadNotifications->count() }}
                        </span>
                    @endif
                </button>

                <div x-show="openNotif" @click.away="openNotif=false"
                     class="absolute right-0 mt-3 w-[33vh] max-h-96 bg-card border border-divider/40 rounded-xl shadow-xl p-4 space-y-3 z-50 hover-scrollbar overflow-y-auto"
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

            <div x-data="{ open:false }" class="relative" x-cloak>
                <button @click="open = !open"
                        class="flex items-center gap-x-3 px-3 py-2 bg-card rounded-full hover:shadow-md active:scale-95 transition duration-200 focus:outline-none border border-divider/40">
                    <div x-data="avatarUpdater()" x-init="init()">
                        <template x-if="photoUrl">
                            <img :src="photoUrl" alt="User Avatar" class="w-8 h-8 rounded-full object-cover border-2 border-primary shadow-md">
                        </template>
                        <template x-if="!photoUrl">
                            <div class="w-8 h-8 flex items-center justify-center bg-layer rounded-full text-sm font-bold uppercase border border-divider/40 shadow-inner">
                                {{ strtoupper(substr($client->firstname ?? 'C', 0, 1)) }}{{ strtoupper(substr($client->middlename ?? 'C', 0, 1)) }}
                            </div>
                        </template>
                    </div>
                    <div class="flex items-center space-x-2 text-xs leading-none">
                        <span class="capitalize text-fg/80">{{ strtolower($client->firstname ?? 'client') }} {{ $client->middlename }}</span>
                        <i class="fa-solid fa-caret-down text-fg/60"></i>
                    </div>
                </button>

                <div x-show="open" @click.away="open=false"
                     class="absolute mt-2 w-60 bg-card border border-divider/40 rounded-2xl shadow-2xl z-50 overflow-hidden transition-all duration-300 ease-out origin-top"
                     x-transition>
                    <div class="px-4 py-3 bg-layer backdrop-blur-sm border-b border-divider/40 text-center">
                        <p class="text-sm font-semibold text-fg">{{ $client->firstname ?? 'Client' }} {{ $client->middlename ?? '' }}</p>
                        <p class="text-xs text-fg/60 mt-0.5">#{{ $client->client_id ?? '0000' }} &bullet; {{ ucfirst($client->role ?? 'client') }}</p>
                    </div>

                    <div class="flex flex-col px-3 py-2 space-y-1">
                        <a href="{{ route('profile') }}"
                           class="flex items-center gap-2 text-fg/80 hover:text-fg hover:bg-layer px-3 py-1.5 rounded-xl transition shadow-sm">
                            <i class="fa-regular fa-user text-primary"></i>
                            <span class="text-sm">Profile</span>
                        </a>
                        <a href="/client/profile/edit"
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

        </div>
    </div>
</header>

<main class="max-w-7xl mx-auto px-6 md:px-10 pt-24 pb-16" x-data="{ tab: 'info', showPortfolio:false, lightbox:{open:false, type:'image', src:null, title:null} }">
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
                    <h1 class="text-3xl font-extrabold tracking-tight leading-snug">{{ $user->firstname }} {{ $user->middlename }} {{ $user->lastname }}</h1>

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

            <div class="mt-2 md:mt-0">
                <p class="px-4 py-2 border border-divider/40 rounded-full text-center w-fit mx-auto md:mx-0">
                    {{ ucfirst($user->role) }}
                </p>
            </div>
        </div>
    </section>

    <div class="max-w-7xl mx-auto py-6 lg:grid lg:grid-cols-4 lg:gap-4">
        <div class="lg:col-span-3">
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
                <section x-show="tab==='info'" x-transition class="lg:col-span-2 space-y-6">
                    <div class="bg-card border border-divider/40 p-6 rounded-3xl shadow">
                        <h3 class="text-xl font-semibold text-primary mb-4 border-b border-divider/40 pb-2 text-center">ABOUT/BIO</h3>
                        <p class="text-sm text-fg/80 text-center">{{ $user->bio ?? $user->about ?? 'N/A' }}</p>
                    </div>

                    <div class="bg-card border border-divider/40 p-6 rounded-3xl shadow">
                        <h3 class="text-xl font-semibold text-primary mb-4 border-b border-divider/40 pb-2">Talent & Personal Info</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                            <div class="flex justify-between"><span class="text-fg/60">Talent(s):</span><span class="ml-2 bg-layer px-2 py-1 rounded-md">{{ $user->talents ?? 'N/A' }}</span></div>
                            <div class="flex justify-between"><span class="text-fg/60">Genre(s):</span><span class="ml-2 bg-layer px-2 py-1 rounded-md">{{ $user->genres ?? 'N/A' }}</span></div>
                            <div class="flex justify-between"><span class="text-fg/60">Birthday:</span><span class="ml-2 bg-layer px-2 py-1 rounded-md">{{ !empty($user->birthdate) ? \Carbon\Carbon::parse($user->birthdate)->format('F d, Y') : 'N/A' }}</span></div>
                            <div class="flex justify-between"><span class="text-fg/60">Address:</span><span class="ml-2 bg-layer px-2 py-1 rounded-md">{{ $addressDisplay ?: 'N/A' }}{{ !empty($user->postal_code) ? ' • '.$user->postal_code : '' }}</span></div>
                            <div class="flex justify-between"><span class="text-fg/60">Contact:</span><span class="ml-2 bg-layer px-2 py-1 rounded-md">{{ $user->contact ?? 'N/A' }}</span></div>
                            <div class="flex justify-between"><span class="text-fg/60">Email:</span><span class="ml-2 bg-layer px-2 py-1 rounded-md">{{ $user->email ?? 'N/A' }}</span></div>
                        </div>
                    </div>

                    <div class="bg-card border border-divider/40 p-6 rounded-3xl shadow">
                        <h3 class="text-xl font-semibold text-primary mb-4 border-b border-divider/40 pb-2">Additional Info</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                            <div class="flex justify-between"><span class="text-fg/60">Service Fee:</span><span class="ml-2 bg-layer px-2 py-1 rounded-md">{{ isset($user->service_fee) ? '₱'.number_format($user->service_fee,2) : 'N/A' }}</span></div>
                            <div class="flex justify-between"><span class="text-fg/60">Session Duration:</span><span class="ml-2 bg-layer px-2 py-1 rounded-md">{{ $user->duration ?? 'N/A' }}</span></div>
                            <div class="flex justify-between"><span class="text-fg/60">Payment Type:</span><span class="ml-2 bg-layer px-2 py-1 rounded-md">{{ $user->payment ?? 'N/A' }}</span></div>
                            <div class="flex justify-between"><span class="text-fg/60">Payment Method:</span><span class="ml-2 bg-layer px-2 py-1 rounded-md">{{ $user->method ?? 'N/A' }}</span></div>
                            <div class="flex justify-between"><span class="text-fg/60">Notice Hours:</span><span class="ml-2 bg-layer px-2 py-1 rounded-md">{{ $user->notice_hours ?? 'N/A' }} hrs</span></div>
                            <div class="flex justify-between"><span class="text-fg/60">Notice Days:</span><span class="ml-2 bg-layer px-2 py-1 rounded-md">{{ $user->notice_days ?? 'N/A' }} days</span></div>
                        </div>
                    </div>

                    @if($isCoach)
                    <div id="portfolio-section" class="w-full">
                        <template x-if="{{ count($portfolioItems) > 0 ? 'true' : 'false' }}">
                            <div x-show="showPortfolio" x-transition
                                 class="grid grid-cols-1 gap-4 w-full">
                                @foreach ($portfolioItems as $item)
                                    @if (!in_array($item['label'], ['Valid ID', 'Selfie with ID']))
                                        <div class="thumb group w-full">
                                            @if ($item['isPdf'])
                                                <a href="{{ $item['url'] }}" target="_blank"
                                                   class="absolute inset-0 flex items-center justify-center w-full">
                                                    <div class="text-center">
                                                        <i class="fa-regular fa-file-pdf text-5xl mb-2"></i>
                                                        <div class="text-xs opacity-80">Open PDF</div>
                                                    </div>
                                                </a>
                                            @else
                                                <img src="{{ $item['url'] }}"
                                                     alt="{{ $item['label'] }}"
                                                     class="w-full h-auto object-cover">
                                                <button
                                                    class="thumb-overlay w-full"
                                                    @click="lightbox={open:true,type:'image',src:'{{ $item['url'] }}',title:'{{ $item['label'] }}'}"
                                                    title="View">
                                                    <i class="fa-solid fa-magnifying-glass-plus text-2xl"></i>
                                                </button>
                                            @endif
                                            <span class="badge">{{ $item['label'] }}</span>
                                        </div>
                                    @endif
                                @endforeach

                            </div>
                        </template>

                        @if (count($portfolioItems) === 0)
                            <div class="flex flex-col items-center justify-center text-fg/60 gap-2 p-6 rounded-lg border border-divider/40 bg-layer h-60 w-full">
                                <i class="fa-regular fa-image text-3xl"></i>
                                <p class="italic">No portfolio files added yet.</p>
                            </div>
                        @endif
                    </div>
                    @endif

                </section>

                <section x-show="tab==='posts'" x-transition class="bg-card p-6 rounded-xl border border-divider/40 shadow lg:col-span-2">
                    <h2 class="text-lg font-bold mb-4 text-fg">Posts</h2>
                    @if ($posts->isEmpty())
                        <div class="text-center text-fg/60">No posts yet.</div>
                    @else
                        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6">
                            @foreach ($posts as $post)
                                @php $isVideo = Str::endsWith($post->media_path, ['.mp4','.webm','.ogg']); @endphp
                                <div class="rounded-2xl overflow-hidden border border-divider/40 bg-card shadow h-60 relative group">
                                    <div class="w-full h-full relative overflow-hidden">
                                        @if ($isVideo)
                                            <video controls class="w-full h-full object-cover"><source src="{{ $post->media_url }}" type="video/mp4"></video>
                                        @else
                                            <img src="{{ $post->media_url }}" class="w-full h-full object-cover transition duration-500 group-hover:scale-105" alt="Post Media">
                                        @endif
                                        <div class="absolute bottom-0 left-0 w-full bg-gradient-to-t from-[rgba(var(--surface-rgb),.9)] to-transparent px-4 py-3">
                                            <p class="text-xs text-fg/70">{{ ucfirst($post->role) }}: {{ $post->user_name }}</p>
                                            <p class="text-sm text-fg font-medium line-clamp-2">{{ $post->caption }}</p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </section>

                @if($isCoach)
                <!-- APPOINTMENT SECTION (with loading states) -->
                <section id="appointment-section" x-show="tab==='appointment'" x-transition class="lg:col-span-2 bg-card p-6 rounded-xl border border-divider/40 shadow">
                    <h2 class="text-lg sm:text-xl font-bold mb-6">Book a Coach {{ $user->firstname }} Appointment</h2>

                    @if (session('success'))
                        <div class="mb-4 p-4 bg-emerald-600 rounded">{{ session('success') }}</div>
                    @elseif (session('error'))
                        <div class="mb-4 p-4 bg-rose-600 rounded">{{ session('error') }}</div>
                    @endif

                    @php
                        $clientId = optional(auth('client')->user())->client_id;
                        $existingAppointment = null;
                        if ($clientId && $isCoach) {
                            $existingAppointment = \App\Models\Appointment::where('client_id', $clientId)
                                ->where('coach_id', $user->coach_id)
                                ->whereIn('status', ['pending','confirmed'])
                                ->latest()->first();
                        }
                    @endphp

                    @if(!$client)
                        <div class="mb-4 p-4 bg-layer border border-divider/40 rounded text-sm">
                            Please <a class="text-primary underline" href="{{ route('login') }}">log in</a> as a client to book an appointment.
                        </div>
                    @elseif($existingAppointment)
                        <div class="mb-4 p-4 bg-layer border border-divider/40 rounded flex items-center gap-3">
                            <i class="fa-solid fa-circle-info text-primary"></i>
                            <span>You already have a pending or confirmed appointment with this coach.</span>
                        </div>

                        <!-- CANCEL FORM with loading -->
                        <form method="POST" action="{{ route('appointments.cancel', $existingAppointment->appointment_id) }}"
                              x-data="{ loading:false }"
                              @submit="if (!confirm('Cancel this appointment?')) { $event.preventDefault(); return } ; loading = true">
                            @csrf
                            <button type="submit"
                                    :disabled="loading"
                                    class="w-full bg-rose-600 hover:bg-rose-700 font-semibold py-3 px-6 rounded-md shadow disabled:opacity-70 disabled:cursor-not-allowed">
                                <span x-show="!loading">Cancel Appointment</span>
                                <span x-show="loading" class="inline-flex items-center gap-2">
                                    <i class="fa-solid fa-spinner animate-spin"></i>
                                    Cancelling...
                                </span>
                            </button>
                        </form>

                    @else
                        <!-- BOOKING FORM with loading + overlay -->
                        <form action="{{ route('appointments.store') }}" method="POST" class="space-y-4"
                              x-data="{ loading:false }"
                              @submit="loading = true">
                            @csrf
                            <input type="hidden" name="client_id" value="{{ $client->client_id }}">
                            <input type="hidden" name="coach_id" value="{{ $user->coach_id }}">
                            <input type="hidden" name="session_type" value="F2F">

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium mb-1">Session Type</label>
                                    <p class="px-4 py-2 rounded bg-layer border border-divider/40">Face-to-Face</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1">Talent(s)</label>
                                    <input type="text" name="talent" value="{{ $user->talents }}" readonly class="w-full px-4 py-2 rounded bg-layer border border-divider/40 cursor-not-allowed">
                                </div>
                            </div>

                            <div class="grid grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm font-medium mb-1">First Name</label>
                                    <input type="text" name="firstname" value="{{ old('firstname', $client->firstname) }}" class="w-full px-4 py-2 rounded bg-layer border border-divider/40" required>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1">Middle Name</label>
                                    <input type="text" name="middlename" value="{{ old('middlename', $client->middlename) }}" class="w-full px-4 py-2 rounded bg-layer border border-divider/40">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1">Last Name</label>
                                    <input type="text" name="lastname" value="{{ old('lastname', $client->lastname) }}" class="w-full px-4 py-2 rounded bg-layer border border-divider/40" required>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-1">Email</label>
                                <input type="email" name="email" value="{{ old('email', $client->email) }}" class="w-full px-4 py-2 rounded bg-layer border border-divider/40" required>
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-1">Contact Number</label>
                                <input type="text" name="contact" value="{{ old('contact', $client->contact) }}" class="w-full px-4 py-2 rounded bg-layer border border-divider/40" required>
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-1">Address</label>
                                <input type="text" name="address" value="{{ old('address', $client->address) }}" class="w-full px-4 py-2 rounded bg-layer border border-divider/40" required>
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-1">Date</label>
                                <input type="date" name="date" value="{{ old('date') }}" class="w-full px-4 py-2 rounded bg-layer border border-divider/40" required>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium mb-1">Start Time</label>
                                    <select name="start_time" class="w-full px-4 py-2 rounded bg-layer border border-divider/40" required>
                                        @foreach(['08:00 AM','09:00 AM','10:00 AM','11:00 AM','12:00 PM','01:00 PM','02:00 PM','03:00 PM','04:00 PM','05:00 PM'] as $t)
                                            <option value="{{ $t }}">{{ $t }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1">End Time</label>
                                    <select name="end_time" class="w-full px-4 py-2 rounded bg-layer border border-divider/40" required>
                                        @foreach(['09:00 AM','10:00 AM','11:00 AM','12:00 PM','01:00 PM','02:00 PM','03:00 PM','04:00 PM','05:00 PM','06:00 PM'] as $t)
                                            <option value="{{ $t }}">{{ $t }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-1">Dance Experience</label>
                                <input type="text" name="experience" value="{{ old('experience') }}" class="w-full px-4 py-2 rounded bg-layer border border-divider/40">
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-1">Purpose</label>
                                <input type="text" name="purpose" value="{{ old('purpose') }}" class="w-full px-4 py-2 rounded bg-layer border border-divider/40">
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-1">Message</label>
                                <textarea name="message" rows="4" class="w-full px-4 py-2 rounded bg-layer border border-divider/40">{{ old('message') }}</textarea>
                            </div>

                            <div class="pt-2">
                                <button type="submit"
                                        :disabled="loading"
                                        class="w-full bg-primary hover:opacity-90 font-semibold py-2 px-4 rounded-md shadow relative disabled:opacity-70 disabled:cursor-not-allowed">
                                    <span x-show="!loading">Book Appointment</span>
                                    <span x-show="loading" class="inline-flex items-center gap-2">
                                        <i class="fa-solid fa-spinner animate-spin"></i>
                                        Booking...
                                    </span>
                                </button>
                            </div>

                            <!-- Lightweight overlay while submitting -->
                            <div x-show="loading" x-cloak
                                 class="fixed inset-0 bg-black/40 backdrop-blur-sm z-[200] grid place-items-center">
                                <div class="bg-card border border-divider/40 rounded-2xl shadow-2xl px-6 py-4 text-center">
                                    <div class="flex items-center justify-center gap-3">
                                        <i class="fa-solid fa-spinner animate-spin text-xl"></i>
                                        <p class="font-medium">Processing your appointment...</p>
                                    </div>
                                    <p class="text-xs text-fg/60 mt-1">Please don’t close this tab.</p>
                                </div>
                            </div>
                        </form>
                    @endif
                </section>

                @endif

                <section x-show="tab==='feedback'" x-transition class="lg:col-span-2 space-y-5">
                    <div class="bg-card border border-divider/40 p-6 rounded-3xl shadow">
                        <h2 class="text-base font-bold text-primary mb-3 border-b border-divider/40 pb-1">Ratings & Feedback</h2>

                        @php
                            $rating = $user->rating ?? 0;
                            $ratingCount = $user->rating_count ?? 0;
                            $full = floor($rating);
                            $half = ($rating - $full) >= 0.5;
                            $empty = 5 - $full - ($half ? 1 : 0);
                        @endphp

                        <div class="flex items-end gap-2 mb-3">
                            <div class="flex items-center gap-1">
                                @for($i=0;$i<$full;$i++)
                                    <i class="fa-solid fa-star text-yellow-400"></i>
                                @endfor
                                @if($half)
                                    <i class="fa-solid fa-star-half-stroke text-yellow-400"></i>
                                @endif
                                @for($i=0;$i<$empty;$i++)
                                    <i class="fa-regular fa-star text-fg/40"></i>
                                @endfor
                            </div>
                            <div class="text-xs text-fg/70">
                                <span class="font-semibold text-fg">{{ number_format($rating, 1) }}</span> / 5.0 ({{ $ratingCount }} {{ Str::plural('rating', $ratingCount) }})
                            </div>
                        </div>

                        @if (!empty($user->comments))
                            <div class="mb-3 text-sm text-fg/80 italic bg-layer p-2 rounded border border-divider/40">
                                <span class="font-semibold not-italic text-fg">Coach's Comments:</span> “{{ $user->comments }}”
                            </div>
                        @endif

                        <div class="mt-3 border-t border-divider/40 pt-3">
                            <h3 class="text-sm font-semibold text-primary mb-2">Leave Your Feedback</h3>
                            <form action="{{ route('ratings.store') }}" method="POST" class="space-y-3" x-data="{ selected:0, hover:0 }">
                                @csrf
                                <input type="hidden" name="coach_id" value="{{ $user->coach_id }}">
                                <input type="hidden" name="rating" :value="selected" required>

                                <div>
                                    <label class="block text-xs font-medium text-fg/80 mb-1">Your Rating</label>
                                    <div class="flex items-center gap-1">
                                        @for ($i=1;$i<=5;$i++)
                                            <i class="fa-star cursor-pointer transition-colors duration-200"
                                               @mouseover="hover={{ $i }}" @mouseleave="hover=0" @click="selected={{ $i }}"
                                               :class="{{ $i }} <= (hover || selected) ? 'fa-solid text-yellow-400' : 'fa-regular text-fg/50'"></i>
                                        @endfor
                                    </div>
                                </div>

                                <div>
                                    <textarea name="comment" rows="2" class="w-full p-2 rounded bg-layer border border-divider/40 focus:outline-none" placeholder="Share your experience..." required></textarea>
                                </div>

                                <button type="submit" class="w-full bg-primary font-semibold py-2 rounded shadow">Submit Rating</button>
                            </form>
                        </div>
                    </div>

                    @if($feedbacks->isNotEmpty())
                        <div class="bg-card border border-divider/40 p-6 rounded-3xl shadow">
                            <h3 class="text-sm font-semibold mb-3">What clients are saying</h3>
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                                @foreach($feedbacks as $feedback)
                                    @php
                                        $fbClient = $feedback->user;
                                        $r = $feedback->rating;
                                        $f = floor($r); $h = ($r-$f)>=.5; $e = 5-$f-($h?1:0);
                                    @endphp
                                    <div class="rounded-2xl border border-divider/40 bg-layer p-4">
                                        <div class="flex items-center gap-3 mb-2">
                                            <div class="w-10 h-10 rounded-full overflow-hidden bg-card grid place-items-center text-sm font-bold">
                                                @if(isset($fbClient) && $fbClient && !empty($fbClient->photo))
                                                    <img src="{{ asset('storage/' . $fbClient->photo) }}" class="w-full h-full object-cover" alt="">
                                                @elseif(isset($fbClient) && $fbClient)
                                                    {{ strtoupper(substr($fbClient->firstname ?? '',0,1) . substr($fbClient->lastname ?? '',0,1)) }}
                                                @else
                                                    NA
                                                @endif
                                            </div>
                                            <div class="leading-tight">
                                                <div class="font-medium text-sm">{{ $fbClient->fullname ?? 'Anonymous' }}</div>
                                                <div class="text-[10px] text-fg/60 uppercase tracking-wide">{{ $fbClient->role ?? 'Client' }}</div>
                                            </div>
                                        </div>
                                        <div class="mb-2">
                                            @for($i=0;$i<$f;$i++) <i class="fa-solid fa-star text-yellow-400 text-xs"></i> @endfor
                                            @if($h) <i class="fa-solid fa-star-half-stroke text-yellow-400 text-xs"></i> @endif
                                            @for($i=0;$i<$e;$i++) <i class="fa-regular fa-star text-fg/40 text-xs"></i> @endfor
                                        </div>
                                        <p class="text-sm text-fg/80 italic">“{{ $feedback->comment }}”</p>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </section>
            </div>
        </div>

        <aside class="w-full h-[500px] bg-card border border-divider/40 p-4 rounded-xl flex flex-col gap-4">
            @if ($isCoach && ( !empty($user->portfolio_path) || !empty($user->valid_id_path) || !empty($user->id_selfie_path) ))
                <button
                    @click="
                      tab = 'info'; showPortfolio = true;
                      $nextTick(() => { const el = document.getElementById('portfolio-section'); if (el) el.scrollIntoView({ behavior:'smooth', block:'start' }); });
                    "
                    class="flex items-center justify-center gap-2 px-6 py-3 bg-layer text-fg font-medium shadow hover:opacity-90 rounded-xl w-full">
                    <i class="fa-solid fa-bars"></i> View Portfolio & IDs
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

    <div x-on:show-portfolio.window="tab='info'; showPortfolio=true"></div>
</main>

<script>
    function avatarUpdater(){
        return {
            photoUrl: '{{ ($client && $client->photo) ? asset('storage/' . $client->photo) : '' }}',
            fetchPhoto(){
                fetch("{{ route('profile.photo') }}")
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
