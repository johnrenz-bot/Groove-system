@php
    // Resolve active user
    $client = session('client');
    $coach  = session('coach');
    $userCard = $client ?? $coach ?? auth()->user();

    // User initials (Blade only; 2 letters)
    $initials = $userCard
        ? strtoupper(substr($userCard->firstname ?? 'C', 0, 1) . substr($userCard->lastname ?? 'C', 0, 1))
        : '';

    // Notifications (safe null check)
    $notifications = $userCard
        ? $userCard->notifications()->latest()->take(5)->get()
        : collect();

    $unreadNotifications = $userCard
        ? $userCard->unreadNotifications
        : collect();
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Groove | Performing Arts Hub</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
  <script src="https://kit.fontawesome.com/43f9926b04.js" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
  <link rel="icon" href="/image/bg/LOG.png" type="image/png" sizes="512x512">
@vite(['resources/css/app.css', 'resources/js/app.js'])

  <style>
    .hover-scrollbar{scrollbar-width:thin;scrollbar-color:transparent transparent;scroll-behavior:smooth;padding-right:6px}
    .hover-scrollbar:hover{scrollbar-color:#71717a transparent}
    .hover-scrollbar::-webkit-scrollbar{width:6px}
    .hover-scrollbar::-webkit-scrollbar-thumb{background:transparent;border-radius:6px;transition:background .3s ease}
    .hover-scrollbar:hover::-webkit-scrollbar-thumb{background:#71717a}

    .scroll-container{scrollbar-gutter:stable both-edges}
    .scroll-container::-webkit-scrollbar{height:8px;background:transparent}
    .scroll-container::-webkit-scrollbar-thumb{background-color:transparent;border-radius:4px;transition:background-color .3s}
    .scroll-container:hover::-webkit-scrollbar-thumb{background-color:rgba(100,100,100,.5)}
    .scroll-container{scrollbar-width:thin;scrollbar-color:transparent transparent}
    .scroll-container:hover{scrollbar-color:rgba(100,100,100,.5) transparent}
  </style>
</head>

<body class="min-h-screen antialiased theme-{{ $appTheme ?? 'light' }} bg-surface text-foreground overflow-x-hidden relative">

  <!-- HEADER -->
  <header
    x-data="{ scrolled:false, mobileOpen:false, notifOpen:false, profileOpen:false }"
    x-init="window.addEventListener('scroll', () => { scrolled = window.scrollY > 10 })"
    class="w-full py-3 md:py-4 px-4 md:px-8 fixed top-0 left-0 z-50 transition duration-300 border-b border-divider/40"
    :class="scrolled ? 'bg-card/80 backdrop-blur shadow-sm' : 'bg-transparent'"
  >
    <div class="max-w-7xl mx-auto flex items-center justify-between">

      <!-- Left: Logo -->
      <a href="/client/home" class="flex items-center gap-3 shrink-0">
        <img src="/image/bg/LOG.png" alt="Logo" class="h-10 md:h-12 w-auto object-contain select-none" />
      </a>

      <!-- Desktop Nav -->
      <nav class="hidden md:flex items-center space-x-2 text-sm font-medium">
        <a href="/client/home"
           class="relative px-4 py-2 rounded-xl text-foreground/70 hover:text-foreground hover:bg-layer hover:border hover:border-divider/40 hover:shadow-md transition-all duration-300">
          Home
        </a>
        <a href="{{ route('talent') }}"
           class="relative px-4 py-2 rounded-xl text-foreground/70 hover:text-foreground hover:bg-layer hover:border hover:border-divider/40 hover:shadow-md transition-all duration-300">
          Talents
        </a>
        <a href="{{ route('about') }}"
           class="relative  border border-divider/50 shadow-inner  px-4 py-2 rounded-xl text-foreground/70 hover:text-foreground hover:bg-layer hover:border hover:border-divider/40 hover:shadow-md transition-all duration-300">
          About
        </a>
        <a href="{{ route('messages.index') }}"
           class="relative px-4 py-2 rounded-xl text-foreground/70 hover:text-foreground hover:bg-layer hover:border hover:border-divider/40 hover:shadow-md transition-all duration-300">
          Messages
        </a>

        
      </nav>

      <!-- Right cluster -->
      <div class="flex items-center gap-2 md:gap-4">

        <!-- Notifications -->
        <div class="relative" @keydown.escape.window="notifOpen=false">
          <button
            @click="notifOpen = !notifOpen"
            class="w-10 h-10 flex items-center justify-center rounded-full bg-layer border border-divider/40 hover:opacity-95 transition relative"
            aria-label="Notifications"
            aria-haspopup="menu"
            :aria-expanded="notifOpen"
          >
            <i class="fa-regular fa-bell" style="color: var(--color-primary)"></i>
            @if ($unreadNotifications->count())
              <span x-show="!notifOpen"
                    class="absolute -top-1 -right-1 w-5 h-5 text-[10px] font-bold text-white rounded-full flex items-center justify-center"
                    style="background: var(--color-primary)">
                {{ $unreadNotifications->count() }}
              </span>
            @endif
          </button>

          <!-- Notif dropdown -->
          <div
            x-cloak
            x-show="notifOpen"
            @click.outside="notifOpen=false"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-2 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="absolute right-0 mt-3 w-[33vh] max-h-96 bg-card border border-divider/40 rounded-xl shadow-xl p-4 space-y-3 z-50 hover-scrollbar overflow-y-auto"
            role="menu"
          >
            <h4 class="text-base font-semibold border-b border-divider/40 pb-2">Notifications</h4>
            @forelse ($unreadNotifications as $notif)
              <div wire:click="$emit('markAsRead', '{{ $notif->id }}')"
                   class="bg-layer hover:opacity-95 rounded-lg p-3 text-sm cursor-pointer transition border border-transparent hover:border-divider/40">
                <p class="font-medium">{{ $notif->data['title'] }}</p>
                <p class="text-foreground/80 text-xs mt-1">{{ $notif->data['message'] }}</p>
                <p class="text-foreground/60 text-xs mt-2">{{ $notif->created_at->diffForHumans() }}</p>
              </div>
            @empty
              <div class="text-center text-foreground/60 italic py-6 text-sm">You're all caught up</div>
            @endforelse
          </div>
        </div>

        <!-- Profile -->
        <div class="relative" @keydown.escape.window="profileOpen=false">
          <button
            @click="profileOpen = !profileOpen"
            class="flex items-center gap-x-2 md:gap-x-3 px-2.5 md:px-3 py-2 bg-layer rounded-full transition duration-200 border border-divider/40 hover:opacity-95"
            aria-label="User Profile Menu"
            aria-haspopup="menu"
            :aria-expanded="profileOpen"
          >
            @if ($userCard && !empty($userCard->photo))
              <img src="{{ asset('storage/' . $userCard->photo) }}" alt="User Avatar"
                   class="w-8 h-8 rounded-full object-cover border-2"
                   style="border-color: var(--color-primary)" />
            @else
              <div class="w-8 h-8 flex items-center justify-center bg-card rounded-full text-sm font-bold uppercase border border-divider/40">
                {{ $initials }}
              </div>
            @endif

            <div class="hidden sm:flex items-center space-x-2 text-xs leading-none">
              <span class="capitalize">
                {{ strtolower($userCard->firstname ?? 'client') }} {{ $userCard->middlename ?? '' }}
              </span>
              <i class="fa-solid fa-caret-down opacity-70"></i>
            </div>
          </button>

          <!-- Profile dropdown -->
          <div
            x-cloak
            x-show="profileOpen"
            @click.outside="profileOpen=false"
            x-transition
            class="absolute right-0 mt-2 w-60 bg-card rounded-2xl shadow-[0_12px_28px_rgba(0,0,0,0.35)] ring-1 ring-divider/40 z-50 overflow-hidden transition-all duration-300 origin-top border border-divider/40"
            role="menu"
          >
            <div class="px-4 py-3 bg-layer border-b border-divider/40 text-center">
              <p class="text-sm font-semibold">{{ $userCard->firstname ?? 'Client' }} {{ $userCard->middlename ?? '' }}</p>
              <p class="text-xs text-foreground/60 mt-0.5">
                #{{ $userCard->client_id ?? '0000' }} &bullet; {{ ucfirst($userCard->role ?? 'client') }}
              </p>
            </div>

            <div class="flex flex-col px-3 py-2 space-y-1">
              <a href="{{ route('profile') }}" class="flex items-center gap-2 hover:bg-layer px-3 py-1.5 rounded-xl transition">
                <i class="fa-regular fa-user opacity-70 text-sm"></i>
                <span class="text-sm">Profile</span>
              </a>
              <a href="/client/profile/EDIT" class="flex items-center gap-2 hover:bg-layer px-3 py-1.5 rounded-xl transition">
                <i class="fa-solid fa-gear opacity-70 text-sm"></i>
                <span class="text-sm">Settings</span>
              </a>
            </div>

            <div class="border-t border-divider/40 px-3 py-2">
              <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                        class="w-full flex items-center gap-2 text-red-500 hover:bg-red-500/10 px-3 py-1.5 rounded-xl text-sm transition">
                  <i class="fa-solid fa-arrow-right-from-bracket text-sm"></i>
                  <span>Logout</span>
                </button>
              </form>
            </div>
          </div>
        </div>

        <!-- Mobile hamburger -->
        <button
          class="md:hidden w-10 h-10 flex items-center justify-center rounded-full bg-layer border border-divider/40"
          @click="mobileOpen = !mobileOpen"
          :aria-expanded="mobileOpen"
          aria-label="Open menu"
        >
          <i class="fa-solid" :class="mobileOpen ? 'fa-xmark' : 'fa-bars'"></i>
        </button>
      </div>
    </div>

    <!-- Mobile Nav (slide-down panel) -->
    <div
      x-cloak
      x-show="mobileOpen"
      x-transition
      class="md:hidden mt-3 px-4"
      @click.outside="mobileOpen=false"
    >
      <nav class="bg-card border border-divider/40 rounded-xl shadow-lg overflow-hidden">
        <a href="/client/home" class="block px-4 py-3 text-sm hover:bg-layer border-b border-divider/40">Home</a>
        <a href="{{ route('talent') }}" class="block px-4 py-3 text-sm hover:bg-layer border-b border-divider/40">Talents</a>
        <a href="{{ route('about') }}" class="block px-4 py-3 text-sm hover:bg-layer border-b border-divider/40">About</a>
        <a href="{{ route('messages.index') }}" class="block px-4 py-3 text-sm hover:bg-layer">Messages</a>
      </nav>
    </div>
  </header>

  <!-- MAIN -->
  <main class="w-full max-w-7xl mx-auto mt-28 md:mt-32 px-4 pb-12">
    <!-- Hero Section -->
    <section class="text-center mb-16 md:mb-20">
      <h1 class="text-4xl md:text-5xl font-bold mb-4 md:mb-6">About <span style="color: var(--color-primary)">Groove</span></h1>
      <p class="text-base md:text-xl text-foreground/70 max-w-3xl mx-auto">
        San Jose Del Monte Bulacan's Web-Based Performing Arts Hub with Smart Chat Support and Studio Locator
      </p>
    </section>

    <!-- Project Context Section -->
    <section class="mb-16 md:mb-20">
      <div class="grid sm:grid-cols-2 md:grid-cols-3 gap-4 md:gap-6 mt-6 md:mt-8">
        <div class="bg-card p-5 rounded-xl border border-divider/40">
          <div class="mb-3">
            <i class="fa-solid fa-magnifying-glass text-xl" style="color: var(--color-primary)"></i>
          </div>
          <h3 class="text-lg md:text-xl font-semibold mb-2">Finding Coaches</h3>
          <p class="text-foreground/70">
            78.9% of artists in San Jose Del Monte Bulacan had difficulty finding available and qualified coaches or choreographers.
          </p>
        </div>

        <div class="bg-card p-5 rounded-xl border border-divider/40">
          <div class="mb-3">
            <i class="fa-solid fa-comments text-xl" style="color: var(--color-primary)"></i>
          </div>
          <h3 class="text-lg md:text-xl font-semibold mb-2">Communication Issues</h3>
          <p class="text-foreground/70">
            82.2% of artists experience delays or difficulty receiving responses when inquiring about availability, rates, or scheduling.
          </p>
        </div>

        <div class="bg-card p-5 rounded-xl border border-divider/40">
          <div class="mb-3">
            <i class="fa-solid fa-location-dot text-xl" style="color: var(--color-primary)"></i>
          </div>
          <h3 class="text-lg md:text-xl font-semibold mb-2">Studio Access</h3>
          <p class="text-foreground/70">
            86.8% of artists reported difficulty finding nearby studios in their area for rehearsals and practice.
          </p>
        </div>
      </div>
    </section>

    <!-- Mission / Vision -->
    <section class="mb-16 md:mb-20">
      <div class="grid md:grid-cols-2 gap-6 md:gap-8 mt-6 md:mt-10">
        <div class="bg-card p-5 md:p-6 rounded-xl border border-divider/40">
          <h3 class="text-lg md:text-xl font-semibold mb-3 md:mb-4" style="color: var(--color-primary)">Our Mission</h3>
          <p class="text-foreground/80">
            To create opportunities for artists to offer their service and showcase their talents by addressing
            the challenges that artists face such as the difficulty of finding coaches and choreographers, delays
            in communication, limited access to nearby studios, and the right platform to share their work with
            other performers.
          </p>
        </div>

        <div class="bg-card p-5 md:p-6 rounded-xl border border-divider/40">
          <h3 class="text-lg md:text-xl font-semibold mb-3 md:mb-4" style="color: var(--color-primary)">Our Vision</h3>
          <p class="text-foreground/80">
            To develop a more accessible and efficient support system for the performing arts community that
            improves the overall experience and opportunities for performing artists, enabling them to thrive
            creatively and professionally.
          </p>
        </div>
      </div>
    </section>

    <!-- Key Features -->
    <section class="mb-16 md:mb-20">
      <h2 class="text-2xl md:text-3xl font-bold mb-4 md:mb-6" style="color: var(--color-primary)">Key Features</h2>

      <div class="grid sm:grid-cols-2 gap-6 md:gap-8">
        <div class="bg-card p-5 md:p-6 rounded-xl border border-divider/40">
          <div class="flex items-center mb-3 md:mb-4">
            <i class="fa-solid fa-robot text-xl mr-3 md:mr-4" style="color: var(--color-primary)"></i>
            <h3 class="text-lg md:text-xl font-semibold">Smart Chat Support</h3>
          </div>
          <p class="text-foreground/80">
            Uses artificial intelligence to provide 24/7 responses to inquiries, ensuring faster and more efficient
            communication between clients and artists.
          </p>
        </div>

        <div class="bg-card p-5 md:p-6 rounded-xl border border-divider/40">
          <div class="flex items-center mb-3 md:mb-4">
            <i class="fa-solid fa-users text-xl mr-3 md:mr-4" style="color: var(--color-primary)"></i>
            <h3 class="text-lg md:text-xl font-semibold">Artist Directory</h3>
          </div>
          <p class="text-foreground/80">
            Allows clients to search for and connect with coaches or choreographers based on specific genres or
            expertise, reducing the difficulty of finding suitable matches.
          </p>
        </div>

        <div class="bg-card p-5 md:p-6 rounded-xl border border-divider/40">
          <div class="flex items-center mb-3 md:mb-4">
            <i class="fa-solid fa-map-location-dot text-xl mr-3 md:mr-4" style="color: var(--color-primary)"></i>
            <h3 class="text-lg md:text-xl font-semibold">Studio Locator</h3>
          </div>
          <p class="text-foreground/80">
            Helps users find nearby rehearsal spaces, addressing the issue of the difficulty of finding studios
            within a preferred distance.
          </p>
        </div>

        <div class="bg-card p-5 md:p-6 rounded-xl border border-divider/40">
          <div class="flex items-center mb-3 md:mb-4">
            <i class="fa-solid fa-handshake text-xl mr-3 md:mr-4" style="color: var(--color-primary)"></i>
            <h3 class="text-lg md:text-xl font-semibold">Community Platform</h3>
          </div>
          <p class="text-foreground/80">
            Enables artists to upload and present their work, offering a platform for visibility and collaboration
            within the performing arts community.
          </p>
        </div>
      </div>
    </section>

    <!-- Dev Team -->
    <section class="mb-16 md:mb-20">
      <h2 class="text-2xl md:text-3xl font-bold mb-4 md:mb-6" style="color: var(--color-primary)">Development Team</h2>
      <p class="text-foreground/70 mb-6 md:mb-8 text-center">
        This capstone project was developed by BSIT students from STI College San Jose Del Monte
      </p>

      <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6">
        <div class="bg-card p-5 rounded-xl border border-divider/40 text-center">
          <h3 class="font-semibold">John Renz C. Bandianon</h3>
          <p class="text-foreground/60 text-sm">Developer</p>
        </div>
        <div class="bg-card p-5 rounded-xl border border-divider/40 text-center">
          <h3 class="font-semibold">Charles John L. Carbonel</h3>
          <p class="text-foreground/60 text-sm">Developer</p>
        </div>
        <div class="bg-card p-5 rounded-xl border border-divider/40 text-center">
          <h3 class="font-semibold">Melaiza B. Fernandez</h3>
          <p class="text-foreground/60 text-sm">Developer</p>
        </div>
        <div class="bg-card p-5 rounded-xl border border-divider/40 text-center">
          <h3 class="font-semibold">Adrian Martin D. Salaysay</h3>
          <p class="text-foreground/60 text-sm">Developer</p>
        </div>
      </div>
    </section>
  </main>

  <!-- FOOTER -->
  <footer class="bg-card border-t border-divider/40 mt-16 md:mt-20 py-8 md:py-10">
    <div class="max-w-7xl mx-auto px-4">
      <div class="flex flex-col md:flex-row justify-between items-center gap-4">
        <div>
          <img src="/image/wc/logo.png" alt="Groove Logo" class="h-10 md:h-12 w-auto mx-auto md:mx-0">
        </div>
        <div class="text-foreground/70 text-xs md:text-sm text-center md:text-right">
          <p>© 2025 Groove: San Jose Del Monte Bulacan's Performing Arts Hub</p>
          <p>STI College San Jose Del Monte - BSIT Capstone Project</p>
        </div>
      </div>
    </div>
  </footer>

  <script>
    // Optional: refresh photo periodically (kept, but initials already Blade-only)
    document.addEventListener('alpine:init', () => {
      // no x-text anywhere; initials rendered server-side
    });
  </script>
</body>
</html>
