@php
    use Illuminate\Support\Str;
    use Carbon\Carbon;

    $client = session('client') ?? session('coach') ?? auth()->user();

    $photoUrl   = $client?->photo ? asset('storage/' . $client->photo) : null;
    // Initials: FIRST + LAST only (Blade)
    $initials   = strtoupper(substr($client?->firstname ?? 'C', 0, 1) . substr($client?->lastname ?? 'C', 0, 1));
    $unreadNotifications = $client?->unreadNotifications ?? collect();

    $now = Carbon::now();
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Groove | Profile</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="preconnect" href="https://fonts.bunny.net" />
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://kit.fontawesome.com/43f9926b04.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <link rel="icon" href="/image/bg/LOG.png" type="image/png" sizes="512x512">
@vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body
    x-data="{ tab: 'about', showUploadBox: false, isConfirmed: false }"
    class="font-sans min-h-screen w-full antialiased theme-{{ $appTheme }} bg-surface text-foreground">

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

    <!-- Main -->
    <main class="max-w-7xl mx-auto px-6 md:px-10 pt-24 md:pt-28 pb-16" x-data="{ tab: 'info' }">
        <!-- Profile hero -->
        <section class="bg-card border border-divider/40 p-6 rounded-3xl shadow-lg">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div class="flex items-center gap-6">
                    <div class="w-24 h-24 rounded-full overflow-hidden shadow-lg border border-divider/40 bg-layer">
                        @if($client?->photo)
                            <img src="{{ asset('storage/' . $client->photo) }}" alt="Client Photo" class="w-full h-full object-cover">
                        @else
                            <div class="flex items-center justify-center h-full text-3xl font-bold text-foreground/50">
                                {{-- Initials: FIRST + LAST only --}}
                                {{ strtoupper(substr($client->firstname ?? 'C', 0, 1)) }}{{ strtoupper(substr($client->lastname ?? 'C', 0, 1)) }}
                            </div>
                        @endif
                    </div>

                    <div class="text-left space-y-1">
                        <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight leading-snug">
                            {{ $client->firstname }} {{ $client->middlename }} {{ $client->lastname }}
                        </h1>
                        <div class="flex items-center gap-2 text-sm">
                            <span class="px-2 py-0.5 rounded-full font-medium capitalize bg-layer border border-divider/40" style="color: var(--color-primary)">
                                {{ ucfirst($client->role) }}
                            </span>
                            <span class="opacity-50">|</span>
                            <span class="inline-block text-sm font-mono bg-layer px-2.5 py-0.5 rounded-md border border-divider/40">
                                ID {{ $client->client_id }}
                            </span>

                                            @if (!empty($client->account_verified))
    <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-green-100 text-green-800 text-sm font-semibold shadow-md">
        <i class="fa-solid fa-circle-check text-green-600"></i>
        Verified
    </span>
@endif
                        </div>


            
                    </div>
     
                </div>

                <div>
                    <a href="{{ route('profile.edit') }}"
                       class="px-4 py-2 rounded-full hover:opacity-95 transition bg-layer border border-divider/40">
                        Edit
                    </a>
                </div>
            </div>
        </section>

        <div class="lg:flex lg:gap-8 mt-8">
            <div class="w-full lg:w-2/3">
                <!-- Tabs -->
                <nav class="flex flex-wrap gap-2 text-sm font-semibold uppercase">
                    <button
                        @click="tab = 'posts'"
                        :class="tab === 'posts' ? 'bg-layer text-foreground shadow border border-divider/40' : 'bg-transparent text-foreground/70 hover:bg-layer border border-transparent hover:border-divider/40'"
                        class="px-4 py-2 rounded-xl transition-all duration-300">
                        Posts
                    </button>
                    <button
                        @click="tab = 'info'"
                        :class="tab === 'info' ? 'bg-layer text-foreground shadow border border-divider/40' : 'bg-transparent text-foreground/70 hover:bg-layer border border-transparent hover:border-divider/40'"
                        class="px-4 py-2 rounded-xl transition-all duration-300">
                        Info
                    </button>
                    <button
                        @click="tab = 'appointment'"
                        :class="tab === 'appointment' ? 'bg-layer text-foreground shadow border border-divider/40' : 'bg-transparent text-foreground/70 hover:bg-layer border border-transparent hover:border-divider/40'"
                        class="px-4 py-2 rounded-xl transition-all duration-300">
                        Appointment
                    </button>
                </nav>

                <div class="mt-8">
                    <!-- Info -->
             <section x-show="tab === 'info'" x-transition>
    <div class="bg-card border border-divider/40 p-6 rounded-2xl shadow-md space-y-5 overflow-hidden">
        <h3 class="text-lg font-bold pb-2 border-b border-divider/40" style="color: var(--color-primary)">Talent & Personal Info</h3>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
            <div class="break-words">
                <span class="block text-foreground/70 font-medium mb-1">Birthday</span>
                <p class="break-words">{{ \Carbon\Carbon::parse($client->birthdate)->format('F d, Y') }}</p>
            </div>

            <div class="sm:col-span-2 break-words">
                <span class="block text-foreground/70 font-medium mb-1">Address</span>
                <p class="break-words">{{ $client->address }}, {{ $client->barangay }}</p>
            </div>

            <div class="break-words">
                <span class="block text-foreground/70 font-medium mb-1">Contact</span>
                <p class="break-words">{{ $client->contact }}</p>
            </div>

            <div class="break-words">
                <span class="block text-foreground/70 font-medium mb-1">Email</span>
                <p class="break-words">{{ $client->email }}</p>
            </div>

            @php
                $idPath = $client?->valid_id_path ?? null;
                $idUrl  = $client?->valid_id_url ?? ($idPath ? asset('storage/'.$idPath) : null);
                $ext    = $idPath ? strtolower(pathinfo($idPath, PATHINFO_EXTENSION)) : null;
                $isImg  = in_array($ext, ['jpg','jpeg','png','gif','webp']);
                $isPdf  = $ext === 'pdf';

                $exists = $idPath ? \Illuminate\Support\Facades\Storage::disk('public')->exists($idPath) : false;
                $filename = $idPath ? basename($idPath) : null;

                $sizeBytes = $exists ? \Illuminate\Support\Facades\Storage::disk('public')->size($idPath) : null;
                $sizeHuman = $sizeBytes
                    ? ( $sizeBytes > 1048576
                        ? number_format($sizeBytes/1048576, 2).' MB'
                        : ($sizeBytes > 1024
                            ? number_format($sizeBytes/1024, 0).' KB'
                            : $sizeBytes.' B') )
                    : null;

                $lastMod = $exists
                    ? \Carbon\Carbon::createFromTimestamp(\Illuminate\Support\Facades\Storage::disk('public')->lastModified($idPath))
                    : null;
            @endphp

            <div class="sm:col-span-2 break-words" x-data="{ showIdModal:false }">
                <span class="block text-foreground/70 font-medium mb-2"> ID</span>

                @if($idUrl && $exists)
                    <div class="flex items-start gap-4 p-4 bg-layer border border-divider/40 rounded-xl">
                        {{-- Thumbnail / icon --}}
                        <div class="shrink-0">
                            @if($isImg)
                                <img src="{{ $idUrl }}" alt="ID"
                                     class="h-24 w-36 object-cover rounded-lg border border-divider/40 cursor-pointer"
                                     @click="showIdModal = true" />
                            @elseif($isPdf)
                                <div class="h-24 w-36 flex items-center justify-center rounded-lg border border-divider/40 bg-card">
                                    <i class="fa-regular fa-file-pdf text-3xl opacity-80"></i>
                                </div>
                            @else
                                <div class="h-24 w-36 flex items-center justify-center rounded-lg border border-divider/40 bg-card">
                                    <i class="fa-regular fa-file text-3xl opacity-80"></i>
                                </div>
                            @endif
                        </div>

                        {{-- Meta + actions --}}
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2 mb-1">
                                @if($ext)
                                    <span class="text-[11px] px-2 py-0.5 rounded-full border border-divider/40 uppercase">
                                        {{ $ext }}
                                    </span>
                                @endif
                            </div>

                            <div class="text-xs text-foreground/70 flex flex-wrap items-center gap-3 mb-3">
                                @if($sizeHuman)<span>{{ $sizeHuman }}</span>@endif
                                @if($lastMod)<span>• Uploaded {{ $lastMod->diffForHumans() }}</span>@endif
                            </div>

                            <div class="flex flex-wrap items-center gap-2">
                                @if($isImg)
                                    <button type="button"
                                            @click="showIdModal = true"
                                            class="px-3 py-1.5 rounded-lg bg-layer border border-divider/40 hover:opacity-95 transition">
                                        <i class="fa-regular fa-eye mr-1"></i> Preview
                                    </button>
                                @elseif($isPdf)
                                    <a href="{{ $idUrl }}" target="_blank"
                                       class="px-3 py-1.5 rounded-lg bg-layer border border-divider/40 hover:opacity-95 transition">
                                        <i class="fa-regular fa-eye mr-1"></i> View PDF
                                    </a>
                                @else
                                    <a href="{{ $idUrl }}" target="_blank"
                                       class="px-3 py-1.5 rounded-lg bg-layer border border-divider/40 hover:opacity-95 transition">
                                        <i class="fa-regular fa-eye mr-1"></i> View file
                                    </a>
                                @endif

                                <a href="{{ $idUrl }}" download
                                   class="px-3 py-1.5 rounded-lg bg-layer border border-divider/40 hover:opacity-95 transition">
                                    <i class="fa-solid fa-download mr-1"></i> Download
                                </a>

                                <button type="button"
                                        @click="navigator.clipboard.writeText('{{ $idUrl }}')"
                                        class="px-3 py-1.5 rounded-lg bg-layer border border-divider/40 hover:opacity-95 transition">
                                    <i class="fa-regular fa-copy mr-1"></i> Copy link
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Image modal --}}
                    @if($isImg)
                        <div x-show="showIdModal" x-transition
                             class="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex items-center justify-center p-4"
                             @click.self="showIdModal=false">
                            <div class="relative max-w-5xl w-full">
                                <button class="absolute -top-10 right-0 text-white text-3xl" @click="showIdModal=false">&times;</button>
                                <img src="{{ $idUrl }}" alt=" ID full preview"
                                     class="w-full max-h-[80vh] object-contain rounded-xl border-4 border-card shadow-2xl" />
                            </div>
                        </div>
                    @endif
                @else
                    <div class="p-4 bg-layer border border-dashed border-divider/50 rounded-xl text-foreground/60 text-sm">
                        N/A
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>


                    <!-- Appointments -->
                    <section x-show="tab === 'appointment'" x-transition>
                        <div class="bg-card border border-divider/40 p-6 rounded-2xl shadow-md">
                            <h3 class="text-xl font-bold mb-4" style="color: var(--color-primary)">My Appointments</h3>

                            @if (session('success'))
                                <div class="px-4 py-2 rounded mb-4 text-white" style="background: #16a34a">{{ session('success') }}</div>
                            @endif
                            @if (session('error'))
                                <div class="px-4 py-2 rounded mb-4 text-white" style="background: #dc2626">{{ session('error') }}</div>
                            @endif

                            @if ($appointments->count())
                                <div class="overflow-x-auto">
                                    <table class="min-w-full text-sm text-left rounded-lg">
                                        <thead class="bg-layer text-foreground/80 uppercase text-xs border-b border-divider/40">
                                            <tr>
                                                <th class="px-4 py-3">ID</th>
                                                <th class="px-4 py-3">Coach</th>
                                                <th class="px-4 py-3">Talent</th>
                                                <th class="px-4 py-3">Date</th>
                                                <th class="px-4 py-3">Time</th>
                                                <th class="px-4 py-3">Session</th>
                                                <th class="px-4 py-3">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-divider/40">
                                            @foreach ($appointments as $appointment)
                                                <tr class="hover:bg-layer">
                                                    <td class="px-4 py-3 font-semibold" style="color: var(--color-primary)">
                                                        {{ $appointment->appointment_id }}
                                                    </td>
                                                    <td class="px-4 py-3">
                                                        <div class="flex items-center gap-3">
                                                            @if ($appointment->coach && $appointment->coach->photo)
                                                                <img src="{{ asset('storage/' . $appointment->coach->photo) }}"
                                                                     alt="Coach Photo"
                                                                     class="w-8 h-8 rounded-full object-cover border border-divider/40">
                                                            @else
                                                                <div class="w-8 h-8 rounded-full bg-layer flex items-center justify-center text-xs border border-divider/40">
                                                                    <i class="fa-regular fa-user"></i>
                                                                </div>
                                                            @endif
                                                            <span>{{ $appointment->coach->full_name ?? 'Unknown Coach' }}</span>
                                                        </div>
                                                    </td>
                                                    <td class="px-4 py-3">{{ $appointment->talent ?? 'N/A' }}</td>
                                                    <td class="px-4 py-3">{{ \Carbon\Carbon::parse($appointment->date)->format('M d, Y') }}</td>
                                                    <td class="px-4 py-3">{{ $appointment->start_time }} - {{ $appointment->end_time }}</td>
                                                    <td class="px-4 py-3">{{ $appointment->session_type ?? 'N/A' }}</td>
                                                    <td class="px-4 py-3">
                                                        <span class="px-2 py-1 rounded text-xs font-semibold border border-divider/40">
                                                            {{ ucfirst($appointment->status) }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-foreground/60 italic">No appointments found.</p>
                            @endif
                        </div>
                    </section>

                    <!-- Posts -->
                    <section x-show="tab === 'posts'" x-transition class="space-y-8">
                        <div x-data="postsManager()" x-init="loadPosts(); startPolling()" class="bg-card border border-divider/40 p-2 rounded-2xl shadow-md">
                            <h3 class="text-xl font-bold mb-6">My Posts</h3>

                            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 relative gap-4">
                                <!-- New Post Button -->
                                <div @click="showUploadForm = true"
                                     class="flex flex-col items-center justify-center border border-dashed border-divider/60 text-foreground/70 bg-card hover:bg-layer transition h-[35vh] cursor-pointer group">
                                    <svg class="w-12 h-12 group-hover:scale-110 transition" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                                    </svg>
                                    <p class="mt-2 font-semibold">New Post</p>
                                </div>

                                <!-- Posts -->
                                <template x-for="post in posts" :key="post.id">
                                    <div class="relative group overflow-hidden border border-divider/40 bg-card shadow hover:shadow-md transition h-[35vh] w-[25vh]">
                                        <!-- Options -->
                                        <div x-data="{ open: false }" class="absolute -top-3 right-3 z-30">
                                            <button @click="open = !open"
                                                    class="bg-layer hover:opacity-95 p-1.5 rounded-full text-sm shadow ring-1 ring-divider/40">
                                                ⋯
                                            </button>
                                            <div x-show="open" @click.outside="open = false" x-transition
                                                 class="absolute right-0 w-48 bg-card border border-divider/40 rounded-xl shadow-xl z-50 overflow-hidden text-sm">
                                                <div class="px-4 py-2 border-b border-divider/40 text-xs text-foreground/70">
                                                    <span class="block font-semibold">Posted:</span>
                                                    <span x-text="formatDate(post.created_at)"></span>
                                                </div>
                                                <button @click="deletePost(post.id)"
                                                        class="w-full text-left px-4 py-2 hover:bg-layer transition">
                                                    Delete Post
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Media -->
                                        <div class="w-full h-full relative overflow-hidden">
                                            <template x-if="isVideo(post.media_url)">
                                                <video controls class="w-full h-full object-cover">
                                                    <source :src="post.media_url" type="video/mp4">
                                                </video>
                                            </template>
                                            <template x-if="!isVideo(post.media_url)">
                                                <img :src="post.media_url" alt="Post"
                                                     class="w-full h-full object-cover cursor-pointer group-hover:scale-105 transition duration-500"
                                                     @click="openImageModal(post.media_url)">
                                            </template>

                                            <div class="absolute bottom-0 left-0 w-full bg-gradient-to-t from-card to-transparent px-4 py-3">
                                                <p class="text-sm font-medium line-clamp-2" x-text="post.caption"></p>
                                            </div>
                                        </div>

                                        <div class="px-4 py-3 bg-layer text-xs flex items-center justify-between border-t border-divider/40">
                                            <div class="flex items-center gap-2 opacity-80">
                                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M10 10a4 4 0 100-8 4 4 0 000 8zM2 18a8 8 0 0116 0H2z" />
                                                </svg>
                                                <span class="font-semibold" x-text="post.client_name"></span>
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                <template x-if="posts.length === 0">
                                    <div class="col-span-full text-center text-foreground/60 italic">
                                        No posts yet. Start sharing your talent.
                                    </div>
                                </template>
                            </div>

                            <!-- Image Modal -->
                            <div x-show="showModal" x-transition @click="closeModal()"
                                 class="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex items-center justify-center">
                                <div class="relative">
                                    <button @click="closeModal()" class="absolute -top-5 -right-5 text-2xl font-bold hover:opacity-80">&times;</button>
                                    <img :src="modalImage" class="max-w-[90vw] max-h-[90vh] rounded-lg shadow-2xl border-4 border-card" @click.stop>
                                </div>
                            </div>

                            <!-- Upload Form Modal -->
                            <div x-show="showUploadForm" x-transition class="fixed inset-0 bg-black/70 backdrop-blur-sm z-50 flex items-center justify-center px-4">
                                <div class="bg-card border border-divider/40 p-6 rounded-2xl shadow-xl w-full max-w-xl relative max-h-[80vh] sm:max-h-[90vh] overflow-y-auto">
                                    <button @click="showUploadForm = false" class="absolute -top-4 -right-4 text-2xl hover:opacity-80">&times;</button>
                                    <h3 class="text-xl font-bold mb-4">Create a New Post</h3>

                                    <form action="{{ route('clientprofile_store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                                        @csrf
                                        <div @dragover.prevent="dragging = true" @dragleave.prevent="dragging = false" @drop.prevent="handleDrop($event)">
                                            <label class="block text-sm font-semibold mb-2">Upload Media</label>

                                            <div @click="$refs.fileInput.click()"
                                                 class="flex flex-col items-center justify-center p-6 border-2 border-dashed rounded-xl cursor-pointer transition duration-300
                                                        h-[40vh] max-h-[40vh] w-full overflow-hidden"
                                                 :class="dragging ? 'border-divider/40 bg-layer' : 'border-divider/40 bg-card'">

                                                <template x-if="!previewUrl">
                                                    <div class="text-center opacity-80">
                                                        <svg class="w-10 h-10 mx-auto mb-2" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v-1.25A5.25 5.25 0 018.25 10H12m0 0l-3-3m3 3l3-3m-3 3v10.5" />
                                                        </svg>
                                                        <p>Drag & drop or click to upload</p>
                                                        <p class="text-xs opacity-70 mt-1">Accepted: images/videos</p>
                                                    </div>
                                                </template>

                                                <template x-if="previewUrl">
                                                    <div class="w-full h-full flex items-center justify-center">
                                                        <template x-if="isImage">
                                                            <img :src="previewUrl" class="max-h-full max-w-full object-contain rounded-lg border border-divider/40 shadow">
                                                        </template>
                                                        <template x-if="!isImage">
                                                            <video controls class="max-h-full max-w-full object-contain rounded-lg border border-divider/40 shadow">
                                                                <source :src="previewUrl" type="video/mp4">
                                                            </video>
                                                        </template>
                                                    </div>
                                                </template>
                                            </div>

                                            <input type="file" name="media" accept="image/*,video/*" x-ref="fileInput" @change="handleFileChange" class="hidden">
                                        </div>

                                        <div>
                                            <label class="block text-sm font-semibold mb-1">Caption</label>
                                            <textarea name="caption" rows="2"
                                                      class="w-full px-4 py-2 bg-layer border border-divider/40 rounded-lg resize-none focus:ring-0"
                                                      placeholder="Write a caption..."></textarea>
                                        </div>

                                        <div class="text-right">
                                            <button type="submit"
                                                    class="px-4 py-2 bg-layer border border-divider/40 hover:opacity-95 rounded-lg">
                                                Post
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </section>

                </div>
            </div>

            <!-- Sidebar -->
            <aside class="w-full lg:w-1/3 mt-8 lg:mt-0 space-y-6">
                <div class="p-4 bg-card border border-divider/40 rounded-xl">
                    <div class="bg-layer p-5 rounded-xl shadow-md space-y-4 border border-divider/40">
                        <div class="text-lg font-semibold flex items-center gap-2">
                            <i class="fa-solid fa-user" style="color: var(--color-primary)"></i>
                            Account
                        </div>
                        <div class="text-sm border-t border-divider/40 pt-4 space-y-3">
                            <div class="flex items-center gap-3">
                                <i class="fa-solid fa-calendar-days opacity-70"></i>
                                <div>
                                    <p class="text-xs opacity-70">Joined</p>
                                    <p>{{ \Carbon\Carbon::parse($client->created_at)->format('M d, Y') }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <i class="fa-solid fa-signal opacity-70"></i>
                                <div>
                                    <p class="text-xs opacity-70">Status</p>
                                    <p style="color: var(--color-primary)">{{ $client->status ?? 'Offline' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </aside>
        </div>
    </main>

    <script>
        function postsManager() {
            return {
                posts: [],
                showUploadForm: false,
                showModal: false,
                modalImage: '',
                previewUrl: null,
                isImage: true,
                dragging: false,

                loadPosts() {
                    fetch("{{ route('profile-posts.fetch') }}")
                        .then(res => res.json())
                        .then(data => this.posts = data)
                        .catch(err => console.error("Error fetching posts:", err));
                },
                startPolling() {
                    this.loadPosts();
                    setInterval(() => this.loadPosts(), 10000);
                },
                deletePost(postId) {
                    fetch(`/profile-posts/${postId}`, {
                        method: 'DELETE',
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                    })
                    .then(res => {
                        if (res.ok) this.posts = this.posts.filter(p => p.id !== postId);
                        else console.error("Failed to delete post.");
                    })
                    .catch(err => console.error("Error deleting post:", err));
                },
                isVideo(path) {
                    return /\.(mp4|webm|ogg)$/i.test(path);
                },
                formatDate(dateString) {
                    const date = new Date(dateString);
                    return date.toLocaleString('en-US', {
                        year: 'numeric', month: 'short', day: 'numeric',
                        hour: 'numeric', minute: '2-digit'
                    });
                },
                openImageModal(imagePath) { this.modalImage = imagePath; this.showModal = true; },
                closeModal() { this.modalImage = ''; this.showModal = false; },
                handleFileChange(e) { const f = e.target.files[0]; this.preview(f); },
                handleDrop(e) { const f = e.dataTransfer.files[0]; this.$refs.fileInput.files = e.dataTransfer.files; this.preview(f); this.dragging = false; },
                preview(file) { if (!file) return; this.isImage = file.type.startsWith('image/'); this.previewUrl = URL.createObjectURL(file); }
            };
        }

        // (Kept for compatibility; avatar is server-rendered initials if no photo)
        function profilePhoto() {
            return {
                photoUrl: '{{ $client && $client->photo ? asset('storage/' . $client->photo) : '' }}',
                fetchPhoto() {
                    fetch("{{ route('profile.photo') }}")
                        .then(res => res.json())
                        .then(data => { this.photoUrl = data.photo_url; });
                },
                init() { this.fetchPhoto(); setInterval(() => this.fetchPhoto(), 5000); }
            };
        }

        function avatarUpdater() {
            return {
                photoUrl: '{{ $client && $client->photo ? asset('storage/' . $client->photo) : '' }}',
                fetchPhoto() {
                    fetch("{{ route('profile.photo') }}")
                        .then(res => res.json())
                        .then(data => { this.photoUrl = data.photo_url; });
                },
                init() { this.fetchPhoto(); setInterval(() => this.fetchPhoto(), 5000); }
            };
        }
    </script>

</body>
</html>
