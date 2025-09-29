@php
    $client = session('client');
    $coach  = session('coach');
    $user   = $client ?? $coach ?? auth()->user();
    $unreadNotifications = $user?->unreadNotifications ?? collect();

    use Illuminate\Support\Str;
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Groove | Performing Arts Hub</title>

  <link rel="icon" href="/image/bg/LOG.png" type="image/png" sizes="512x512">
  <link rel="apple-touch-icon" href="/image/wc/logo.png" sizes="180x180">

  {{-- Fonts --}}
  <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet">

  {{-- Vite build --}}
  @vite(['resources/css/app.css','resources/js/Client/home.js'])

  {{-- Icons & Alpine --}}
  <script src="https://kit.fontawesome.com/43f9926b04.js" crossorigin="anonymous" defer></script>
  <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen antialiased theme-{{ $appTheme ?? 'light' }} bg-surface text-foreground overflow-x-hidden relative">

  {{-- Toast messages if needed --}}
  @if(session('show_welcome'))
    <div x-data="{ show:true }" x-init="setTimeout(()=>show=false,3000)" x-show="show" x-transition
        class="fixed bottom-6 right-6border  text-slate-800 px-5 py-4 rounded-xl shadow-lg flex items-center gap-3 z-50">
      <div>
        <div class="font-semibold">Welcome!</div>
        <div class="capitalize font-medium text-slate-600">{{ $coach->fullname ?? 'Coach' }}</div>
      </div>
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

  
<main x-data="mainApp()" class="relative z-10 mt-24 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto text-fg-3">

  {{-- TALENT CHOOSER --}}
  <section x-show="!selectedTalent" id="talent-section" class="flex items-center flex-col">
    <h1 class="text-5xl  font-progress skew-x-7 mb-6">
      CHOICE TALENTS OF CHOREOGRAPHER
    </h1>

    <div class="flex justify-center flex-wrap gap-6">
      @foreach ([
        ['img'=>'dance.jpg','title'=>'Dance','desc'=>'Connect with dancers.'],
        ['img'=>'singg.png','title'=>'Singing','desc'=>'Explore your voice.'],
        ['img'=>'acting.jpg','title'=>'Acting','desc'=>'Refine your drama skills.'],
        ['img'=>'theater.jpg','title'=>'Theater','desc'=>'Stage experience matters.'],
      ] as $card)
        <div
          @click="
            selectedTalent='{{ $card['title'] }}';
            viewMode='coach';
            setQuery('talent', selectedTalent);
            setQuery('view', viewMode);
          "
          class="relative w-[22%] min-w-[240px] h-[70vh] skew-x-[-7deg] hover:scale-105 transition cursor-pointer rounded-xl shadow-lg group"
        >
          <img src="/image/wc/{{ $card['img'] }}" class="w-full h-full object-cover skew-x-[2deg] rounded-xl" alt="{{ $card['title'] }} Image" />
          <div class="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent flex flex-col justify-end items-center p-6 text-center rounded-xl">
            <p class=" text-2xl mb-1 px-4 py-1 font-progress">{{ $card['title'] }}</p>
            <span class="w-10 h-1 bg-gray-300 rounded-full opacity-0 group-hover:opacity-100 transition mb-2"></span>
            <p class="text-sm  opacity-0 group-hover:opacity-100 transition">{{ $card['desc'] }}</p>
          </div>
        </div>
      @endforeach
    </div>
  </section>
  <!-- TALENT HEADER + TOGGLE -->
  <section x-show="selectedTalent" x-transition class="mt-6">
    <div class="flex items-center justify-between gap-3 rounded-2xl bg-white/70 backdrop-blur supports-[backdrop-filter]:bg-white/50 border border-black/5 px-3 sm:px-4 py-2.5">
      <button
        type="button"
        @click="onTalentChange(''); selectedCoach=null;"
        class="inline-flex items-center gap-2 text-sm text-neutral-600 hover:text-neutral-800 rounded-lg px-3 py-1.5 ring-1 ring-black/10 hover:ring-black/20 focus:outline-none focus-visible:ring-2 focus-visible:ring-black/30"
        aria-label="Back"
      >
        <i class="fa fa-arrow-left text-xs"></i>
        <span class="hidden sm:inline">Back</span>
      </button>

      <h2 class="mx-auto text-center text-2xl sm:text-3xl font-progress uppercase tracking-wide text-neutral-700">
        <span x-text="selectedTalent"></span>
      </h2>

      <div class="flex shrink-0 rounded-lg ring-1 ring-black/10 overflow-hidden">
        <button
          type="button"
          @click="viewMode='coach'; setQuery('view','coach')"
          :class="viewMode === 'coach' ? 'bg-neutral-900 text-white' : 'bg-white text-neutral-700 hover:bg-neutral-50'"
          class="px-3 sm:px-4 py-1.5 text-xs sm:text-sm font-medium transition"
        >
          Coaches
        </button>
        <button
          type="button"
          @click="viewMode='community'; setQuery('view','community')"
          :class="viewMode === 'community' ? 'bg-neutral-900 text-white' : 'bg-white text-neutral-700 hover:bg-neutral-50'"
          class="px-3 sm:px-4 py-1.5 text-xs sm:text-sm font-medium transition"
        >
          Community
        </button>
      </div>
    </div>
  </section>
<!-- COACH GRID -->
<div x-show="selectedTalent && viewMode==='coach'" x-transition class="mt-6">
<!-- Filters (sticky) -->
<div class="sticky top-16 z-20">
  <div class="rounded-2xl bg-white/80 backdrop-blur supports-[backdrop-filter]:bg-white/60 ring-1 ring-black/10 px-4 py-3">
    <form id="coach-filters" method="GET" action="{{ url()->current() }}"
          class="grid grid-cols-1 sm:grid-cols-4 gap-3 items-end">

      <!-- Local filter state -->
      <div
        x-data="{
          // URL mirrors
          get currentGenre(){ return new URLSearchParams(location.search).get('genre') ?? '' },
          get currentLocation(){ return new URLSearchParams(location.search).get('location') ?? '' },
          get currentFeeMax(){ return new URLSearchParams(location.search).get('fee_max') ?? '' },

          // Seed from server so selections persist after full reload
          tempGenre: @js(request('genre','')),
          tempLocation: @js(request('location','')),
          tempFeeMax: @js(request('fee_max','')),

          // Helpers
          clamp(n){ const v = Number(n); if (Number.isNaN(v)) return ''; return Math.min(10000, Math.max(0, Math.trunc(v))); },
          get dirty(){
            return this.tempGenre !== this.currentGenre
                || this.tempLocation !== this.currentLocation
                || String(this.tempFeeMax ?? '') !== String(this.currentFeeMax ?? '');
          }
        }"
        x-init="$nextTick(() => {
          // ensure native input values match on first paint
          const g=$el.querySelector('#genre'), l=$el.querySelector('#location'), f=$el.querySelector('#fee_max');
          if (g) g.value = tempGenre ?? '';
          if (l) l.value = tempLocation ?? '';
          if (f) f.value = tempFeeMax ?? '';
        })"
        class="contents"
      >

        <!-- preserve context (server fallbacks survive reload) -->
        <input type="hidden" name="talent" :value="selectedTalent" value="{{ request('talent','') }}">
        <input type="hidden" name="view"   :value="viewMode"        value="{{ request('view','coach') }}">

        <!-- Genre -->
        <div class="space-y-1">
          <label for="genre" class="block text-xs font-semibold text-neutral-600">Genre</label>
          <select id="genre" name="genre" x-model="tempGenre"
                  class="w-full rounded-lg border border-neutral-300 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-neutral-900/20">
            <option value="">All genres</option>
            <template x-if="selectedTalent">
              <template x-for="g in genreOptions" :key="g">
                <option :value="g" x-text="g"></option>
              </template>
            </template>
            <template x-if="!selectedTalent">
              <optgroup label="All Talents">
                <template x-for="(list, group) in talentCatalog" :key="group">
                  <template x-for="g in list" :key="group + '-' + g">
                    <option :value="g" x-text="`${group}: ${g}`"></option>
                  </template>
                </template>
              </optgroup>
            </template>
          </select>
        </div>

        <!-- Location -->
        <div class="space-y-1">
          <label for="location" class="block text-xs font-semibold text-neutral-600">Location</label>
          <select id="location" name="location" x-model="tempLocation"
                  class="w-full rounded-lg border border-neutral-300 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-neutral-900/20">
            <option value="">(San Jose del Monte) — Any barangay</option>
            <template x-for="b in barangaysSJDM" :key="b">
              <option :value="b" x-text="b"></option>
            </template>
          </select>
        </div>

        <!-- Service Fee (Max ₱) -->
        <div class="space-y-1">
          <label for="fee_max" class="block text-xs font-semibold text-neutral-600">Service Fee (Max ₱)</label>
          <div class="relative">
            <input
              id="fee_max"
              name="fee_max"
              type="number"
              min="0"
              max="10000"
              step="1"
              inputmode="numeric"
              x-model="tempFeeMax"
              x-on:input="tempFeeMax = clamp($event.target.value)"
              :class="[
                'w-full rounded-lg bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2',
                'border',
                (Number(tempFeeMax) > 10000 ? 'border-red-400 focus:ring-red-300' : 'border-neutral-300 focus:ring-neutral-900/20')
              ].join(' ')"
              placeholder="Any"
              aria-describedby="fee-help"
            >
            <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-xs text-neutral-400">₱</span>
          </div>
        </div>

        <!-- Confirm -->
        <div class="flex sm:justify-end">
          <button
            type="submit"
            x-data="{ submitting:false }"
            x-on:click.prevent="
              submitting = true;
              tempFeeMax = clamp(tempFeeMax);

              setQuery('genre', tempGenre);
              setQuery('location', tempLocation);
              setQuery('fee_max', String(tempFeeMax||''));
              $nextTick(() => {
                document.getElementById('genre').value = tempGenre;
                document.getElementById('location').value = tempLocation;
                document.getElementById('fee_max').value = tempFeeMax;
                document.getElementById('coach-filters').submit();
              });
            "
            :disabled="!dirty || submitting"
            class="group inline-flex items-center gap-2 rounded-xl px-5 py-2.5 text-sm font-semibold
                   text-white bg-gradient-to-r from-neutral-900 to-zinc-800
                   hover:from-black hover:to-zinc-700 active:scale-[0.99]
                   shadow-lg shadow-black/10 ring-1 ring-black/10
                   focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-neutral-900
                   disabled:opacity-50 disabled:cursor-not-allowed transition"
            aria-label="Apply filters"
          >
            <!-- Check icon -->
            <svg x-show="!submitting" class="w-4 h-4 transition-transform group-hover:scale-110"
                 viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
              <path fill-rule="evenodd"
                    d="M16.707 5.293a1 1 0 010 1.414l-7.25 7.25a1 1 0 01-1.414 0l-3-3a1 1 0 111.414-1.414l2.293 2.293 6.543-6.543a1 1 0 011.414 0z"
                    clip-rule="evenodd"/>
            </svg>
            <!-- Spinner while submitting -->
            <svg x-show="submitting" class="w-4 h-4 animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke-width="4"></circle>
              <path class="opacity-75" d="M4 12a8 8 0 018-8" stroke-width="4" stroke-linecap="round"></path>
            </svg>
            <span x-show="!submitting">Confirm</span>
            <span x-show="submitting">Applying…</span>
          </button>
        </div>

      </div>
    </form>
  </div>
</div>


  <!-- Cards -->
  <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
    @forelse ($coaches as $coach)
      @php
        $talentsArray = collect(explode(',', (string) $coach->talents))
                          ->map(fn($t) => trim($t))->filter()->values()->all();
      @endphp

      <div
        x-data="{
          talents: @js($talentsArray),
          genres: @js($coach->genres ? array_values(array_filter(array_map('trim', explode(',', $coach->genres)))) : []),
          barangay: @js($coach->barangay_name ?? $coach->barangay ?? ''),
          city: @js($coach->city_name ?? $coach->city ?? ''),
          fee: @js((int) ($coach->service_fee ?? 0)),
          iconFor(t){
            const k=(t||'').toLowerCase();
            const map={ dance:'fa-music', singing:'fa-microphone', acting:'fa-theater-masks', theater:'fa-theater-masks' };
            return map[k] ?? 'fa-star';
          },
          norm(s){
            return (s||'').toString().toLowerCase()
              .normalize('NFD').replace(/[\u0300-\u036f]/g,'')
              .replace(/[^a-z0-9]+/g,'');
          },
          matchesFilters(genre, location, feeMax){
            const g=this.norm(genre||'');
            const l=this.norm(location||'');
            const max = Number(feeMax || 0);

            const genreOk=!g || this.genres.some(x=>{
              const nx=this.norm(x);
              return nx===g || nx.includes(g) || g.includes(nx);
            });
            const nb=this.norm(this.barangay);
            const nc=this.norm(this.city);
            const locationOk=!l || nb.includes(l) || l.includes(nb) || nc.includes(l) || l.includes(nc);

            const feeOk = !max || (Number(this.fee || 0) <= max);
            return genreOk && locationOk && feeOk;
          }
        }"
        x-show="
          talents.map(t=>t.toLowerCase()).includes((selectedTalent||'').toLowerCase())
          && matchesFilters(selectedGenre, selectedLocation, selectedFeeMax)
        "
        @click="selectedCoach = @js($coach)"
        class="relative group h-[260px] rounded-2xl overflow-hidden bg-card bg-surface
               hover:shadow-[0_0_20px_rgba(255,255,255,0.2)] transition-all cursor-pointer border"
      >
        @if ($coach->photo)
          <img src="{{ asset('storage/'.$coach->photo) }}"
               class="absolute inset-0 w-full h-full object-cover opacity-10 blur-[1px] rounded-xl select-none pointer-events-none"
               alt="Coach Photo" />
        @else
          <span class="absolute font-extrabold text-7xl opacity-10 top-3 left-5 select-none pointer-events-none">
            {{ Str::upper(Str::substr($coach->firstname,0,1).Str::substr($coach->lastname,0,1)) }}
          </span>
        @endif

        <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/30 to-transparent group-hover:from-black/80 rounded-2xl transition-all duration-300"></div>

        <div class="absolute bottom-3 left-1/2 -translate-x-1/2 w-[90%] bg-white/10 backdrop-blur-md rounded-lg px-4 py-3 z-10 shadow-md border border-white/10">
          <div class="flex justify-between text-sm font-semibold text-white/90">
            <span>{{ $coach->firstname }} {{ $coach->lastname }}</span>
            <span>#{{ $coach->coach_id }}</span>
          </div>

          <div class="mt-2 flex items-center justify-between text-xs text-zinc-300">
            <div class="flex items-center gap-2">
              <i class="fa" :class="iconFor(selectedTalent)"></i>
              <span class="bg-black/60 px-3 py-0.5 rounded-full uppercase tracking-wide font-semibold" x-text="selectedTalent"></span>
            </div>

            <span class="bg-black/60 px-3 py-0.5 rounded-full font-semibold" x-text="'₱' + (fee ?? 0)"></span>
          </div>

          <div class="mt-2 flex flex-wrap gap-1">
            <template x-for="t in talents" :key="t">
              <span class="text-[10px] px-2 py-[2px] rounded-full bg-white/5 border border-white/10 text-white/90" x-text="t"></span>
            </template>
          </div>
        </div>
      </div>
    @empty
      <div class="col-span-full">
        <p class="text-center text-zinc-500 mt-8">No coaches available.</p>
      </div>
    @endforelse
  </div>
</div>

<!-- COACH MODAL (dark/glass) -->
<div x-show="selectedCoach" x-cloak @click.outside="selectedCoach=null"
     class="fixed inset-0 bg-black/90 flex items-center justify-center z-50 p-6">
  <div @click.stop
       class="relative w-full max-w-md bg-gradient-to-br from-white/10 to-white/5 border border-white/20 rounded-2xl p-6 shadow-xl backdrop-blur-md overflow-auto max-h-[90vh] text-white">
    <button @click="selectedCoach=null" class="absolute top-4 right-4 text-white text-2xl font-bold hover:text-red-400" aria-label="Close">&times;</button>

    <div class="text-7xl font-extrabold text-fg opacity-5 absolute top-4 left-6 z-0"
         x-text="(selectedCoach.firstname?.charAt(0) + selectedCoach.lastname?.charAt(0)).toUpperCase()"></div>

    <div class="relative z-10">
      <h2 class="text-2xl font-extrabold mb-1" x-text="selectedCoach.firstname + ' ' + selectedCoach.lastname"></h2>
      <p class="font-bold text-purple-300 uppercase text-sm" x-text="selectedCoach.role ?? 'coach/choreographer'"></p>

      <!-- Optional: fee badge under the header -->
      <div class="mt-2">
        <span x-show="selectedCoach.service_fee"
              class="inline-flex items-center gap-2 px-2.5 py-1 rounded-full bg-zinc-800/70 border border-white/10 text-xs">
          <i class="fa fa-tag"></i>
          <span x-text="'₱' + Number(selectedCoach.service_fee || 0).toLocaleString()"></span>
          <template x-if="selectedCoach.duration">
            <span class="opacity-70">•</span>
          </template>
          <span x-show="selectedCoach.duration" x-text="selectedCoach.duration"></span>
        </span>
      </div>

      <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm text-fg-3">
        <div>
          <p class="font-semibold">User ID</p>
          <p class="font-bold" x-text="'#'+selectedCoach.coach_id"></p>
        </div>
        <div>
          <p class="font-semibold">City</p>
          <p class="font-bold" x-text="selectedCoach.city_name ?? selectedCoach.city"></p>
        </div>
        <div>
          <p class="font-semibold">Barangay</p>
          <p class="font-bold" x-text="selectedCoach.barangay_name ?? selectedCoach.barangay"></p>
        </div>
        <!-- CHANGED: show Service Fee here -->
        <div>
          <p class="font-semibold">Service Fee</p>
          <p class="font-bold" x-text="'₱' + Number(selectedCoach.service_fee || 0).toLocaleString()"></p>
        </div>
        <div>
          <p class="font-semibold">Talents</p>
          <p class="font-bold" x-text="selectedCoach.talents"></p>
        </div>
        <div>
          <p class="font-semibold">Email</p>
          <p class="font-bold" x-text="selectedCoach.email"></p>
        </div>
        <div>
          <p class="font-semibold">Phone</p>
          <p class="font-bold" x-text="selectedCoach.contact"></p>
        </div>
      </div>

      <div class="mt-4">
        <p class="text-zinc-400 text-sm font-semibold mb-1">Genres</p>
        <template x-for="genre in (selectedCoach.genres ? selectedCoach.genres.split(',') : [])" :key="genre">
          <span class="px-3 py-1 text-xs bg-zinc-700 border border-zinc-500 font-bold rounded-full inline-block mr-2 mt-1" x-text="genre.trim()"></span>
        </template>
      </div>

      <div class="mt-6 flex gap-4">
        <a :href="'{{ route('messages.index') }}?with_id=' + (selectedCoach.coach_id ?? '') + '&with_type=coach'"
           class="w-full py-3 border border-zinc-500 hover:bg-zinc-800 text-white rounded-xl text-center text-base font-bold transition">
          Message <span x-text="selectedCoach.firstname"></span>
        </a>
        <a :href="'{{ route('user.profile', ['id' => '__ID__']) }}'.replace('__ID__', selectedCoach.coach_id)"
           class="w-full py-3 border border-zinc-500 hover:bg-zinc-800 text-white rounded-xl text-center text-base font-bold transition">
          View Profile
        </a>
      </div>
    </div>
  </div>
</div>


  {{-- COMMUNITY --}}
  <section
    x-show="selectedTalent && viewMode === 'community'"
    x-transition
    data-community-anchor
    class="flex flex-col lg:flex-row gap-6 px-6 py-6 max-w-7xl mx-auto text-fg">

    <div class="flex-1 space-y-8 ">
      <div class="flex items-center gap-5 px-6 py-5 rounded-2xl border-fg border backdrop-blur-md shadow-lg card">
        <div class="w-16 h-16 flex items-center justify-center rounded-full bg-card text-fg text-xs font-semibold uppercase border-4 border-divider hadow-md ring-2 ring-zinc-700 ring-offset-2 ring-fg">
          <span x-text="selectedTalent || 'Groove'"></span>
        </div>
        <div>
          <h2 class="text-3xl font-extrabold tracking-wider text-fg bg-clip-text bg-gradient-to-r from-[var(--card)] via-[var(--brand)] to-[var(--card)] uppercase drop-shadow">
            <span x-text="selectedTalent || 'Groove'"></span> Community
          </h2>
          <p class="text-sm text-zinc-300 mt-1">Share your talent and vibe with the Groove community ✨</p>
        </div>
      </div>

      {{-- Create Post --}}
      <div x-data="{ showForm: false }" class="card">
        <div
          @click="showForm = true"
          x-show="!showForm"
          class="flex items-center gap-3 px-4 py-3 border  rounded-2xl bg- backdrop-blur cursor-pointer transition hover:border-zinc-500">
          @if ($client && $client->photo)
          @else
            <div class="w-8 h-8 flex items-center justify-center bg-layer rounded-full text-sm font-bold uppercase">
              {{ strtoupper(substr($client->firstname ?? 'C', 0, 1)) }}{{ strtoupper(substr($client->middlename ?? 'C', 0, 1)) }}
            </div>
          @endif
          <span class="text-sm italic">Input something...</span>
        </div>

        <form
          x-show="showForm"
          x-transition
          action="{{ route('community.store') }}"
          method="POST"
          enctype="multipart/form-data"
          class="mt-6 bg-card border text-fg border-border rounded-2xl p-6 shadow-lg space-y-6"
          x-data="mediaUploader()">
          @csrf
          <input type="hidden" name="talent" :value="selectedTalent">
          <input type="hidden" name="redirect_talent" :value="selectedTalent">
          <input type="hidden" name="redirect_view" value="community">

          <div class="flex items-center gap-4">
            <div class="w-11 h-11 flex items-center justify-center bg-card font-bold rounded-full uppercase shadow overflow-hidden">
              @if ($client && $client->photo)
                <img src="{{ asset('storage/' . $client->photo) }}" alt="Profile Photo" class="w-full h-full object-cover rounded-full" />
              @else
                {{ strtoupper(substr($client->firstname ?? 'C', 0, 1)) }}
              @endif
            </div>
            <div class="flex flex-col">
              <span class="text-base font-semibold capitalize">
                {{ auth('client')->user()->firstname ?? 'Client' }} {{ auth('client')->user()->lastname ?? '' }}
              </span>
              <span class="text-xs ">Posting to <span class="capitalize" x-text="selectedTalent"></span> community</span>
            </div>
          </div>

          <div @dragover.prevent="dragging = true" @dragleave.prevent="dragging = false" @drop.prevent="handleDrop($event)">
            <label class="block text-sm font-semibold  mb-2">Upload Media</label>
            <div @click="$refs.fileInput.click()" class="flex flex-col items-center justify-center p-6 border-2 border-dashed rounded-2xl cursor-pointer transition">
              <template x-if="!previewUrl">
                <div class="text-center space-y-2  bg-card ">
                  <svg class="w-12 h-12 mx-auto" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v-1.25A5.25 5.25 0 018.25 10H12m0 0l-3-3m3 3l3-3m-3 3v10.5" />
                  </svg>
                  <p class=" text-sm">Drag & drop or click to upload</p>
                  <p class="text-xs">Image or video, max 10MB</p>
                </div>
              </template>

              <template x-if="previewUrl">
                <div>
                  <div class="overflow-hidden rounded-lg border  shadow-md" style="width: 65vh; height: 256px;">
                    <template x-if="isImage">
                      <img :src="previewUrl" class="w-full h-full object-cover" alt="Media Preview">
                    </template>
                    <template x-if="!isImage">
                      <video controls class="w-full h-full object-cover">
                        <source :src="previewUrl" type="video/mp4">
                        Your browser does not support the video tag.
                      </video>
                    </template>
                  </div>
                </div>
              </template>
            </div>
            <input name="media" type="file" accept="image/*,video/*" class="hidden" x-ref="fileInput" @change="handleFile($event)">
          </div>

          <div>
            <label for="caption" class="block text-sm font-semibold text-zinc-300 mb-1">Caption</label>
            <textarea name="caption" id="caption" rows="3" class="w-full px-4 py-3 bg-card text-fg border border-zinc-700 rounded-xl resize-none focus:outline-none focus:ring-2 focus:ring-purple-500 placeholder-zinc-500" placeholder="Express your thoughts or message..."></textarea>
          </div>

          <div class="text-end">
            <button type="submit" class="px-6 py-2.5 bg-gradie  font-semibold rounded-xl shadow-md transition">
              Post to <span x-text="selectedTalent"></span>
            </button>
          </div>
        </form>
      </div>

      {{-- Posts --}}
      <div x-data="postFetcher()" x-init="$watch('selectedTalent', value => loadPosts(value)); loadPosts(selectedTalent); startPolling();" class="mx-auto w-full max-w-4xl space-y-6 mt-4 text-fg">
        <template x-for="post in posts" :key="post.id">
          <div x-data="{ showCommentForm:false, commentText:'', loadCommentsOnce(){ if(!$store.commentStore.comments[post.id]){ $store.commentStore.loadComments(post.id); } } }"
              class="relative bg-card backdrop-blur-sm p-2 rounded-2xl shadow-lg border border-zinc-800 space-y-4 transition hover:border-zinc-600">
            <div x-data="{ open:false }" class="absolute top-3 right-3 z-30">
              <button @click="open=!open" class="p-2  hover:text:white text-xl">⋯</button>
              <div x-show="open" @click.outside="open=false" x-transition class="absolute right-0 mt-2 w-40 bg-card border border-zinc-700 rounded-xl shadow-2xl z-50 text-sm overflow-hidden">
                <div class="px-3 py-2 border-b border-zinc-700 text-xs  bg-card">
                  <span class="block font-semibold ">Posted:</span>
                  <span x-text="formatDate(post.created_at)"></span>
                </div>
                <template x-if="post.is_owner">
                  <form :action="`/community/posts/${post.id}`" method="POST" @submit.prevent="deletePost(post.id)">
                    <input type="hidden" name="_method" value="DELETE">
                    <input type="hidden" name="_token" :value="document.querySelector('meta[name=csrf-token]').content">
                    <button type="submit" class="w-full px-3 py-2 text-left hover:bg-red-600/90 hover:text-white transition">Delete Post</button>
                  </form>
                </template>
              </div>
            </div>

            <div class="flex items-center gap-3">
              <div class="w-11 h-11 rounded-full overflow-hidden bg-fg-2 text-fg-2 flex items-center justify-center text-sm font-bold uppercase shadow-md">
                <template x-if="post.poster_photo">
                  <img :src="post.poster_photo" alt="Avatar" class="w-full h-full object-cover" />
                </template>
                <template x-if="!post.poster_photo">
                  <span x-text="post.poster_name?.charAt(0).toUpperCase() ?? 'U'"></span>
                </template>
              </div>
              <div class="text-sm">
                <p class=" font-semibold" x-text="post.poster_name"></p>
                <p class=" text-sm leading-relaxed" x-text="post.caption"></p>
              </div>
            </div>

            <template x-if="post.media_path">
              <div class="rounded-xl overflow-hidden border border-zinc-700/80 max-h-[450px] shadow-md">
                <template x-if="post.media_path.toLowerCase().endsWith('.jpg') || post.media_path.toLowerCase().endsWith('.jpeg') || post.media_path.toLowerCase().endsWith('.png')">
                  <img :src="post.media_path" class="w-full object-cover hover:scale-[1.02] transition duration-500" />
                </template>

                <template x-if="post.media_path.toLowerCase().endsWith('.mp4')">
                  <video controls class="w-full rounded-md max-h-[400px] object-cover">
                    <source :src="post.media_path" type="video/mp4">
                    Your browser does not support the video tag.
                  </video>
                </template>
              </div>
            </template>

            <div class="flex justify-center items-center text-fg-3 text-sm pt-1 space-x-6">
              <button @click="reactToPost(post.id)" class="transition flex items-center gap-1 font-medium" :class="post.reacted ? 'text-pink-500' : 'hover:text-pink-400'">
                <i :class="post.reacted ? 'fa-solid fa-heart' : 'fa-regular fa-heart'"></i>
                <span x-text="post.reacts"></span>
              </button>
              <button @click="showCommentForm = !showCommentForm; if(showCommentForm) loadCommentsOnce();" class="transition flex items-center gap-1 hover:text-blue-400 font-medium">
                <i class="fa-regular fa-comment"></i>
                <span x-text="post.comments_count"></span>
              </button>
            </div>

            <div x-show="showCommentForm" x-transition class="mt-3 border-t border-zinc-700 pt-4 space-y-3 text-fg">
              <div x-show="$store.commentStore.comments[post.id]?.length" class="space-y-3">
                <template x-for="c in $store.commentStore.comments[post.id]" :key="c.id">
                  <div class="flex items-start gap-3 bg-fg p-3 rounded-lg border border-zinc-700">
                    <template x-if="c.photo">
                      <img :src="c.photo" alt="Profile" class="w-9 h-9 rounded-full object-cover shadow" />
                    </template>
                    <template x-if="!c.photo">
                      <div class="w-9 h-9 rounded-full bg-card flex items-center justify-center font-bold text-sm select-none" x-text="c.initial"></div>
                    </template>
                    <div class="flex flex-col">
                      <span class="font-semibold text-sm" x-text="c.name"></span>
                      <span class="text-sm" x-text="c.body"></span>
                      <span class="text-xs  mt-1" x-text="c.date"></span>
                    </div>
                  </div>
                </template>
              </div>
              <form @submit.prevent="$store.commentStore.submitComment(post.id, commentText).then(()=>{ commentText=''; $store.commentStore.loadComments(post.id); })" class="flex items-start gap-2">
                <textarea x-model="commentText" rows="2" placeholder="Write a comment..." class="flex-grow p-1 rounded-lg bg-card border border-zinc-600 focus:ring-2 focus:ring-blue-500 focus:outline-none text-sm resize-none text-white placeholder-zinc-500"></textarea>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded-lg text-sm text-white font-semibold shadow-md transition whitespace-nowrap">
                  <i class="fa-solid fa-paper-plane"></i>
                </button>
              </form>
            </div>
          </div>
        </template>
      </div>
    </div>

    <aside class="w-full lg:w-1/3 space-y-6">
      <div class="bg-card text-fg backdrop-blur-md rounded-xl p-6 shadow-xl space-y-4">
        <div class="space-y-1">
          <h2 class="text-2xl font-extrabold bg-clip-text  uppercase tracking-wide">
            Groove Community
          </h2>
          <p class="text-sm">A hub for dancers, artists, and creatives on Groove. Join the discussion and share your talent!</p>
        </div>
        <div class="pt-4 border-t border-zinc-700 text-sm space-y-2">
          <div><strong>Talent:</strong> <span x-text="selectedTalent || 'Hip Hop'"></span></div>
          <div><strong>Category:</strong> Performance Art</div>
          <div><strong>Created:</strong> Jul 2025</div>
        </div>
      </div>

      <div class="bg-card  bg-line p-4 rounded-xl text-sm text-fg space-y-2">
        <h3 class="font-bold ">📜 Rules</h3>
        <ul class="list-disc list-inside space-y-1">
          <li>Be respectful to others.</li>
          <li>Only post Groove-related content.</li>
          <li>No spam or self-promotion.</li>
        </ul>
      </div>
    </aside>
  </section>
</main>


<script>
document.addEventListener('alpine:init', () => {
  // =========== Main Alpine Component ===========
  Alpine.data('mainApp', () => ({
    /** URL / server-seeded state */
    selectedTalent: @js(request('talent','')) || new URLSearchParams(location.search).get('talent') || '',
    selectedCoach: null,
    viewMode: @js(request('view','coach')) || new URLSearchParams(location.search).get('view') || 'coach',

    setQuery(k, v) {
      const u = new URL(location);
      if (v === '' || v === null || v === undefined) u.searchParams.delete(k);
      else u.searchParams.set(k, v);
      history.replaceState({}, '', u);
    },

    /** Catalogs */
    talentCatalog: {
      'Dance': ['Hip-hop','Breaking','Popping','Locking','Krump','House','Waacking','Voguing','Tutting','Animation','Litefeet','Memphis Jookin','Urban','Street','Choreography','Lyrical','Contemporary','Modern','Jazz','Theatre Jazz','Heels','Commercial','K-pop','J-pop','Ballet','Classical Ballet','Neoclassical','Pointe','Character','Ballroom','Waltz','Tango','Viennese Waltz','Foxtrot','Quickstep','Latin Ballroom','Cha-cha','Rumba','Samba','Paso Doble','Jive','Swing','Lindy Hop','Charleston','Balboa','West Coast Swing','East Coast Swing','Salsa (On1)','Salsa (On2)','Bachata (Sensual)','Bachata (Dominican)','Kizomba','Zouk','Afrobeats','Amapiano','Azonto','Dancehall','Reggaeton','Bollywood','Bhangra','Garba','Kathak Fusion','Tap','Irish','Flamenco','Belly Dance (Raqs Sharqi)','Hula','Tahitian','Cheer','Pom','Majorette','Drill','Freestyle','Experimental','Contact Improvisation','Capoeira','Folk/Traditional (incl. Tinikling, Cariñosa)'],
      'Singing': ['Pop','K-pop','J-pop','OPM','R&B','Contemporary R&B','Neo-Soul','Soul','Funk','Gospel','Ballad','Power Ballad','Acoustic','Singer-Songwriter','Indie','Alternative','Rock','Pop Rock','Alt Rock','Classic Rock','Punk','New Wave','Metal','Metalcore','Hard Rock','Hip-hop/Rap','Trap','Boom Bap','Spoken Word','EDM','House','Techno','Trance','Drum & Bass','Dubstep','Electropop','Dance','Country','Bluegrass','Folk','Americana','Blues','Jazz','Swing','Big Band','Bossa Nova','Latin','Reggaeton','Salsa','Bachata','Bolero','Mariachi','Reggae','Ska','Afrobeats/Amapiano (Vocal)','World','Classical','Opera','Art Song','Oratorio','Musical Theater','A Cappella','Barbershop','Choral','Lullaby/Children','Lo-fi','Ambient','Experimental','Holiday'],
      'Theater': ['Stage Acting','Musical','Shakespearean','Classical Greek/Roman','Period/Farce','Comedy','Drama','Melodrama','Improvisation','Devised Theater','Physical Theatre','Movement-Based','Mask Work','Pantomime','Commedia dell’arte','Absurdist','Epic/Brechtian','Realism/Naturalism','Site-Specific/Immersive','Monologue','Reader’s Theater','Puppetry','Shadow Play','Children’s Theatre','Experimental/Avant-garde'],
      'Acting': ['Film Acting','TV Acting','Web Series/Streaming','Teleserye/Soap','Commercial/Advert','Hosting/Presenting','Model/Print','Comedy/Sketch','Sitcom (Multi-cam)','Single-cam Drama','Action/Thriller','Rom-com','Period Piece','Voice Acting','Animation VO','Video Game VO','ADR/Dubbing','Narration/Documentary','Audiobook','Green Screen','Motion Capture/Performance Capture','Stunt/Action Basics','Audition Technique','Cold Reading','On-Camera Technique','Method Acting','Meisner Technique','Chekhov Technique','Classical Technique','Improvisation for Actors']
    },

    /** SJDM barangays */
    barangaysSJDM: [
      'Ciudad Real','Dulong Bayan','Francisco Homes - Guijo','Francisco Homes - Mulawin','Francisco Homes - Narra','Francisco Homes - Yakal',
      'Gaya-Gaya','Graceville','Gumaoc Central','Gumaoc East','Gumaoc West','Kaybanban','Kaypian','Maharlika',
      'Muzon Proper','Muzon East','Muzon West','Muzon South','Paradise III','Poblacion','Poblacion I','San Isidro','San Manuel',
      'Santa Cruz I','Santa Cruz II','Santa Cruz III','Santa Cruz IV','Santa Cruz V',
      'Fatima I','Fatima II','Fatima III','Fatima IV','Fatima V',
      'San Pedro','Citrus',
      'San Rafael I','San Rafael II','San Rafael III','San Rafael IV','San Rafael V',
      'Assumption','Lawang Pari','Santo Niño I','Santo Niño II','San Martin de Porres',
      'San Martin I','San Martin II','San Martin III','San Martin IV',
      'Minuyan Proper','Minuyan I','Minuyan II','Minuyan III','Minuyan IV','Minuyan V','Minuyan VI',
      'Santa Cruz (Area D)','Area I (cluster)','Area H (cluster)','Area G (cluster)','Tungkong Mangga','Santo Cristo'
    ],

    /** Computed + handlers */
    get genreOptions() {
      const t = (this.selectedTalent || '').trim();
      return this.talentCatalog[t] || [];
    },
    get selectedGenre()    { return new URLSearchParams(location.search).get('genre') || '' },
    get selectedLocation() { return new URLSearchParams(location.search).get('location') || '' },
    get selectedFeeMax()   { return new URLSearchParams(location.search).get('fee_max') || '' },

    onTalentChange(t) {
      this.selectedTalent = t || '';
      this.setQuery('talent', this.selectedTalent || '');
      this.setQuery('view', this.viewMode || 'coach');
      this.setQuery('genre',''); // clear stale genre when talent changes
      if (this.viewMode === 'community' && this.selectedTalent) {
        requestAnimationFrame(() => {
          document.querySelector('[data-community-anchor]')?.scrollIntoView({ behavior:'smooth' });
        });
      }
    },

    // Runs automatically when component mounts
    init() {
      if (this.viewMode === 'community' && this.selectedTalent) {
        requestAnimationFrame(() => {
          document.querySelector('[data-community-anchor]')?.scrollIntoView({ behavior:'smooth' });
        });
      }
    }
  }));

  // =========== Stores & Helpers (moved from inline <script>) ===========
  Alpine.store('commentStore', {
    comments: {},
    async loadComments(postId) {
      try {
        const res = await fetch(`/community/posts/${postId}/comments`);
        if (!res.ok) throw new Error('Failed to fetch comments');
        const data = await res.json();
        this.comments[postId] = data;
      } catch (err) { console.error(err); }
    },
    async submitComment(postId, commentText) {
      if (!commentText.trim()) return;
      try {
        const res = await fetch(`/community/posts/${postId}/comments`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
          },
          body: JSON.stringify({ comment: commentText })
        });
        if (!res.ok) console.error("Failed to post comment:", await res.json());
        else this.loadComments(postId);
      } catch (err) { console.error("Error submitting comment:", err); }
    }
  });

  // Expose helpers for x-data usage in the template
  window.mediaUploader = function mediaUploader() {
    return {
      dragging:false, previewUrl:null, isImage:false,
      handleDrop(e){ this.dragging=false; const f=e.dataTransfer.files[0]; if(f){ this.readFile(f); this.$refs.fileInput.files=e.dataTransfer.files; } },
      handleFile(e){ const f=e.target.files[0]; if(f){ this.readFile(f); } else { this.previewUrl=null; this.isImage=false; } },
      readFile(file){ const r=new FileReader(); r.onload=(ev)=>{ this.previewUrl=ev.target.result; this.isImage=file.type.startsWith('image/'); }; r.readAsDataURL(file); }
    }
  };

  window.postFetcher = function postFetcher() {
    return {
      posts: [], pollingInterval:null,
      currentUserId: '{{ auth('client')->user()?->client_id ?? auth('coach')->user()?->coach_id ?? '' }}',
      currentUserRole: '{{ auth('client')->check() ? 'client' : (auth('coach')->check() ? 'coach' : '') }}',

      init(){
        const parent = this.$el.closest('[x-data]');
        if (parent && parent.__x && parent.__x.$data && parent.__x.$data.selectedTalent) {
          this.loadPosts(parent.__x.$data.selectedTalent);
        }
        this.startPolling();
        // Keep watching the nearest mainApp component's selectedTalent
        this.$watch('$el.closest(\'[x-data]\').__x.$data.selectedTalent', (v)=> this.loadPosts(v));
      },

      loadPosts(talent){
        if (!talent) return;
        fetch(`/community/posts?talent=${encodeURIComponent(talent)}`)
          .then(r=>{ if(!r.ok) throw new Error(r.status); return r.json(); })
          .then(d=>{ this.posts=d; })
          .catch(e=>console.error('Error loading posts:', e));
      },

      startPolling(){
        if (this.pollingInterval) clearInterval(this.pollingInterval);
        this.pollingInterval = setInterval(()=>{
          const parent = this.$el.closest('[x-data]');
          const talent = parent?.__x?.$data?.selectedTalent;
          if (talent) this.loadPosts(talent);
        }, 10000);
      },

      formatDate(s){ const o={year:'numeric',month:'short',day:'numeric',hour:'2-digit',minute:'2-digit'}; return new Date(s).toLocaleDateString(undefined,o); },

      async deletePost(id){
        if (!confirm('Are you sure you want to delete this post?')) return;
        try {
          await fetch(`/community/posts/${id}`, { method:'DELETE', headers:{ 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content } });
          this.posts = this.posts.filter(p=>p.id!==id);
        } catch { alert('Failed to delete post.'); }
      },

      reactToPost(id){
        const post = this.posts.find(p=>p.id===id);
        const wasReacted = post?.reacted;
        if (post){ post.reacted=!wasReacted; post.reacts = wasReacted ? Math.max(0,(post.reacts||0)-1) : (post.reacts||0)+1; }
        fetch(`/community/posts/${id}/react`, {
          method:'POST',
          headers:{ 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept':'application/json' }
        })
          .then(r=>{ if(!r.ok) throw new Error(r.status); return r.json(); })
          .then(d=>{ const p=this.posts.find(p=>p.id===id); if(p){ p.reacts=d.reacts; p.reacted=d.reacted; } })
          .catch(e=>{
            console.error('Error reacting to post:', e);
            const p=this.posts.find(p=>p.id===id);
            if(p){ p.reacted=wasReacted; p.reacts = wasReacted ? (p.reacts+1) : Math.max(0,p.reacts-1); }
          });
      }
    }
  };

  window.avatarUpdater = function avatarUpdater() {
    return {
      photoUrl: '{{ $user && $user->photo ? asset('storage/' . $user->photo) : '' }}',
      fetchPhoto(){
        fetch('{{ route('profile.photo') }}')
          .then(r=>r.json())
          .then(d=>{ this.photoUrl = d.photo_url; });
      },
      init(){ this.fetchPhoto(); setInterval(()=>this.fetchPhoto(), 5000); }
    }
  };
}); 
</script>
</body>
</html>
