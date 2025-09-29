@php
    $client = session('client');
    $coach  = session('coach');
    $authUser = $client ?? $coach ?? auth()->user();
    $unreadNotifications = $authUser?->unreadNotifications ?? collect();
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    {{-- Meta --}}
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Groove | Coach Dashboard')</title>

  <link rel="icon" href="/image/bg/LOG.png" type="image/png" sizes="512x512">
    <link rel="apple-touch-icon" href="/image/wc/logo.png" sizes="180x180">

    {{-- Fonts --}}
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet">

    {{-- Tailwind (Vite) --}}
@vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- FontAwesome & Alpine --}}
    <script src="https://kit.fontawesome.com/43f9926b04.js" crossorigin="anonymous" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    <style>
        html, body { margin:0; padding:0; }
        [x-cloak] { display: none !important; }

        /* neutral scrollbars */
        .hover-scrollbar { scrollbar-width: thin; }
        .hover-scrollbar::-webkit-scrollbar { height: 8px; }
        .hover-scrollbar::-webkit-scrollbar-thumb { border-radius:6px; }

        /* neutral containers (no background) */
        .card { border:1px solid #e5e7eb; border-radius:14px; box-shadow:0 2px 10px rgba(15,23,42,.04); }
        .pill { border:1px solid #e5e7eb; border-radius:9999px; }
    </style>
</head>
<body class="min-h-screen antialiased theme-{{ $appTheme}} overflow-x-hidden relative">

    {{-- Toasts --}}
    @if(session('show_welcome'))
        <div x-data="{ show: true }"
             x-init="setTimeout(() => { show = false; fetch('{{ route('clear.welcome') }}',{method:'POST',headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}','Content-Type':'application/json'}}); }, 3000)"
             x-show="show" x-transition
             class="fixed bottom-6 right-6 border border-slate-200 px-5 py-4 rounded-xl shadow-lg flex items-center gap-3 z-50">
            <div>
                <div class="font-semibold">Welcome!</div>
                <div class="capitalize font-medium">{{ $coach->fullname ?? 'Coach' }}</div>
            </div>
        </div>
    @endif

    @if(session('latest_announcement'))
        <div x-data="{ show: true }"
             x-init="setTimeout(() => { show = false; fetch('{{ route('clear.latest_announcement') }}',{method:'POST',headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}','Content-Type':'application/json'}}); }, 3000)"
             x-show="show" x-transition
             class="fixed bottom-6 right-6 px-5 py-4 rounded-xl shadow-lg z-50">
            <div class="font-semibold">Announcement</div>
            <div class="font-medium">{{ session('latest_announcement')->message }}</div>
        </div>
    @endif


  <header x-data="{ scrolled: false }"
          x-init="window.addEventListener('scroll', () => { scrolled = window.scrollY > 10 })"
          class="fixed top-0 left-0 right-0 z-50 transition duration-300"
          :class="scrolled ? 'backdrop-blur-sm border-b border-ui' : 'bg-transparent'">
    <div class="max-w-7xl mx-auto px-4 lg:px-8 py-3">
      <div class="flex items-center gap-4">
        <!-- Logo -->
        <a href="/coach/home" class="flex items-center gap-3 shrink-0 focus:outline-none focus:ring-2 focus:ring-purple-500/60 rounded-xl">
          <img src="/image/wc/logo.png" alt="Logo" class="h-10 w-auto object-contain select-none" />
          <span class="sr-only">Home</span>
        </a>

          <!-- Nav -->
          <nav class="hidden md:flex flex-1 justify-center gap-2 lg:gap-4 text-sm font-medium">
          <a href="/coach/home"
           class="relative px-4 py-2 rounded-xl text-foreground/70 hover:text-foreground hover:bg-layer hover:border hover:border-divider/40 hover:shadow-md transition-all duration-300">
          Home
        </a>
        <a href="{{ route('Talent') }}"
           class="relative px-4 py-2 rounded-xl text-foreground/70 hover:text-foreground hover:bg-layer hover:border hover:border-divider/40 hover:shadow-md transition-all duration-300">
          Talents
        </a>
        <a href="{{ route('about') }}"
           class="relative px-4 py-2 rounded-xl text-foreground/70 hover:text-foreground hover:bg-layer hover:border hover:border-divider/40 hover:shadow-md transition-all duration-300">
          About
        </a>
        <a href="{{ route('messages.index') }}"
           class="relative px-4 py-2 rounded-xl text-foreground/70 hover:text-foreground hover:bg-layer hover:border hover:border-divider/40 hover:shadow-md transition-all duration-300">
          Messages
        </a>
          </nav>

        <!-- Right -->
        <div class="ml-auto flex items-center gap-3">
          <!-- Notifications -->
          <div x-data="{ openNotif: false }" class="relative" x-cloak>
            <button @click="openNotif = !openNotif" class="w-10 h-10 flex items-center justify-center rounded-full hover:bg-layer transition relative">
              <i class="fa-regular fa-bell text-fg"></i>
              @if($unreadNotifications->count())
                <span x-show="!openNotif" class="absolute -top-1 -right-1 w-5 h-5 text-[10px] font-bold text-fg bg-layer border border-ui rounded-full grid place-items-center">{{ $unreadNotifications->count() }}</span>
              @endif
            </button>
            <div x-show="openNotif" @click.away="openNotif = false" class="absolute right-0 mt-3 w-80 max-h-96 bg-card border border-ui rounded-xl shadow-ui p-4 space-y-3 z-50 overflow-y-auto" x-transition x-cloak>
              <h4 class="text-base font-semibold border-b border-ui pb-2">Notifications</h4>
              @forelse ($unreadNotifications as $notif)
                <button type="button" wire:click="$emit('markAsRead', '{{ $notif->id }}')" class="w-full text-left bg-layer rounded-lg p-3 text-sm transition hover:ring-ui">
                  <p class="font-medium">{{ $notif->data['title'] }}</p>
                  <p class="text-muted text-xs mt-1">{{ $notif->data['message'] }}</p>
                  <p class="text-muted text-[11px] mt-2">{{ $notif->created_at->diffForHumans() }}</p>
                </button>
              @empty
                <div class="text-center text-muted italic py-6 text-sm">You're all caught up</div>
              @endforelse
            </div>
          </div>

          <!-- Profile -->
          <div x-data="{ open:false, photoUrl:null }" class="relative" x-cloak>
            <button @click="open = !open" class="flex items-center gap-x-3 px-3 py-2 bg-layer rounded-full transition duration-200 border border-ui bg-card" aria-label="User Profile Menu">
            @if ($coach && !empty($coach->photo))
                <img src="{{ asset('storage/' . $coach->photo) }}" alt="Avatar"
                     class="w-8 h-8 rounded-full object-cover border border-ui">
              @else
                <div class="w-8 h-8 grid place-items-center bg-layer rounded-full text-sm font-bold uppercase border border-ui">
                  {{ strtoupper(substr($coach->firstname ?? 'C', 0, 1)) }}
                </div>
              @endif
              <span class="hidden sm:inline text-xs capitalize">{{ strtolower($coach->firstname ?? 'coach') }} {{ strtolower($coach->middlename ?? '') }}</span>
              <i class="fa-solid fa-caret-down text-muted"></i>
            </button>
            <div x-show="open" @click.away="open=false" x-transition class=" absolute right-0 mt-2 w-60 bg-card border border-ui rounded-2xl shadow-ui ring-1 ring-ui z-50 overflow-hidden">
              <div class="px-4 py-3 bg-layer border-b border-ui text-center bg-card" >
                <p class="text-sm font-semibold">{{ $coach->firstname ?? 'Coach' }} {{ $coach->middlename ?? '' }}</p>
                <p class="text-xs text-muted mt-0.5">#{{ $coach->coach_id ?? '0000' }} • {{ ucfirst($coach->role ?? 'coach') }}</p>
              </div>
              <div class="flex flex-col px-3 py-2">
                <a href="{{ route('Profile') }}" class="flex items-center gap-2 hover:bg-layer px-3 py-1.5 rounded-xl transition"><i class="fa-regular fa-user text-muted text-sm"></i><span class="text-sm">Profile</span></a>
                <a href="{{ route('PROFILE.EDIT') }}" class="flex items-center gap-2 hover:bg-layer px-3 py-1.5 rounded-xl transition"><i class="fa-solid fa-gear text-muted text-sm"></i><span class="text-sm">Settings</span></a>
              </div>
              <div class="border-t border-ui px-3 py-2">
                <form method="POST" action="{{ route('logout') }}">@csrf
                  <button type="submit" class="w-full flex items-center gap-2 hover:bg-layer px-3 py-1.5 rounded-xl text-sm transition"><i class="fa-solid fa-arrow-right-from-bracket text-sm"></i><span>Logout</span></button>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </header>

   <main class="max-w-7xl mx-auto px-4 md:px-10 pt-24 md:pt-28 pb-16 space-y-10 md:space-y-12">
  
  <!-- Hero (client-style UI, coach content) -->
  <section class="relative rounded-3xl border border-divider/40 overflow-hidden shadow-md md:shadow-lg h-[34vh] sm:h-[38vh] md:h-[42vh] flex items-center justify-center bg-card">
    <video autoplay loop muted playsinline aria-hidden="true" class="absolute inset-0 w-full h-full object-cover">
      <source src="{{ asset('media/groove-feature-vid.mp4') }}" type="video/mp4" />
    </video>

    <!-- overlay -->
    <div class="absolute inset-0 bg-gradient-to-t from-black/55 via-black/25 to-transparent"></div>

    <div class="relative z-10 text-center text-fg-3 px-6">
      <h1 class="text-2xl sm:text-3xl md:text-4xl font-extrabold tracking-tight drop-shadow">
        <span id="greeting">Hello</span>, {{ $coach->firstname ?? 'Coach' }} {{ $coach->middlename ?? '' }}!
      </h1>
      <p class="mt-2 text-xs sm:text-sm md:text-base opacity-90 max-w-2xl mx-auto">
        Ready to explore your next dance adventure? Watch the feature, connect with coaches & choreographers, and keep growing.
      </p>
      <div class="mt-4 flex items-center justify-center gap-3">
        <a href="{{ route('Talent') }}" class="px-4 py-2 rounded-xl text-sm font-semibold bg-primary text-fg hover:bg-primary/90">Explore Talents</a>
        <a href="{{ route('messages.index') }}" class="px-4 py-2 rounded-xl border border-slate-300 text-sm bg-card/70 hover:bg-layer">Messages</a>
      </div>
    </div>
  </section>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 md:gap-10">
    <div class="lg:col-span-2 space-y-6">

      <!-- Recommended Users (client-style UI, coach data) -->
      <section x-data="{ selectedUser: null }" class="border border-divider/40 rounded-3xl shadow-sm bg-card">
        @if ($recommendedUsers->isEmpty())
          <p class="p-5 text-foreground/60 italic text-sm">No recommendations yet.</p>
        @else
          <div class="flex items-center justify-between px-4 pt-4">
            <h3 class="text-base sm:text-lg font-semibold">Recommended for you</h3>
          </div>

          <div class="scroll-container flex gap-3 sm:gap-5 overflow-x-auto px-4 pb-4 snap-x snap-mandatory hide-scrollbar">
            @foreach ($recommendedUsers as $recUser)
              <article
                @click="selectedUser = {{ $recUser->toJson() }}"
                role="button"
                tabindex="0"
                @keydown.enter.prevent="selectedUser = {{ $recUser->toJson() }}"
                @keydown.space.prevent="selectedUser = {{ $recUser->toJson() }}"
                class="min-w-[175px] sm:min-w-[210px] snap-start rounded-2xl p-4 sm:p-5 border border-divider/40 bg-layer text-center shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all cursor-pointer outline-none focus:ring-2 focus:ring-primary/40 focus:ring-offset-1 focus:ring-offset-transparent"
                aria-label="Open profile card"
              >
                <div class="relative w-16 h-16 sm:w-20 sm:h-20 mx-auto rounded-full overflow-hidden bg-card text-foreground flex items-center justify-center font-bold text-lg sm:text-xl mb-3 border border-divider/40">
                  @if ($recUser->photo)
                    <img src="{{ asset('storage/' . $recUser->photo) }}" alt="{{ $recUser->firstname }}'s avatar" class="w-full h-full object-cover rounded-full" />
                  @else
                    {{ strtoupper(substr($recUser->firstname, 0, 1)) }}{{ strtoupper(substr($recUser->lastname, 0, 1)) }}
                  @endif
                </div>

                <p class="font-semibold text-sm sm:text-base truncate">
                  {{ $recUser->firstname }}
                  @if(!empty($recUser->middlename))
                    {{ strtoupper(substr($recUser->middlename, 0, 1)) }}.
                  @endif
                  @if(!empty($recUser->lastname))
                    {{ $recUser->lastname }}
                  @endif
                </p>

                <p class="text-[11px] sm:text-xs text-foreground/60 capitalize mt-1 tracking-wide">
                  {{ $recUser->role }}
                </p>

                        @if (!empty($coach->account_verified))
    <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-green-100 text-green-800 text-sm font-semibold shadow-md">
        <i class="fa-solid fa-circle-check text-green-600"></i>
    </span>
@endif

                <div class="hidden sm:flex items-center justify-center gap-2 mt-2 text-[11px] text-foreground/60">
                  @if(!empty($recUser->talent))
                    <span class="px-2 py-0.5 rounded-full border border-divider/40 bg-card/60 truncate max-w-[120px]">{{ $recUser->talent }}</span>
                  @endif
                  @if(!empty($recUser->city_name))
                    <span class="px-2 py-0.5 rounded-full border border-divider/40 bg-card/60 truncate max-w-[120px]">{{ $recUser->city_name }}</span>
                  @endif
                </div>
              </article>
            @endforeach
          </div>
        @endif

        <!-- User modal (Verified badge only inside modal) -->
        <div
          x-show="selectedUser"
          x-cloak
          @keydown.escape.window="selectedUser=null"
          @click.self="selectedUser=null"
          class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 bg-black/50 backdrop-blur-sm"
          aria-modal="true" role="dialog"
        >
          <div
            @click.stop
            x-transition.opacity
            class="relative w-full max-w-md bg-card border border-divider/40 rounded-3xl p-5 sm:p-6 shadow-2xl overflow-auto max-h-[90vh]"
          >
            <button
              @click="selectedUser = null"
              class="absolute top-4 right-4 opacity-80 hover:opacity-100 text-xl font-bold w-9 h-9 grid place-items-center rounded-full hover:bg-layer"
              aria-label="Close"
            >&times;</button>

            <div class="flex items-center gap-3 sm:gap-4 mb-4">
              <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-full overflow-hidden border border-divider/40 bg-layer flex items-center justify-center font-bold">
                <template x-if="selectedUser?.photo">
                  <img :src="'{{ asset('storage') }}/' + (selectedUser?.photo || '')" alt="Avatar" class="w-full h-full object-cover">
                </template>
                <template x-if="!selectedUser?.photo">
                  <span x-text="((selectedUser?.firstname?.[0] || '') + (selectedUser?.lastname?.[0] || '')).toUpperCase() || 'N/A'"></span>
                </template>
              </div>

              <div class="min-w-0">
                <h2 class="text-xl sm:text-2xl font-extrabold flex items-center gap-2">
                  <span class="truncate" x-text="selectedUser ? (selectedUser.firstname + ' ' + (selectedUser.middlename ? (selectedUser.middlename[0] + '. ') : '') + (selectedUser.lastname || '')) : ''"></span>
                  <template x-if="selectedUser?.account_verified">
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-[10px] sm:text-xs font-semibold shrink-0">
                      <i class="fa-solid fa-circle-check"></i> Verified
                    </span>
                  </template>
                </h2>
                <p class="font-semibold text-foreground/60 uppercase text-[10px] sm:text-xs mt-0.5" x-text="selectedUser?.role || 'User'"></p>
              </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm mb-4">
              <div>
                <p class="text-foreground/60 text-[11px] font-medium">User ID</p>
                <p class="font-semibold" x-text="selectedUser?.id ? ('#' + selectedUser.id) : 'N/A'"></p>
              </div>
              <div>
                <p class="text-foreground/60 text-[11px] font-medium">Location</p>
                <p class="font-semibold" x-text="selectedUser?.address || selectedUser?.city_name || 'N/A'"></p>
              </div>
              <div>
                <p class="text-foreground/60 text-[11px] font-medium">Email</p>
                <p class="font-semibold break-all" x-text="selectedUser?.email || 'N/A'"></p>
              </div>
              <div>
                <p class="text-foreground/60 text-[11px] font-medium">Phone</p>
                <p class="font-semibold" x-text="selectedUser?.contact || 'N/A'"></p>
              </div>
            </div>

            <template x-if="selectedUser?.talent">
              <div class="mt-2">
                <p class="text-sm text-foreground/60">Talent</p>
                <p class="font-medium" x-text="selectedUser.talent"></p>
              </div>
            </template>

            <div class="mt-3">
              <p class="text-sm text-foreground/60">About</p>
              <p x-text="selectedUser?.about || selectedUser?.bio || 'No description available.'"></p>
            </div>

            <div class="mt-5 grid grid-cols-2 gap-3">
              <a
                :href="selectedUser ? '{{ route('messages.index') }}' + '?with_id=' + selectedUser.id + '&with_type=' + selectedUser.role : '#'"
                class="px-4 py-2 rounded-xl text-center font-semibold border border-divider/40 hover:bg-layer focus-visible:ring-2 focus-visible:ring-primary/40"
              >Message</a>
              <a
                :href="selectedUser ? '/userprofile/' + selectedUser.id : '#'"
                class="px-4 py-2 rounded-xl text-center font-semibold bg-primary text-fg hover:bg-primary/90 focus-visible:ring-2 focus-visible:ring-primary/40"
              >View Profile</a>
            </div>
          </div>
        </div>
      </section>

      <!-- Appointments (coach view fields preserved) -->
      <section class="border border-divider/40 p-4 md:p-5 rounded-3xl shadow-sm bg-card">
        <div class="flex items-center justify-between mb-4 border-b border-divider/40 pb-3">
          <h3 class="text-lg font-semibold">Appointments</h3>
          <a href="{{ route('appointments.index') }}" class="text-xs sm:text-sm font-semibold text-primary hover:underline">Manage</a>
        </div>
        @forelse ($appointments as $appointment)
          <div class="flex flex-col md:flex-row md:items-center md:justify-between border border-divider/40 p-3 md:p-4 mb-4 rounded-2xl bg-layer/70 text-sm gap-3">
            <div class="flex md:block items-center justify-between w-full md:w-16">
              <span class="font-semibold text-foreground/60 text-[10px]">ID</span>
              <span class="text-[11px] md:block">{{ $appointment->appointment_id ?? 'N/A' }}</span>
            </div>

            <div class="flex items-center gap-2 w-full md:w-44">
              @if ($appointment->client && $appointment->client->photo)
                <img src="{{ asset('storage/' . $appointment->client->photo) }}" class="w-9 h-9 rounded-full object-cover border border-divider/40" alt="client">
              @else
                <div class="w-9 h-9 rounded-full grid place-items-center text-[10px] font-semibold border border-divider/40 bg-card">
                  {{ strtoupper(substr($appointment->client->firstname ?? 'C',0,1)) }}{{ strtoupper(substr($appointment->client->lastname ?? 'N',0,1)) }}
                </div>
              @endif
              <div class="flex flex-col">
                <span class="font-semibold text-foreground/60 text-[10px]">Client</span>
                <span class="text-[11px]">{{ $appointment->client->firstname ?? 'N/A' }} {{ $appointment->client->lastname ?? '' }}</span>
              </div>
            </div>

            <div class="flex flex-row md:flex-col items-center justify-between md:items-start w-full md:w-28">
              <span class="font-semibold text-foreground/60 text-[10px]">Date</span>
              <span class="text-[11px]">{{ \Carbon\Carbon::parse($appointment->date)->format('M d, Y') }}</span>
            </div>
            <div class="flex flex-row md:flex-col items-center justify-between md:items-start w-full md:w-28">
              <span class="font-semibold text-foreground/60 text-[10px]">Time</span>
              <span class="text-[11px]">{{ $appointment->start_time }} - {{ $appointment->end_time }}</span>
            </div>
            <div class="flex flex-row md:flex-col items-center justify-between md:items-start w-full md:w-28">
              <span class="font-semibold text-foreground/60 text-[10px]">Talent</span>
              <span class="text-[11px]">{{ ucfirst($appointment->talent ?? '—') }}</span>
            </div>
            <div class="flex flex-row md:flex-col items-center justify-between md:items-start w-full md:w-28">
              <span class="font-semibold text-foreground/60 text-[10px]">Status</span>
              <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold mt-1 border border-divider/40">
                {{ ucfirst($appointment->status) }}
              </span>
            </div>
          </div>
        @empty
          <p class="text-foreground/60 italic text-sm">You have no appointments yet.</p>
        @endforelse
      </section>

      <!-- Coaches & Talents (same datasets, client-style cards) -->
      <section class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <!-- Top Coaches -->
<div class="border border-divider/40 rounded-3xl p-5 sm:p-6 shadow-sm bg-card">
  <h4 class="text-lg font-semibold mb-4 sm:mb-6">Top Coaches</h4>

  @if ($topVerifiedCoaches->isEmpty())
    <p class="text-foreground/60 italic text-sm">No verified coaches available at the moment.</p>
  @else
    <div class="space-y-3 sm:space-y-4">
      @foreach ($topVerifiedCoaches as $coachItem)
        <div
          class="rounded-2xl px-4 py-3 flex items-center justify-between border border-divider/40 bg-layer shadow-sm hover:bg-layer/80 transition cursor-pointer"
          x-data
          @click="$dispatch('open-user', { coach_id: '{{ $coachItem->coach_id }}' })"
        >
          <div class="flex items-center gap-3">
            <div class="w-6 text-xs font-bold text-gray-600">#{{ $loop->iteration }}</div>

            @if (!empty($coachItem->photo))
              <img
                src="{{ asset('storage/' . $coachItem->photo) }}"
                class="w-12 h-12 rounded-full object-cover border border-divider/40"
                alt="{{ $coachItem->firstname }} {{ $coachItem->lastname }}"
              >
            @else
              <div class="w-12 h-12 rounded-full grid place-items-center text-sm font-semibold border border-divider/40 bg-card">
                {{ strtoupper(substr($coachItem->firstname,0,1)) }}{{ strtoupper(substr($coachItem->lastname,0,1)) }}
              </div>
            @endif

            <div>
              <div class="flex items-center gap-2">
                <p class="font-semibold text-sm sm:text-base">
                  {{ $coachItem->firstname }} {{ $coachItem->lastname }}
                </p>

                @if (!empty($coachItem->account_verified))
                  <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-green-100 text-green-800 text-[10px] sm:text-xs font-semibold shadow-md">
                    <i class="fa-solid fa-circle-check text-green-600 text-[10px]"></i>
                    Verified
                  </span>
                @endif
              </div>

              <p class="text-xs text-foreground/60 capitalize mt-0.5">
                {{ $coachItem->talents ?? 'N/A' }}
              </p>
            </div>
          </div>

          <div class="flex items-center gap-2">
            <i class="fa-solid fa-star text-yellow-500"></i>
            <span class="text-sm">
              {{ is_null($coachItem->rating) ? 'N/A' : number_format((float)$coachItem->rating, 1) }}
            </span>
          </div>
        </div>
      @endforeach
    </div>
  @endif
</div>


        <!-- Top Talents -->
        <div class="p-5 sm:p-6 rounded-3xl shadow-sm border border-divider/40 bg-card">
          <h3 class="text-lg sm:text-xl font-extrabold border-b border-divider/40 pb-3 mb-6">🔝 Top Talents</h3>
          @php $rank = 1; @endphp
          @foreach ($topTalentsWithCount->take(3) as $talent => $count)
            @if (!empty($talent))
              @if ($rank === 1)
                <div class="flex items-center justify-between px-4 py-4 rounded-2xl mb-4 border border-divider/40 bg-layer">
                  <div class="text-base sm:text-lg font-bold truncate">{{ $talent }}</div>
                  <span class="text-xs sm:text-sm font-semibold border border-divider/40 px-3 py-1 rounded-full">#1 Talent</span>
                </div>
              @else
                <div class="flex items-center justify-between px-4 py-3 rounded-2xl mb-3 border border-divider/40 bg-layer/70">
                  <span class="text-sm sm:text-base truncate">{{ $talent }}</span>
                  <span class="text-xs sm:text-sm">#{{ $rank }}</span>
                </div>
              @endif
              @php $rank++; @endphp
            @endif
          @endforeach
          @if ($rank === 1)
            <div class="text-foreground/60 text-sm">No talents added yet.</div>
          @endif
        </div>
      </section>
    </div>

    <!-- Aside -->
    <aside class="space-y-6">
      <!-- Profile card (coach data) -->
      <div class="w-full h-auto sm:h-64 flex flex-col items-center justify-center p-6 rounded-3xl space-y-3 text-center border border-divider/40 bg-card">
        <div x-data="avatarUpdater()" x-init="init()">
          <template x-if="photoUrl">
            <img :src="photoUrl" alt="Profile Photo" class="w-20 h-20 sm:w-24 sm:h-24 border border-divider/40 rounded-full object-cover shadow-sm" />
          </template>
          <template x-if="!photoUrl">
            <div class="w-20 h-20 sm:w-24 sm:h-24 flex items-center justify-center rounded-full text-2xl sm:text-3xl font-bold uppercase border border-divider/40 bg-layer">
              {{ strtoupper(substr($coach->firstname ?? 'C',0,1)) }}{{ strtoupper(substr($coach->lastname ?? 'C',0,1)) }}
        
            </div>
          </template>
        </div>
        <p class="text-base sm:text-lg font-semibold">
          {{ $coach->firstname ?? 'Client' }} {{ $coach->lastname }}
        </p>

                 @if (!empty($coach->account_verified))
    <span class="inline-flex items-center gap-2 px-3  rounded-full bg-green-100 text-green-800 text-sm font-semibold shadow-md">
        <i class="fa-solid fa-circle-check text-green-600"></i>
        Verified
    </span>
@endif
        <p class="text-xs sm:text-sm text-foreground/60">#{{ $coach->coach_id ?? '0000' }} &bullet; {{ ucfirst($coach->role ?? 'client') }}</p>
        <a href="/coach/profile" class="px-4 py-2 border border-divider/40 rounded-full text-sm hover:bg-layer">Profile</a>
      </div>

      <!-- Calendar (unchanged logic, restyled) -->
      <div class="border border-divider/40 rounded-3xl p-4 sm:p-5 shadow-sm bg-card" id="calendar">
        <div class="flex justify-between items-center mb-4">
          <h4 class="font-semibold">Calendar</h4>
          <a href="{{ route('calendar') }}" class="opacity-80 hover:opacity-100" aria-label="Open calendar"><i class="fa-solid fa-expand"></i></a>
        </div>
        <div class="grid grid-cols-7 text-center text-[10px] sm:text-xs text-foreground/60 font-medium border-b border-divider/40 pb-2 mb-2">
          <span>Sun</span><span>Mon</span><span>Tue</span><span>Wed</span><span>Thu</span><span>Fri</span><span>Sat</span>
        </div>
        <div id="calendar-days" class="grid grid-cols-7 text-center text-xs sm:text-sm gap-y-1"></div>
      </div>


         <!-- Submit Ticket -->
      <div class="relative border border-divider/40 rounded-3xl p-6 shadow-sm bg-gradient-to-b from-card to-card/80 text-center hover:shadow-lg transition-all duration-300">
        <div class="absolute -top-6 left-1/2 -translate-x-1/2">
          <div class="w-14 h-14 flex items-center justify-center rounded-full bg-primary text-vb shadow-md ring-4 ring-card">
            <i class="fa-solid fa-ticket-simple text-xl"></i>
          </div>
        </div>
        <div class="mt-10">
          <h4 class="text-lg font-extrabold tracking-tight mb-1">Submit Ticket</h4>
          <p class="text-sm text-foreground/60 mb-5">Instant help & issue reporting</p>
          <button type="button" onclick="openTicketModal()" class="w-full py-3 rounded-xl bg-primary hover:bg-primary/90 active:scale-[0.98] text-fg font-semibold shadow-md transition-all duration-200">Create Ticket</button>
        </div>
      </div>
    </aside>
  </div>

  <!-- Studios map -->
  <section>
    <div class="flex items-center justify-between mb-2">
      <p class="text-xl sm:text-2xl text-foreground/60 font-bold select-none">STUDIOS</p>
      <div class="text-xs sm:text-sm text-foreground/60">Metro area</div>
    </div>
    <div class="rounded-3xl border border-divider/40 bg-card overflow-hidden">
      <iframe
        src="https://www.google.com/maps/embed?pb=!1m16!1m12!1m3!1d61730.37468240366!2d120.99412321212131!3d14.760667026083794!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!2m1!1sDANCE%20studio!5e0!3m2!1sen!2sph!4v1747613518621!5m2!1sen!2sph"
        width="100%" height="360" style="border:0;" allowfullscreen loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="Studios map"
        class="block w-full h-[320px] sm:h-[360px]"></iframe>
    </div>
  </section>

  <!-- Utilities -->
  <style>
    .hide-scrollbar{scrollbar-width:none;-ms-overflow-style:none}
    .hide-scrollbar::-webkit-scrollbar{display:none}
  </style>
</main>



{{-- ========================= TICKET MODAL ========================= --}}
<div id="ticketModal" class="hidden fixed inset-0 z-[60]" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="ticketTitle">
  <div class="absolute inset-0 bg-black/60 opacity-0 transition-opacity" id="ticketBackdrop"></div>

  <div class="absolute inset-0 flex items-start md:items-center justify-center overflow-y-auto p-4">
    <div class="w-full max-w-xl bg-card text-foreground rounded-2xl shadow-2xl border border-divider/40 translate-y-6 opacity-0 transition-all duration-200" id="ticketPanel">
      <div class="flex items-center justify-between px-6 py-4 border-b border-divider/40">
        <div class="flex items-center gap-3">
          <span class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-primary/10">
            <svg viewBox="0 0 24 24" class="h-5 w-5"><path fill="currentColor" d="M3 7a2 2 0 0 1 2-2h5v3a2 2 0 1 0 0 4v3H5a2 2 0 0 1-2-2V7zm13-2h3a2 2 0 0 1 2 2v6a2 2 0 0 1-2 2h-3v-3a2 2 0 1 0 0-4V5z"/></svg>
          </span>
          <h3 id="ticketTitle" class="text-lg font-semibold">Submit Ticket</h3>
        </div>
        <button class="text-2xl leading-none opacity-70 hover:opacity-100 px-2" type="button" aria-label="Close modal" onclick="closeTicketModal()">&times;</button>
      </div>

      <form id="ticketForm" class="px-6 pt-6 pb-4 space-y-4" onsubmit="return handleTicketSubmit(event)" enctype="multipart/form-data">
        {{-- Hidden IDs to link ticket to user --}}
        <input type="hidden" name="client_id" value="{{ $client->client_id ?? '' }}">
        <input type="hidden" name="coach_id"  value="{{ $coach->coach_id  ?? '' }}">

        {{-- Top banner for non-field errors --}}
        <div id="ticketBanner" class="hidden rounded-xl border px-4 py-3 text-sm"></div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="text-sm font-medium">Name</label>
          <input
  name="name"
  type="text"
  value="{{ old('name', trim(($coach->firstname ?? '') . ' ' . ($coach->lastname ?? ''))) }}"
  class="mt-1 w-full px-3 py-2 rounded-lg bg-layer border border-divider/40 focus:outline-none focus:ring-2 focus:ring-zinc-500"
  placeholder="Your name"
  required
>
            <p class="field-error hidden text-xs mt-1 text-red-400" data-for="name"></p>
          </div>
          <div>
            <label class="text-sm font-medium">Email</label>
            <input name="email" type="email"
                   value="{{ $client->email ?? $coach->email ?? '' }}"
                   class="mt-1 w-full px-3 py-2 rounded-lg bg-layer border border-divider/40 focus:outline-none focus:ring-2 focus:ring-zinc-500"
                   placeholder="you@example.com" required>
            <p class="field-error hidden text-xs mt-1 text-red-400" data-for="email"></p>
          </div>
        </div>

        <div>
          <label class="text-sm font-medium">Subject</label>
          <input name="subject" type="text"
                 class="mt-1 w-full px-3 py-2 rounded-lg bg-layer border border-divider/40 focus:outline-none focus:ring-2 focus:ring-zinc-500"
                 placeholder="Short summary" required>
          <p class="field-error hidden text-xs mt-1 text-red-400" data-for="subject"></p>
        </div>

        <div>
          <label class="text-sm font-medium">Message</label>
          <textarea name="message" rows="6"
                    class="mt-1 w-full px-3 py-2 rounded-lg bg-layer border border-divider/40 focus:outline-none focus:ring-2 focus:ring-zinc-500"
                    placeholder="Describe the issue or request…" required></textarea>
          <p class="field-error hidden text-xs mt-1 text-red-400" data-for="message"></p>
        </div>

        <div>
          <label class="text-sm font-medium">Attachment (image, optional)</label>
          <input id="ticketAttachment" name="attachment" type="file" accept="image/*"
                 class="mt-1 w-full px-3 py-2 rounded-lg bg-layer border border-divider/40">
          <p class="text-xs text-foreground/60 mt-1">Max ~5MB. JPG/PNG/WebP/GIF.</p>
          <p id="fileError" class="hidden text-xs mt-1 text-red-400">File is too large (max 5MB).</p>
          <p class="field-error hidden text-xs mt-1 text-red-400" data-for="attachment"></p>
        </div>

        <div class="pt-2">
          <button id="ticketSubmitBtn" type="submit"
                  class="w-full h-12 rounded-xl bg-primary text-fg font-semibold hover:opacity-90 transition inline-flex items-center justify-center gap-2">
            <svg id="ticketSpinner" class="hidden h-5 w-5 animate-spin" viewBox="0 0 24 24">
              <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none" opacity=".25"></circle>
              <path d="M22 12a10 10 0 0 1-10 10" fill="currentColor"></path>
            </svg>
            <span id="ticketBtnText">Submit request</span>
          </button>
        </div>
      </form>
    </div>
  </div>

  {{-- Loading overlay --}}
  <div id="ticketLoadingOverlay" class="hidden fixed inset-0 z-[70]  place-items-center bg-black/40 backdrop-blur-sm">
    <div class="rounded-2xl bg-card/90 border border-divider/40 px-5 py-4 shadow-xl flex items-center gap-3">
      <svg class="h-6 w-6 animate-spin" viewBox="0 0 24 24">
        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none" opacity=".25"></circle>
        <path d="M22 12a10 10 0 0 1-10 10" fill="currentColor"></path>
      </svg>
      <span class="text-sm font-medium">Submitting ticket…</span>
    </div>
  </div>
</div>


<script>
    const today = new Date();
    const year = today.getFullYear();
    const month = today.getMonth();
    const firstDay = new Date(year, month, 1);
    const lastDay = new Date(year, month + 1, 0);
    const totalDays = lastDay.getDate();
    const startDay = firstDay.getDay();
    const calendarDays = document.getElementById("calendar-days");
    calendarDays.innerHTML = "";

    for (let i = 0; i < startDay; i++) calendarDays.innerHTML += `<span></span>`;
    for (let i = 1; i <= totalDays; i++) {
      const isToday = i === today.getDate();
      calendarDays.innerHTML += `<span class="py-1 rounded-md ${isToday ? 'bg-zinc-700 text-white font-bold' : 'hover:bg-zinc-700'}">${i}</span>`;
    }


  // ===== Modal elements =====
  const ticketModal   = document.getElementById('ticketModal');
  const backdrop      = document.getElementById('ticketBackdrop');
  const panel         = document.getElementById('ticketPanel');

  // ===== Open / Close modal (with transitions & ESC/overlay close) =====
  function openTicketModal() {
    ticketModal.classList.remove('hidden');
    requestAnimationFrame(() => {
      backdrop.classList.remove('opacity-0');
      panel.classList.remove('opacity-0', 'translate-y-6');
    });
    setTimeout(() => ticketModal.querySelector('input[name="name"]')?.focus(), 50);
    document.addEventListener('keydown', escClose);
    backdrop.addEventListener('click', closeTicketModal, { once: true });
  }

  function closeTicketModal() {
    backdrop.classList.add('opacity-0');
    panel.classList.add('opacity-0', 'translate-y-6');
    document.removeEventListener('keydown', escClose);
    setTimeout(() => ticketModal.classList.add('hidden'), 180);
  }
  function escClose(e){ if (e.key === 'Escape') closeTicketModal(); }

  // ===== Toast (replaces alert) =====
  function showToast(message, type = 'success') {
    const t = document.createElement('div');
    t.className = `fixed right-4 top-4 z-[80] max-w-sm rounded-xl border px-4 py-3 shadow-xl
                   ${type === 'success'
                      ? 'bg-emerald-500/10 border-emerald-500/30 text-emerald-200'
                      : 'bg-red-500/10 border-red-500/30 text-red-200'}`;
    t.innerHTML = `<div class="flex items-start gap-3">
      <span class="mt-0.5">${type === 'success' ? '✅' : '⚠️'}</span>
      <div class="text-sm leading-snug">${message}</div>
    </div>`;
    document.body.appendChild(t);
    setTimeout(() => {
      t.style.opacity = '0'; t.style.transform = 'translateY(-6px)';
      setTimeout(() => t.remove(), 200);
    }, 2400);
  }

  // ===== Loading toggle (button spinner + overlay + disable inputs) =====
  function setSubmittingState(isLoading) {
    const btn     = document.getElementById('ticketSubmitBtn');
    const spin    = document.getElementById('ticketSpinner');
    const text    = document.getElementById('ticketBtnText');
    const overlay = document.getElementById('ticketLoadingOverlay');
    const form    = document.getElementById('ticketForm');

    if (isLoading) {
      btn.disabled = true;
      spin.classList.remove('hidden');
      text.textContent = 'Submitting…';
      overlay.classList.remove('hidden');
      Array.from(form.elements).forEach(el => el.disabled = el.type !== 'hidden');
    } else {
      btn.disabled = false;
      spin.classList.add('hidden');
      text.textContent = 'Submit request';
      overlay.classList.add('hidden');
      Array.from(form.elements).forEach(el => el.disabled = false);
    }
  }

  // ===== Field error helpers (Laravel 422) =====
  function clearFieldErrors(form){
    form.querySelectorAll('.field-error')
      .forEach(el => { el.textContent=''; el.classList.add('hidden'); });
    form.querySelectorAll('.input-error')
      .forEach(el => el.classList.remove('input-error','border-red-500/50','focus:ring-red-500'));
    form.querySelectorAll('[aria-invalid="true"]')
      .forEach(el => el.setAttribute('aria-invalid','false'));
  }
  function renderFieldErrors(form, errors){
    let firstErrorInput = null;
    Object.entries(errors).forEach(([field, msgs]) => {
      const msg   = Array.isArray(msgs) ? msgs[0] : String(msgs || '');
      const help  = form.querySelector(`.field-error[data-for="${field}"]`);
      const input = form.querySelector(`[name="${field}"]`);
      if (help) { help.textContent = msg; help.classList.remove('hidden'); }
      if (input) {
        input.classList.add('input-error','border-red-500/50','focus:ring-red-500');
        input.setAttribute('aria-invalid','true');
        if (!firstErrorInput) firstErrorInput = input;
      }
    });
    if (firstErrorInput) firstErrorInput.focus({ preventScroll:false });
  }

  // ===== File size guard (~5MB) =====
  const fileInput = document.getElementById('ticketAttachment');
  const fileError = document.getElementById('fileError');
  if (fileInput) {
    fileInput.addEventListener('change', () => {
      fileError?.classList.add('hidden');
      const f = fileInput.files?.[0];
      if (f && f.size > 5 * 1024 * 1024) {
        fileError?.classList.remove('hidden');
        fileInput.value = '';
      }
    });
  }

  // ===== Anti-spam cooldown =====
  let lastSubmitTime = 0;           // ms timestamp
  const cooldownMs   = 30_000;      // 30 seconds

  // ===== Submit handler (build FormData first; JSON-aware; no alerts) =====
  async function handleTicketSubmit(e) {
    e.preventDefault();
    const form   = e.target;
    const banner = document.getElementById('ticketBanner');

    // Cooldown (prevent spam)
    const now = Date.now();
    if (now - lastSubmitTime < cooldownMs) {
      showToast('Please wait a few seconds before submitting again.', 'error');
      return false;
    }
    lastSubmitTime = now;

    clearFieldErrors(form);
    if (banner) { banner.classList.add('hidden'); banner.textContent = ''; }

    try {
      // Capture FormData BEFORE disabling inputs
      const fd = new FormData(form);

      setSubmittingState(true);

      const res = await fetch(@json(route('tickets.store')), {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
          'Accept': 'application/json'
        },
        body: fd
      });

      if (res.status === 422) {
        const data = await res.json();
        renderFieldErrors(form, data.errors || {});
        throw new Error(data.message || 'Validation failed');
      }
      if (!res.ok) {
        let reason = 'submit_failed';
        try { const j = await res.json(); reason = j.reason || j.message || JSON.stringify(j); } catch(_){}
        throw new Error(reason);
      }

      showToast('Ticket submitted. Our team will get back to you.', 'success');
      form.reset();
      setTimeout(() => closeTicketModal(), 300);

    } catch (err) {
      if (banner) {
        banner.className = "rounded-xl border px-4 py-3 text-sm bg-red-500/10 border-red-500/30 text-red-200";
        banner.textContent = 'Failed to submit: ' + (err?.message ?? 'Please try again.');
        banner.classList.remove('hidden');
      }
      showToast('Failed to submit ticket.', 'error');
    } finally {
      setSubmittingState(false);
    }
    return false;
  }

   function avatarUpdater() {
        return {
            photoUrl: '{{ $coach->photo ? asset('storage/' . $coach->photo) : '' }}',
            fetchPhoto() {
                fetch("{{ route('coach.profile.photo') }}")
                    .then(res => res.json())
                    .then(data => { if (data.photo_url) this.photoUrl = data.photo_url; });
            },
            init() {
                this.fetchPhoto();
                setInterval(() => this.fetchPhoto(), 5000);
            }
        };
    }
  
</script>


</body>
</html>
