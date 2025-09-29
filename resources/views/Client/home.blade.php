{{-- resources/views/Client/home.blade.php --}}
@php
  // Resolve logged-in principal (client preferred, then coach, then default auth)
  $client = session('client');
  $coach  = session('coach');
  $user   = $client ?? $coach ?? auth()->user();

  $initials = $client ? strtoupper(substr($client->firstname, 0, 1) . substr($client->lastname, 0, 1)) : '';

  $notifications        = $user ? $user->notifications()->latest()->take(5)->get() : collect();
  $unreadNotifications  = $user ? $user->unreadNotifications : collect();
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Groove | Performing Arts Hub</title>

    {{-- CSRF token for AJAX/forms --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Fonts & icons --}}
    <link rel="stylesheet" href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600">
    <script src="https://kit.fontawesome.com/43f9926b04.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    {{-- Favicon --}}
    <link rel="icon" href="/image/bg/LOG.png" type="image/png" sizes="512x512">

    {{-- Laravel Vite: compile & include CSS/JS --}}
@vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* Hide native video controls */
        video::-webkit-media-controls,
        video::-webkit-media-controls-enclosure,
        video::-webkit-media-controls-panel {
            display: none !important;
        }

        /* Scrollbar hover styles */
        .hover-scrollbar {
            scrollbar-width: thin;
            scrollbar-color: transparent transparent;
            scroll-behavior: smooth;
            padding-right: 6px;
        }
        .hover-scrollbar:hover {
            scrollbar-color: #9ca3af transparent;
        }
        .hover-scrollbar::-webkit-scrollbar {
            width: 6px;
        }
        .hover-scrollbar::-webkit-scrollbar-thumb {
            background: transparent;
            border-radius: 6px;
            transition: background .3s ease;
        }
        .hover-scrollbar:hover::-webkit-scrollbar-thumb {
            background: #9ca3af;
        }

        /* Horizontal scroll containers */
        .scroll-container {
            scrollbar-gutter: stable both-edges;
            scrollbar-width: thin;
            scrollbar-color: transparent transparent;
        }
        .scroll-container::-webkit-scrollbar {
            height: 8px;
            background: transparent;
        }
        .scroll-container::-webkit-scrollbar-thumb {
            background-color: transparent;
            border-radius: 4px;
            transition: background-color .3s;
        }
        .scroll-container:hover::-webkit-scrollbar-thumb {
            background-color: rgba(100,100,100,.5);
        }
        .scroll-container:hover {
            scrollbar-color: rgba(100,100,100,.5) transparent;
        }
    </style>
</head>
<body class="min-h-screen antialiased theme-{{ $appTheme }} bg-surface text-foreground overflow-x-hidden relative">

  {{-- Welcome toast --}}
  @if(session('show_welcome'))
    <div
      x-data="{ show: true }"
      x-init="setTimeout(() => {
        show = false;
        fetch('{{ route('clear.welcome') }}', {
          method: 'POST',
          headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' }
        });
      }, 3500)"
      x-show="show" x-transition
      class="fixed bottom-6 right-6 px-5 py-4 rounded-xl shadow-lg flex items-center gap-3 z-50 border border-divider/40 bg-card">
      <div>
        <div class="font-bold text-sm">Welcome!</div>
        <div class="capitalize font-semibold">{{ $client->fullname ?? 'Client' }}</div>
      </div>
    </div>
  @endif

  {{-- Announcement toast --}}
  @if(session('latest_announcement_shown'))
    <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 5000)" x-show="show"
         class="fixed bottom-6 right-6 px-5 py-4 rounded-xl shadow-lg z-50 border border-divider/40 bg-card">
      <div class="font-bold">{{ $latestAnnouncement->title ?? 'Announcement' }}</div>
      <div class="mt-1">{{ $latestAnnouncement->message }}</div>
    </div>
  @endif

  <header
    x-data="{ scrolled:false, mobileOpen:false }"
    x-init="window.addEventListener('scroll',()=>{ scrolled = window.scrollY > 10 })"
    class="w-full py-4 px-4 md:px-8 fixed top-0 left-0 z-50 transition duration-300 border-b border-divider/40"
    :class="scrolled ? 'bg-card/80 backdrop-blur' : 'bg-transparent'">

    <div class="flex items-center justify-between max-w-7xl mx-auto gap-4">
      {{-- Left: logo --}}
      <div class="flex items-center gap-3">
        <img src="/image/bg/LOG.png" alt="Logo" class="h-10 md:h-12 w-auto object-contain select-none" />
      </div>

      {{-- Desktop nav --}}
      <nav class="hidden md:flex items-center gap-2 text-sm font-medium">
<a href="/client/home"
           class="relative  border border-divider/50 shadow-inner px-4 py-2 rounded-xl text-foreground/70 hover:text-foreground hover:bg-layer hover:border hover:border-divider/40 hover:shadow-md transition-all duration-300">
          Home
        </a>
        <a href="{{ route('talent') }}"
           class="relative px-4 py-2 rounded-xl text-foreground/70 hover:text-foreground hover:bg-layer hover:border hover:border-divider/40 hover:shadow-md transition-all duration-300">
          Talents
        </a>
        <a href="{{ route('about') }}"
           class="relative   px-4 py-2 rounded-xl text-foreground/70 hover:text-foreground hover:bg-layer hover:border hover:border-divider/40 hover:shadow-md transition-all duration-300">
          About
        </a>
        <a href="{{ route('messages.index') }}"
           class="relative px-4 py-2 rounded-xl text-foreground/70 hover:text-foreground hover:bg-layer hover:border hover:border-divider/40 hover:shadow-md transition-all duration-300">
          Messages
        </a>
      </nav>

      {{-- Right: notifications + profile + mobile hamburger --}}
      <div class="flex items-center gap-3 md:gap-4">
        {{-- Notifications --}}
        <div x-data="{ openNotif:false }" class="relative">
          <button @click="openNotif = !openNotif"
                  class="w-10 h-10 flex items-center justify-center rounded-full bg-layer border border-divider/40 hover:opacity-95 transition relative"
                  aria-label="Notifications">
            <i class="fa-regular fa-bell" style="color: var(--color-primary)"></i>
            @if ($unreadNotifications->count())
              <span x-show="!openNotif"
                    class="absolute -top-1 -right-1 min-w-[20px] h-5 px-1 text-[10px] font-bold text-fg rounded-full flex items-center justify-center"
                    style="background: var(--color-primary)">
                {{ $unreadNotifications->count() }}
              </span>
            @endif
          </button>

          <div x-show="openNotif" @click.away="openNotif=false" x-transition
               class="absolute right-0 mt-3 w-80 max-h-96 bg-card border border-divider/40 rounded-xl shadow-lg p-4 space-y-3 z-50 hover-scrollbar overflow-y-auto">
            <h4 class="text-base font-semibold border-b border-divider/40 pb-2">Notifications</h4>
            @forelse ($unreadNotifications as $notif)
              <div wire:click="$emit('markAsRead', '{{ $notif->id }}')"
                   class="rounded-lg p-3 text-sm bg-layer hover:opacity-95 cursor-pointer transition border border-transparent hover:border-divider/40">
                <p class="font-medium">{{ $notif->data['title'] }}</p>
                <p class="text-foreground/80 text-xs mt-1">{{ $notif->data['message'] }}</p>
                <p class="text-foreground/60 text-xs mt-2">{{ $notif->created_at->diffForHumans() }}</p>
              </div>
            @empty
              <div class="text-center text-foreground/60 italic py-6 text-sm">You're all caught up</div>
            @endforelse
          </div>
        </div>

        {{-- Profile --}}
        <div x-data="{ open:false }" class="relative" x-cloak>
          <button @click="open = !open"
                  class="hidden sm:flex items-center gap-x-3 px-3 py-2 rounded-full bg-layer border border-divider/40 hover:opacity-95 transition"
                  aria-label="User Profile Menu">
            <div x-data="avatarUpdater()" x-init="init()">
              <template x-if="photoUrl">
                <img :src="photoUrl" alt="User Avatar"
                     class="w-8 h-8 rounded-full object-cover border border-divider/40" />
              </template>
              <template x-if="!photoUrl">
                <div class="w-8 h-8 flex items-center justify-center rounded-full text-sm font-bold uppercase bg-card border border-divider/40">
                  {{ strtoupper(substr($client->firstname ?? 'C', 0, 1)) }}{{ strtoupper(substr($client->middlename ?? 'C', 0, 1)) }}
                </div>
              </template>
            </div>
            <div class="hidden md:flex items-center space-x-2 text-xs leading-none">
              <span class="capitalize text-foreground/90">{{ strtolower($client->firstname ?? 'client') }} {{ $client->middlename }}</span>
              <i class="fa-solid fa-caret-down opacity-70"></i>
            </div>
          </button>

          <div x-show="open" @click.away="open=false" x-transition
               class="absolute right-0 mt-2 w-60 bg-card border border-divider/40 rounded-2xl shadow-xl z-50 overflow-hidden">
            <div class="px-4 py-3 border-b border-divider/40 text-center">
              <p class="text-sm font-semibold">{{ $client->firstname ?? 'Client' }} {{ $client->middlename ?? '' }}</p>
              <p class="text-xs text-foreground/60 mt-0.5">
                #{{ $client->client_id ?? '0000' }} &bullet; {{ ucfirst($client->role ?? 'client') }}
              </p>
            </div>

            <div class="flex flex-col px-3 py-2 space-y-1">
              <a href="{{ route('profile') }}"
                 class="flex items-center gap-2 hover:bg-layer px-3 py-1.5 rounded-xl transition">
                <i class="fa-regular fa-user opacity-70 text-sm"></i><span class="text-sm">Profile</span>
              </a>
              <a href="/client/profile/edit"
                 class="flex items-center gap-2 hover:bg-layer px-3 py-1.5 rounded-xl transition">
                <i class="fa-solid fa-gear opacity-70 text-sm"></i><span class="text-sm">Settings</span>
              </a>

           
            </div>

            <div class="border-t border-divider/40 px-3 py-2">
              <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                        class="w-full flex items-center gap-2 text-red-500 hover:bg-red-500/10 px-3 py-1.5 rounded-xl text-sm transition">
                  <i class="fa-solid fa-arrow-right-from-bracket text-sm"></i><span>Logout</span>
                </button>
              </form>
            </div>
          </div>
        </div>

        {{-- Mobile: hamburger --}}
        <button class="md:hidden w-10 h-10 flex items-center justify-center rounded-lg bg-layer border border-divider/40"
                @click="mobileOpen = !mobileOpen" aria-label="Open menu">
          <i class="fa-solid fa-bars"></i>
        </button>
      </div>
    </div>

    {{-- Mobile menu panel --}}
    <div class="md:hidden max-w-7xl mx-auto px-4"
         x-show="mobileOpen" x-transition @click.away="mobileOpen=false">
      <div class="mt-3 rounded-2xl border border-divider/40 bg-card shadow-lg overflow-hidden">
        <nav class="flex flex-col text-sm">
          <a href="/client/home" class="px-4 py-3 border-b border-divider/40 hover:bg-layer">Home</a>
          <a href="{{ route('talent') }}" class="px-4 py-3 border-b border-divider/40 hover:bg-layer">Talents</a>
          <a href="{{ route('about') }}" class="px-4 py-3 border-b border-divider/40 hover:bg-layer">About</a>
          <a href="{{ route('messages.index') }}" class="px-4 py-3 border-b border-divider/40 hover:bg-layer">Messages</a>
          <button type="button" onclick="openTicketModal()" class="text-left px-4 py-3 hover:bg-layer text-primary font-semibold">
            <i class="fa-solid fa-ticket-simple mr-2"></i> Submit Ticket
          </button>
        </nav>
      </div>
    </div>
  </header>

 <main class="max-w-7xl mx-auto px-4 md:px-10 pt-24 md:pt-28 pb-16 space-y-10 md:space-y-12">
  
  <!-- Hero -->
  <section class="relative rounded-3xl border border-divider/40 overflow-hidden shadow-md md:shadow-lg h-[34vh] sm:h-[38vh] md:h-[42vh] flex items-center justify-center bg-card">
    <video autoplay loop muted playsinline aria-hidden="true" class="absolute inset-0 w-full h-full object-cover">
      <source src="{{ asset('media/groove-feature-vid.mp4') }}" type="video/mp4" />
    </video>

    <!-- overlay -->
    <div class="absolute inset-0 bg-gradient-to-t from-black/50 via-black/20 to-transparent"></div>
    <div class="relative z-10 text-center text-fg-3 px-6">
      <h1 class="text-2xl sm:text-3xl md:text-4xl font-extrabold tracking-tight drop-shadow">Find your groove</h1>
      <p class="mt-2 text-xs sm:text-sm md:text-base opacity-90">Connect with coaches, book sessions, and explore studios near you.</p>
    </div>
  </section>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 md:gap-10">
    <div class="lg:col-span-2 space-y-6">

      <!-- Recommendations -->
      <section x-data="{ selectedUser: null }" class="border border-divider/40 rounded-3xl shadow-sm bg-card">
        @if ($recommendedUsers->isEmpty())
          <p class="p-5 text-foreground/60 italic text-sm">No recommendations yet.</p>
        @else
          <div class="flex items-center justify-between px-4 pt-4">
            <h3 class="text-base sm:text-lg font-semibold">Recommended for you</h3>
          </div>

          <div class="scroll-container flex gap-3 sm:gap-5 overflow-x-auto px-4 pb-4 snap-x snap-mandatory hide-scrollbar">
            @foreach ($recommendedUsers as $userCard)
              <article
                @click="selectedUser = {{ json_encode($userCard) }}"
                role="button"
                tabindex="0"
                @keydown.enter.prevent="selectedUser = {{ json_encode($userCard) }}"
                @keydown.space.prevent="selectedUser = {{ json_encode($userCard) }}"
                class="min-w-[175px] sm:min-w-[210px] snap-start rounded-2xl p-4 sm:p-5 border border-divider/40 bg-layer text-center shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all cursor-pointer outline-none focus:ring-2 focus:ring-primary/40 focus:ring-offset-1 focus:ring-offset-transparent"
                aria-label="Open profile card"
              >
                <div class="relative w-16 h-16 sm:w-20 sm:h-20 mx-auto rounded-full overflow-hidden bg-card text-foreground flex items-center justify-center font-bold text-lg sm:text-xl mb-3 border border-divider/40">
                  @if ($userCard->photo)
                    <img src="{{ asset('storage/' . $userCard->photo) }}" alt="{{ $userCard->firstname }}'s avatar" class="w-full h-full object-cover rounded-full" />
                  @else
                    {{ strtoupper(substr($userCard->firstname, 0, 1)) }}{{ strtoupper(substr($userCard->lastname, 0, 1)) }}
                  @endif
                </div>

                <p class="font-semibold text-sm sm:text-base truncate">
                  {{ $userCard->firstname }}
                  @if(!empty($userCard->middlename))
                    {{ strtoupper(substr($userCard->middlename, 0, 1)) }}.
                  @endif
                  @if(!empty($userCard->lastname))
                    {{ $userCard->lastname }}
                  @endif
                </p>

                <p class="text-[11px] sm:text-xs text-foreground/60 capitalize mt-1 tracking-wide">
                  {{ $userCard->role }}
                </p>
               @if (!empty($client->account_verified))
    <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-green-100 text-green-800 text-sm font-semibold shadow-md">
        <i class="fa-solid fa-circle-check text-green-600"></i>
    </span>
@endif
                <div class="hidden sm:flex items-center justify-center gap-2 mt-2 text-[11px] text-foreground/60">
                  @if(!empty($userCard->talent))
                    <span class="px-2 py-0.5 rounded-full border border-divider/40 bg-card/60 truncate max-w-[120px]">{{ $userCard->talents }}</span>
                  @endif
                  @if(!empty($userCard->city_name))
                    <span class="px-2 py-0.5 rounded-full border border-divider/40 bg-card/60 truncate max-w-[120px]">{{ $userCard->city_name }}</span>
                  @endif
                </div>
                 
              </article>
            @endforeach
          </div>
        @endif

        <!-- User modal -->
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
            class="relative w-full max-w-md  bg-card border border-divider/40 rounded-3xl p-5 sm:p-6 shadow-2xl overflow-auto max-h-[90vh]"
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

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm mb-6">
              <div>
                <p class="text-foreground/60 font-semibold">Initials</p>
                <p class="font-bold" x-text="((selectedUser?.firstname?.[0] || '') + (selectedUser?.lastname?.[0] || '')).toUpperCase() || 'N/A'"></p>
              </div>
              <div>
                <p class="text-foreground/60 font-semibold">Location</p>
                <p class="font-bold" x-text="selectedUser?.address || selectedUser?.city_name || 'N/A'"></p>
              </div>
              <div>
                <p class="text-foreground/60 font-semibold">Talent</p>
                <p class="font-bold" x-text="selectedUser?.talents || 'N/A'"></p>
              </div>
              <div>
                <p class="text-foreground/60 font-semibold">Phone</p>
                <p class="font-bold" x-text="selectedUser?.contact || 'N/A'"></p>
              </div>
            </div>

            <div class="mb-6 space-y-1">
              <p class="text-foreground/60 text-sm font-semibold">About</p>
              <p class="text-sm leading-relaxed" x-text="selectedUser?.bio || 'No description available.'"></p>
            </div>

            <template x-if="selectedUser?.email">
              <div class="mb-6">
                <p class="text-foreground/60 font-semibold">Email</p>
                <p class="font-bold break-all" x-text="selectedUser?.email || 'N/A'"></p>
              </div>
            </template>

            <div class="flex flex-col sm:flex-row gap-3 sm:gap-4">
              <a
                :href="selectedUser ? '{{ route('messages.index') }}' + '?with_id=' + selectedUser.id + '&with_type=' + selectedUser.role : '#'"
                class="w-full py-3 rounded-xl text-center text-sm sm:text-base font-semibold border border-divider/40 hover:bg-layer focus-visible:ring-2 focus-visible:ring-primary/40"
              >
                Message
              </a>
              <a
                :href="selectedUser ? '/userprofile/' + selectedUser.id : '#'"
                class="w-full py-3 rounded-xl text-center text-sm sm:text-base font-semibold bg-primary text-fg hover:bg-primary/90 focus-visible:ring-2 focus-visible:ring-primary/40"
              >
                View Profile
              </a>
            </div>
          </div>
        </div>
      </section>

      <!-- Appointments -->
      <section class="border border-divider/40 p-4 md:p-5 rounded-3xl shadow-sm bg-card">
        <div class="flex items-center justify-between mb-4 border-b border-divider/40 pb-3">
          <h3 class="text-lg font-semibold">Appointments</h3>
        </div>
        @forelse ($appointments as $appointment)
          <div class="flex flex-col md:flex-row md:items-center md:justify-between border border-divider/40 p-3 md:p-4 mb-4 rounded-2xl bg-layer/70 text-sm gap-3">
            <div class="flex md:block items-center justify-between w-full md:w-16">
              <span class="font-semibold text-foreground/60 text-[10px]">ID</span>
              <span class="text-[11px] md:block">{{ $appointment->appointment_id ?? 'N/A' }}</span>
            </div>
            <div class="flex items-center gap-2 w-full md:w-40">
              <div class="relative w-9 h-9">
                @if ($appointment->coach && $appointment->coach->photo)
                  <img src="{{ asset('storage/' . $appointment->coach->photo) }}" alt="{{ $appointment->coach->firstname }}'s photo" class="w-9 h-9 rounded-full object-cover border border-divider/40" />
                @else
                  <div class="w-9 h-9 flex items-center justify-center rounded-full text-[10px] uppercase border border-divider/40 bg-card">
                    {{ strtoupper(substr($appointment->coach->firstname ?? 'C', 0, 1)) }}{{ strtoupper(substr($appointment->coach->lastname ?? 'N', 0, 1)) }}
                  </div>
                @endif
              </div>
              <div class="flex flex-col">
                <span class="font-semibold text-foreground/60 text-[10px]">Coach</span>
                <span class="text-[11px]">{{ $appointment->coach->firstname ?? 'N/A' }}</span>
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
              <span class="font-semibold text-foreground/60 text-[10px]">Session</span>
              <span class="text-[11px]">{{ ucfirst($appointment->session_type ?? '—') }}</span>
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

      <section class="grid grid-cols-1 md:grid-cols-2 gap-6">
       
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
            <div class="flex justify-between items-center px-4 py-4 rounded-2xl mb-4 border border-divider/40 bg-layer">
              <div class="text-base sm:text-lg font-bold truncate">{{ $talent }}</div>
              <span class="text-xs sm:text-sm font-semibold border border-divider/40 px-3 py-1 rounded-full">#{{ $rank }}</span>
            </div>
            @php $rank++; @endphp
          @endforeach
        </div>
      </section>
    </div>

    <!-- Aside -->
    <aside class="space-y-6">
      <div class="w-full h-auto sm:h-64 flex flex-col items-center justify-center p-6 rounded-3xl space-y-3 text-center border border-divider/40 bg-card">
        <div x-data="avatarUpdater()" x-init="init()">
          <template x-if="photoUrl">
            <img :src="photoUrl" alt="Profile Photo" class="w-20 h-20 sm:w-24 sm:h-24 border border-divider/40 rounded-full object-cover shadow-sm" />
          </template>
          <template x-if="!photoUrl">
            <div class="w-20 h-20 sm:w-24 sm:h-24 flex items-center justify-center rounded-full text-2xl sm:text-3xl font-bold uppercase border border-divider/40 bg-layer">
              {{ strtoupper(substr($client->firstname ?? 'C', 0, 1)) }}{{ strtoupper(substr($client->middlename ?? 'C', 0, 1)) }}
            </div>
          </template>
        </div>
<p class="text-base sm:text-lg font-semibold capitalize">
    {{ ucwords(strtolower($client->firstname ?? '')) }}
    {{ ucwords(strtolower($client->middlename ?? '')) }}
    {{ ucwords(strtolower($client->lastname ?? '')) }}
</p>
                @if (!empty($client->account_verified))
    <span class="inline-flex items-center gap-2 px-3  rounded-full bg-green-100 text-green-800 text-sm font-semibold shadow-md">
        <i class="fa-solid fa-circle-check text-green-600"></i>
        Verified
    </span>
@endif
        <p class="text-xs sm:text-sm text-foreground/60">#{{ $client->client_id ?? '0000' }} &bullet; {{ ucfirst($client->role ?? 'client') }}</p>

        
        
        <a href="{{ route('profile') }}" class="px-4 py-2 border border-divider/40 rounded-full text-sm hover:bg-layer">Profile</a>
        
      </div>

      <!-- Calendar -->
      <div class="border border-divider/40 rounded-3xl p-4 sm:p-5 shadow-sm bg-card" id="calendar">
        <div class="flex justify-between items-center mb-4">
          <h4 class="font-semibold">Calendar</h4>
          <a href="{{ route('calendar') }}" class="opacity-80 hover:opacity-100" aria-label="Open calendar"><i class="fa-solid fa-expand"></i></a>
        </div>
        <div class="grid grid-cols-7 text-center text-[10px] sm:text-xs text-fg font-medium border-b border-divider/40 pb-2 mb-2">
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
  value="{{ old('name', trim(($client->firstname ?? '') . ' ' . ($client->lastname ?? ''))) }}"
  class="mt-1 w-full px-3 py-2 rounded-lg bg-layer border border-divider/40 focus:outline-none focus:ring-2 focus:ring-zinc-500"
  placeholder="Your name"
  required
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
  <div id="ticketLoadingOverlay" class="hidden fixed inset-0 z-[70] place-items-center bg-black/40 backdrop-blur-sm">
    <div class="rounded-2xl bg-card/90 border border-divider/40 px-5 py-4 shadow-xl flex items-center gap-3">
      <svg class="h-6 w-6 animate-spin" viewBox="0 0 24 24">
        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none" opacity=".25"></circle>
        <path d="M22 12a10 10 0 0 1-10 10" fill="currentColor"></path>
      </svg>
      <span class="text-sm font-medium">Submitting ticket…</span>
    </div>
  </div>
</div>



{{-- ========================= SCRIPTS ========================= --}}
<script>
  // ===== Calendar (original behavior) =====
  document.addEventListener("DOMContentLoaded", () => {
    const today = new Date();
    const year = today.getFullYear();
    const month = today.getMonth();
    const firstDay = new Date(year, month, 1);
    const lastDay = new Date(year, month + 1, 0);
    const totalDays = lastDay.getDate();
    const startDay = firstDay.getDay();
    const calendarDays = document.getElementById("calendar-days");
    if (!calendarDays) return;
    calendarDays.innerHTML = "";
    for (let i = 0; i < startDay; i++) calendarDays.innerHTML += `<span></span>`;
    for (let i = 1; i <= totalDays; i++) {
      const isToday = i === today.getDate();
      calendarDays.innerHTML += `
        <span class="py-1 rounded-md transition duration-150 ${isToday ? "bg-shadow text-fg font-bold" : "hover:bg-zinc-700 text-fg"}">${i}</span>`;
    }
  });

  // ===== Greeting (original behavior) =====
  document.addEventListener('DOMContentLoaded', () => {
    const hour = new Date().getHours();
    let greeting = "Hello";
    if (hour >= 5 && hour < 12) greeting = "Good Morning";
    else if (hour >= 12 && hour < 17) greeting = "Good Afternoon";
    else if (hour >= 17 && hour < 21) greeting = "Good Evening";
    else greeting = "Good Night";
    const g = document.getElementById('greeting');
    if (g) g.textContent = greeting;
  });

  // ===== Modal transitions & accessibility =====
  const ticketModal = document.getElementById('ticketModal');
  const backdrop = document.getElementById('ticketBackdrop');
  const panel = document.getElementById('ticketPanel');

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

  // ===== Toast (non-blocking, replaces alert) =====
  function showToast(message, type = 'success') {
    const t = document.createElement('div');
    t.className = `fixed right-4 top-4 z-[80] max-w-sm rounded-xl border px-4 py-3 shadow-xl
                   ${type === 'success' ? 'bg-emerald-500/10 border-emerald-500/30 text-emerald-200'
                                        : 'bg-red-500/10 border-red-500/30 text-red-200'}`;
    t.innerHTML = `<div class="flex items-start gap-3">
        <span class="mt-0.5">${type === 'success' ? '✅' : '⚠️'}</span>
        <div class="text-sm leading-snug">${message}</div>
      </div>`;
    document.body.appendChild(t);
    setTimeout(() => { t.style.opacity = '0'; t.style.transform = 'translateY(-6px)'; setTimeout(() => t.remove(), 200); }, 2400);
  }

  // ===== Loading state toggles =====
  function setSubmittingState(isLoading) {
    const btn = document.getElementById('ticketSubmitBtn');
    const spin = document.getElementById('ticketSpinner');
    const text = document.getElementById('ticketBtnText');
    const overlay = document.getElementById('ticketLoadingOverlay');
    const form = document.getElementById('ticketForm');

    if (isLoading) {
      btn.disabled = true;
      spin.classList.remove('hidden');
      text.textContent = 'Submitting…';
      overlay.classList.remove('hidden');
      // Disable inputs (except hidden) AFTER FormData is captured
      Array.from(form.elements).forEach(el => el.disabled = el.type !== 'hidden');
    } else {
      btn.disabled = false;
      spin.classList.add('hidden');
      text.textContent = 'Submit request';
      overlay.classList.add('hidden');
      Array.from(form.elements).forEach(el => el.disabled = false);
    }
  }

  // ===== Field error helpers =====
  function clearFieldErrors(form){
    form.querySelectorAll('.field-error').forEach(el => { el.textContent=''; el.classList.add('hidden'); });
    form.querySelectorAll('.input-error').forEach(el => el.classList.remove('input-error','border-red-500/50','focus:ring-red-500'));
    form.querySelectorAll('[aria-invalid="true"]').forEach(el => el.setAttribute('aria-invalid','false'));
  }

  function renderFieldErrors(form, errors){
    let firstErrorInput = null;
    Object.entries(errors).forEach(([field, msgs]) => {
      const msg = Array.isArray(msgs) ? msgs[0] : String(msgs || '');
      const target = form.querySelector(`.field-error[data-for="${field}"]`);
      const input  = form.querySelector(`[name="${field}"]`);
      if (target) { target.textContent = msg; target.classList.remove('hidden'); }
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
      fileError.classList.add('hidden');
      const f = fileInput.files?.[0];
      if (f && f.size > 5 * 1024 * 1024) {
        fileError.classList.remove('hidden');
        fileInput.value = '';
      }
    });
  }

  // ===== Submit handler (build FormData BEFORE disabling inputs) =====
  async function handleTicketSubmit(e) {
    e.preventDefault();
    const form   = e.target;
    const banner = document.getElementById('ticketBanner');

    clearFieldErrors(form);
    banner.classList.add('hidden');
    banner.textContent = '';

    try {
      // 1) Capture FormData while inputs are enabled
      const fd = new FormData(form);

      // 2) Now enter loading state (disables inputs)
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
      banner.className = "rounded-xl border px-4 py-3 text-sm bg-red-500/10 border-red-500/30 text-red-200";
      banner.textContent = 'Failed to submit: ' + (err?.message ?? 'Please try again.');
      banner.classList.remove('hidden');
      showToast('Failed to submit ticket.', 'error');
    } finally {
      setSubmittingState(false); // re-enable inputs
    }
    return false;
  }

  // ===== Avatar poller (unchanged) =====
  function avatarUpdater(){
    return {
      photoUrl: '{{ $client && $client->photo ? asset('storage/' . $client->photo) : '' }}',
      fetchPhoto(){
        fetch("{{ route('profile.photo') }}")
          .then(r=>r.json())
          .then(d=>{ this.photoUrl = d.photo_url; });
      },
      init(){ this.fetchPhoto(); setInterval(()=>this.fetchPhoto(), 5000); }
    }
  }
</script>
</body>
</html>
