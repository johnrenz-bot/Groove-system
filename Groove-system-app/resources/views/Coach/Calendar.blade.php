@php
    $client = session('client');
    $coach  = session('coach');
    $user   = $client ?? $coach ?? auth()->user();
    $unreadNotifications = $user?->unreadNotifications ?? collect();
@endphp

<!DOCTYPE html>
<html lang="en" class="theme-dark">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Coach Calendar</title>

  <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet" />
  <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
  <script src="https://kit.fontawesome.com/43f9926b04.js" crossorigin="anonymous"></script>

  <link rel="icon" href="/image/bg/LOG.png" type="image/png" sizes="512x512">
  <link rel="apple-touch-icon" href="/image/wc/logo.png" sizes="180x180">

@vite(['resources/css/app.css', 'resources/js/app.js'])

  <style>
    [x-cloak] { display: none !important; }

    /* ===================== FullCalendar Styling ===================== */
    .fc-daygrid-body table { border-collapse: separate !important; border-spacing: 6px !important; width: 100%; }
    .fc-scrollgrid,
    .fc-scrollgrid-section-body td,
    .fc-daygrid-day,
    .fc-scrollgrid-section-header,
    .fc-col-header-cell,
    .fc-view-harness { border: none !important; }

    .fc .fc-toolbar-title { color: var(--fg); font-weight: 800; font-size: 1.4rem; letter-spacing: .02em; }

    .fc .fc-button {
      background: var(--layer); color: var(--fg);
      border: 1px solid var(--border); border-radius: 9999px;
      padding: .375rem 1rem; transition: transform .15s ease, filter .15s ease;
    }
    .fc .fc-button:hover { filter: brightness(1.05); transform: translateY(-1px); }
    .fc .fc-button:active { transform: translateY(0); }

    .fc-col-header-cell {
      background: transparent !important;
      color: var(--muted); font-weight: 700; text-transform: uppercase;
      font-size: .75rem; letter-spacing: .08em; padding: .35rem .25rem;
      border-bottom: 1px solid var(--border) !important;
    }

    .day-cell { background: var(--card); color: var(--fg); border-radius: .9rem; padding: .25rem; transition: filter .2s ease, transform .15s ease; }
    .day-cell:hover { filter: brightness(1.03); transform: translateY(-1px); }
    .is-today { outline: 2px solid var(--ring); outline-offset: -2px; }

    .fc-event, .fc-event-main { background: var(--layer) !important; color: var(--fg) !important; border: none !important; border-radius: .6rem !important; padding: .15rem .3rem !important; transition: transform .2s ease, filter .2s ease; }
    .fc-event:hover { transform: translateY(-1px); filter: brightness(1.05); }

    .fc-scroller::-webkit-scrollbar { height: 8px; width: 8px; background: transparent; }
    .fc-scroller::-webkit-scrollbar-thumb { background: var(--border); border-radius: 6px; }
    .fc-scroller { scrollbar-width: thin; scrollbar-color: var(--border) transparent; }

    .event-tip { position: fixed; z-index: 9999; background: var(--card); color: var(--fg); border: 1px solid var(--border); border-radius: .75rem; padding: .5rem .65rem; font-size: .75rem; pointer-events: none; max-width: 280px; line-height: 1.3; }

    .card-surface { background: var(--card); border: 1px solid var(--border); }

    .fc-popover, .fc-popover .fc-popover-header { background-color: var(--card) !important; color: var(--fg) !important; border: 1px solid var(--border) !important; }
    .fc-popover .fc-popover-header .fc-popover-title { color: var(--muted) !important; }
    .fc-popover .fc-popover-close { color: var(--fg) !important; }
  </style>

  <script>
    // Minimal theme helper. We default to DARK on first load.
    window.addTheme = function(name, vars = {}){
      const el = document.documentElement;
      el.classList.forEach(c => { if(c.startsWith('theme-')) el.classList.remove(c); });
      el.classList.add('theme-' + name);
      for (const [k,v] of Object.entries(vars)) el.style.setProperty('--' + k, v);
      localStorage.setItem('theme', name);
    }

    (function initTheme(){
      const saved = localStorage.getItem('theme');
      if(!saved){
        // default to dark; variables already set in :root
        document.documentElement.classList.add('theme-dark');
      } else {
        // If you ever add a light palette, set it here.
        document.documentElement.classList.add('theme-' + saved);
      }
    })();
  </script>
</head>

<body class="min-h-screen antialiased theme-{{ $appTheme}} overflow-x-hidden relative">
  <!-- Header -->
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

  <!-- Main -->
  <div class="w-full max-w-7xl mx-auto mt-24 px-4 flex gap-6">
    <!-- Calendar -->
    <div class="flex-1 card-surface rounded-2xl p-4">
      <div id="calendar"></div>
    </div>

    <!-- Right Column -->
    <div x-data="{ selected: null }" class="w-[360px] flex flex-col gap-6">
      <!-- Appointments List -->
      <div class="flex-1 overflow-y-auto card-surface rounded-2xl p-4">
        <h2 class="text-lg font-bold border-b border-ui pb-2">Your Booked Appointments</h2>
        <div class="flex flex-col gap-3 mt-3">
          @forelse($appointments as $index => $appointment)
            @if($appointment->status === 'cancelled')
              @continue
            @endif
            <div @click="selected = {{ $index }}" :class="selected === {{ $index }} ? 'ring-accent' : 'border border-ui'" class="p-3 rounded-xl hover:bg-layer transition cursor-pointer">
              <p class="font-semibold">{{ $appointment->talent ?? 'No Talent Specified' }}</p>
              <p class="text-xs text-muted">{{ \Carbon\Carbon::parse($appointment->date)->format('M d, Y') }} • {{ \Carbon\Carbon::parse($appointment->start_time)->format('g:i A') }}</p>
              <p class="text-xs mt-1">Status:
                <span :class="{
                  'text-success': '{{ $appointment->status }}' === 'confirmed',
                  'text-muted' : '{{ $appointment->status }}' === 'pending',
                  'text-danger': '{{ $appointment->status }}' === 'cancelled',
                  'text-fg'    : '{{ $appointment->status }}' === 'completed'
                }">{{ ucfirst($appointment->status) }}</span>
              </p>
            </div>
          @empty
            <p class="text-xs text-muted mt-2">No booked appointments yet.</p>
          @endforelse
        </div>
      </div>

      <!-- Appointment Details -->
      <div class="flex-1 overflow-y-auto">
        @foreach($appointments as $index => $appointment)
          @if($appointment->status === 'cancelled')
            @continue
          @endif
          <div x-show="selected === {{ $index }}" x-cloak class="card-surface p-4 rounded-2xl transition">
            @php $clientA = $appointment->client; @endphp
            <div class="flex items-center gap-3 mb-3">
              @if($clientA && $clientA->photo)
                <img src="{{ asset('storage/' . $clientA->photo) }}" alt="Client Avatar" class="w-12 h-12 rounded-full border border-ui">
              @elseif($clientA)
                <div class="w-12 h-12 grid place-items-center rounded-full border border-ui bg-layer font-bold">
                  {{ strtoupper(substr($clientA->firstname,0,1) . substr($clientA->lastname ?? '',0,1)) }}
                </div>
              @endif
              <div>
                <h3 class="text-lg font-bold">{{ $clientA->firstname ?? '' }} {{ $clientA->lastname ?? '' }}</h3>
                <p class="text-xs text-muted">ID: {{ $clientA->client_id ?? 'N/A' }}</p>
              </div>
            </div>

            <div class="text-sm text-muted space-y-1">
              <div><span class="font-semibold text-fg">Session:</span> {{ $appointment->session_type ?? 'N/A' }}</div>
              <div><span class="font-semibold text-fg">Talent:</span> {{ $appointment->talent ?? 'N/A' }}</div>
              <div><span class="font-semibold text-fg">Email:</span> {{ $appointment->email ?? 'N/A' }}</div>
              <div><span class="font-semibold text-fg">Phone:</span> {{ $appointment->contact ?? 'N/A' }}</div>
              <div><span class="font-semibold text-fg">Date:</span> {{ \Carbon\Carbon::parse($appointment->date)->format('M d, Y') }}</div>
              <div><span class="font-semibold text-fg">Time:</span> {{ \Carbon\Carbon::parse($appointment->start_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($appointment->end_time)->format('g:i A') }}</div>
              <div><span class="font-semibold text-fg">Experience:</span> {{ $appointment->experience ?? 'N/A' }}</div>
              <div><span class="font-semibold text-fg">Purpose:</span> {{ $appointment->purpose ?? 'N/A' }}</div>
            </div>

            <div class="mt-3 flex justify-end">
              @php $statusMap = [ 'pending' => 'badge ring-ui', 'completed' => 'badge ring-ui', 'cancelled' => 'badge ring-ui', 'confirmed' => 'badge ring-ui' ]; @endphp
              <span class="px-2 py-0.5 text-xs font-semibold rounded-full {{ $statusMap[$appointment->status] ?? 'badge ring-ui' }}">{{ ucfirst($appointment->status) }}</span>
            </div>
          </div>
        @endforeach
      </div>
    </div>
  </div>

  <script>
    // Server events
    document.addEventListener('DOMContentLoaded', () => {
      const events = [
        @foreach($events as $event)
          {
            id: '{{ $event['id'] }}',
            title: '{{ addslashes($event['title']) }}',
            start: '{{ $event['start'] }}',
            end:   '{{ $event['end'] }}',
            extendedProps: {
              status: '{{ $event['extendedProps']['status'] ?? '' }}',
              client: '{{ addslashes($event['extendedProps']['client'] ?? '') }}',
            }
          }@if(!$loop->last),@endif
        @endforeach
      ];

      const STATUS = {
        pending:   { label: 'Pending',   class: 'pill ring-ui' },
        confirmed: { label: 'Confirmed', class: 'pill ring-ui' },
        completed: { label: 'Completed', class: 'pill ring-ui' },
        cancelled: { label: 'Cancelled', class: 'pill ring-ui' }
      };

      let tipEl = null;
      const showTip = (html, x, y) => {
        hideTip();
        tipEl = document.createElement('div');
        tipEl.className = 'event-tip';
        tipEl.innerHTML = html || '';
        document.body.appendChild(tipEl);
        const pad = 12; const { innerWidth, innerHeight } = window; const rect = tipEl.getBoundingClientRect();
        tipEl.style.left = Math.min(x + pad, innerWidth - rect.width - pad) + 'px';
        tipEl.style.top  = Math.min(y + pad, innerHeight - rect.height - pad) + 'px';
      };
      const hideTip = () => { if (tipEl) { tipEl.remove(); tipEl = null; } };

      const calendarEl = document.getElementById('calendar');
      const cal = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        initialDate: new Date().toISOString().slice(0,10),
        height: 720,
        expandRows: false,
        aspectRatio: 1.8,
        dayMaxEvents: true,
        eventDisplay: 'block',
        selectable: true,
        events,
        headerToolbar: { left: 'prev,next today', center: 'title', right: 'dayGridMonth,timeGridWeek,timeGridDay' },
        dayCellClassNames: (arg) => { const base = ['day-cell']; if (arg.isToday) base.push('is-today'); const dow = arg.date.getDay(); if (dow === 0 || dow === 6) base.push('is-weekend'); return base; },
        dayHeaderClassNames: ['bg-transparent','font-bold','uppercase','text-xs','tracking-wide','py-3','px-2','select-none'],
        buttonClassNames: ['font-semibold','rounded-full','px-4','py-1.5','mx-1','transition','duration-200','focus-visible:outline'],
        titleClassNames: ['font-extrabold','text-2xl','tracking-wide','select-none','mb-5','pt-2','pb-1','border-b','border-ui'],
        eventContent: (info) => {
          const statusKey = (info.event.extendedProps.status || '').toLowerCase();
          const conf = STATUS[statusKey];
          const wrap = document.createElement('div'); wrap.className = 'flex items-center gap-2';
          const title = document.createElement('div'); title.className = 'text-[12px] leading-tight font-medium truncate'; title.textContent = info.event.title; wrap.appendChild(title);
          if (conf) { const pill = document.createElement('span'); pill.className = `ml-auto text-[10px] px-2 py-0.5 rounded-full ${conf.class}`; pill.textContent = conf.label; wrap.appendChild(pill); }
          return { domNodes: [wrap] };
        },
        eventMouseEnter: (info) => {
          const client = info.event.extendedProps.client || '—';
          const statusKey = (info.event.extendedProps.status || '').toLowerCase();
          const label = STATUS[statusKey]?.label || '—';
          const startStr = info.event.start ? new Date(info.event.start).toLocaleString() : '';
          const endStr   = info.event.end ? new Date(info.event.end).toLocaleString() : '';
          const html = `<div class="font-semibold mb-1">${info.event.title}</div>
                        <div class="text-muted"><div><span>Client:</span> ${client}</div>
                        <div><span>Status:</span> ${label}</div>
                        <div class="mt-1">${startStr}${endStr ? ' - ' + endStr : ''}</div></div>`;
          showTip(html, info.jsEvent.clientX, info.jsEvent.clientY);
        },
        eventMouseLeave: hideTip,
        eventMouseMove: (info) => {
          if (!tipEl) return; const pad = 12; const { innerWidth, innerHeight } = window; const rect = tipEl.getBoundingClientRect();
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
