@php
    $client = session('client');
    $coach  = session('coach');
    $user   = $client ?? $coach ?? auth()->user();

    $notifications       = $user ? $user->notifications()->latest()->take(5)->get() : collect();
    $unreadNotifications = $user ? $user->unreadNotifications : collect();
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Calendar</title>

@vite(['resources/css/app.css', 'resources/js/app.js'])

  <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet" />
  <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
  <script src="https://kit.fontawesome.com/43f9926b04.js" crossorigin="anonymous"></script>

  <link rel="icon" href="/image/bg/LOG.png" type="image/png" sizes="512x512">

  <style>
    /* Hide Alpine elements until hydrated */
    [x-cloak] { display: none !important; }

    /* FullCalendar — theme-aware using CSS variables from app.css */
    .fc-daygrid-body table { border-collapse: separate !important; border-spacing: 6px !important; width: 100%; }
    .fc-scrollgrid, .fc-scrollgrid-section-body td, .fc-daygrid-day,
    .fc-scrollgrid-section-header, .fc-col-header-cell, .fc-view-harness { border: none !important; }

    .fc .fc-toolbar-title {
      color: var(--foreground) !important;
      font-weight: 800;
      font-size: 1.4rem;
      letter-spacing: .02em;
    }

    /* Buttons */
    .fc .fc-button {
      background: var(--layer) !important;
      color: var(--foreground) !important;
      border: 1px solid var(--divider) !important;
      border-radius: 9999px !important;
      padding: .375rem 1rem !important;
      box-shadow: 0 4px 12px color-mix(in oklab, var(--color-primary) 25%, transparent);
      transition: transform .15s ease, box-shadow .15s ease, background .15s ease;
    }
    .fc .fc-button:hover {
      background: color-mix(in oklab, var(--layer) 80%, var(--surface));
      box-shadow: 0 6px 16px color-mix(in oklab, var(--color-primary) 45%, transparent);
      transform: translateY(-1px);
    }
    .fc .fc-button:active { transform: translateY(0); }

    /* Day headers */
    .fc-col-header-cell {
      background: transparent !important;
      color: var(--color-primary) !important;
      font-weight: 700;
      text-transform: uppercase;
      font-size: .75rem;
      letter-spacing: .08em;
      padding: .35rem .25rem;
      border-bottom: 1px solid var(--divider) !important;
    }

    /* Day cells */
    .day-cell {
      background: color-mix(in oklab, var(--card) 90%, transparent);
      color: var(--foreground);
      border-radius: .9rem;
      padding: .25rem;
      transition: background .2s ease, box-shadow .2s ease;
    }
    .day-cell:hover {
      background: color-mix(in oklab, var(--layer) 92%, var(--card));
      box-shadow: inset 0 6px 18px color-mix(in oklab, var(--color-primary) 25%, transparent);
    }

    /* Today */
    .is-today {
      position: relative;
      box-shadow:
        inset 0 0 0 2px color-mix(in oklab, var(--color-primary) 35%, transparent),
        0 0 26px color-mix(in oklab, var(--color-primary) 25%, transparent);
      background: linear-gradient(180deg,
        color-mix(in oklab, var(--color-primary) 18%, transparent),
        color-mix(in oklab, var(--card) 85%, transparent));
    }
    .is-today .fc-daygrid-day-number { color: var(--foreground) !important; font-weight: 800; }

    /* Weekends */
    .is-weekend {
      background: linear-gradient(180deg,
        color-mix(in oklab, var(--color-primary) 10%, transparent),
        color-mix(in oklab, var(--card) 85%, transparent));
    }

    /* Events */
    .fc-event, .fc-event-main {
      background: color-mix(in oklab, var(--layer) 85%, var(--card)) !important;
      color: color-mix(in oklab, var(--color-primary) 70%, var(--foreground)) !important;
      border: none !important;
      border-radius: .6rem !important;
      padding: .15rem .3rem !important;
      box-shadow:
        0 0 0 1px color-mix(in oklab, var(--color-primary) 25%, transparent),
        0 4px 12px color-mix(in oklab, var(--color-primary) 35%, transparent);
      transition: box-shadow .2s ease, transform .2s ease, background .2s ease, color .2s ease;
    }
    .fc-event:hover {
      background: var(--color-primary) !important;
      color: white !important;
      transform: translateY(-1px);
      box-shadow: 0 8px 20px color-mix(in oklab, var(--color-primary) 55%, transparent);
    }

    /* Scrollbar */
    .fc-scroller::-webkit-scrollbar { height: 8px; width: 8px; background: transparent; }
    .fc-scroller::-webkit-scrollbar-thumb {
      background: color-mix(in oklab, var(--color-primary) 45%, transparent);
      border-radius: 6px;
    }
    .fc-scroller { scrollbar-width: thin; scrollbar-color: color-mix(in oklab, var(--color-primary) 45%, transparent) transparent; }

    /* Tooltip */
    .event-tip {
      position: fixed;
      z-index: 9999;
      background: color-mix(in oklab, var(--surface) 96%, black);
      color: var(--foreground);
      border: 1px solid color-mix(in oklab, var(--color-primary) 40%, var(--divider));
      border-radius: .75rem;
      padding: .5rem .65rem;
      font-size: .75rem;
      box-shadow: 0 12px 28px rgba(0,0,0,.45), 0 6px 12px color-mix(in oklab, var(--color-primary) 25%, transparent);
      pointer-events: none;
      max-width: 280px;
      line-height: 1.3;
    }

    /* Popover */
    .fc-popover, .fc-popover .fc-popover-header {
      background-color: var(--card) !important;
      color: var(--foreground) !important;
      border: 1px solid var(--divider) !important;
    }
    .fc-popover .fc-popover-header .fc-popover-title { color: color-mix(in oklab, var(--foreground) 70%, transparent) !important; }
    .fc-popover .fc-popover-close { color: var(--foreground) !important; }
  </style>
</head>

<body class="min-h-screen antialiased theme-{{ $appTheme ?? 'light' }} bg-surface text-foreground pt-20">

  <!-- Header -->
  <header
    x-data="{ scrolled: false }"
    x-init="window.addEventListener('scroll', () => { scrolled = window.scrollY > 10 })"
    class="fixed top-0 left-0 right-0 z-50 transition duration-300 border-b border-divider/40"
    :class="scrolled ? 'bg-card/80 backdrop-blur shadow-sm' : 'bg-transparent'">
    <div class="max-w-7xl mx-auto px-4 lg:px-8 py-3">
      <div class="flex items-center gap-4">
        <!-- Left: Logo -->
        <div class="flex items-center gap-3 shrink-0">
          <img src="/image/wc/logo.png" alt="Logo" class="h-10 w-auto object-contain select-none" />
        </div>

        <!-- Center: Nav -->
        <nav class="hidden md:flex flex-1 justify-center gap-2 lg:gap-4 text-sm font-medium">
        <a href="/client/home"
           class="relative  px-4 py-2 rounded-xl text-foreground/70 hover:text-foreground hover:bg-layer hover:border hover:border-divider/40 hover:shadow-md transition-all duration-300">
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

        <!-- Right: Actions -->
        <div class="ml-auto flex items-center gap-3">
          <!-- Notifications -->
          <div x-data="{ openNotif: false }" class="relative" x-cloak>
            <button @click="openNotif = !openNotif"
                    class="w-10 h-10 flex items-center justify-center rounded-full bg-layer border border-divider/40 hover:opacity-95 transition relative">
              <i class="fa-regular fa-bell text-lg" style="color: var(--color-primary)"></i>
              @if ($unreadNotifications->count())
                <span x-show="!openNotif"
                      class="absolute -top-1 -right-1 w-5 h-5 text-[10px] font-bold text-white rounded-full flex items-center justify-center"
                      style="background: var(--color-primary)">
                  {{ $unreadNotifications->count() }}
                </span>
              @endif
            </button>

            <!-- Notification Dropdown -->
            <div x-show="openNotif" @click.away="openNotif = false"
                 class="absolute right-0 mt-3 w-80 max-h-96 bg-card border border-divider/40 rounded-xl shadow-xl p-4 space-y-3 z-50 overflow-y-auto"
                 x-transition x-cloak>
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
          <div x-data="{ open: false }" class="relative" x-cloak>
            <button @click="open = !open"
              class="flex items-center gap-x-3 px-3 py-2 bg-layer rounded-full
                     hover:opacity-95 active:scale-95 transition duration-200 border border-divider/40"
              aria-label="User Profile Menu">

              <div x-data="{ photoUrl: null }">
                <template x-if="photoUrl">
                  <img :src="photoUrl" alt="User Avatar"
                       class="w-8 h-8 rounded-full object-cover border-2"
                       style="border-color: var(--color-primary)">
                </template>
                <template x-if="!photoUrl">
                  <div class="w-8 h-8 flex items-center justify-center bg-card rounded-full
                              text-sm font-bold uppercase border border-divider/40">
                    {{ strtoupper(substr($user->firstname ?? 'C', 0, 1)) }}{{ strtoupper(substr($user->middlename ?? 'C', 0, 1)) }}
                  </div>
                </template>
              </div>

              <div class="flex items-center space-x-2 text-xs leading-none">
                <span class="capitalize">
                  {{ strtolower($user->firstname ?? 'client') }} {{ $user->middlename }}
                </span>
                <i class="fa-solid fa-caret-down opacity-70"></i>
              </div>
            </button>

            <div x-show="open" @click.away="open = false"
              class="absolute right-0 mt-2 w-60 bg-card border border-divider/40 rounded-2xl
                     shadow-[0_12px_28px_rgba(0,0,0,0.35)] ring-1 ring-divider/40 z-50 overflow-hidden transition-all duration-300 origin-top"
              x-transition x-cloak>

              <div class="px-4 py-3 bg-layer border-b border-divider/40 text-center">
                <p class="text-sm font-semibold">
                  {{ $user->firstname ?? 'Client' }} {{ $user->middlename ?? '' }}
                </p>
                <p class="text-xs text-foreground/60 mt-0.5">
                  #{{ $user->client_id ?? '0000' }} &bullet; {{ ucfirst($user->role ?? 'client') }}
                </p>
              </div>

              <div class="flex flex-col px-3 py-2 space-y-1">
                <a href="{{ route('profile') }}"
                   class="flex items-center gap-2 hover:bg-layer px-3 py-1.5 rounded-xl transition">
                  <i class="fa-regular fa-user text-sm opacity-70"></i>
                  <span class="text-sm">Profile</span>
                </a>
                <a href="/client/profile/edit"
                   class="flex items-center gap-2 hover:bg-layer px-3 py-1.5 rounded-xl transition">
                  <i class="fa-solid fa-gear text-sm opacity-70"></i>
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
        </div><!-- /Right -->
      </div>
    </div>
  </header>

  <!-- Main -->
  <main class="max-w-6xl mx-auto px-4">
    <div class="bg-card border border-divider/40 rounded-2xl p-5 mt-6">
      <div id="calendar"></div>
    </div>
  </main>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      // Events from server
      const events = [
        @foreach ($events as $event)
        {
          id: '{{ $event['id'] }}',
          title: '{{ addslashes($event['title']) }}',
          start: '{{ $event['start'] }}',
          end: '{{ $event['end'] }}',
          extendedProps: {
            status: '{{ $event['extendedProps']['status'] ?? '' }}',
            coach: '{{ addslashes($event['extendedProps']['coach'] ?? '') }}',
          }
        }@if (!$loop->last),@endif
        @endforeach
      ];

      // Status pills
      const STATUS = {
        pending:   { label: 'Pending',   class: 'bg-yellow-500/20 text-yellow-300 ring-1 ring-yellow-400/40' },
        confirmed: { label: 'Confirmed', class: 'bg-emerald-500/20 text-emerald-300 ring-1 ring-emerald-400/40' },
        completed: { label: 'Completed', class: 'bg-purple-500/25 text-purple-200 ring-1 ring-purple-400/40' },
        cancelled: { label: 'Cancelled', class: 'bg-rose-500/20 text-rose-300 ring-1 ring-rose-400/40' }
      };

      // Tooltip
      let tipEl = null;
      const showTip = (html, x, y) => {
        hideTip();
        tipEl = document.createElement('div');
        tipEl.className = 'event-tip';
        tipEl.innerHTML = html || '';
        document.body.appendChild(tipEl);

        const pad = 12;
        const { innerWidth, innerHeight } = window;
        const rect = tipEl.getBoundingClientRect();
        tipEl.style.left = Math.min(x + pad, innerWidth - rect.width - pad) + 'px';
        tipEl.style.top  = Math.min(y + pad, innerHeight - rect.height - pad) + 'px';
      };
      const hideTip = () => { if (tipEl) { tipEl.remove(); tipEl = null; } };

      // Calendar
      const cal = new FullCalendar.Calendar(document.getElementById('calendar'), {
        initialView: 'dayGridMonth',
        initialDate: new Date().toISOString().slice(0, 10),

        height: 720,
        expandRows: false,
        aspectRatio: 1.8,
        dayMaxEvents: true,
        events,

        headerToolbar: {
          left: 'prev,next today',
          center: 'title',
          right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },

        dayCellClassNames: (arg) => {
          const base = ['day-cell'];
          if (arg.isToday) base.push('is-today');
          if (arg.date.getDay() === 0 || arg.date.getDay() === 6) base.push('is-weekend');
          return base;
        },

        eventContent: function(info) {
          const statusKey = (info.event.extendedProps.status || '').toLowerCase();
          const conf = STATUS[statusKey];

          const wrap = document.createElement('div');
          wrap.className = 'flex items-center gap-2';
          const title = document.createElement('div');
          title.className = 'text-[12px] leading-tight font-medium truncate';
          title.textContent = info.event.title;
          wrap.appendChild(title);

          if (conf) {
            const pill = document.createElement('span');
            pill.className = `ml-auto text-[10px] px-2 py-0.5 rounded-full ${conf.class}`;
            pill.textContent = conf.label;
            wrap.appendChild(pill);
          }
          return { domNodes: [wrap] };
        },

        eventMouseEnter: function(info) {
          const coach = info.event.extendedProps.coach || '—';
          const statusKey = (info.event.extendedProps.status || '').toLowerCase();
          const label = STATUS[statusKey]?.label || '—';
          const start = info.event.start ? new Date(info.event.start).toLocaleString() : '';
          const html = `
            <div class="font-semibold mb-1">${info.event.title}</div>
            <div class="opacity-80">
              <div><span class="opacity-70">Coach:</span> ${coach}</div>
              <div><span class="opacity-70">Status:</span> ${label}</div>
              <div class="mt-1">${start}</div>
            </div>`;
          showTip(html, info.jsEvent.clientX, info.jsEvent.clientY);
        },
        eventMouseLeave: hideTip,
        eventMouseMove: function(info) {
          if (!tipEl) return;
          const pad = 12;
          const { innerWidth, innerHeight } = window;
          const rect = tipEl.getBoundingClientRect();
          tipEl.style.left = Math.min(info.jsEvent.clientX + pad, innerWidth - rect.width - pad) + 'px';
          tipEl.style.top  = Math.min(info.jsEvent.clientY + pad, innerHeight - rect.height - pad) + 'px';
        },
      });

      cal.render();
      window.addEventListener('scroll', hideTip);
      window.addEventListener('resize', hideTip);
    });
  </script>
</body>
</html>
