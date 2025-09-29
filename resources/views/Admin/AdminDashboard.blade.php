{{-- resources/views/Admin/AdminDashboard.blade.php --}}
@php
  // Safe defaults if controller forgets to pass something
  $appTheme = $appTheme ?? 'light';

  $talentCounts = $talentCounts ?? [];
  $dancer    = $talentCounts['dance']['total']   ?? 0;
  $singer    = $talentCounts['sing']['total']    ?? 0;
  $actor     = $talentCounts['acting']['total']  ?? 0;
  $thespian  = $talentCounts['theater']['total'] ?? 0;

  $totalUsers = $dancer + $singer + $actor + $thespian;
  $p = fn($n) => $totalUsers > 0 ? round(($n / $totalUsers) * 100, 1) : 0;

  $dancerPercent   = $p($dancer);
  $singerPercent   = $p($singer);
  $actorPercent    = $p($actor);
  $thespianPercent = $p($thespian);

  $dancerDash   = $dancerPercent;
  $singerDash   = $singerPercent;
  $actorDash    = $actorPercent;
  $thespianDash = $thespianPercent;

  $singerOffset   = -$dancerDash;
  $actorOffset    = -($dancerDash + $singerDash);
  $thespianOffset = -($dancerDash + $singerDash + $actorDash);

  // Analytics (defensive)
  $systemMetrics    = $systemMetrics    ?? ['cpu'=>0,'memory'=>0,'disk'=>0];
  $maintenance      = $maintenance      ?? ['last_deploy'=>'N/A','bugs_fixed'=>0,'security_status'=>'N/A','last_backup'=>'N/A'];
  $activeUserCount  = $activeUserCount  ?? 0;
  $suspiciousLogins = $suspiciousLogins ?? 0;

  $totalClients        = $totalClients        ?? 0;
  $totalCoaches        = $totalCoaches        ?? 0;
  $totalCommunityPosts = $totalCommunityPosts ?? 0;
  $totalComments       = $totalComments       ?? 0;
  $activeToday         = $activeToday         ?? 0;
  $mostActiveHour      = $mostActiveHour      ?? null;

  // Passcode toggle from session
  $needsPasscode = session('require_admin_passcode') === true;
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Groove · Admin Dashboard</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">

  {{-- Icons / Fonts --}}
  <link rel="icon" href="/image/bg/LOG.png" type="image/png" sizes="512x512">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
  <script src="https://kit.fontawesome.com/43f9926b04.js" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

  {{-- Tailwind + your app bundle --}}
@vite(['resources/css/app.css', 'resources/js/app.js'])

  <style>
    [x-cloak]{display:none!important}
    body{font-family:"Instrument Sans",ui-sans-serif,system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial}
    .donut-chart{position:relative;width:200px;height:200px;border-radius:50%;display:flex;align-items:center;justify-content:center}
  </style>
</head>

<body x-data="{ sidebarOpen: false }" class="min-h-screen antialiased theme-{{ $appTheme }} bg-surface text-foreground">

<div class="flex min-h-screen">
  {{-- SIDEBAR --}}
  <aside class="w-64 h-screen bg-card text-foreground border-r border-divider/40 flex flex-col justify-between shadow-sm">
    <div class="flex flex-col p-6 space-y-8">
      <div class="flex items-center gap-3">
        <img src="/image/bg/LOG.png" alt="Logo" class="h-12 w-auto object-contain select-none" />
      </div>

        <nav class="flex flex-col space-y-1 text-sm font-medium p-3">
          <a href="dashboard"
            class="flex items-center px-4 py-2 rounded-lg text-foreground bg-layer shadow-inner border border-divider/50">
            <i class="fas fa-home mr-3 w-5 opacity-70"></i> Dashboard
          </a>
          <a href="{{ route('admin.users') }}"
            class="flex items-center px-4 py-2 rounded-lg text-foreground/80 hover:text-foreground hover:bg-layer">
            <i class="fas fa-users mr-3 w-5 opacity-70"></i> Users
          </a>
          <a href="{{ route('admin.control') }}"
            class="flex items-center px-4 py-2 rounded-lg text-foreground/80 hover:text-foreground hover:bg-layer">
            <i class="fas fa-layer-group mr-3 w-5 opacity-70"></i> Control
          </a>
          <a href="{{ route('admin.transaction') }}"
            class="flex items-center px-4 py-2 rounded-lg text-foreground/80 hover:text-foreground hover:bg-layer">
            <i class="fas fa-user-friends mr-3 w-5 opacity-70"></i> Transactions
          </a>

           {{-- ✅ NEW: Tickets --}}
  <a href="{{ route('admin.tickets') }}"
     class="flex items-center px-4 py-2 rounded-lg
            {{ request()->routeIs('admin.Admintickets') ? 'text-foreground bg-layer shadow-inner border border-divider/50' : 'text-foreground/80 hover:text-foreground hover:bg-layer' }}">
    <i class="fas fa-ticket-alt mr-3 w-5 opacity-70"></i> Tickets
  </a>
        </nav>
      
    </div>

    <div class="p-6 border-t border-divider/40">
      <div class="w-full" x-data="{ openUser:false }">
        <button @click="openUser = !openUser"
                class="flex items-center gap-3 px-4 py-3 bg-layer rounded-xl w-full border border-divider/40">
          <div class="flex-1 text-left">
            <p class="text-sm font-semibold truncate">{{ optional(Auth::guard('admin')->user())->name ?? 'Administrator' }}</p>
            <p class="text-xs text-foreground/60 truncate">{{ optional(Auth::guard('admin')->user())->email ?? 'admin@example.com' }}</p>
          </div>
          <i class="fas fa-chevron-down text-xs transition-transform" :class="{ 'rotate-180': openUser }"></i>
        </button>

        <div x-show="openUser" x-transition x-cloak class="mt-2 bg-card border border-divider/40 rounded-xl shadow-lg">
          <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="w-full text-left px-4 py-2 text-sm hover:bg-layer flex items-center gap-2 text-primary">
              <i class="fas fa-sign-out-alt"></i> Logout
            </button>
          </form>
        </div>
      </div>
    </div>
  </aside>

  {{-- MAIN --}}
  <main class="flex-1 px-8 py-6 overflow-y-auto bg-surface">
    <div class="w-full flex justify-between items-start md:items-center mb-6">
      <h1 class="text-2xl font-bold">Dashboard Overview</h1>

      {{-- Notifications --}}
      <div x-data="notifications()" x-init="init()" class="relative">
        <button @click="panelOpen = !panelOpen"
                class="flex items-center gap-2 px-4 py-2 bg-card border border-divider/40 rounded-2xl">
          <i class="fas fa-bell text-lg" style="color: var(--color-primary)"></i>
          <span class="text-sm font-medium">Notifications</span>
          <i class="fas fa-chevron-down text-xs ml-1 transition-transform" :class="{ 'rotate-180': panelOpen }"></i>
        </button>

        <div x-show="panelOpen" x-transition
             class="absolute right-0 mt-2 w-80 bg-card border border-divider/40 rounded-2xl shadow-lg max-h-80 overflow-y-auto z-50">
          <template x-for="note in notifications" :key="note.id">
            <div class="flex items-start p-4 gap-3 border-b border-divider/40 hover:bg-layer">
              <div class="w-2 h-2 rounded-full mt-2" style="background: var(--color-primary)"></div>
              <div class="flex-1">
                <p class="text-sm font-semibold" x-text="note.title"></p>
                <p class="text-xs text-foreground/80 mt-0.5" x-text="note.message"></p>
                <p class="text-xs text-foreground/60 mt-1" x-text="note.time"></p>
              </div>
            </div>
          </template>
          <template x-if="notifications.length === 0">
            <div class="p-4 text-center text-foreground/60 text-sm">No notifications</div>
          </template>
        </div>
      </div>
    </div>

    {{-- KPI CARDS --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 my-8">
      <div class="bg-card p-6 rounded-lg flex items-center justify-between border border-divider/40 shadow-sm">
        <div>
          <p class="text-4xl font-bold">{{ $dancer }}</p>
          <p class="text-sm text-foreground/60 mt-1">DANCERS</p>
        </div>
        <i class="fas fa-person-running text-5xl opacity-40"></i>
      </div>

      <div class="bg-card p-6 rounded-lg flex items-center justify-between border border-divider/40 shadow-sm">
        <div>
          <p class="text-4xl font-bold">{{ $singer }}</p>
          <p class="text-sm text-foreground/60 mt-1">SINGERS</p>
        </div>
        <i class="fas fa-microphone text-5xl opacity-40"></i>
      </div>

      <div class="bg-card p-6 rounded-lg flex items-center justify-between border border-divider/40 shadow-sm">
        <div>
          <p class="text-4xl font-bold">{{ $actor }}</p>
          <p class="text-sm text-foreground/60 mt-1">ACTORS</p>
        </div>
        <i class="fas fa-masks-theater text-5xl opacity-40"></i>
      </div>

      <div class="bg-card p-6 rounded-lg flex items-center justify-between border border-divider/40 shadow-sm">
        <div>
          <p class="text-4xl font-bold">{{ $thespian }}</p>
          <p class="text-sm text-foreground/60 mt-1">THESPIANS</p>
        </div>
        <i class="fas fa-theater-masks text-5xl opacity-40"></i>
      </div>
    </div>

    {{-- DISTRIBUTION + SYSTEM BOXES --}}
    <section class="flex flex-col lg:flex-row gap-6 mb-8">
      {{-- Donut chart --}}
      <div class="w-full lg:w-1/2 bg-card p-6 rounded-lg border border-divider/40 flex flex-col md:flex-row items-center justify-center md:justify-start gap-6">
        <div class="flex-shrink-0 relative">
          <div class="donut-chart">
            <svg width="200" height="200" viewBox="0 0 40 40">
              <circle cx="20" cy="20" r="15.915" fill="none" stroke="rgba(0,0,0,0.2)" stroke-width="8"></circle>

              <circle cx="20" cy="20" r="15.915" fill="none" stroke="#d97706" stroke-width="8"
                      stroke-dasharray="{{ $dancerDash }} {{ 100 - $dancerDash }}" stroke-dashoffset="0"></circle>

              <circle cx="20" cy="20" r="15.915" fill="none" stroke="#16a34a" stroke-width="8"
                      stroke-dasharray="{{ $singerDash }} {{ 100 - $singerDash }}" stroke-dashoffset="{{ $singerOffset }}"></circle>

              <circle cx="20" cy="20" r="15.915" fill="none" stroke="#dc2626" stroke-width="8"
                      stroke-dasharray="{{ $actorDash }} {{ 100 - $actorDash }}" stroke-dashoffset="{{ $actorOffset }}"></circle>

              <circle cx="20" cy="20" r="15.915" fill="none" stroke="#2563eb" stroke-width="8"
                      stroke-dasharray="{{ $thespianDash }} {{ 100 - $thespianDash }}" stroke-dashoffset="{{ $thespianOffset }}"></circle>
            </svg>
            <div class="absolute inset-0 flex flex-col items-center justify-center">
              <span class="text-2xl font-bold">{{ $totalUsers }}</span>
              <span class="text-sm text-foreground/60">TOTAL USERS</span>
            </div>
          </div>
        </div>

        <div class="flex-grow w-full md:w-auto mt-4 md:mt-0">
          <h3 class="text-lg font-semibold mb-4">Talent Distribution</h3>
          <div class="space-y-2">
            <div class="flex justify-between items-center">
              <div class="flex items-center space-x-2">
                <span class="w-3 h-3 rounded-full" style="background:#d97706"></span>
                <span>Dancer</span>
              </div>
              <span class="text-foreground/80">{{ $dancerPercent }}% ({{ $dancer }})</span>
            </div>
            <div class="flex justify-between items-center">
              <div class="flex items-center space-x-2">
                <span class="w-3 h-3 rounded-full" style="background:#16a34a"></span>
                <span>Singer</span>
              </div>
              <span class="text-foreground/80">{{ $singerPercent }}% ({{ $singer }})</span>
            </div>
            <div class="flex justify-between items-center">
              <div class="flex items-center space-x-2">
                <span class="w-3 h-3 rounded-full" style="background:#dc2626"></span>
                <span>Actor</span>
              </div>
              <span class="text-foreground/80">{{ $actorPercent }}% ({{ $actor }})</span>
            </div>
            <div class="flex justify-between items-center">
              <div class="flex items-center space-x-2">
                <span class="w-3 h-3 rounded-full" style="background:#2563eb"></span>
                <span>Thespian</span>
              </div>
              <span class="text-foreground/80">{{ $thespianPercent }}% ({{ $thespian }})</span>
            </div>
          </div>
        </div>
      </div>

      {{-- System boxes --}}
      <div class="w-full lg:w-1/2 flex flex-col gap-6">
        <div class="bg-card border border-divider/40 p-6 rounded-lg">
          <h3 class="text-lg font-bold mb-4">System Monitoring</h3>
          <ul class="text-foreground/80 space-y-2 text-sm">
            <li><i class="fas fa-microchip mr-2" style="color:#16a34a"></i> CPU Usage: <span class="font-medium text-foreground">{{ $systemMetrics['cpu'] }}%</span></li>
            <li><i class="fas fa-memory  mr-2" style="color:#2563eb"></i> Memory Usage: <span class="font-medium text-foreground">{{ $systemMetrics['memory'] }}%</span></li>
            <li><i class="fas fa-hdd     mr-2" style="color:#d97706"></i> Disk Space: <span class="font-medium text-foreground">{{ $systemMetrics['disk'] }}%</span></li>
            <li><i class="fas fa-users   mr-2" style="color:#6366f1"></i> Active Users Today: <span class="font-medium text-foreground">{{ $activeUserCount }}</span></li>
            <li><i class="fas fa-exclamation-triangle mr-2" style="color:#dc2626"></i> Suspicious Logins: <span class="font-medium text-foreground">{{ $suspiciousLogins }}</span></li>
          </ul>
        </div>

        <div class="bg-card border border-divider/40 p-6 rounded-lg">
          <h3 class="text-lg font-bold mb-4">System Maintenance</h3>
          <ul class="text-foreground/80 space-y-2 text-sm">
            <li><i class="fas fa-code-branch mr-2" style="color:#14b8a6"></i> Last Deployment: <span class="font-medium text-foreground">{{ $maintenance['last_deploy'] }}</span></li>
            <li><i class="fas fa-bug         mr-2" style="color:#dc2626"></i> Bugs Fixed This Week: <span class="font-medium text-foreground">{{ $maintenance['bugs_fixed'] }}</span></li>
            <li><i class="fas fa-shield-alt  mr-2" style="color:#16a34a"></i> Security Checks: <span class="font-medium text-foreground">{{ $maintenance['security_status'] }}</span></li>
            <li><i class="fas fa-database    mr-2" style="color:#ca8a04"></i> Last Backup: <span class="font-medium text-foreground">{{ $maintenance['last_backup'] }}</span></li>
          </ul>
        </div>
      </div>
    </section>

    {{-- ANALYTICS --}}
    <div class="p-6 bg-card rounded-lg border border-divider/40">
      <h1 class="text-2xl font-bold mb-6">📊 System Analytics & Reports</h1>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-layer p-4 rounded border border-divider/40">
          <h2 class="font-semibold text-lg mb-2">Total Clients</h2>
          <p class="text-3xl">{{ $totalClients }}</p>
        </div>
        <div class="bg-layer p-4 rounded border border-divider/40">
          <h2 class="font-semibold text-lg mb-2">Total Coaches</h2>
          <p class="text-3xl">{{ $totalCoaches }}</p>
        </div>
        <div class="bg-layer p-4 rounded border border-divider/40">
          <h2 class="font-semibold text-lg mb-2">Community Posts</h2>
          <p class="text-3xl">{{ $totalCommunityPosts }}</p>
        </div>
        <div class="bg-layer p-4 rounded border border-divider/40">
          <h2 class="font-semibold text-lg mb-2">Total Comments</h2>
          <p class="text-3xl">{{ $totalComments }}</p>
        </div>
        <div class="bg-layer p-4 rounded border border-divider/40">
          <h2 class="font-semibold text-lg mb-2">Users Active Today</h2>
          <p class="text-3xl">{{ $activeToday }}</p>
        </div>
        <div class="bg-layer p-4 rounded border border-divider/40">
          <h2 class="font-semibold text-lg mb-2">Most Active Hour</h2>
          <p class="text-3xl">{{ $mostActiveHour ? $mostActiveHour->hour . ':00' : 'N/A' }}</p>
        </div>
      </div>
    </div>
  </main>
</div>

{{-- PASSCODE OVERLAY (plain black bg, no extra container, no close btn) --}}
<div x-data="passcodeRoot({ open: {{ $needsPasscode ? 'true' : 'false' }} })" x-init="init()">

  <div x-cloak x-show="open"
       @keydown.escape.window="maybeClose()"
       @click.self="maybeClose()"
       x-transition:enter="transition ease-out duration-300"
       x-transition:enter-start="opacity-0"
       x-transition:enter-end="opacity-100"
       x-transition:leave="transition ease-in duration-200"
       x-transition:leave-start="opacity-100"
       x-transition:leave-end="opacity-0"
       class="fixed inset-0 z-[999] flex items-center justify-center bg-black p-4"
       role="dialog" aria-modal="true" :aria-labelledby="$id('passcode-title')">

    <!-- CARD -->
    <div
      x-transition:enter="transition ease-out duration-300"
      x-transition:enter-start="opacity-0 translate-y-6 scale-95"
      x-transition:enter-end="opacity-100 translate-y-0 scale-100"
      x-transition:leave="transition ease-in duration-200"
      x-transition:leave-start="opacity-100 translate-y-0 scale-100"
      x-transition:leave-end="opacity-0 translate-y-6 scale-95"
      class="mx-4 w-full max-w-md rounded-[28px] overflow-hidden
             border border-white/10 bg-white/5 backdrop-blur-xl text-white
             shadow-[0_20px_80px_-20px_rgba(0,0,0,.85),inset_0_1px_0_rgba(255,255,255,.04)] p-8 relative">

      <!-- Logo -->
      <div class="flex justify-center mb-6">
        <div class="relative">
          <div class="absolute -inset-3 rounded-2xl bg-emerald-400/10 blur-xl"></div>
          <img src="{{ asset('image/bg/LOG.png') }}" alt="Logo"
               class="relative h-14 w-14 object-contain drop-shadow-[0_6px_24px_rgba(0,0,0,.4)]" />
        </div>
      </div>

      <!-- Header -->
      <div class="text-center mb-4">
        <h2 :id="$id('passcode-title')" class="text-2xl font-bold tracking-tight">Admin Passcode</h2>
        <p class="mt-1 text-sm text-zinc-300">Enter the 6-digit code to continue.</p>
      </div>

      <!-- Form -->
      <form @submit.prevent="submit" class="space-y-3">
        <!-- DIGITS: wrap + center to avoid overflow -->
        <div class="flex flex-wrap justify-center gap-2">
          <template x-for="(v, i) in digits" :key="i">
            <input data-digit
                   x-model="digits[i]"
                   type="text" inputmode="numeric" pattern="[0-9]*" maxlength="1"
                   @input="onInput($event, i)" @keydown.backspace.prevent="onBackspace($event, i)"
                   class="w-12 h-12 md:w-14 md:h-14 text-center text-lg md:text-xl font-semibold
                          rounded-2xl border border-white/10 bg-white/10
                          focus:outline-none focus:ring-2 focus:ring-emerald-400/70 focus:border-emerald-400/70
                          placeholder-white/40"
                   placeholder="•"
                   autocomplete="one-time-code" aria-label="Passcode digit" />
          </template>
        </div>

        <!-- Error -->
        <p class="min-h-5 text-sm" :class="error ? 'text-rose-400' : 'text-transparent'" x-text="error || ' '"></p>

        <!-- Submit -->
        <button type="submit" :disabled="loading"
                class="mt-1 w-full inline-flex items-center justify-center rounded-xl px-4 py-2.5
                       bg-emerald-500 text-white font-semibold tracking-tight
                       shadow-[0_10px_30px_-10px_rgba(16,185,129,.6)]
                       ring-1 ring-white/10 transition
                       hover:bg-emerald-600 disabled:opacity-60 disabled:cursor-not-allowed">
          <span x-show="!loading">Verify</span>
          <span x-show="loading" class="inline-flex items-center gap-2">
            <svg class="animate-spin h-4 w-4" viewBox="0 0 24 24" fill="none">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" fill="currentColor"></path>
            </svg>
            Verifying…
          </span>
        </button>
      </form>

      <p class="text-xs text-zinc-400 mt-4 text-center">
        Having trouble? Ask an administrator to resend your passcode.
      </p>
    </div>
  </div>
</div>

<script>
function getCsrf() {
  const el = document.querySelector('meta[name="csrf-token"]');
  return el ? el.getAttribute('content') : '';
}

function notifications(){
  return {
    panelOpen:false, notifications:[], pollHandle:null,
    init(){ this.fetchNotifications(); this.pollHandle=setInterval(()=>this.fetchNotifications(),30000); },
    async fetchNotifications(){
      try{
        const res=await fetch("{{ route('admin.notifications.fetch') }}",{ headers:{ 'Accept':'application/json' }});
        if(!res.ok) return;
        const data=await res.json();
        this.notifications=Array.isArray(data.notifications)?data.notifications:[];
      }catch(e){ console.error('Failed to fetch notifications:',e); }
    }
  }
}

function passcodeRoot({ open }) {
  return {
    open,
    loading: false,
    error: '',
    digits: ['', '', '', '', '', ''],

    init() {
      if (!this.open) return;
      this.$nextTick(() => {
        const first = this.$root.querySelector('[data-digit]');
        first && first.focus();
      });
    },

    onInput(e, i) {
      const v = (e.target.value || '').replace(/\D/g, '').slice(0, 1);
      this.digits[i] = v;
      e.target.value = v;
      if (v && i < 5) {
        const next = this.$root.querySelectorAll('[data-digit]')[i + 1];
        next && next.focus();
      }
    },

    onBackspace(e, i) {
      if (e.target.value) {
        this.digits[i] = '';
        e.target.value = '';
        return;
      }
      if (i > 0) {
        const prev = this.$root.querySelectorAll('[data-digit]')[i - 1];
        prev && prev.focus();
      }
    },

    async submit() {
      this.error = '';
      const code = this.digits.join('');
      if (code.length !== 6) {
        this.error = 'Please enter all 6 digits.';
        return;
      }
      this.loading = true;

      try {
        const res = await fetch("{{ route('admin.passcode.verify') }}", {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': getCsrf(),
            'Accept': 'application/json'
          },
          body: JSON.stringify({ code })
        });

        const data = await res.json().catch(() => ({}));

        if (res.ok && data.ok) {
          this.open = false;
          window.location.reload();
          return;
        }

        this.error = data.message || 'Invalid or expired code.';
      } catch (e) {
        this.error = 'Network error, please try again.';
      } finally {
        this.loading = false;
      }
    }
  }
}
</script>
</body>
</html>
