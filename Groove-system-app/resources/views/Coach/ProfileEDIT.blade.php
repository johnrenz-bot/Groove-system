@php
    $user = session('client') ?? session('coach');

    $photoUrl = $user?->photo ? asset('storage/' . $user->photo) : null;
    $initials = strtoupper(substr($user?->firstname ?? '', 0, 1) . substr($user?->lastname ?? '', 0, 1));

    $unreadNotifications = $user ? $user->unreadNotifications : collect();

@endphp

<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Groove | Coach Profile</title>

  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link rel="icon" href="/image/white.png" type="image/png" />
  <link rel="preconnect" href="https://fonts.bunny.net" />
  <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
  <link href="{{ asset('css/app.css') }}" rel="stylesheet" />
  <script src="https://kit.fontawesome.com/43f9926b04.js" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

  <link rel="icon" href="/image/bg/LOG.png" type="image/png" sizes="512x512">
  <link rel="apple-touch-icon" href="/image/wc/logo.png" sizes="180x180">

  <style>
    [x-cloak]{display:none!important;}
    .hover-scrollbar{scrollbar-width:thin;scrollbar-color:transparent transparent;scroll-behavior:smooth;padding-right:6px}
    .hover-scrollbar:hover{scrollbar-color:#71717a transparent}
    .hover-scrollbar::-webkit-scrollbar{width:6px}
    .hover-scrollbar::-webkit-scrollbar-thumb{background:transparent;border-radius:6px;transition:background .3s ease}
    .hover-scrollbar:hover::-webkit-scrollbar-thumb{background:#71717a}
  </style>

@vite(['resources/css/app.css', 'resources/js/app.js'])

</head>

<body
  x-data="{
    tab: 'profile',
    photoPreview: '{{ $coach->photo ? asset('storage/' . $coach->photo) : '' }}',
    hasPhoto: {{ $coach->photo ? 'true' : 'false' }},
    uploadPhoto(event) {
      const file = event.target.files[0];
      this.photoPreview = file ? URL.createObjectURL(file) : this.photoPreview;
      this.hasPhoto = !!file;
    }
  }"
  class="font-sans min-h-screen w-full overflow-x-hidden theme-{{ $appTheme }} bg-surface text-foreground"
>

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
        <nav class="hidden md:flex flex-1 justify-center gap-2 lg:gap-4 text-sm font-medium">  <a href="/coach/home"
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

  {{-- ================= MAIN ================= --}}
  <main class="flex gap-8 px-8 py-10 max-w-7xl pt-28 mx-auto">

    {{-- Sidebar --}}
    <aside class="w-64 bg-card/90 backdrop-blur-md rounded-2xl border border-divider/40 p-6 shadow-2xl sticky top-6 h-fit">
      <h2 class="text-2xl font-bold mb-6 tracking-wide">Account</h2>
      <ul class="space-y-3 text-sm font-medium">
        <li>
          <button @click="tab='profile'"
                  :class="tab==='profile' ? 'bg-primary/10 text-foreground ring-1 ring-primary/30' : ''"
                  class="w-full text-left px-4 py-2 rounded-xl hover:bg-layer transition">
            ✨ Profile
          </button>
        </li>
        <li>
          <button @click="tab='account'"
                  :class="tab==='account' ? 'bg-primary/10 text-foreground ring-1 ring-primary/30' : ''"
                  class="w-full text-left px-4 py-2 rounded-xl hover:bg-layer transition">
            🆔 Account ID
          </button>
        </li>
        <li>
          <button @click="tab='info'"
                  :class="tab==='info' ? 'bg-primary/10 text-foreground ring-1 ring-primary/30' : ''"
                  class="w-full text-left px-4 py-2 rounded-xl hover:bg-layer transition">
            📝 Personal Info
          </button>
        </li>
        <li>
          <button @click="tab='rate'"
                  :class="tab==='rate' ? 'bg-primary/10 text-foreground ring-1 ring-primary/30' : ''"
                  class="w-full text-left px-4 py-2 rounded-xl hover:bg-layer transition">
            💰 Personal Rate
          </button>
        </li>
      </ul>
    </aside>

    {{-- Right Column --}}
    <section class="flex-1 space-y-10">

      {{-- Profile Tab --}}
      <form method="POST" action="{{ route('COACH.UPDATE') }}" enctype="multipart/form-data"
            x-show="tab==='profile'" x-cloak
            class="bg-card/90 backdrop-blur-xl rounded-2xl border border-divider/40 p-8 space-y-6 shadow-2xl transition-all">
        @csrf @method('PUT')

        <div class="border-b border-divider/40 pb-4">
          <h3 class="text-2xl font-bold tracking-wide">EDIT PROFILE</h3>
          <p class="text-sm text-foreground/70">Update your avatar and name.</p>
        </div>

        <div class="flex items-center gap-6">
          <template x-if="hasPhoto">
            <img :src="photoPreview" class="w-20 h-20 rounded-full object-cover border-2 border-primary shadow-md" alt="Avatar Preview">
          </template>
          <template x-if="!hasPhoto">
            <div class="w-20 h-20 flex items-center justify-center rounded-full bg-primary text-primary-foreground text-xl font-semibold border-2 border-primary shadow-md uppercase">
              {{ strtoupper(substr($coach->firstname ?? 'C', 0, 1)) }}
            </div>
          </template>

          <input type="file" name="photo" accept="image/*" @change="uploadPhoto($event)"
                 class="text-sm text-foreground file:mr-3 file:bg-layer file:border file:border-divider/40 file:text-foreground file:px-4 file:py-1 rounded-lg hover:file:shadow-lg" />
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <input name="firstname" value="{{ old('firstname', $coach->firstname) }}" placeholder="First Name" type="text"
                 class="w-full px-4 py-2 bg-layer text-foreground border border-divider/40 rounded-xl">
          <input name="middlename" value="{{ old('middlename', $coach->middlename) }}" placeholder="Middle Name" type="text"
                 class="w-full px-4 py-2 bg-layer text-foreground border border-divider/40 rounded-xl">
          <input name="lastname" value="{{ old('lastname', $coach->lastname) }}" placeholder="Last Name" type="text"
                 class="w-full px-4 py-2 bg-layer text-foreground border border-divider/40 rounded-xl">
        </div>

        <textarea name="about" rows="3" placeholder="Tell us about yourself..."
                  class="w-full px-4 py-2 bg-layer text-foreground border border-divider/40 rounded-xl">{{ old('about', $coach->about) }}</textarea>

        <div class="text-right">
          <button type="submit"
                  class="bg-primary hover:opacity-90 text-primary-foreground font-semibold px-6 py-2 rounded-xl shadow-md transition">
            Save Changes
          </button>
        </div>
      </form>

      {{-- Account Tab --}}
      <form method="POST" action="{{ route('COACH.UPDATE') }}"
            x-show="tab==='account'" x-cloak
            class="bg-card/90 backdrop-blur-xl rounded-2xl border border-divider/40 p-8 space-y-6 shadow-2xl transition-all">
        @csrf @method('PUT')

        <div class="border-b border-divider/40 pb-4">
          <h3 class="text-2xl font-bold tracking-wide">COACH ID</h3>
          <p class="text-sm text-foreground/70">Edit your unique Coach ID.</p>
        </div>

        <input name="coach_id" type="text" value="{{ old('coach_id', $coach->coach_id) }}" placeholder="COACH-ID"
               class="w-full px-4 py-2 bg-layer text-foreground/90 border border-divider/40 rounded-xl">

        <div class="text-right">
          <button type="submit"
                  class="bg-primary hover:opacity-90 text-primary-foreground font-semibold px-6 py-2 rounded-xl shadow-md transition">
            Save Changes
          </button>
        </div>
      </form>

      {{-- Info Tab --}}
      <form method="POST" action="{{ route('COACH.UPDATE') }}"
            x-show="tab==='info'" x-cloak
            class="bg-card/90 backdrop-blur-xl rounded-2xl border border-divider/40 p-8 space-y-6 shadow-2xl transition-all">
        @csrf @method('PUT')

        <div class="border-b border-divider/40 pb-4">
          <h3 class="text-2xl font-bold tracking-wide">PERSONAL INFORMATION</h3>
          <p class="text-sm text-foreground/70">Update your contact information.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <input name="email" type="email" value="{{ old('email', $coach->email) }}" placeholder="Email"
                 class="w-full px-4 py-2 bg-layer text-foreground border border-divider/40 rounded-xl">
          <input name="contact" type="text" value="{{ old('contact', $coach->contact) }}" placeholder="Contact Number"
                 class="w-full px-4 py-2 bg-layer text-foreground border border-divider/40 rounded-xl">
          <input name="address" type="text" value="{{ old('address', $coach->address) }}" placeholder="Address"
                 class="w-full px-4 py-2 bg-layer text-foreground border border-divider/40 rounded-xl">
          <input name="barangay" type="text" value="{{ old('barangay', $coach->barangay) }}" placeholder="Barangay"
                 class="w-full px-4 py-2 bg-layer text-foreground border border-divider/40 rounded-xl">
        </div>

        <div class="text-right">
          <button type="submit"
                  class="bg-primary hover:opacity-90 text-primary-foreground font-semibold px-6 py-2 rounded-xl shadow-md transition">
            Save Changes
          </button>
        </div>
      </form>

      {{-- Personal Rate Tab --}}
      <form method="POST" action="{{ route('COACH.UPDATE') }}"
            x-show="tab==='rate'" x-cloak
            x-data="{
              formData: {
                service_fee: '{{ old('service_fee', $coach->service_fee) }}',
                duration: '{{ old('duration', $coach->duration) }}',
                payment: '{{ old('payment', $coach->payment) }}',
                notice_hours: {{ old('notice_hours', $coach->notice_hours) ?? 0 }},
                notice_days:  {{ old('notice_days',  $coach->notice_days)  ?? 0 }},
              }
            }"
            class="bg-card/90 backdrop-blur-xl rounded-2xl border border-divider/40 p-8 space-y-6 shadow-2xl transition-all">
        @csrf @method('PUT')

        <div class="border-b border-divider/40 pb-4">
          <h3 class="text-2xl font-bold tracking-wide">PERSONAL RATE</h3>
          <p class="text-sm text-foreground/70">Set your hourly or session rate and related settings.</p>
        </div>

        <div class="relative w-full md:w-1/2">
          <span class="absolute inset-y-0 left-3 flex items-center text-foreground/60">₱</span>
          <input name="appointment_price" type="number" step="0.01"
                 value="{{ old('appointment_price', $coach->appointment_price) }}"
                 placeholder="Enter Amount"
                 class="w-full pl-8 pr-4 py-2 bg-layer text-foreground border border-divider/40 rounded-xl">
          @error('appointment_price')
            <p class="text-rose-500 text-sm mt-1">{{ $message }}</p>
          @enderror
        </div>

        <div>
          <label class="block text-xs font-bold text-foreground/70 mb-1">Session Duration</label>
          <p class="text-sm text-foreground/70 mb-1">How long does one session usually take?</p>
          <input type="text" name="duration" placeholder="e.g., 1 hour, 90 minutes" required
                 class="w-full h-11 px-3 bg-layer text-foreground border border-divider/40 rounded-lg"
                 x-model="formData.duration"
                 value="{{ old('duration', $coach->duration) }}">
          @error('duration')
            <p class="text-rose-500 text-sm mt-1">{{ $message }}</p>
          @enderror
        </div>

        <div>
          <label class="block text-xs font-bold text-foreground/70 mb-1">Payment Method</label>
          <p class="text-sm text-foreground/70 mb-1">Select your preferred mode of payment.</p>
          <select name="payment" required
                  class="w-full h-11 px-3 bg-layer text-foreground border border-divider/40 rounded-lg"
                  x-model="formData.payment">
            <option value="" disabled>Select Payment Method</option>
            <option value="cash"   {{ old('payment', $coach->payment) === 'cash'   ? 'selected' : '' }}>Cash</option>
            <option value="online" {{ old('payment', $coach->payment) === 'online' ? 'selected' : '' }}>Online Payment</option>
          </select>
          @error('payment')
            <p class="text-rose-500 text-sm mt-1">{{ $message }}</p>
          @enderror
        </div>

        <div>
          <label class="block text-xs font-bold text-foreground/70 mb-1">Minimum Notice Required</label>
          <p class="text-sm text-foreground/70 mb-1">How many hours or days in advance should clients notify you?</p>
          <div class="flex gap-4">
            <div class="flex items-center gap-2">
              <input type="number" name="notice_hours" placeholder="0"
                     class="w-20 h-11 px-3 bg-layer text-foreground border border-divider/40 rounded-lg"
                     min="0" max="99"
                     x-model.number="formData.notice_hours"
                     @input="formData.notice_hours = Math.min(formData.notice_hours ?? 0, 99)"
                     value="{{ old('notice_hours', $coach->notice_hours) }}">
              <span class="text-foreground/70 text-sm">Hours</span>
            </div>
            <div class="flex items-center gap-2">
              <input type="number" name="notice_days" placeholder="0"
                     class="w-20 h-11 px-3 bg-layer text-foreground border border-divider/40 rounded-lg"
                     min="0" max="30"
                     x-model.number="formData.notice_days"
                     @input="formData.notice_days = Math.min(formData.notice_days ?? 0, 30)"
                     value="{{ old('notice_days', $coach->notice_days) }}">
              <span class="text-foreground/70 text-sm">Days</span>
            </div>
          </div>

          <div class="text-rose-500 text-sm mt-1" x-show="Number(formData.notice_hours) > 99">
            Maximum is 99 hours
          </div>
          <div class="text-rose-500 text-sm mt-1" x-show="Number(formData.notice_days) > 30">
            Maximum is 30 days
          </div>

          @error('notice_hours')
            <p class="text-rose-500 text-sm mt-1">{{ $message }}</p>
          @enderror
          @error('notice_days')
            <p class="text-rose-500 text-sm mt-1">{{ $message }}</p>
          @enderror
        </div>

        <div class="text-right">
          <button type="submit"
                  class="bg-primary hover:opacity-90 text-primary-foreground font-semibold px-6 py-2 rounded-xl shadow-md transition">
            Save Changes
          </button>
        </div>
      </form>

    </section>
  </main>

  {{-- ================= SCRIPTS ================= --}}
  <script>
    // Avatar updater — now also keeps the theme class in sync (theme-{{ $appTheme }})
    function avatarUpdater() {
      return {
        photoUrl: '{{ $coach->photo ? asset('storage/' . $coach->photo) : '' }}',

        fetchPhoto() {
          fetch("{{ route('coach.profile.photo') }}", { headers: { 'Accept': 'application/json' } })
            .then(res => res.ok ? res.json() : null)
            .then(data => { if (data && data.photo_url) this.photoUrl = data.photo_url; })
            .catch(() => {});
        },

        ensureThemeClass() {
          const theme = '{{ $appTheme }}'; // 'dark' | 'light' | 'system'
          const b = document.body;
          // remove old theme-* then apply
          [...b.classList].forEach(c => { if (c.startsWith('theme-')) b.classList.remove(c); });
          b.classList.add(`theme-${theme}`);
        },

        init() {
          this.ensureThemeClass();
          this.fetchPhoto();
          // Keep avatar fresh and re-affirm theme occasionally
          setInterval(() => { this.fetchPhoto(); this.ensureThemeClass(); }, 5000);
        }
      };
    }
  </script>
</body>
</html>
