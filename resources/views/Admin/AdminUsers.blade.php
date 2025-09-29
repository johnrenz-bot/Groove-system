{{-- resources/views/Admin/users.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Groove · Admin · Users</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">

  {{-- Icons / Fonts --}}
  <link rel="icon" href="/image/white.png" type="image/png" />
  <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
  <script src="https://kit.fontawesome.com/43f9926b04.js" crossorigin="anonymous"></script>
  <link rel="icon" href="/image/bg/LOG.png" type="image/png" sizes="512x512">

  {{-- Tailwind + Vite --}}
  @vite(['resources/css/app.css', 'resources/js/app.js'])

  <style>
    body{font-family:"Instrument Sans",ui-sans-serif,system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial}
    [hidden]{display:none!important}
    .modal-open{overflow:hidden}

    /* ✅ Fixed-height modals (coach and client) */
    .coach-modal-body{
      max-height:75vh;
      overflow-y:auto;
      -webkit-overflow-scrolling:touch;
    }
    .client-modal-body{
      max-height:75vh;
      overflow-y:auto;
      -webkit-overflow-scrolling:touch;
    }

    /* 📸 Fixed-height, scrollable media previews */
    .portfolio-fixed{
      height:420px;
      overflow-y:auto;
      -webkit-overflow-scrolling:touch;
    }
    .portfolio-fixed img{
      width:100%;
      height:auto;
      object-fit:contain;
      display:block;
      user-select:none;
    }
    .media-fixed-sm{
      height:420px;
      overflow-y:auto;
      -webkit-overflow-scrolling:touch;
    }
    .media-fixed-sm img{
      width:100%;
      height:auto;
      object-fit:contain;
      display:block;
    }


    
    /* Optional subtle scrollbars */
    .portfolio-fixed::-webkit-scrollbar,
    .media-fixed-sm::-webkit-scrollbar{width:8px;height:8px}
    .portfolio-fixed::-webkit-scrollbar-thumb,
    .media-fixed-sm::-webkit-scrollbar-thumb{background:rgba(120,120,120,.35);border-radius:8px}
    .portfolio-fixed::-webkit-scrollbar-track,
    .media-fixed-sm::-webkit-scrollbar-track{background:transparent}
  </style>
</head>

@php
use Illuminate\Support\Str;

$activeTab = request('tab', 'choreo') === 'client' ? 'client' : 'choreo';
$selectedTalent = strtolower(trim(request('talent', '')));
$searchUserId   = trim(request('uid', ''));

$statusColor = function ($status) {
  $s = strtolower((string)$status);
  return match ($s) {
    'online' => 'text-[color:#16a34a]',
    'busy'   => 'text-[color:#dc2626]',
    'away'   => 'text-[color:#ca8a04]',
    'offline'=> 'text-foreground/50',
    default  => 'text-foreground/70',
  };
};
$pillStatus = function ($status) {
  return strtolower((string)$status) === 'active' ? 'bg-[color:#16a34a]' : 'bg-[color:#dc2626]';
};
$initials = function ($first, $last) {
  $fi = $first ? Str::upper(Str::substr($first, 0, 1)) : '';
  $li = $last ? Str::upper(Str::substr($last, 0, 1)) : '';
  return $fi.$li;
};
$fullName = function ($first, $last) {
  return trim(($first ?? '').' '.($last ?? ''));
};
$filter = function ($item, $idKey) use ($selectedTalent, $searchUserId) {
  $ok = true;
  if ($selectedTalent !== '') {
    $tal = strtolower((string)($item->talent ?? $item->talents ?? ''));
    $ok = $ok && str_contains($tal, $selectedTalent);
  }
  if ($searchUserId !== '') {
    $ok = $ok && str_contains((string)($item->{$idKey} ?? ''), $searchUserId);
  }
  return $ok;
};
$choreos = collect($coaches ?? [])->filter(fn($c) => $filter($c, 'coach_id'));
$clients = collect($clients ?? [])->filter(fn($c) => $filter($c, 'client_id'));
@endphp

<body class="min-h-screen antialiased theme-{{ $appTheme }} bg-surface text-foreground">
<div class="flex min-h-screen">

  {{-- SIDEBAR --}}
  <header class="w-64 h-screen bg-card border-r border-divider/40 text-foreground flex flex-col justify-between shadow-sm">
    <div class="flex flex-col p-6 space-y-8">
      <div class="flex items-center gap-3">
        <img src="/image/bg/LOG.png" alt="Logo" class="h-12 w-auto object-contain select-none" />
      </div>

      <nav class="flex flex-col space-y-1 text-sm font-medium p-3">
        <a href="/admin/dashboard" class="flex items-center px-4 py-2 rounded-lg text-foreground/80 hover:text-foreground hover:bg-layer">
          <i class="fas fa-home mr-3 w-5 opacity-70"></i> Dashboard
        </a>
        <a href="{{ route('Adminusers') }}" class="flex items-center px-4 py-2 rounded-lg text-foreground bg-layer shadow-inner border border-divider/50">
          <i class="fas fa-users mr-3 w-5 opacity-70"></i> Users
        </a>
        <a href="{{ route('admin.control') }}" class="flex items-center px-4 py-2 rounded-lg text-foreground/80 hover:text-foreground hover:bg-layer">
          <i class="fas fa-layer-group mr-3 w-5 opacity-70"></i> Control
        </a>
        <a href="{{ route('Admintransaction') }}" class="flex items-center px-4 py-2 rounded-lg text-foreground/80 hover:text-foreground hover:bg-layer">
          <i class="fas fa-user-friends mr-3 w-5 opacity-70"></i> Transactions
        </a>
        <a href="{{ route('admin.Admintickets') }}" class="flex items-center px-4 py-2 rounded-lg {{ request()->routeIs('admin.Admintickets') ? 'text-foreground bg-layer shadow-inner border border-divider/50' : 'text-foreground/80 hover:text-foreground hover:bg-layer' }}">
          <i class="fas fa-ticket-alt mr-3 w-5 opacity-70"></i> Tickets
        </a>
      </nav>
    </div>

    <div class="p-6 border-t border-divider/40 space-y-4">
      <div class="w-full">
        <button class="flex items-center gap-3 px-4 py-2 bg-layer rounded-xl w-full border border-divider/40">
          <div class="flex-1 text-left">
            <p class="text-sm font-semibold truncate">{{ Auth::guard('admin')->user()->name }}</p>
            <p class="text-xs text-foreground/60 truncate">{{ Auth::guard('admin')->user()->email }}</p>
          </div>
        </button>
        <div class="mt-2 bg-card border border-divider/40 rounded-xl overflow-hidden shadow-lg">
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

  {{-- MAIN --}}
  <main class="flex-1 px-8 py-6 overflow-y-auto bg-surface">

    <div class="w-full flex justify-between items-end mb-6">
      <div class="flex flex-col space-y-2 w-full sm:w-1/3 md:w-1/4">
        <div class="flex bg-layer rounded-md p-1 border border-divider/40">
          <span class="flex-1 text-center py-2 px-4 rounded-md bg-card text-foreground text-sm font-medium border border-divider/40"> MEMBERS </span>
        </div>

        {{-- Tabs (server-side via query) --}}
        <div class="flex rounded-md p-1 border border-transparent">
          <a href="{{ request()->fullUrlWithQuery(['tab' => 'choreo', 'talent' => $selectedTalent, 'uid' => $searchUserId]) }}"
             class="flex-1 text-center py-2 px-5 rounded-md text-sm font-medium transition {{ $activeTab === 'choreo' ? 'bg-layer text-foreground border border-divider/40' : 'text-foreground/70 hover:bg-layer/80' }}">
            CHOREO
          </a>
          <a href="{{ request()->fullUrlWithQuery(['tab' => 'client', 'talent' => $selectedTalent, 'uid' => $searchUserId]) }}"
             class="flex-1 text-center py-2 px-4 rounded-md text-sm font-medium transition {{ $activeTab === 'client' ? 'bg-layer text-foreground border border-divider/40' : 'text-foreground/70 hover:bg-layer/80' }}">
            CLIENTS
          </a>
        </div>
      </div>

      {{-- Filters (server-side via GET) --}}
      <form method="GET" class="w-full sm:w-1/2 flex flex-col sm:flex-row gap-4 items-stretch sm:items-center">
        <input type="hidden" name="tab" value="{{ $activeTab }}">
        <div class="flex-1">
          <select name="talent" class="w-full px-3 py-2 bg-card text-foreground text-sm rounded-md border border-divider/40 focus:outline-none focus:ring-2" style="--tw-ring-color: var(--color-primary)">
            <option value="" {{ $selectedTalent==='' ? 'selected' : '' }}>ALL TALENTS</option>
            <option value="dance"   {{ $selectedTalent==='dance' ? 'selected' : '' }}>DANCE</option>
            <option value="sing"    {{ $selectedTalent==='sing' ? 'selected' : '' }}>SING</option>
            <option value="acting"  {{ $selectedTalent==='acting' ? 'selected' : '' }}>ACTING</option>
            <option value="theater" {{ $selectedTalent==='theater' ? 'selected' : '' }}>THEATER</option>
          </select>
        </div>
        <div class="flex-1">
          <input type="text" name="uid" value="{{ $searchUserId }}" placeholder="Search by User ID" maxlength="10" inputmode="numeric" pattern="[0-9]*"
                 class="w-full px-3 py-2 bg-card text-foreground text-sm rounded-md border border-divider/40 focus:outline-none focus:ring-2" style="--tw-ring-color: var(--color-primary)">
        </div>
        <button class="px-4 py-2 bg-layer rounded-md border border-divider/40 text-sm font-medium">Apply</button>
        <a href="{{ request()->url().'?tab='.$activeTab }}" class="px-4 py-2 bg-card rounded-md border border-divider/40 text-sm font-medium">Reset</a>
      </form>
    </div>

    <div class="py-5 px-4 sm:px-6 bg-card rounded-lg shadow-sm border border-divider/40 w-full">
      <h2 class="text-xl font-semibold mb-4">User List</h2>

      <div class="w-full flex items-center text-foreground/60 text-xs sm:text-sm uppercase border-b border-divider/40 pb-2">
        <div class="w-[7%] text-center">ID</div>
        <div class="w-[8%] text-center">Photo</div>
        <div class="w-[15%] text-left pl-4">Name</div>
        <div class="w-[15%] text-left">Role</div>
        <div class="w-[15%] text-left">Talent</div>
        <div class="w-[15%] text-left">Contact</div>
        <div class="w-[20%] text-left">Email</div>
        <div class="w-[10%] text-center">Status</div>
        <div class="w-[10%] text-center">Verification</div>
        <div class="w-[5%] text-center">View</div>
      </div>

      {{-- CHOREOS --}}
      @if($activeTab === 'choreo')
        @forelse($choreos as $coach)
          @php $modalId = 'coach-modal-'.$coach->coach_id; @endphp

          <div class="w-full flex items-center text-sm py-3 border-b border-divider/40 hover:bg-layer transition">
            <div class="w-[7%] text-center">{{ $coach->coach_id }}</div>
            <div class="w-[8%] flex justify-center">
              @if(!empty($coach->photo))
                <img src="{{ '/storage/'.$coach->photo }}" alt="{{ $coach->firstname }}" class="h-8 w-8 rounded-full object-cover border border-divider/40">
              @else
                <div class="h-8 w-8 rounded-full bg-layer text-foreground flex items-center justify-center text-xs font-bold uppercase border border-divider/40">
                  {{ $initials($coach->firstname ?? '', $coach->lastname ?? '') }}
                </div>
              @endif
            </div>
            <div class="w-[15%] text-left pl-4">{{ $fullName($coach->firstname ?? '', $coach->lastname ?? '') }}</div>
            <div class="w-[15%] text-left">{{ $coach->role ?? '' }}</div>
            <div class="w-[15%] text-left">{{ $coach->talents ?? '' }}</div>
            <div class="w-[15%] text-left">{{ $coach->contact ?? '' }}</div>
            <div class="w-[20%] text-left">{{ $coach->email ?? '' }}</div>
            <div class="w-[10%] text-center">
              <span class="font-medium {{ $statusColor($coach->status ?? '') }}">{{ Str::upper($coach->status ?? '') }}</span>
            </div>
            <div class="w-[10%] text-center">
              <form action="{{ url('/admin/coach/verify/'.$coach->coach_id) }}" method="POST">
                @csrf
                <button
                  type="submit"
                  {{ $coach->account_verified ? 'disabled' : '' }}
                  class="font-semibold py-1.5 px-3 rounded-lg border transition
                         {{ $coach->account_verified
                            ? 'bg-layer border-divider/40 opacity-60 cursor-not-allowed'
                            : 'bg-card border-divider/40 hover:bg-layer' }}">
                  {{ $coach->account_verified ? 'Verified' : 'Verify' }}
                </button>
              </form>
            </div>

            <div class="w-[5%] flex justify-center">
              <button class="opacity-80 hover:opacity-100" data-open="{{ $modalId }}" style="color: var(--color-primary)">
                <i class="fas fa-eye text-sm"></i>
              </button>
            </div>
          </div>

          {{-- ✅ Redesigned Coach Modal (fixed height like client) --}}
          <div id="{{ $modalId }}" class="fixed inset-0 bg-black/40 backdrop-blur-sm items-center justify-center z-50 p-4 hidden">
            <div class="bg-card border border-divider/40 rounded-2xl w-full max-w-3xl overflow-hidden shadow-2xl relative">

              {{-- Close --}}
              <button class="absolute top-4 right-4 z-10 p-2 rounded-full opacity-80 hover:opacity-100" data-close="{{ $modalId }}" aria-label="Close">
                <i class="fas fa-times text-lg"></i>
              </button>

              {{-- Header --}}
              <div class="p-6 pb-4 border-b border-divider/40 relative">
                <div class="flex flex-col md:flex-row items-center md:items-start gap-5">
                  <div class="flex-shrink-0 relative">
                    <div class="h-28 w-28 rounded-full border border-divider/40 overflow-hidden flex items-center justify-center bg-layer text-foreground text-3xl font-bold uppercase shadow-sm">
                      @if(!empty($coach->photo))
                        <img src="{{ asset('storage/'.$coach->photo) }}" alt="Profile Photo" class="h-full w-full object-cover"/>
                      @else
                        {{ $initials($coach->firstname ?? '', $coach->lastname ?? '') }}
                      @endif
                    </div>
                    <span class="absolute bottom-2 right-2 w-4 h-4 rounded-full border-2 border-card {{ $pillStatus($coach->status ?? '') }}"></span>
                  </div>

                  <div class="flex-1 text-center md:text-left w-full">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                      <div>
                        <h2 class="text-2xl font-semibold">{{ $fullName($coach->firstname ?? '', $coach->lastname ?? '') }}</h2>
                        <div class="mt-2 inline-flex items-center gap-2 bg-layer px-3 py-1 rounded-full text-sm border border-divider/40">
                          <i class="fas fa-id-badge opacity-70 text-xs"></i>
                          <span class="uppercase tracking-wide">{{ $coach->role ?? '' }}</span>
                        </div>
                      </div>

                      {{-- Verify --}}
                      <div class="text-sm">
                        <form action="{{ url('/admin/coach/verify/'.$coach->coach_id) }}" method="POST">
                          @csrf
                          <button
                            type="submit"
                            {{ $coach->account_verified ? 'disabled' : '' }}
                            class="font-semibold py-1.5 px-3 rounded-lg border transition
                                   {{ $coach->account_verified
                                      ? 'bg-layer border-divider/40 opacity-60 cursor-not-allowed'
                                      : 'bg-card border-divider/40 hover:bg-layer' }}">
                            {{ $coach->account_verified ? 'Verified' : 'Verify' }}
                          </button>
                        </form>
                      </div>
                    </div>

                    {{-- Status pill --}}
                    <div class="flex flex-wrap gap-2 mt-3">
                      <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full border
                                   {{ $pillStatus($coach->status ?? '') === 'bg-[color:#16a34a]' ? 'border-[color:#16a34a] text-[color:#16a34a]' : 'border-[color:#dc2626] text-[color:#dc2626]' }}">
                        <i class="fas fa-circle text-[10px]"></i> {{ ucfirst($coach->status ?? 'offline') }}
                      </span>
                    </div>
                  </div>
                </div>
              </div>

              {{-- Body (fixed-height + scroll) --}}
              <div class="p-6 coach-modal-body">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 text-sm">
                  {{-- LEFT: Info --}}
                  <div class="space-y-4">
                    <div class="bg-layer p-4 rounded-xl border border-divider/40">
                      <h3 class="font-semibold mb-3 flex items-center gap-2">
                        <i class="fas fa-info-circle opacity-70"></i> <span>Info</span>
                      </h3>
                      <div class="space-y-2">
                        <p class="flex justify-between gap-3"><span class="opacity-70">Talent(s):</span><span class="font-medium text-right">{{ $coach->talents ?? '—' }}</span></p>
                        <p class="flex justify-between gap-3"><span class="opacity-70">Genre(s):</span><span class="text-right">{{ $coach->genres ?? '—' }}</span></p>
                        <p class="flex justify-between gap-3"><span class="opacity-70">Contact:</span><span class="text-right">{{ $coach->contact ?? '—' }}</span></p>
                        <p class="flex justify-between gap-3"><span class="opacity-70">Email:</span><span class="text-right break-all"><a href="mailto:{{ $coach->email ?? '' }}" class="hover:underline">{{ $coach->email ?? '—' }}</a></span></p>
                      </div>
                    </div>

                    @if(!empty($coach->bio))
                      <div class="bg-layer p-4 rounded-xl border border-divider/40">
                        <h3 class="font-semibold mb-3 flex items-center gap-2">
                          <i class="fas fa-user opacity-70"></i> <span>Bio</span>
                        </h3>
                        <p class="leading-relaxed">{{ $coach->bio }}</p>
                      </div>
                    @endif
                  </div>

                  {{-- RIGHT: Portfolio & Verification --}}
                  <div class="space-y-4">
                    @php
                      $portfolioUrl = !empty($coach->portfolio_path) ? asset('storage/'.$coach->portfolio_path) : null;
                      $validUrl  = !empty($coach->valid_id_path)  ? asset('storage/'.$coach->valid_id_path)  : null;
                      $selfieUrl = !empty($coach->id_selfie_path) ? asset('storage/'.$coach->id_selfie_path) : null;
                      $isImg = function($u){ return $u && in_array(strtolower(pathinfo($u, PATHINFO_EXTENSION)), ['jpg','jpeg','png','webp','gif']); };
                    @endphp

                    {{-- Portfolio --}}
                    @if($portfolioUrl)
                      <div class="bg-layer p-4 rounded-xl border border-divider/40">
                        <div class="flex items-center justify-between mb-2">
                          <h3 class="font-semibold flex items-center gap-2">
                            <i class="fas fa-briefcase opacity-70"></i> <span>Portfolio</span>
                          </h3>
                          <div class="flex gap-2">
                            <a href="{{ $portfolioUrl }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 bg-card border border-divider/40 hover:bg-layer px-3 py-1.5 rounded-lg text-sm font-medium">
                              <i class="fas fa-external-link-alt"></i> Open
                            </a>
                            <a href="{{ $portfolioUrl }}" download class="inline-flex items-center gap-2 bg-card border border-divider/40 hover:bg-layer px-3 py-1.5 rounded-lg text-sm font-medium">
                              <i class="fas fa-download"></i> Download
                            </a>
                          </div>
                        </div>
                        @if($isImg($portfolioUrl))
                          <div class="rounded-lg border border-divider/40 bg-card portfolio-fixed">
                            <img src="{{ $portfolioUrl }}" alt="Portfolio" loading="lazy" />
                          </div>
                        @else
                          <div class="rounded-lg overflow-hidden border border-divider/40 bg-card">
                            <iframe src="{{ $portfolioUrl }}" class="w-full h-[420px]" loading="lazy" referrerpolicy="no-referrer"></iframe>
                          </div>
                        @endif
                      </div>
                    @endif

                  {{-- Verification Files --}}
@if($validUrl || $selfieUrl)
  <div class="bg-layer p-4 rounded-xl border border-divider/40">
    <div class="flex items-center justify-between mb-2">
      <h3 class="font-semibold flex items-center gap-2">
        <i class="fas fa-shield-check opacity-70"></i> <span>Verification Files</span>
      </h3>
    </div>

    <div class="grid md:grid-cols-2 gap-4 min-w-0">
      {{-- Valid ID --}}
      @if($validUrl)
        <div class="bg-card border border-divider/40 rounded-lg p-3 min-w-0">
          <div class="flex items-center justify-between mb-2">
            <p class="text-sm font-medium">Valid ID</p>
            <div class="flex gap-2">
              <a href="{{ $validUrl }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 border border-divider/40 px-3 py-1.5 rounded-md text-xs hover:bg-layer">
                <i class="fas fa-external-link-alt"></i> Open
              </a>
              <a href="{{ $validUrl }}" download class="inline-flex items-center gap-2 border border-divider/40 px-3 py-1.5 rounded-md text-xs hover:bg-layer">
                <i class="fas fa-download"></i> Download
              </a>
            </div>
          </div>

          @if($isImg($validUrl))
            <div class="rounded-md border border-divider/40 bg-card preview-box">
              <img src="{{ $validUrl }}" alt="Valid ID" loading="lazy"
                   class="media-fluid rounded-md cursor-zoom-in" data-lightbox="true" />
            </div>
          @else
            <div class="rounded-md overflow-hidden border border-divider/40 bg-card preview-box">
              <iframe src="{{ $validUrl }}" class="w-full h-full" loading="lazy" referrerpolicy="no-referrer"></iframe>
            </div>
          @endif
        </div>
      @endif

      {{-- ID Selfie --}}
      @if($selfieUrl)
        <div class="bg-card border border-divider/40 rounded-lg p-3 min-w-0">
          <div class="flex items-center justify-between mb-2">
            <p class="text-sm font-medium">ID Selfie</p>
            <div class="flex gap-2">
              <a href="{{ $selfieUrl }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 border border-divider/40 px-3 py-1.5 rounded-md text-xs hover:bg-layer">
                <i class="fas fa-external-link-alt"></i> Open
              </a>
              <a href="{{ $selfieUrl }}" download class="inline-flex items-center gap-2 border border-divider/40 px-3 py-1.5 rounded-md text-xs hover:bg-layer">
                <i class="fas fa-download"></i> Download
              </a>
            </div>
          </div>

          @if($isImg($selfieUrl))
            <div class="rounded-md border border-divider/40 bg-card preview-box">
              <img src="{{ $selfieUrl }}" alt="ID Selfie" loading="lazy"
                   class="media-fluid rounded-md cursor-zoom-in" data-lightbox="true" />
            </div>
          @else
            <div class="rounded-md overflow-hidden border border-divider/40 bg-card preview-box">
              <iframe src="{{ $selfieUrl }}" class="w-full h-full" loading="lazy" referrerpolicy="no-referrer"></iframe>
            </div>
          @endif
        </div>
      @endif
    </div>
  </div>
@endif


                  </div>
                </div>
              </div>
            </div>
          </div>
        @empty
          <div class="w-full py-8 text-center text-foreground/60"> No choreographers found matching your criteria </div>
        @endforelse
      @endif

      {{-- CLIENTS --}}
      @if($activeTab === 'client')
        @forelse($clients as $client)
          @php $modalId = 'client-modal-'.$client->client_id; @endphp

          <div class="w-full flex items-center text-sm py-3 border-b border-divider/40 hover:bg-layer transition">
            <div class="w-[7%] text-center">{{ $client->client_id }}</div>
            <div class="w-[8%] flex justify-center">
              @if(!empty($client->photo))
                <img src="{{ '/storage/'.$client->photo }}" alt="{{ $client->firstname }}" class="h-8 w-8 rounded-full object-cover border border-divider/40">
              @else
                <div class="h-8 w-8 rounded-full bg-layer text-foreground flex items-center justify-center text-xs font-bold uppercase border border-divider/40">
                  {{ $initials($client->firstname ?? '', $client->lastname ?? '') }}
                </div>
              @endif
            </div>
            <div class="w-[15%] text-left pl-4">{{ $fullName($client->firstname ?? '', $client->lastname ?? '') }}</div>
            <div class="w-[15%] text-left">{{ $client->role ?? '' }}</div>
            <div class="w-[15%] text-left">{{ $client->talent ?? '' }}</div>
            <div class="w-[15%] text-left">{{ $client->contact ?? '' }}</div>
            <div class="w-[20%] text-left">{{ $client->email ?? '' }}</div>
            <div class="w-[10%] text-center">
              <span class="font-medium {{ $statusColor($client->status ?? '') }}">{{ Str::upper($client->status ?? '') }}</span>
            </div>
            <div class="w-[10%] text-center">
              <form action="{{ route('admin.client.verify', $client) }}" method="POST">
                @csrf
                <button
                  type="submit"
                  {{ $client->account_verified ? 'disabled' : '' }}
                  class="font-semibold py-1.5 px-3 rounded-lg border transition
                         {{ $client->account_verified
                            ? 'bg-layer border-divider/40 opacity-60 cursor-not-allowed'
                            : 'bg-card border-divider/40 hover:bg-layer' }}">
                  {{ $client->account_verified ? 'Verified' : 'Verify' }}
                </button>
              </form>
            </div>

            <div class="w-[5%] flex justify-center">
              <button class="opacity-80 hover:opacity-100" data-open="{{ $modalId }}" style="color: var(--color-primary)">
                <i class="fas fa-eye text-sm"></i>
              </button>
            </div>
          </div>

          {{-- Client Modal (unchanged visual, fixed height already) --}}
          <div id="{{ $modalId }}" class="fixed inset-0 bg-black/30 backdrop-blur-sm items-center justify-center z-50 p-4 hidden">
            <div class="bg-card border border-divider/40 rounded-2xl w-full max-w-3xl overflow-hidden shadow-2xl relative">
              {{-- Close --}}
              <button class="absolute top-4 right-4 z-10 p-2 rounded-full opacity-80 hover:opacity-100" data-close="{{ $modalId }}">
                <i class="fas fa-times text-lg"></i>
              </button>

              {{-- Header --}}
              <div class="p-6 pb-4 border-b border-divider/40 relative">
                <div class="flex flex-col md:flex-row items-center md:items-start gap-5">
                  <div class="flex-shrink-0 relative">
                    <div class="h-28 w-28 rounded-full border border-divider/40 overflow-hidden flex items-center justify-center bg-layer text-foreground text-3xl font-bold uppercase shadow-sm">
                      @if(!empty($client->photo))
                        <img src="{{ '/storage/'.$client->photo }}" alt="Profile Photo" class="h-full w-full object-cover"/>
                      @else
                        {{ $initials($client->firstname ?? '', $client->lastname ?? '') }}
                      @endif
                    </div>
                    <span class="absolute bottom-2 right-2 w-4 h-4 rounded-full border-2 border-card {{ $pillStatus($client->status ?? '') }}"></span>
                  </div>

                  <div class="flex-1 text-center md:text-left w-full">
                    <div class="flex flex-col gap-3">
                      <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                        <div>
                          <h2 class="text-2xl font-semibold">{{ $fullName($client->firstname ?? '', $client->lastname ?? '') }}</h2>
                          <div class="mt-2 inline-flex items-center gap-2 bg-layer px-3 py-1 rounded-full text-sm border border-divider/40">
                            <i class="fas fa-id-badge opacity-70 text-xs"></i>
                            <span class="uppercase tracking-wide">{{ $client->role ?? '' }}</span>
                          </div>
                        </div>

                        {{-- Admin verify action --}}
                        <div class="text-sm">
                          <form action="{{ route('admin.client.verify', $client) }}" method="POST">
                            @csrf
                            <button
                              type="submit"
                              {{ $client->account_verified ? 'disabled' : '' }}
                              class="font-semibold py-1.5 px-3 rounded-lg border transition
                                     {{ $client->account_verified
                                        ? 'bg-layer border-divider/40 opacity-60 cursor-not-allowed'
                                        : 'bg-card border-divider/40 hover:bg-layer' }}">
                              {{ $client->account_verified ? 'Verified' : 'Verify' }}
                            </button>
                          </form>
                        </div>
                      </div>

                      {{-- Quick status pills --}}
                      <div class="flex flex-wrap gap-2">
                        <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full border
                                     {{ $pillStatus($client->status ?? '') === 'bg-[color:#16a34a]' ? 'border-[color:#16a34a] text-[color:#16a34a]' : 'border-[color:#dc2626] text-[color:#dc2626]' }}">
                          <i class="fas fa-circle text-[10px]"></i> {{ ucfirst($client->status ?? 'offline') }}
                        </span>

                        <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full border
                                     {{ ($client->email_verified ?? false) ? 'border-[color:#16a34a] text-[color:#16a34a]' : 'border-[color:#dc2626] text-[color:#dc2626]' }}">
                          <i class="fas fa-envelope"></i> Email: {{ ($client->email_verified ?? false) ? 'Verified' : 'Unverified' }}
                        </span>

                        <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full border
                                     {{ ($client->account_verified ?? false) ? 'border-[color:#16a34a] text-[color:#16a34a]' : 'border-[color:#dc2626] text-[color:#dc2626]' }}">
                          <i class="fas fa-shield-check"></i> Account: {{ ($client->account_verified ?? false) ? 'Approved' : 'Pending' }}
                        </span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              {{-- Body (fixed-height, scrollable) --}}
              <div class="p-6 client-modal-body">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 text-sm">
                  {{-- LEFT COLUMN --}}
                  <div class="space-y-4">
                    {{-- Basic info --}}
                    <div class="bg-layer p-4 rounded-xl border border-divider/40">
                      <h3 class="font-semibold mb-3 flex items-center gap-2">
                        <i class="fas fa-info-circle opacity-70"></i> <span>Basic Information</span>
                      </h3>
                      <div class="space-y-2">
                        <p class="flex justify-between">
                          <span class="opacity-70">Talent:</span>
                          <span class="font-medium">{{ $client->talent ?? 'N/A' }}</span>
                        </p>
                        @if(!empty($client->genre))
                          <p class="flex justify-between">
                            <span class="opacity-70">Genre:</span>
                            <span>{{ $client->genre }}</span>
                          </p>
                        @endif
                        <p class="flex justify-between">
                          <span class="opacity-70">Birthdate:</span>
                          <span>{{ optional($client->birthdate)->format('M d, Y') ?? '—' }}</span>
                        </p>
                        <p class="flex justify-between">
                          <span class="opacity-70">Username:</span>
                          <span>{{ $client->username }}</span>
                        </p>
                      </div>
                    </div>

                    {{-- Contact --}}
                    <div class="bg-layer p-4 rounded-xl border border-divider/40">
                      <h3 class="font-semibold mb-3 flex items-center gap-2">
                        <i class="fas fa-address-card opacity-70"></i> <span>Contact Information</span>
                      </h3>
                      <div class="space-y-2">
                        <p class="flex justify-between">
                          <span class="opacity-70">Contact:</span>
                          <span>{{ $client->contact ?? '—' }}</span>
                        </p>
                        <p class="flex justify-between">
                          <span class="opacity-70">Email:</span>
                          <a href="mailto:{{ $client->email ?? '' }}" class="hover:underline break-all">{{ $client->email ?? '—' }}</a>
                        </p>
                        <p class="flex justify-between">
                          <span class="opacity-70">Email status:</span>
                          <span class="{{ ($client->email_verified ?? false) ? 'text-[color:#16a34a]' : 'text-[color:#dc2626]' }}">
                            {{ ($client->email_verified ?? false) ? 'Verified' : 'Unverified' }}
                          </span>
                        </p>
                      </div>
                    </div>

                    {{-- Address --}}
                    <div class="bg-layer p-4 rounded-xl border border-divider/40">
                      <h3 class="font-semibold mb-3 flex items-center gap-2">
                        <i class="fas fa-map-marker-alt opacity-70"></i> <span>Address</span>
                      </h3>
                      <div class="space-y-2">
                        <p class="flex justify-between"><span class="opacity-70">Street:</span> <span>{{ $client->street ?? '—' }}</span></p>
                        <p class="flex justify-between"><span class="opacity-70">Barangay:</span> <span>{{ $client->barangay_name ?? '—' }}</span></p>
                        <p class="flex justify-between"><span class="opacity-70">City:</span> <span>{{ $client->city_name ?? '—' }}</span></p>
                        <p class="flex justify-between"><span class="opacity-70">Province:</span> <span>{{ $client->province_name ?? '—' }}</span></p>
                        <p class="flex justify-between"><span class="opacity-70">Region:</span> <span>{{ $client->region_name ?? '—' }}</span></p>
                        <p class="flex justify-between"><span class="opacity-70">Postal Code:</span> <span>{{ $client->postal_code ?? '—' }}</span></p>
                        <p class="flex justify-between"><span class="opacity-70">Full Address:</span> <span class="text-right">{{ $client->full_address ?? '—' }}</span></p>
                      </div>
                    </div>

                    {{-- Approval meta --}}
                    <div class="bg-layer p-4 rounded-xl border border-divider/40">
                      <h3 class="font-semibold mb-3 flex items-center gap-2">
                        <i class="fas fa-shield-alt opacity-70"></i> <span>Approval</span>
                      </h3>
                      <div class="space-y-2">
                        <p class="flex justify-between">
                          <span class="opacity-70">Account Verified:</span>
                          <span class="{{ ($client->account_verified ?? false) ? 'text-[color:#16a34a]' : 'text-[color:#dc2626]' }}">
                            {{ ($client->account_verified ?? false) ? 'Yes' : 'No' }}
                          </span>
                        </p>
                        <p class="flex justify-between">
                          <span class="opacity-70">Approved At:</span>
<span>{{ $client->approved_at?->timezone(config('app.timezone'))?->format('M d, Y h:i A') ?? '—' }}</span>
                        </p>
                        <p class="flex justify-between">
                          <span class="opacity-70">Approved By:</span>
                          <span>{{ optional(optional($client->approver)->name)->__toString() ?? ( $client->approved_by ? ('Admin #'.$client->approved_by) : '—' ) }}</span>
                        </p>
                      </div>
                    </div>
                  </div>

                  {{-- RIGHT COLUMN --}}
                  <div class="space-y-4">
                    {{-- Bio --}}
                    @if(!empty($client->bio))
                      <div class="bg-layer p-4 rounded-xl border border-divider/40">
                        <h3 class="font-semibold mb-3 flex items-center gap-2">
                          <i class="fas fa-user opacity-70"></i> <span>Bio</span>
                        </h3>
                        <p class="leading-relaxed">{{ $client->bio }}</p>
                      </div>
                    @endif

                    {{-- Portfolio --}}
                    @if(!empty($client->portfolio))
                      <div class="bg-layer p-4 rounded-xl border border-divider/40">
                        <h3 class="font-semibold mb-3 flex items-center gap-2">
                          <i class="fas fa-briefcase opacity-70"></i> <span>Portfolio</span>
                        </h3>
                        <a href="{{ '/storage/'.$client->portfolio }}" target="_blank"
                           class="inline-flex items-center gap-2 bg-card border border-divider/40 hover:bg-layer px-4 py-2 rounded-lg transition text-sm font-medium">
                          <i class="fas fa-external-link-alt"></i> View Portfolio
                        </a>
                      </div>
                    @endif

                    {{-- VALID ID (image/PDF aware) --}}
                    @php
                      $validUrl = $client->valid_id_url ?? ($client->valid_id_path ? asset('storage/'.$client->valid_id_path) : null);
                      $isValidImg = $validUrl ? in_array(strtolower(pathinfo($validUrl, PATHINFO_EXTENSION)), ['jpg','jpeg','png','webp','gif']) : false;
                    @endphp

                    @if($validUrl)
                      <div class="bg-layer p-4 rounded-xl border border-divider/40">
                        <div class="flex items-center justify-between mb-3">
                          <h3 class="font-semibold flex items-center gap-2">
                            <i class="fas fa-id-card opacity-70"></i> <span>Valid ID</span>
                          </h3>
                          <div class="flex gap-2">
                            <a href="{{ $validUrl }}" target="_blank" rel="noopener"
                               class="inline-flex items-center gap-2 bg-card border border-divider/40 hover:bg-layer px-3 py-1.5 rounded-lg text-sm font-medium">
                              <i class="fas fa-external-link-alt"></i> Open
                            </a>
                            <a href="{{ $validUrl }}" download
                               class="inline-flex items-center gap-2 bg-card border border-divider/40 hover:bg-layer px-3 py-1.5 rounded-lg text-sm font-medium">
                              <i class="fas fa-download"></i> Download
                            </a>
                          </div>
                        </div>

                        @if($isValidImg)
                          <div class="rounded-md border border-divider/40 bg-card media-fixed-sm">
                            <img
                              src="{{ $validUrl }}"
                              alt="Valid ID"
                              loading="lazy"
                              class="w-full h-auto object-contain rounded-md cursor-zoom-in"
                              data-lightbox="true"
                            />
                          </div>
                        @else
                          <div class="rounded-md overflow-hidden border border-divider/40 bg-card">
                            <iframe src="{{ $validUrl }}" class="w-full h-[420px]" loading="lazy" referrerpolicy="no-referrer"></iframe>
                          </div>
                        @endif
                      </div>
                    @endif
                    {{-- /VALID ID --}}
                  </div>
                </div>
              </div>
            </div>
          </div>

        @empty
          <div class="w-full py-8 text-center text-foreground/60"> No clients found matching your criteria </div>
        @endforelse
      @endif
    </div>
  </main>
</div>


{{-- Tiny modal script (no frameworks) --}}
<script>
document.addEventListener('click', function(e){
  const openBtn  = e.target.closest('[data-open]');
  const closeBtn = e.target.closest('[data-close]');
  if (openBtn) {
    const id = openBtn.getAttribute('data-open');
    const el = document.getElementById(id);
    if (el) {
      el.classList.remove('hidden'); el.classList.add('flex');
      document.documentElement.classList.add('modal-open');
    }
  }
  if (closeBtn) {
    const id = closeBtn.getAttribute('data-close');
    const el = document.getElementById(id);
    if (el) {
      el.classList.add('hidden'); el.classList.remove('flex');
      document.documentElement.classList.remove('modal-open');
    }
  }
});
document.addEventListener('keydown', function(e){
  if (e.key === 'Escape') {
    document.querySelectorAll('[id^="coach-modal-"],[id^="client-modal-"]').forEach(el=>{
      el.classList.add('hidden'); el.classList.remove('flex');
      document.documentElement.classList.remove('modal-open');
    });
  }
});
</script>

{{-- Lightbox only for elements that OPT-IN via data-lightbox="true" --}}
<script>
(function(){
  document.addEventListener('click', function(e){
    const img = e.target.closest('[data-lightbox="true"]');
    if(img && img.getAttribute('src')){
      window.open(img.getAttribute('src'), '_blank', 'noopener');
    }
  });
})();
</script>

</body>
</html>
