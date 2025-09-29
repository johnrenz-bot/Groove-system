@php
    $user = session('client') ?? session('coach') ?? (object)[
        'firstname' => 'Guest',
        'lastname' => '',
        'middlename' => '',
        'photo' => null,
        'client_id' => '0000',
        'role' => 'client',
        'email' => '',
        'contact' => '',
        'address' => '',
        'barangay' => '',
        'bio' => ''
    ];

    $photoUrl   = $user?->photo ? asset('storage/' . $user->photo) : null;
    // Initials: FIRST + LAST only
    $initials   = strtoupper(substr($user?->firstname ?? 'C', 0, 1) . substr($user?->lastname ?? 'L', 0, 1));
    $notifications = auth()->user()?->notifications()?->latest()?->take(5)?->get() ?? collect();
    $unreadNotifications = auth()->user() ? auth()->user()->unreadNotifications : collect();
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Groove | Profile</title>
    <link rel="icon" href="/image/bg/LOG.png" type="image/png" sizes="512x512" />
    <link rel="preconnect" href="https://fonts.bunny.net" />
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://kit.fontawesome.com/43f9926b04.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

@vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body
  class="font-sans w-full overflow-x-hidden antialiased theme-{{ $appTheme ?? 'light' }} bg-surface text-foreground"
  x-data="{ tab: 'about', showUploadBox: false, isConfirmed: false }">
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
  
<main
  class="relative z-10 max-w-7xl mx-auto mt-24 md:mt-28 px-4 md:px-6 gap-6 md:gap-10 flex flex-col md:flex-row"
  x-data="{
    tab: 'profile',
    photoPreview: '{{ $user->photo ? asset('storage/' . $user->photo) : '' }}',
    hasPhoto: {{ $user->photo ? 'true' : 'false' }},
    uploadPhoto(event) {
      const file = event.target.files[0];
      if (!file) return;
      const formData = new FormData();
      formData.append('photo', file);
      fetch('{{ route('client.photo.upload') }}', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: formData
      })
      .then(res => res.json())
      .then(data => { this.photoPreview = data.photo_url; this.hasPhoto = true; })
      .catch(err => console.error('Upload failed:', err));
    }
  }">

  <!-- Sidebar -->
  <aside class="w-full md:w-64 bg-card backdrop-blur-md rounded-2xl border border-divider/40 p-6 shadow-2xl sticky md:top-28 top-4 h-fit">
    <h2 class="text-2xl font-bold mb-6 tracking-wide">Account</h2>
    <ul class="grid grid-cols-2 md:block gap-2 md:space-y-3 text-sm font-medium">
      <li><button @click="tab = 'profile'" :class="tab === 'profile' ? 'bg-layer text-foreground ring-1 ring-divider/40' : ''" class="w-full text-left px-4 py-2 rounded-xl hover:bg-layer transition">✨ Profile</button></li>
      <li><button @click="tab = 'account'" :class="tab === 'account' ? 'bg-layer text-foreground ring-1 ring-divider/40' : ''" class="w-full text-left px-4 py-2 rounded-xl hover:bg-layer transition">🆔 Account ID</button></li>
      <li><button @click="tab = 'info'" :class="tab === 'info' ? 'bg-layer text-foreground ring-1 ring-divider/40' : ''" class="w-full text-left px-4 py-2 rounded-xl hover:bg-layer transition">📝 Personal Info</button></li>
      <li><button @click="tab = 'password'" :class="tab === 'password' ? 'bg-layer text-foreground ring-1 ring-divider/40' : ''" class="w-full text-left px-4 py-2 rounded-xl hover:bg-layer transition">🔒 Change Password</button></li>
    </ul>
  </aside>

  <!-- Content -->
  <section class="flex-1 w-full space-y-10">
    {{-- Profile Tab --}}
    <form method="POST" action="{{ route('client.profile.update') }}" enctype="multipart/form-data"
          x-show="tab === 'profile'"
          class="bg-card backdrop-blur-xl rounded-2xl border border-divider/40 p-6 md:p-8 space-y-6 shadow-2xl transition-all">
      @csrf @method('PUT')

      <div class="border-b border-divider/40 pb-4">
        <h3 class="text-2xl font-semibold tracking-wide">👤 Edit Profile</h3>
        <p class="text-sm text-foreground/70">Update your avatar and full name.</p>
      </div>

      <div class="flex items-center gap-6">
        <template x-if="hasPhoto">
          <img :src="photoPreview" class="w-20 h-20 rounded-full object-cover border-2 shadow-md" style="border-color: var(--color-primary)" alt="Avatar Preview">
        </template>
        <template x-if="!hasPhoto">
          <div class="w-20 h-20 flex items-center justify-center rounded-full bg-layer text-xl font-semibold border border-divider/40 shadow-md uppercase" style="color: var(--color-primary)">
            {{-- Initials FIRST + LAST only --}}
            {{ strtoupper(substr($user->firstname ?? 'C', 0, 1)) }}{{ strtoupper(substr($user->lastname ?? 'L', 0, 1)) }}
          </div>
        </template>

        <label for="photo" class="text-sm border border-divider/40 rounded-lg px-4 py-2 hover:opacity-90 transition cursor-pointer bg-layer">
          <input type="file" name="photo" id="photo" accept="image/*" @change="uploadPhoto($event)" class="sr-only" />
          Choose Photo
        </label>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <label for="firstname" class="block text-sm font-medium text-foreground/70">First Name</label>
          <input name="firstname" id="firstname" value="{{ old('firstname', $user->firstname) }}" type="text" placeholder="First Name"
                 class="w-full px-4 py-2 bg-layer border border-divider/40 rounded-xl focus:ring-0">
        </div>
        <div>
          <label for="middlename" class="block text-sm font-medium text-foreground/70">Middle Name</label>
          <input name="middlename" id="middlename" value="{{ old('middlename', $user->middlename) }}" type="text" placeholder="Middle Name"
                 class="w-full px-4 py-2 bg-layer border border-divider/40 rounded-xl focus:ring-0">
        </div>
        <div>
          <label for="lastname" class="block text-sm font-medium text-foreground/70">Last Name</label>
          <input name="lastname" id="lastname" value="{{ old('lastname', $user->lastname) }}" type="text" placeholder="Last Name"
                 class="w-full px-4 py-2 bg-layer border border-divider/40 rounded-xl focus:ring-0">
        </div>
      </div>

      <div>
        <label for="bio" class="block text-sm font-medium text-foreground/70">Bio</label>
        <textarea name="bio" id="bio" rows="3" placeholder="Tell us about yourself..."
                  class="w-full px-4 py-2 bg-layer border border-divider/40 rounded-xl resize-none focus:ring-0">{{ old('bio', $user->bio) }}</textarea>
      </div>

      <div class="text-right">
        <button type="submit" class="font-semibold px-6 py-2 rounded-xl shadow-md transition text-white hover:opacity-90" style="background: var(--color-primary)">
          Save Changes
        </button>
      </div>
    </form>

    {{-- Account ID Tab --}}
    <form method="POST" action="{{ route('client.profile.update') }}" x-show="tab === 'account'"
          class="bg-card backdrop-blur-xl rounded-2xl border border-divider/40 p-6 md:p-8 space-y-6 shadow-2xl transition-all">
      @csrf @method('PUT')

      <div class="border-b border-divider/40 pb-4">
        <h3 class="text-2xl font-semibold tracking-wide">🆔 Account ID</h3>
        <p class="text-sm text-foreground/70">Customize your public display tag.</p>
      </div>

      <input name="client_id" id="client_id" value="{{ old('client_id', $user->client_id) }}" type="text" placeholder="#TAGLINE"
             class="w-full px-4 py-2 bg-layer border border-divider/40 rounded-xl focus:ring-0"
             maxlength="4" oninput="this.value = this.value.replace(/[^0-9]/g, '')">

      <div class="text-right">
        <button type="submit" class="font-semibold px-6 py-2 rounded-xl shadow-md transition text-white hover:opacity-90" style="background: var(--color-primary)">
          Save Changes
        </button>
      </div>
    </form>

    {{-- Personal Info Tab --}}
    <form method="POST" action="{{ route('client.profile.update') }}" x-show="tab === 'info'"
          class="bg-card backdrop-blur-xl rounded-2xl border border-divider/40 p-6 md:p-8 space-y-6 shadow-2xl transition-all">
      @csrf @method('PUT')

      <div class="border-b border-divider/40 pb-4">
        <h3 class="text-2xl font-semibold tracking-wide">📇 Personal Information</h3>
        <p class="text-sm text-foreground/70">Keep your contact details up to date.</p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label for="email" class="block text-sm font-medium text-foreground/70">Email</label>
          <input name="email" id="email" type="email" value="{{ old('email', $user->email) }}" placeholder="Email"
                 class="w-full px-4 py-2 bg-layer border border-divider/40 rounded-xl focus:ring-0">
        </div>
        <div>
          <label for="contact" class="block text-sm font-medium text-foreground/70">Contact Number</label>
          <input name="contact" id="contact" type="text" value="{{ old('contact', $user->contact) }}" placeholder="Contact Number"
                 class="w-full px-4 py-2 bg-layer border border-divider/40 rounded-xl focus:ring-0">
        </div>
        <div>
          <label for="address" class="block text-sm font-medium text-foreground/70">Address</label>
          <input name="address" id="address" type="text" value="{{ old('address', $user->address) }}" placeholder="Address"
                 class="w-full px-4 py-2 bg-layer border border-divider/40 rounded-xl focus:ring-0">
        </div>
        <div>
          <label for="barangay" class="block text-sm font-medium text-foreground/70">Barangay</label>
          <input name="barangay" id="barangay" type="text" value="{{ old('barangay', $user->barangay) }}" placeholder="Barangay"
                 class="w-full px-4 py-2 bg-layer border border-divider/40 rounded-xl focus:ring-0">
        </div>
      </div>

      <div class="text-right">
        <button type="submit" class="font-semibold px-6 py-2 rounded-xl shadow-md transition text-white hover:opacity-90" style="background: var(--color-primary)">
          Save Changes
        </button>
      </div>
    </form>

    {{-- Password Tab --}}
    <form method="POST" action="{{ route('client.profile.password.update') }}" x-show="tab === 'password'"
          class="bg-card backdrop-blur-xl rounded-2xl border border-divider/40 p-6 md:p-8 space-y-6 shadow-2xl transition-all">
      @csrf @method('PUT')

      <div class="border-b border-divider/40 pb-4">
        <h3 class="text-2xl font-semibold tracking-wide">🔒 Change Password</h3>
        <p class="text-sm text-foreground/70">Update your password securely.</p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label for="current_password" class="block text-sm font-medium text-foreground/70">Current Password</label>
          <input name="current_password" id="current_password" type="password" placeholder="Current Password"
                 class="w-full px-4 py-2 bg-layer border border-divider/40 rounded-xl focus:ring-0">
        </div>
        <div>
          <label for="new_password" class="block text-sm font-medium text-foreground/70">New Password</label>
          <input name="new_password" id="new_password" type="password" placeholder="New Password"
                 class="w-full px-4 py-2 bg-layer border border-divider/40 rounded-xl focus:ring-0">
        </div>
        <div>
          <label for="new_password_confirmation" class="block text-sm font-medium text-foreground/70">Confirm New Password</label>
          <input name="new_password_confirmation" id="new_password_confirmation" type="password" placeholder="Confirm New Password"
                 class="w-full px-4 py-2 bg-layer border border-divider/40 rounded-xl focus:ring-0">
        </div>
      </div>

      <div class="text-right">
        <button type="submit" class="font-semibold px-6 py-2 rounded-xl shadow-md transition text-white hover:opacity-90" style="background: var(--color-primary)">
          Update Password
        </button>
      </div>
    </form>
  </section>
</main>

<script>
  function avatarUpdater() {
    return {
      photoUrl: '{{ $user->photo ? asset('storage/' . $user->photo) : '' }}',
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
