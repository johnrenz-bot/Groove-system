{{-- resources/views/Admin/AdminTickets.blade.php --}}
@php
  use App\Models\Ticket;
  use Illuminate\Support\Str;


  // Global totals
  $total   = Ticket::count();
  $open    = Ticket::where('status','open')->count();
  $pending = Ticket::where('status','pending')->count();
  $closed  = Ticket::where('status','closed')->count();

  // Filters (from query string)
  $q         = request('q');
  $fStatus   = request('status');
  $fPriority = request('priority');
@endphp

<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Groove · Admin Tickets</title>

  @vite(['resources/css/app.css','resources/js/app.js'])
  <link rel="icon" href="/image/bg/LOG.png" type="image/png" sizes="512x512">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <script src="https://kit.fontawesome.com/43f9926b04.js" crossorigin="anonymous"></script>
  <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
  <meta name="csrf-token" content="{{ csrf_token() }}" />

  <style>
    [x-cloak]{display:none!important;}
    .custom-scrollbar{scrollbar-width:thin; scrollbar-color:#6b7280 transparent;}
    .custom-scrollbar:hover{scrollbar-color:#71717a transparent;}
    .custom-scrollbar::-webkit-scrollbar{width:8px; height:8px;}
    .custom-scrollbar::-webkit-scrollbar-track{background:transparent;}
    .custom-scrollbar::-webkit-scrollbar-thumb{background:#6b7280; border-radius:6px;}
    .custom-scrollbar::-webkit-scrollbar-thumb:hover{background:#4b5563;}
  </style>


@vite(['resources/css/app.css', 'resources/js/app.js'])

</head>

<body class="min-h-screen antialiased theme-{{ $appTheme }} bg-surface text-foreground">
<div class="flex min-h-screen">
  {{-- ===== Sidebar (styled like Dashboard) ===== --}}
  <aside class="w-64 h-screen bg-card text-foreground border-r border-divider/40 flex flex-col justify-between shadow-sm">
    <div class="flex flex-col p-6 space-y-6">
      {{-- Brand --}}
      <div class="flex items-center gap-3">
        <img src="/image/bg/LOG.png" alt="Logo" class="h-12 w-auto object-contain select-none" />
      </div>

      {{-- Nav --}}
      <nav class="flex flex-col space-y-1 text-sm font-medium p-1">
        <a href="dashboard"
           class="flex items-center px-4 py-2 rounded-lg {{ request()->is('admin/dashboard*') ? 'text-foreground bg-layer shadow-inner border border-divider/50' : 'text-foreground/80 hover:text-foreground hover:bg-layer' }}">
          <i class="fas fa-home mr-3 w-5 opacity-70"></i> Dashboard
        </a>
        <a href="{{ route('admin.users') }}"
           class="flex items-center px-4 py-2 rounded-lg {{ request()->is('admin/users*') ? 'text-foreground bg-layer shadow-inner border border-divider/50' : 'text-foreground/80 hover:text-foreground hover:bg-layer' }}">
          <i class="fas fa-users mr-3 w-5 opacity-70"></i> Users
        </a>
        <a href="{{ route('admin.control') }}"
           class="flex items-center px-4 py-2 rounded-lg {{ request()->is('admin/control*') ? 'text-foreground bg-layer shadow-inner border border-divider/50' : 'text-foreground/80 hover:text-foreground hover:bg-layer' }}">
          <i class="fas fa-layer-group mr-3 w-5 opacity-70"></i> Control
        </a>
        <a href="{{ route('admin.transaction') }}"
           class="flex items-center px-4 py-2 rounded-lg {{ request()->is('admin/transaction*') ? 'text-foreground bg-layer shadow-inner border border-divider/50' : 'text-foreground/80 hover:text-foreground hover:bg-layer' }}">
          <i class="fas fa-receipt mr-3 w-5 opacity-70"></i> Transactions
        </a>

        {{-- Tickets (active) --}}
        <a href="{{ route('admin.tickets') }}"
           class="flex items-center px-4 py-2 rounded-lg {{ request()->routeIs('admin.tickets') ? 'text-foreground bg-layer shadow-inner border border-divider/50' : 'text-foreground/80 hover:text-foreground hover:bg-layer' }}">
          <i class="fas fa-ticket-alt mr-3 w-5 opacity-70"></i> Tickets
         
        </a>
      </nav>
    </div>

    {{-- User card --}}
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

  {{-- ===== Main ===== --}}
  <main class="flex-1 px-8 py-6 overflow-y-auto bg-surface">
    {{-- ===== HEADER (matched to Dashboard header) ===== --}}
    <div class="w-full flex justify-between items-start md:items-center mb-6">
      <div>
        <h1 class="text-2xl font-bold">Support Tickets</h1>
        <p class="text-sm text-foreground/70 mt-1">
          View and track user-submitted tickets. Total in system:
          <span class="font-semibold">{{ $total }}</span>
        </p>
      </div>

      {{-- Notifications dropdown (same UX as Dashboard) --}}
      <div class="flex items-center gap-3">
        <div class="text-right text-sm text-foreground/70 hidden sm:block">
          <div>Signed in as <span class="font-semibold">{{ $adminName ?? 'Admin' }}</span></div>
          @if(!empty($adminEmail))
            <div class="opacity-70">{{ $adminEmail }}</div>
          @endif
        </div>

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
    </div>

    {{-- KPI cards --}}
    <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <div class="bg-card border border-divider/40 rounded-xl p-4">
        <div class="text-sm text-foreground/60">Total</div>
        <div class="text-2xl font-bold mt-1">{{ $total }}</div>
      </div>
      <div class="bg-card border border-divider/40 rounded-xl p-4">
        <div class="text-sm text-foreground/60">Open</div>
        <div class="text-2xl font-bold mt-1">{{ $open }}</div>
      </div>
      <div class="bg-card border border-divider/40 rounded-xl p-4">
        <div class="text-sm text-foreground/60">Pending</div>
        <div class="text-2xl font-bold mt-1">{{ $pending }}</div>
      </div>
      <div class="bg-card border border-divider/40 rounded-xl p-4">
        <div class="text-sm text-foreground/60">Closed</div>
        <div class="text-2xl font-bold mt-1">{{ $closed }}</div>
      </div>
    </section>

    {{-- Filters --}}
    <form method="GET" class="bg-card border border-divider/40 rounded-xl p-4 mt-6">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
        <div>
          <label class="text-xs text-foreground/60">Search</label>
          <input type="text" name="q" value="{{ $q }}"
                 class="w-full mt-1 px-3 py-2 rounded-lg bg-layer border border-divider/40"
                 placeholder="Subject, name, email…">
        </div>
        <div>
          <label class="text-xs text-foreground/60">Status</label>
          <select name="status" class="w-full mt-1 px-3 py-2 rounded-lg bg-layer border border-divider/40">
            <option value="">Any</option>
            <option value="open"    @selected($fStatus==='open')>Open</option>
            <option value="pending" @selected($fStatus==='pending')>Pending</option>
            <option value="closed"  @selected($fStatus==='closed')>Closed</option>
          </select>
        </div>
        <div>
          <label class="text-xs text-foreground/60">Priority</label>
          <select name="priority" class="w-full mt-1 px-3 py-2 rounded-lg bg-layer border border-divider/40">
            <option value="">Any</option>
            <option value="low"     @selected($fPriority==='low')>Low</option>
            <option value="normal"  @selected($fPriority==='normal')>Normal</option>
            <option value="high"    @selected($fPriority==='high')>High</option>
          </select>
        </div>
        <div class="flex items-end gap-2">
          <button class="w-full px-4 py-2 rounded-lg bg-primary text-white hover:opacity-90">Apply</button>
          <a href="{{ url()->current() }}"
             class="w-full px-4 py-2 text-center rounded-lg bg-layer border border-divider/40 hover:bg-layer/70">Clear</a>
        </div>
      </div>
    </form>

    {{-- Tickets Table --}}
    <section class="mt-6 bg-card border border-divider/40 rounded-xl overflow-hidden">
      <div class="overflow-x-auto custom-scrollbar">
        <table class="min-w-full text-sm">
          <thead class="bg-layer">
          <tr class="text-left">
            <th class="px-4 py-3 border-b border-divider/40">ID</th>
            <th class="px-4 py-3 border-b border-divider/40">Subject</th>
            <th class="px-4 py-3 border-b border-divider/40">From</th>
            <th class="px-4 py-3 border-b border-divider/40">Linked</th>
            <th class="px-4 py-3 border-b border-divider/40">Status</th>
            <th class="px-4 py-3 border-b border-divider/40">Priority</th>
            <th class="px-4 py-3 border-b border-divider/40">Attach</th>
            <th class="px-4 py-3 border-b border-divider/40">Created</th>
            <th class="px-4 py-3 border-b border-divider/40 text-right">Action</th>
          </tr>
          </thead>
          <tbody>
          @forelse ($tickets as $t)
            @php
              $attachUrl = $t->attachment_path ? asset('storage/'.$t->attachment_path) : null;

              $payload = [
                'id'              => $t->id,
                'subject'         => $t->subject,
                'name'            => $t->name,
                'email'           => $t->email,
                'status'          => $t->status,
                'priority'        => $t->priority,
                'client_id'       => $t->client_id,
                'coach_id'        => $t->coach_id,
                'message'         => $t->message,
                'created_at'      => optional($t->created_at)->toDateTimeString(),
                'attachment_url'  => $attachUrl,
                'attachment_name' => $t->attachment_name,
                'attachment_mime' => $t->attachment_mime,
                'attachment_size' => $t->attachment_size,
              ];

              $statusColors = [
                'open'    => 'bg-emerald-600/15 text-emerald-500 border-emerald-600/30',
                'pending' => 'bg-amber-600/15 text-amber-500 border-amber-600/30',
                'closed'  => 'bg-zinc-600/15 text-zinc-400 border-zinc-600/30',
              ];
              $prioColors = [
                'low'    => 'bg-sky-600/15 text-sky-500 border-sky-600/30',
                'normal' => 'bg-indigo-600/15 text-indigo-500 border-indigo-600/30',
                'high'   => 'bg-rose-600/15 text-rose-500 border-rose-600/30',
              ];
              $badgeS = $statusColors[strtolower($t->status)] ?? 'bg-layer text-foreground/70 border-divider/40';
              $badgeP = $prioColors[strtolower($t->priority)] ?? 'bg-layer text-foreground/70 border-divider/40';
            @endphp

            <tr class="even:bg-layer/40">
              <td class="px-4 py-3 border-b border-divider/40 font-mono">#{{ $t->id }}</td>

              <td class="px-4 py-3 border-b border-divider/40">
                <div class="font-semibold">{{ $t->subject }}</div>
                <div class="text-foreground/60 text-xs line-clamp-1">
                  {{ Str::limit(strip_tags($t->message), 80) }}
                </div>
              </td>

              <td class="px-4 py-3 border-b border-divider/40">
                <div class="font-medium">{{ $t->name }}</div>
                <div class="text-foreground/60 text-xs">{{ $t->email }}</div>
              </td>

              <td class="px-4 py-3 border-b border-divider/40 text-xs">
                @if($t->client_id)
                  <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full border border-divider/40">
                    <i class="fa-regular fa-user"></i> Client #{{ $t->client_id }}
                  </span>
                @endif
                @if($t->coach_id)
                  <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full border border-divider/40 ml-1">
                    <i class="fa-solid fa-user-tie"></i> Coach #{{ $t->coach_id }}
                  </span>
                @endif
                @if(!$t->client_id && !$t->coach_id)
                  <span class="text-foreground/60">—</span>
                @endif
              </td>

              <td class="px-4 py-3 border-b border-divider/40">
                <span class="inline-flex items-center px-2 py-0.5 rounded-full border {{ $badgeS }} capitalize">
                  {{ strtolower($t->status) }}
                </span>
              </td>

              <td class="px-4 py-3 border-b border-divider/40">
                <span class="inline-flex items-center px-2 py-0.5 rounded-full border {{ $badgeP }} capitalize">
                  {{ strtolower($t->priority) }}
                </span>
              </td>

              <td class="px-4 py-3 border-b border-divider/40">
                @if($attachUrl)
                  <a href="{{ $attachUrl }}" target="_blank" class="inline-flex items-center gap-1 text-sm hover:underline">
                    <i class="fa-regular fa-file"></i>
                    {{ $t->attachment_name ?? 'file' }}
                  </a>
                @else
                  <span class="text-foreground/60 text-sm">—</span>
                @endif
              </td>

              <td class="px-4 py-3 border-b border-divider/40 text-sm whitespace-nowrap">
                {{ optional($t->created_at)->format('Y-m-d H:i') }}
              </td>

             <td class="px-4 py-3 border-b border-divider/40 text-right">
    <button
      type="button"
      class="px-3 py-1.5 rounded-lg border border-divider/40 hover:bg-layer text-sm mr-2"
      data-payload='@json($payload)'
      onclick="openViewModal(this)">
      View
    </button>

   <a href="https://mail.google.com/mail/?view=cm&to={{ $t->email }}&su={{ urlencode('Re: '.$t->subject) }}"
   target="_blank"
   class="inline-block px-3 py-1.5 rounded-lg border border-divider/40 hover:bg-layer text-sm bg-primary text-white">
   Message
</a>

</td>

            </tr>
          @empty
            <tr>
              <td colspan="9" class="px-4 py-8 text-center text-foreground/60">
                No tickets found.
              </td>
            </tr>
          @endforelse
          </tbody>
        </table>
      </div>

      {{-- Pagination (keeps filters) --}}
      <div class="px-4 py-3 border-t border-divider/40">
        {{ $tickets->appends(['q'=>$q,'status'=>$fStatus,'priority'=>$fPriority])->onEachSide(1)->links() }}
      </div>
    </section>
  </main>
</div>

{{-- ===== View Modal ===== --}}
<div id="viewTicketModal" class="hidden fixed inset-0 z-50">
  <div class="absolute inset-0 bg-black/60" onclick="closeViewModal()"></div>

  <div class="absolute inset-0 flex items-start md:items-center justify-center overflow-y-auto p-4">
    <div class="w-full max-w-2xl bg-card text-foreground rounded-2xl shadow-2xl border border-divider/40">
      <div class="flex items-center justify-between px-6 py-4 border-b border-divider/40">
        <h3 id="v-subject" class="text-lg font-semibold">Ticket</h3>
        <button class="text-2xl leading-none opacity-70 hover:opacity-100 px-2" onclick="closeViewModal()">&times;</button>
      </div>

      <div class="px-6 pt-6 pb-2 space-y-4 text-sm">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <div class="text-foreground/60">From</div>
            <div class="font-medium" id="v-name">—</div>
            <div class="text-xs text-foreground/60" id="v-email">—</div>
          </div>
          <div>
            <div class="text-foreground/60">Meta</div>
            <div class="text-xs">Status: <span class="font-semibold" id="v-status">—</span></div>
            <div class="text-xs">Priority: <span class="font-semibold" id="v-priority">—</span></div>
            <div class="text-xs">Created: <span class="font-semibold" id="v-created">—</span></div>
          </div>
        </div>

        <div>
          <div class="text-foreground/60 mb-1">Message</div>
          <div id="v-message" class="whitespace-pre-wrap leading-relaxed bg-layer border border-divider/40 rounded-lg p-3"></div>
        </div>

        <div id="v-attach-wrap" class="hidden">
          <div class="text-foreground/60 mb-1">Attachment</div>
          <a id="v-attach-link" href="#" target="_blank"
             class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-divider/40 hover:bg-layer">
            <i class="fa-regular fa-file"></i>
            <span id="v-attach-name" class="font-medium">file</span>
            <span id="v-attach-meta" class="text-xs text-foreground/60"></span>
          </a>
        </div>
      </div>

      <div class="px-6 py-4 border-t border-divider/40 flex justify-end">
        <button class="px-4 py-2 rounded-lg bg-layer border border-divider/40 hover:bg-layer/70" onclick="closeViewModal()">Close</button>
      </div>
    </div>
  </div>
</div>

{{-- ===== Scripts ===== --}}
<script>
  function getCsrf() {
    const el = document.querySelector('meta[name="csrf-token"]');
    return el ? el.getAttribute('content') : '';
  }

  // Notifications (same behavior as Dashboard)
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

  function openViewModal(btn){
    try {
      const data = JSON.parse(btn.dataset.payload || '{}');

      document.getElementById('v-subject').textContent = data.subject || 'Ticket';
      document.getElementById('v-name').textContent    = data.name || '—';
      document.getElementById('v-email').textContent   = data.email || '—';

      document.getElementById('v-status').textContent   = cap(data.status || '—');
      document.getElementById('v-priority').textContent = cap(data.priority || '—');
      document.getElementById('v-created').textContent  = data.created_at || '—';

      document.getElementById('v-message').textContent  = data.message || '—';

      const wrap = document.getElementById('v-attach-wrap');
      const link = document.getElementById('v-attach-link');
      const name = document.getElementById('v-attach-name');
      const meta = document.getElementById('v-attach-meta');

      if (data.attachment_url) {
        wrap.classList.remove('hidden');
        link.href = data.attachment_url;
        name.textContent = data.attachment_name || 'file';
        const size = fmtBytes(data.attachment_size);
        meta.textContent = [data.attachment_mime, size].filter(Boolean).join(' • ');
      } else {
        wrap.classList.add('hidden');
        link.removeAttribute('href');
        name.textContent = '';
        meta.textContent = '';
      }

      document.getElementById('viewTicketModal').classList.remove('hidden');
    } catch (e) {
      alert('Failed to open ticket.');
      console.error(e);
    }
  }

  function closeViewModal(){
    document.getElementById('viewTicketModal').classList.add('hidden');
  }

  function cap(s){ s = (s ?? '').toString(); return s.charAt(0).toUpperCase() + s.slice(1); }
  function fmtBytes(bytes){
    if (!bytes || isNaN(bytes)) return '';
    const sizes = ['B','KB','MB','GB','TB'];
    const i = Math.floor(Math.log(bytes)/Math.log(1024));
    return (bytes/Math.pow(1024,i)).toFixed(1)+' '+sizes[i];
  }
</script>
</body>
</html>
