@php
    $client = session('client');
    $coach = session('coach');
    $user = $client ?? $coach ?? auth()->user();

    $unreadNotifications = $user?->unreadNotifications ?? collect();
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
    <link rel="apple-touch-icon" href="/image/wc/logo.png" sizes="180x180">

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
        <a href="{{ route('talents') }}"
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
                <a href="{{ route('coach.profile') }}" class="flex items-center gap-2 hover:bg-layer px-3 py-1.5 rounded-xl transition"><i class="fa-regular fa-user text-muted text-sm"></i><span class="text-sm">Profile</span></a>
                <a href="{{ route('coach.profile.edit') }}" class="flex items-center gap-2 hover:bg-layer px-3 py-1.5 rounded-xl transition"><i class="fa-solid fa-gear text-muted text-sm"></i><span class="text-sm">Settings</span></a>
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



 <!-- MAIN -->
  <main class="w-full max-w-7xl mx-auto mt-32 px-4 pb-12">
    <!-- Hero Section -->
    <section class="text-center mb-20">
      <h1 class="text-5xl font-bold mb-6">About <span style="color: var(--color-primary)">Groove</span></h1>
      <p class="text-xl text-foreground/70 max-w-3xl mx-auto">
        San Jose Del Monte Bulacan's Web-Based Performing Arts Hub with Smart Chat Support and Studio Locator
      </p>
    </section>

    <!-- Project Context Section -->
    <section class="mb-20">
      <div class="grid md:grid-cols-3 gap-6 mt-8">
        <div class="bg-card p-5 rounded-xl border border-divider/40">
          <div class="mb-3">
            <i class="fa-solid fa-magnifying-glass text-xl" style="color: var(--color-primary)"></i>
          </div>
          <h3 class="text-xl font-semibold mb-2">Finding Coaches</h3>
          <p class="text-foreground/70">
            78.9% of artists in San Jose Del Monte Bulacan had difficulty finding available and qualified coaches or choreographers.
          </p>
        </div>

        <div class="bg-card p-5 rounded-xl border border-divider/40">
          <div class="mb-3">
            <i class="fa-solid fa-comments text-xl" style="color: var(--color-primary)"></i>
          </div>
          <h3 class="text-xl font-semibold mb-2">Communication Issues</h3>
          <p class="text-foreground/70">
            82.2% of artists experience delays or difficulty receiving responses when inquiring about availability, rates, or scheduling.
          </p>
        </div>

        <div class="bg-card p-5 rounded-xl border border-divider/40">
          <div class="mb-3">
            <i class="fa-solid fa-location-dot text-xl" style="color: var(--color-primary)"></i>
          </div>
          <h3 class="text-xl font-semibold mb-2">Studio Access</h3>
          <p class="text-foreground/70">
            86.8% of artists reported difficulty finding nearby studios in their area for rehearsals and practice.
          </p>
        </div>
      </div>
    </section>

    <!-- Mission / Vision -->
    <section class="mb-20">
      <div class="grid md:grid-cols-2 gap-8 mt-10">
        <div class="bg-card p-6 rounded-xl border border-divider/40">
          <h3 class="text-xl font-semibold mb-4" style="color: var(--color-primary)">Our Mission</h3>
          <p class="text-foreground/80">
            To create opportunities for artists to offer their service and showcase their talents by addressing
            the challenges that artists face such as the difficulty of finding coaches and choreographers, delays
            in communication, limited access to nearby studios, and the right platform to share their work with
            other performers.
          </p>
        </div>

        <div class="bg-card p-6 rounded-xl border border-divider/40">
          <h3 class="text-xl font-semibold mb-4" style="color: var(--color-primary)">Our Vision</h3>
          <p class="text-foreground/80">
            To develop a more accessible and efficient support system for the performing arts community that
            improves the overall experience and opportunities for performing artists, enabling them to thrive
            creatively and professionally.
          </p>
        </div>
      </div>
    </section>

    <!-- Key Features -->
    <section class="mb-20">
      <h2 class="text-3xl font-bold mb-6" style="color: var(--color-primary)">Key Features</h2>

      <div class="grid md:grid-cols-2 gap-8">
        <div class="bg-card p-6 rounded-xl border border-divider/40">
          <div class="flex items-center mb-4">
            <i class="fa-solid fa-robot text-xl mr-4" style="color: var(--color-primary)"></i>
            <h3 class="text-xl font-semibold">Smart Chat Support</h3>
          </div>
          <p class="text-foreground/80">
            Uses artificial intelligence to provide 24/7 responses to inquiries, ensuring faster and more efficient
            communication between clients and artists.
          </p>
        </div>

        <div class="bg-card p-6 rounded-xl border border-divider/40">
          <div class="flex items-center mb-4">
            <i class="fa-solid fa-users text-xl mr-4" style="color: var(--color-primary)"></i>
            <h3 class="text-xl font-semibold">Artist Directory</h3>
          </div>
          <p class="text-foreground/80">
            Allows clients to search for and connect with coaches or choreographers based on specific genres or
            expertise, reducing the difficulty of finding suitable matches.
          </p>
        </div>

        <div class="bg-card p-6 rounded-xl border border-divider/40">
          <div class="flex items-center mb-4">
            <i class="fa-solid fa-map-location-dot text-xl mr-4" style="color: var(--color-primary)"></i>
            <h3 class="text-xl font-semibold">Studio Locator</h3>
          </div>
          <p class="text-foreground/80">
            Helps users find nearby rehearsal spaces, addressing the issue of the difficulty of finding studios
            within a preferred distance.
          </p>
        </div>

        <div class="bg-card p-6 rounded-xl border border-divider/40">
          <div class="flex items-center mb-4">
            <i class="fa-solid fa-handshake text-xl mr-4" style="color: var(--color-primary)"></i>
            <h3 class="text-xl font-semibold">Community Platform</h3>
          </div>
          <p class="text-foreground/80">
            Enables artists to upload and present their work, offering a platform for visibility and collaboration
            within the performing arts community.
          </p>
        </div>
      </div>
    </section>

    <!-- Dev Team -->
    <section class="mb-20">
      <h2 class="text-3xl font-bold mb-6" style="color: var(--color-primary)">Development Team</h2>
      <p class="text-foreground/70 mb-8 text-center">
        This capstone project was developed by BSIT students from STI College San Jose Del Monte
      </p>

      <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
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
  <footer class="bg-card border-t border-divider/40 mt-20 py-10">
    <div class="max-w-7xl mx-auto px-4">
      <div class="flex flex-col md:flex-row justify-between items-center">
        <div class="mb-6 md:mb-0">
          <img src="/image/wc/logo.png" alt="Groove Logo" class="h-12 w-auto mx-auto md:mx-0">
        </div>
        <div class="text-foreground/70 text-sm text-center md:text-right">
          <p>© 2025 Groove: San Jose Del Monte Bulacan's Performing Arts Hub</p>
          <p>STI College San Jose Del Monte - BSIT Capstone Project</p>
        </div>
      </div>
    </div>
  </footer>

  <script>
    function avatarUpdater() {
      return {
        photoUrl: '{{ $client && $client->photo ? asset('storage/' . $client->photo) : '' }}',
        fetchPhoto() {
          fetch("{{ route('profile.photo') }}")
            .then(res => res.json())
            .then(data => { this.photoUrl = data.photo_url; });
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
