@php
$client = session('client');
$coach = session('coach');

$user = auth()->user() ?? $client ?? $coach;

$notifications = $user ? $user->notifications()->latest()->take(5)->get() : collect();
$unreadNotifications = $user ? $user->unreadNotifications : collect();

$initials = $client ? strtoupper(substr($client->firstname,0,1) . substr($client->lastname,0,1)) : '';
@endphp

<!DOCTYPE html>
<html lang="en" >
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Groove | Coach Appointments</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link rel="icon" href="/image/white.png" type="image/png" />
  <link rel="preconnect" href="https://fonts.bunny.net" />
  <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://kit.fontawesome.com/43f9926b04.js" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

  <link rel="icon" href="/image/bg/LOG.png" type="image/png" sizes="512x512">

  {{-- Your compiled assets --}}
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans min-h-screen w-full antialiased theme-{{ $appTheme }} bg-surface text-foreground">
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
                <img src="{{ asset('storage/' . $coach->photo) }}" alt="Avatar" class="w-8 h-8 rounded-full object-cover border border-ui">
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

  <div class="max-w-6xl mx-auto p-6 rounded-xl shadow-lg mt-20">
    <h1 class="text-3xl font-bold mb-6 text-center text-fg">Appointments List</h1>

    @php
      // ✅ Deduplicate BEFORE rendering so only one display per client+coach+date
      // (change the key if you want a different uniqueness rule)
      $uniqueAppointments = $appointments
          ->unique(function ($a) {
              return ($a->client_id ?? 'na').'-'.($a->coach_id ?? 'na').'-'.($a->date ?? 'na');
          })
          ->values(); // keep Alpine indexes contiguous
    @endphp

    @if ($uniqueAppointments->isEmpty())
      <p class="text-center text-muted">No appointments found.</p>
    @else
      <div x-data="{ currentIndex: 0 }" class="space-y-6">
        @foreach ($uniqueAppointments as $index => $appointment)
          <div x-show="currentIndex === {{ $index }}"
               x-transition:enter="transition ease-out duration-300"
               x-transition:enter-start="opacity-0 translate-y-4"
               x-transition:enter-end="opacity-100 translate-y-0"
               x-transition:leave="transition ease-in duration-200"
               x-transition:leave-start="opacity-100 translate-y-0"
               x-transition:leave-end="opacity-0 -translate-y-4"
               class="max-w-4xl mx-auto bg-white/60 rounded-lg shadow-md p-6 space-y-6 text-fg">

            <div class="flex justify-between items-center border-b pb-4">
              <div>
                <h2 class="text-lg font-semibold">Request Appointment</h2>
                <span class="text-sm text-muted">Appointment ID #{{ $appointment->appointment_id }}</span>
              </div>

              <div class="flex flex-col items-center gap-3">
                <div class="flex gap-4">
                  <button @click="currentIndex = (currentIndex - 1 + {{ $uniqueAppointments->count() }}) % {{ $uniqueAppointments->count() }}"
                          class="px-4 py-2 rounded-xl bg-white/10 text-fg backdrop-blur-md border border-white/30 shadow hover:bg-white/20 transition">
                    <i class="fa-solid fa-chevron-left"></i>
                  </button>
                  <button @click="currentIndex = (currentIndex + 1) % {{ $uniqueAppointments->count() }}"
                          class="px-4 py-2 rounded-xl bg-white/10 text-fg backdrop-blur-md border border-white/30 shadow hover:bg-white/20 transition">
                    <i class="fa-solid fa-chevron-right"></i>
                  </button>
                </div>
                <span class="text-sm text-muted">{{ $index + 1 }} of {{ $uniqueAppointments->count() }}</span>
              </div>
            </div>

            <div>
              <h3 class="font-semibold">Client Information</h3>
              <div class="flex items-center space-x-4">
                @php
                  $fn = $appointment->client->firstname ?? '';
                  $ln = $appointment->client->lastname ?? '';
                  $avatarInitials = strtoupper(mb_substr($fn, 0, 1) . mb_substr($ln, 0, 1));
                @endphp

                @if (!empty($appointment->client?->photo))
                  <img src="{{ asset('storage/' . $appointment->client->photo) }}" alt="Client Photo" class="w-20 h-20 rounded-full object-cover border border-zinc-400">
                @else
                  <div class="w-20 h-20 rounded-full bg-layer flex items-center justify-center text-fg text-xl font-semibold select-none border border-ui">
                    {{ $avatarInitials ?: 'NA' }}
                  </div>
                @endif

                <div>
                  <p class="font-bold">
                    {{ $appointment->client->firstname ?? 'N/A' }}
                    {{ $appointment->client->lastname ?? '' }}
                  </p>
                  <p class="text-sm text-muted">{{ $appointment->client->email ?? 'N/A' }}</p>
                  <p class="text-sm text-muted">{{ $appointment->client->contact ?? 'N/A' }}</p>
                </div>
              </div>
            </div>

            <div class="bg-layer p-4 rounded-md">
              <h4 class="text-sm font-semibold mb-2">Purpose</h4>
              <p class="text-foreground/80">{{ $appointment->purpose ?? 'N/A' }}</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 bg-layer p-4 rounded-md">
              <div>
                <p class="text-sm font-medium text-foreground/70">Date</p>
                <p class="text-fg">
                  {{ \Carbon\Carbon::parse($appointment->date)->format('l, d F Y') }}
                </p>
              </div>
              <div>
                <p class="text-sm font-medium text-foreground/70">Time</p>
                <p class="text-fg">{{ $appointment->start_time }} - {{ $appointment->end_time }}</p>
              </div>
            </div>

            <div>
              <h4 class="text-sm font-semibold mb-1">Experience</h4>
              <p class="text-foreground/80">{{ $appointment->experience ?? 'N/A' }}</p>
            </div>

            <div>
              <p class="text-sm font-semibold mb-1">Status:</p>

              @if ($appointment->status === 'pending')
                <div class="flex flex-wrap justify-end gap-3 mt-2">
                  {{-- Decline --}}
                  <form action="{{ route('appointments.decline', ['appointment' => $appointment->appointment_id]) }}"
                        method="POST"
                        onsubmit="return confirm('Are you sure you want to decline this appointment?');">
                    @csrf
                    <button type="submit"
                            class="px-4 py-2 rounded-full text-sm font-semibold border border-red-600 text-red-600 bg-white hover:bg-red-600 hover:text-white transition-all duration-200">
                      <i class="fa-solid fa-xmark mr-1"></i> Decline
                    </button>
                  </form>

                  {{-- Confirm --}}
                  <form action="{{ route('appointments.confirm', ['appointment' => $appointment->appointment_id]) }}"
                        method="POST">
                    @csrf
                    <button type="submit"
                            class="px-4 py-2 rounded-full text-sm font-semibold text-white bg-sky-600 hover:bg-sky-700 transition-all duration-200">
                      <i class="fa-solid fa-check mr-1"></i> Confirm
                    </button>
                  </form>
                </div>
              @else
                <span class="inline-block px-2 py-1 rounded-full text-xs font-bold
                  {{ $appointment->status === 'confirmed' ? 'bg-green-600 text-white' :
                     ($appointment->status === 'cancelled' ? 'bg-red-600 text-white' : 'bg-zinc-600 text-white') }}">
                  {{ ucfirst($appointment->status) }}
                </span>

                @if ($appointment->status === 'confirmed')
                  <form class="inline-block ml-2"
                        action="{{ route('appointments.complete', ['appointment' => $appointment->appointment_id]) }}"
                        method="POST"
                        onsubmit="return confirm('Mark this appointment as completed?');">
                    @csrf
                    <button type="submit"
                            class="px-3 py-1.5 rounded-full text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 transition">
                      Mark Completed
                    </button>
                  </form>
                @endif
              @endif
            </div>

            @if ($appointment->feedback)
              <div class="mt-4">
                <h4 class="text-sm font-semibold mb-1">Client Feedback</h4>
                <p class="italic text-foreground/80">"{{ $appointment->feedback }}"</p>
                <p class="text-sm text-muted">Rating: {{ $appointment->rating }}/5</p>
              </div>
            @endif
          </div>
        @endforeach
      </div>
    @endif
  </div>

  <script>
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
