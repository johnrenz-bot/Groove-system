@php
    $meta = [
        'light' => ['bg' => '#f8fafc', 'fg' => '#0f172a', 'sub' => 'Classic light UI'],
        'dark'  => ['bg' => '#0b1220', 'fg' => '#e5e7eb', 'sub' => 'Low-light, OLED friendly'],
        'ocean' => ['bg' => '#0e2a35', 'fg' => '#a7f3d0', 'sub' => 'Cool teal accents'],
    ];
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Groove Admin Dashboard — Theme Control</title>

  {{-- CSRF for AJAX --}}
  <meta name="csrf-token" content="{{ csrf_token() }}">

  {{-- Favicons --}}
  <link rel="icon" href="/image/bg/LOG.png" type="image/png" sizes="512x512">
  <link rel="apple-touch-icon" href="/image/wc/logo.png" sizes="180x180">

  {{-- Fonts / Icons / Tailwind (CDN) --}}
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://kit.fontawesome.com/43f9926b04.js" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

  @vite(['resources/css/app.css','resources/js/app.js'])

  <style>
    [x-cloak]{display:none !important;}
    body{font-family:"Instrument Sans",ui-sans-serif,system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial;}
    /* smoother focus rings */
    .focus-ring{outline:none;box-shadow:0 0 0 2px var(--color-primary);}
  </style>
</head>

<body x-data="{ sidebarOpen:false, openModal:false }" class="min-h-screen theme-{{ $current }} bg-surface text-foreground antialiased">
<div class="flex min-h-screen">

  {{-- LEFT SIDEBAR --}}
  <header class="w-64 h-screen bg-card text-foreground border-r border-divider/50 flex flex-col justify-between shadow-sm sticky top-0">
    <div class="flex flex-col p-6 space-y-8">
      <div class="flex items-center gap-3">
        <img src="/image/bg/LOG.png" alt="Logo" class="h-12 w-auto object-contain select-none" />
      </div>

      <nav class="flex flex-col space-y-1 text-sm font-medium p-3">
        <a href="dashboard" class="flex items-center px-4 py-2 rounded-lg text-foreground/80 hover:text-foreground hover:bg-layer transition">
          <i class="fas fa-home mr-3 w-5 opacity-70"></i> Dashboard
        </a>
        <a href="{{ route('admin.users') }}" class="flex items-center px-4 py-2 rounded-lg text-foreground/80 hover:text-foreground hover:bg-layer transition">
          <i class="fas fa-users mr-3 w-5 opacity-70"></i> Users
        </a>
        <a href="{{ route('admin.control') }}" class="flex items-center px-4 py-2 rounded-lg text-foreground bg-layer shadow-inner border border-divider/50">
          <i class="fas fa-layer-group mr-3 w-5 opacity-70"></i> Control
        </a>
        <a href="{{ route('admin.transaction') }}" class="flex items-center px-4 py-2 rounded-lg text-foreground/80 hover:text-foreground hover:bg-layer transition">
          <i class="fas fa-user-friends mr-3 w-5 opacity-70"></i> Transactions
        </a>
        {{-- ✅ Tickets --}}
        <a href="{{ route('admin.tickets') }}"
           class="flex items-center px-4 py-2 rounded-lg
                  {{ request()->routeIs('admin.tickets') ? 'text-foreground bg-layer shadow-inner border border-divider/50' : 'text-foreground/80 hover:text-foreground hover:bg-layer' }}">
          <i class="fas fa-ticket-alt mr-3 w-5 opacity-70"></i> Tickets
        </a>
      </nav>
    </div>

    <div class="p-6 border-t border-divider/50 space-y-4">
      <div class="flex justify-center">
        {{-- Trigger Announcements (works with modal below) --}}
        <button @click="openModal = true"
                class="relative p-2 bg-layer rounded-full hover:opacity-90 transition border border-divider/40 focus:outline-none focus:ring-2"
                style="--tw-ring-color: var(--color-primary)">
          <i class="fas fa-bullhorn"></i>
          <span class="absolute top-0 right-0 w-2 h-2 rounded-full ring-1 ring-[var(--color-card)]" style="background: var(--color-primary)"></span>
        </button>
      </div>

      <div class="w-full" x-data="{ openUser:false }">
        <button @click="openUser = !openUser"
                class="flex items-center gap-3 px-4 py-3 bg-layer rounded-xl hover:opacity-95 hover:shadow-sm transition w-full border border-divider/40 focus:outline-none focus:ring-2"
                style="--tw-ring-color: var(--color-primary)">
          <div class="flex-1 text-left">
            <p class="text-sm font-semibold truncate">{{ optional(Auth::guard('admin')->user())->name ?? 'Admin' }}</p>
            <p class="text-xs text-foreground/60 truncate">{{ optional(Auth::guard('admin')->user())->email ?? 'admin@example.com' }}</p>
          </div>
          <i class="fas fa-chevron-down text-xs transition-transform duration-200 opacity-70" :class="{ 'rotate-180': openUser }"></i>
        </button>

        <div x-show="openUser" x-transition x-cloak class="mt-2 bg-card border border-divider/50 rounded-xl overflow-hidden shadow-lg">
          <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="w-full text-left px-4 py-2 text-sm text-[crimson] hover:bg-layer transition flex items-center gap-2">
              <i class="fas fa-sign-out-alt"></i> Logout
            </button>
          </form>
        </div>
      </div>
    </div>
  </header>

  {{-- MAIN CONTENT --}}
  <main class="flex-1 px-6 md:px-8 py-6 overflow-y-auto bg-surface">
    {{-- Page header --}}
    <div class="w-full flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between mb-6">
      <h1 class="text-2xl font-bold">Theme Control</h1>
      <div class="flex items-center gap-2">
        <button @click="openModal = true"
                class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-primary-foreground rounded-2xl hover:opacity-95 transition focus:outline-none focus:ring-2"
                style="--tw-ring-color: var(--color-primary)">
          <i class="fas fa-bullhorn"></i>
          <span class="text-sm font-medium">Announcements</span>
        </button>
        <a href="{{ route('admin.control') }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-card border border-divider/40 rounded-2xl hover:opacity-95 hover:shadow-sm transition focus:outline-none focus:ring-2"
           style="--tw-ring-color: var(--color-primary)">
          <i class="fas fa-rotate-right" style="color: var(--color-primary)"></i>
          <span class="text-sm font-medium">Refresh</span>
        </a>
      </div>
    </div>

    {{-- Flash message --}}
    @if (session('status'))
      <div class="mb-4 rounded-xl border border-divider/40 bg-card px-4 py-3 text-foreground" role="alert" aria-live="polite">
        {{ session('status') }}
      </div>
    @endif

    {{-- Theme Controller Card --}}
    <div class="overflow-hidden rounded-2xl border border-divider/40 bg-card shadow-sm">
      <div class="flex items-center justify-between gap-3 border-b border-divider/40 px-6 py-4">
        <div>
          <h2 class="text-lg font-semibold leading-tight">Appearance</h2>
          <p class="text-sm text-foreground/60">Pick a theme, preview it, then save.</p>
        </div>
        <span class="inline-flex items-center gap-2 rounded-full bg-layer px-3 py-1 text-xs font-medium">
          <i class="fa-solid fa-circle-info opacity-80"></i> Live preview
        </span>
      </div>

<form id="themeForm" method="POST" action="{{ route('admin.control.updateTheme') }}" class="p-6">
        @csrf

        @error('theme')
          <div class="mb-4 rounded-lg border border-divider/40 bg-layer px-4 py-3" role="alert" aria-live="assertive">
            {{ $message }}
          </div>
        @enderror

<div x-data="{ selected: '{{ $current }}' }"
     class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
  @foreach (['light','dark','ocean'] as $theme)
    <label
      for="t-{{ $theme }}"
      :class="selected === '{{ $theme }}'
                ? 'border-[var(--color-primary)] shadow-md'
                : 'border-divider/40 hover:border-divider/60 hover:shadow-sm'"
      class="group block cursor-pointer rounded-xl border-2 p-4 transition">
      <div class="flex items-start gap-3">
        <input id="t-{{ $theme }}" type="radio" name="theme" value="{{ $theme }}"
               class="mt-1 h-4 w-4"
               style="accent-color: var(--color-primary)"
               x-model="selected"
               aria-describedby="t-{{ $theme }}-desc" />
        <div>
          <div class="flex items-center gap-2">
            <span class="font-semibold capitalize">{{ $theme }}</span>
            <template x-if="selected === '{{ $theme }}'">
              <span class="rounded-full bg-layer px-2 py-0.5 text-[10px] font-semibold border border-divider/40"
                    style="color: var(--color-primary)">Current</span>
            </template>
          </div>
          <div id="t-{{ $theme }}-desc" class="text-xs text-foreground/60">
            {{ $meta[$theme]['sub'] }}
          </div>
        </div>
      </div>

      {{-- Preview tile --}}
      <div class="mt-4">
        <div class="overflow-hidden rounded-lg border border-divider/40">
          <div class="px-4 py-2 text-sm font-semibold"
               style="background: {{ $meta[$theme]['bg'] }}; color: {{ $meta[$theme]['fg'] }}">
            Preview header
          </div>
          <div class="space-y-3 bg-card p-4">
            <div class="h-2 w-full rounded bg-layer"></div>
            <div class="h-2 w-3/4 rounded bg-layer"></div>
            <div class="mt-2 grid grid-cols-3 gap-2">
              <div class="h-8 rounded" style="background: {{ $meta[$theme]['bg'] }}"></div>
              <div class="h-8 rounded" style="background: {{ $meta[$theme]['bg'] }}"></div>
              <div class="h-8 rounded" style="background: {{ $meta[$theme]['bg'] }}"></div>
            </div>
          </div>
        </div>
      </div>
    </label>
  @endforeach
</div>


        {{-- Actions --}}
        <div class="mt-6 flex items-center justify-end gap-3">
          <a href="{{ route('admin.control') }}"
             class="inline-flex items-center gap-2 rounded-lg border border-divider/40 bg-card px-4 py-2 text-sm hover:opacity-95 transition focus:outline-none focus:ring-2"
             style="--tw-ring-color: var(--color-primary)">
            Cancel
          </a>
          <button id="saveBtn" type="submit"
                  class="inline-flex items-center gap-2 rounded-lg border px-4 py-2 text-sm font-semibold hover:opacity-90 transition focus:outline-none focus:ring-2"
                  style="background: var(--color-primary); border-color: var(--color-primary); color: var(--color-primary-foreground); --tw-ring-color: var(--color-primary);">
            <i class="fa-solid fa-floppy-disk"></i> Save theme
          </button>
        </div>
      </form>
    </div>

    {{-- Tip --}}
    <div class="mt-4 rounded-xl border border-divider/40 bg-card px-4 py-3">
      <span class="text-sm">
        Tip: Apply globally by putting <code class="px-1 bg-layer rounded">theme-@{{ $current }}</code> on the
        <code class="px-1 bg-layer rounded">&lt;body&gt;</code>.
      </span>
    </div>
  </main>
</div>

{{-- 📦 ANNOUNCEMENTS MODAL (Improved + Teleported) --}}
<template x-teleport="body">
  <div
    x-show="openModal"
    x-cloak
    x-trap="openModal"
    @keydown.escape.window="openModal=false"
    @click.self="openModal=false"
    class="fixed inset-0 z-[100] flex items-center justify-center px-4"
    aria-labelledby="annc-title"
    aria-describedby="annc-desc"
    role="dialog"
    aria-modal="true"
  >
    {{-- Backdrop --}}
    <div
      class="absolute inset-0 bg-black/30"
      x-show="openModal"
      x-transition:enter="transition ease-out duration-200"
      x-transition:enter-start="opacity-0"
      x-transition:enter-end="opacity-100"
      x-transition:leave="transition ease-in duration-150"
      x-transition:leave-start="opacity-100"
      x-transition:leave-end="opacity-0"
    ></div>

    {{-- Panel --}}
    <div
      x-show="openModal"
      x-transition:enter="transition ease-out duration-200 motion-reduce:duration-0"
      x-transition:enter-start="opacity-0 translate-y-2 sm:translate-y-0 sm:scale-95"
      x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
      x-transition:leave="transition ease-in duration-150 motion-reduce:duration-0"
      x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
      x-transition:leave-end="opacity-0 translate-y-2 sm:translate-y-0 sm:scale-95"
      class="relative w-full max-w-md rounded-2xl border border-divider/50 bg-card p-6 shadow-2xl focus:outline-none"
      @click.stop
      @keydown.tab.prevent="
        const f=[...$el.querySelectorAll('a,button,input,textarea,select,[tabindex]:not([tabindex=\'-1\'])')].filter(el=>!el.hasAttribute('disabled') && !el.getAttribute('aria-hidden'));
        if(!f.length) return;
        const i=f.indexOf(document.activeElement);
        if($event.shiftKey) (f[i<=0?f.length-1:i-1]).focus(); else (f[i===f.length-1?0:i+1]).focus();
      "
    >
      {{-- Close --}}
      <button
        @click="openModal=false"
        class="absolute top-4 right-4 inline-flex h-9 w-9 items-center justify-center rounded-lg border border-divider/40 bg-layer text-foreground/80 hover:text-foreground hover:shadow focus:outline-none focus:ring-2 focus:ring-offset-2"
        style="--tw-ring-color: var(--color-primary); --tw-ring-offset-color: var(--color-card);"
        type="button"
        aria-label="Close announcements"
      >&times;</button>

      {{-- Header --}}
      <h2 id="annc-title" class="mb-1 text-2xl font-semibold">System Announcements</h2>
      <p id="annc-desc" class="mb-4 border-b border-divider/40 pb-2 text-sm text-foreground/60">
        View the latest updates from admins. Type and send new announcements below.
      </p>

      {{-- Feed --}}
      <div class="bg-layer mb-4 h-64 overflow-y-auto rounded-xl border border-divider/40 p-4 shadow-inner"
           x-ref="feed" role="log" aria-live="polite" aria-relevant="additions">
        <template x-for="(msg, idx) in $store.announcements.items" :key="(msg.time ?? '') + (msg.text ?? '') + idx">
          <div class="mb-2">
            <p class="text-sm">
              <span class="font-semibold" style="color: var(--color-primary)">#Admin:</span>
              <span x-text="msg.text"></span>
            </p>
            <p class="text-xs text-foreground/60" x-text="msg.time"></p>
          </div>
        </template>
        <template x-if="$store.announcements.items.length === 0">
          <p class="text-sm text-foreground/60">No announcements yet.</p>
        </template>
      </div>

      @auth('admin')
      {{-- Composer --}}
      <form class="flex gap-3" @submit.prevent="$store.announcements.send($event)" x-data="{ sending:false }" @submit="sending=true; setTimeout(()=>sending=false, 600)">
        <label for="annc-input" class="sr-only">Announcement</label>
        <input id="annc-input" x-ref="msg" name="msg" type="text" placeholder="Write an announcement…"
               class="flex-1 rounded-xl border border-divider/50 bg-card p-3 placeholder:text-foreground/60 focus:outline-none focus:ring-2"
               style="--tw-ring-color: var(--color-primary)" required autocomplete="off" />
        <button type="submit"
                class="rounded-xl bg-primary px-5 py-2 font-semibold text-primary-foreground shadow transition hover:opacity-90 disabled:opacity-60"
                :disabled="sending">
          <span x-show="!sending"><i class="fas fa-paper-plane mr-2"></i>Send</span>
          <span x-show="sending">Sending…</span>
        </button>
      </form>
      @endauth

      {{-- Toast --}}
      <div x-show="$store.announcements.successMessage" x-transition
           class="pointer-events-none absolute bottom-6 left-1/2 -translate-x-1/2 rounded-lg bg-primary px-4 py-2 text-primary-foreground shadow-lg"
           role="status" aria-live="polite">
        <span x-text="$store.announcements.successMessage"></span>
      </div>
    </div>
  </div>

  {{-- Lock body scroll while open --}}
  <div x-show="openModal" x-cloak x-effect="$root.ownerDocument.body.classList.toggle('overflow-hidden', openModal)"></div>
</template>

{{-- Alpine store for announcements (safe stub) --}}
<script>
  document.addEventListener('alpine:init', () => {
    Alpine.store('announcements', {
      items: [],
      successMessage: '',
      send(e) {
        const input = e.target.querySelector('[name="msg"]');
        if (!input?.value) return;
        this.items.unshift({ text: input.value, time: new Date().toLocaleString() });
        this.successMessage = 'Announcement sent!';
        input.value = '';
        setTimeout(() => this.successMessage = '', 1800);
      }
    });
  });
</script>

{{-- Live preview + AJAX save (fallback to normal POST) --}}
<script>
  (function () {
    const body = document.body;
    const radios = document.querySelectorAll('input[name="theme"]');
    const form = document.getElementById('themeForm');

    function applyTheme(theme) {
      body.classList.remove('theme-light','theme-dark','theme-ocean');
      body.classList.add('theme-' + theme);
    }

    // Sync initial
    applyTheme('{{ $current }}');

    // Live preview on change
    radios.forEach(r => r.addEventListener('change', e => applyTheme(e.target.value)));

    // AJAX save with graceful fallback
    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      const fd = new FormData(form);
      try {
        const resp = await fetch(form.action, {
          method: 'POST',
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
          },
          body: fd
        });
        if (!resp.ok) throw new Error('Bad status');

        const toast = document.createElement('div');
        toast.className = 'fixed bottom-4 right-4 z-50 rounded-lg border border-divider/40 bg-card px-4 py-2 shadow';
        toast.textContent = 'Theme saved!';
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 2000);
      } catch (err) {
        form.submit(); // fallback full POST
      }
    });
  })();
</script>
</body>
</html>
