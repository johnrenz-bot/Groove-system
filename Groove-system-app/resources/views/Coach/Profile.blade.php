@php
    use Carbon\Carbon;

    $coach = auth('coach')->user();
    $user = $coach ?? (session('client') ?? session('coach') ?? auth()->user());
    $unreadNotifications = $user?->unreadNotifications ?? collect();

    $coachFirst = $coach->firstname ?? '';
    $coachMiddle = $coach->middlename ?? '';
    $coachLast = $coach->lastname ?? '';
    $coachInitials = strtoupper(mb_substr($coachFirst ?: 'C', 0, 1) . mb_substr($coachLast ?: 'C', 0, 1));
    $coachAbout = $coach->about ?? $coach->bio ?? null;
    $coachTalents = $coach->talents ?? null;
    $coachBirthFormatted = $coach->birthdate ? Carbon::parse($coach->birthdate)->format('F d, Y') : '—';

    $addressBits = collect([
        $coach->street ?? null,
        $coach->barangay_name ?? ($coach->barangay ?? null),
        $coach->city_name ?? null,
        $coach->province_name ?? null,
        $coach->region_name ?? null,
    ])->filter()->implode(', ');

    $addressDisplay = $addressBits ?: trim(($coach->address ?? '') . (isset($coach->barangay) ? ', '.$coach->barangay : '')) ?: '—';
    $coachContact = $coach->contact ?: '—';
    $coachEmail   = $coach->email   ?: '—';
    $coachPublicId = $coach->coach_id ?: '0000';
    $coachRole = $coach->role ? ucfirst($coach->role) : 'Coach';
    $coachPhoto = $coach?->photo ? asset('storage/' . $coach->photo) : null;

    $coachPortfolio = $coach?->portfolio_path ?? $coach?->portfolio ?? null;
    $portfolioUrl = $coachPortfolio ? asset('storage/' . $coachPortfolio) : null;
@endphp

<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Groove | Coach Dashboard</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
  <script src="https://kit.fontawesome.com/43f9926b04.js" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="{{ asset('css/app.css') }}" rel="stylesheet" />
  <link rel="icon" href="/image/bg/LOG.png" type="image/png" sizes="512x512">
  <link rel="apple-touch-icon" href="/image/wc/logo.png" sizes="180x180">
  <style>
    [x-cloak]{display:none!important;}
    .custom-scrollbar::-webkit-scrollbar{height:8px}
    .custom-scrollbar::-webkit-scrollbar-track{background:#27272a;border-radius:10px}
    .custom-scrollbar::-webkit-scrollbar-thumb{background:linear-gradient(90deg,#716d76,#605e63);border-radius:10px}
    .custom-scrollbar::-webkit-scrollbar-thumb:hover{background:linear-gradient(90deg,#464547,#3c3c3d)}
    .custom-scrollbar{scrollbar-width:thin;scrollbar-color:#424044 #27272a}
    .hover-scrollbar{scrollbar-width:thin;scrollbar-color:transparent transparent;scroll-behavior:smooth;padding-right:6px}
    .hover-scrollbar:hover{scrollbar-color:#71717a transparent}
    .hover-scrollbar::-webkit-scrollbar{width:6px}
    .hover-scrollbar::-webkit-scrollbar-thumb{background:transparent;border-radius:6px;transition:background .3s ease}
    .hover-scrollbar:hover::-webkit-scrollbar-thumb{background:#71717a}
    
  </style>

@vite(['resources/css/app.css', 'resources/js/app.js'])

</head>

<body class="min-h-screen w-full overflow-x-hidden font-sans theme-{{ $appTheme }} bg-surface text-foreground">
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

  <main class="max-w-7xl mx-auto px-6 md:px-10 pt-24 pb-16" x-data="{ tab: 'posts' }">
    {{-- Profile Header --}}
    <section class="bg-card border border-divider/40 p-6 rounded-3xl text-foreground shadow-lg">
      <div class="flex flex-col md:flex-row md:items-center md:justify-between">
        <div class="flex items-center gap-6">
          {{-- Profile Photo --}}
          <div class="w-24 h-24 rounded-full overflow-hidden shadow-lg bg-layer border border-divider/40">
            @if ($coachPhoto)
              <img src="{{ $coachPhoto }}" alt="Coach Photo" class="w-full h-full object-cover">
            @else
              <div class="flex items-center justify-center h-full w-full text-3xl font-bold text-foreground/40">
                {{ $coachInitials }}
              </div>
            @endif
          </div>

          {{-- Name and Info --}}
          <div class="text-left space-y-1">
            <h1 class="text-3xl font-extrabold tracking-tight leading-snug">
              {{ trim($coachFirst . ' ' . $coachMiddle . ' ' . $coachLast) }}
            </h1>

            <div class="flex items-center flex-wrap gap-2 text-sm text-primary">
              <span class="bg-primary/15  px-2 py-0.5 rounded-full font-medium capitalize">
                {{ $coachRole }}
              </span>
              <span class="text-foreground/50">|</span>
              <span class="inline-block text-sm text-foreground/80 font-mono bg-primary/10 px-2.5 py-0.5 rounded-md">
                ID {{ $coachPublicId }}
              </span>

        @if (!empty($coach->account_verified))
    <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-green-100 text-green-800 text-sm font-semibold shadow-md">
        <i class="fa-solid fa-circle-check text-green-600"></i>
        Verified
    </span>
@endif

            </div>
          </div>
        </div>

        <div class="mt-4 md:mt-0">
          <a href="{{ route('PROFILE.EDIT') }}"
             class="px-4 py-2 border border-divider/40 rounded-full hover:bg-layer text-foreground transition">
            Edit
          </a>
        </div>
      </div>
    </section>

    {{-- Navigation Tabs --}}
    <nav class="flex mt-8 space-x-2 text-sm font-semibold uppercase">
      <button @click="tab='posts'"
              :class="tab==='posts' ? 'bg-layer/70 text-foreground shadow-md border-divider/40' : 'bg-layer/40 text-foreground/70 hover:bg-layer/70 hover:text-foreground border-divider/40'"
              class="px-4 py-2 rounded-xl transition-all duration-300 border">
        Posts
      </button>

      <button @click="tab='about'"
              :class="tab==='about' ? 'bg-layer/70 text-foreground shadow-md border-divider/40' : 'bg-layer/40 text-foreground/70 hover:bg-layer/70 hover:text-foreground border-divider/40'"
              class="px-4 py-2 rounded-xl transition-all duration-300 border">
        About
      </button>

      <button @click="tab='appointment'"
              :class="tab==='appointment' ? 'bg-layer/70 text-foreground shadow-md border-divider/40' : 'bg-layer/40 text-foreground/70 hover:bg-layer/70 hover:text-foreground border-divider/40'"
              class="px-4 py-2 rounded-xl transition-all duration-300 border">
        Appointment
      </button>

      <button @click="tab='feedback'"
              :class="tab==='feedback' ? 'bg-layer/70 text-foreground shadow-md border-divider/40' : 'bg-layer/40 text-foreground/70 hover:bg-layer/70 hover:text-foreground border-divider/40'"
              class="px-4 py-2 rounded-xl transition-all duration-300 border">
        Ratings & Feedback
      </button>
    </nav>

    {{-- Content Area --}}
    <div class="flex flex-col lg:flex-row gap-8 mt-6">
      {{-- Left Column --}}
      <div class="w-full lg:w-2/3">
        <div class="relative min-h-[820px]">

          {{-- Posts Tab --}}
          <section x-show="tab==='posts'" x-transition class="absolute inset-0 w-full h-full overflow-y-auto pr-1">
            <div x-data="postsManager()" x-init="loadPosts(); startPolling()"
                 class="bg-card/90 border border-divider/40 p-6 rounded-2xl shadow-md">
              <h3 class="text-xl font-bold mb-6">My Posts</h3>

              <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 relative gap-4">
                {{-- New Post --}}
                <div @click="showUploadForm = true"
                     class="flex flex-col items-center justify-center border border-dashed border-divider/40 text-foreground/70 bg-layer hover:bg-layer/80 transition h-[35vh] cursor-pointer group">
                  <svg class="w-12 h-12 group-hover:scale-110 transition" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                  </svg>
                  <p class="mt-2 font-semibold">New Post</p>
                </div>

                {{-- Posts --}}
                <template x-for="post in posts" :key="post.id">
                  <div class="relative group overflow-hidden border border-divider/40 bg-layer shadow hover:shadow-neutral-600/30 transition h-[35vh] w-full ">
                    <div x-data="{ open:false }" class="absolute -top-3 right-3 z-30">
                      <button @click="open=!open"
                              class="bg-card hover:bg-layer p-1.5 rounded-full text-foreground/80 hover:text-foreground text-xl shadow-lg ring-1 ring-divider/40">⋯
                      </button>
                      <div x-show="open" @click.outside="open=false" x-transition
                           class="absolute right-0 w-48 bg-card border border-divider/40 rounded-xl shadow-xl z-50 overflow-hidden text-sm">
                        <div class="px-4 py-2 border-b border-divider/40 text-xs text-foreground/60">
                          <span class="block font-semibold text-foreground/80">Posted:</span>
                          <span x-text="formatDate(post.created_at)"></span>
                        </div>
                        <button @click="deletePost(post.id)"
                                class="w-full text-left px-4 py-2 hover:bg-rose-600 hover:text-white transition">
                          Delete Post
                        </button>
                      </div>
                    </div>

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

                      <div class="absolute bottom-0 left-0 w-full bg-gradient-to-t from-card/90 to-transparent px-4 py-3">
                        <p class="text-sm font-medium line-clamp-2" x-text="post.caption"></p>
                      </div>
                    </div>

                    <div class="px-4 py-3 bg-card/80 text-xs text-foreground/70 flex items-center justify-between backdrop-blur-sm">
                      <div class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                          <path d="M10 10a4 4 0 100-8 4 4 0 000 8zM2 18a8 8 0 0116 0H2z" />
                        </svg>
                        <span class="text-foreground font-semibold" x-text="post.coach_name"></span>
                      </div>
                    </div>
                  </div>
                </template>

                <template x-if="posts.length === 0">
                  <div class="col-span-full text-center text-foreground/60 italic py-8">
                    No posts yet. Start sharing your talent.
                  </div>
                </template>
              </div>

              {{-- Image Modal --}}
              <div x-show="showModal" x-transition @click="closeModal()"
                   class="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex items-center justify-center">
                <div class="relative">
                  <button @click="closeModal()" class="absolute -top-5 -right-5 text-white text-3xl font-bold hover:text-rose-400">&times;</button>
                  <img :src="modalImage" class="max-w-full max-h-[90vh] rounded-lg shadow-2xl border-4 border-white" @click.stop>
                </div>
              </div>
{{-- Upload Modal --}}
<div x-show="showUploadForm" x-transition.opacity
     class="fixed inset-0 z-50 flex items-center justify-center px-4"
     aria-labelledby="create-post-title" role="dialog" aria-modal="true">

  <!-- Overlay -->
  <div class="absolute inset-0 bg-black/70 backdrop-blur-sm"></div>

  <!-- Modal -->
  <div
    class="relative w-full max-w-xl bg-neutral-800 text-foreground border border-divider/40
           rounded-2xl shadow-2xl max-h-[85vh] overflow-y-auto"
    x-data="{ previewUrl:null, isImage:true, dragging:false }"
    @dragover.prevent="dragging=true"
    @dragleave.prevent="dragging=false"
    @drop.prevent="
      $refs.fileInput.files = $event.dataTransfer.files;
      const f = $event.dataTransfer.files[0];
      if(f){ isImage=f.type.startsWith('image/'); previewUrl=URL.createObjectURL(f); }
      dragging=false;
    "
  >
    <!-- Header with close button inside -->
    <div class="flex items-center justify-between px-6 pt-6 pb-4 border-b border-divider/40">
      <h3 id="create-post-title" class="text-xl font-bold">Create a New Post</h3>
      <button @click="showUploadForm=false"
              class="w-8 h-8 flex items-center justify-center rounded-full
                     bg-layer hover:bg-layer/80 text-2xl leading-none text-foreground/80 hover:text-foreground
                     focus:outline-none focus:ring-2 focus:ring-primary/30"
              aria-label="Close">
        &times;
      </button>
    </div>

    <!-- Body -->
    <form action="{{ route('Coachprofile.Store') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-6">
      @csrf

      <!-- Upload area -->
      <div>
        <label class="block text-sm font-semibold mb-2">Upload Media</label>
        <div
          @click="$refs.fileInput.click()"
          class="relative flex flex-col items-center justify-center p-8
                 border-2 border-dashed rounded-xl cursor-pointer transition duration-300
                 bg-neutral-500 hover:bg-neutral-600"
          :class="dragging ? 'border-primary/50 ring-2 ring-primary/30' : 'border-divider/40'"
        >
          <!-- Empty state -->
          <template x-if="!previewUrl">
            <div class="text-center text-foreground/70">
              <svg class="w-12 h-12 mx-auto mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v-1.25A5.25 5.25 0 018.25 10H12m0 0l-3-3m3 3l3-3m-3 3v10.5" />
              </svg>
              <p class="font-medium">Drag & drop or click to upload</p>
              <p class="text-xs text-foreground/60 mt-1">Accepted: images/videos</p>
            </div>
          </template>

          <!-- Preview -->
          <template x-if="previewUrl">
            <div class="relative w-full flex items-center justify-center max-h-60 overflow-hidden rounded-lg">
              <template x-if="isImage">
                <img :src="previewUrl" class="max-h-60 object-contain rounded-lg border border-divider/40 shadow">
              </template>
              <template x-if="!isImage">
                <video controls class="max-h-60 object-contain rounded-lg border border-divider/40 shadow">
                  <source :src="previewUrl" type="video/mp4">
                </video>
              </template>
              <!-- remove button inside preview -->
              <button
                @click.stop="previewUrl=null; $refs.fileInput.value=''; isImage=true;"
                class="absolute top-2 right-2 w-8 h-8 flex items-center justify-center
                       bg-black/70 text-white rounded-full hover:bg-black focus:outline-none focus:ring-2 focus:ring-primary/30"
                aria-label="Remove file">
                &times;
              </button>
            </div>
          </template>
        </div>
        <input type="file" name="media" accept="image/*,video/*" x-ref="fileInput"
               class="hidden"
               @change="
                 const f = $event.target.files[0];
                 if(f){ isImage=f.type.startsWith('image/'); previewUrl=URL.createObjectURL(f); }
               ">
      </div>

      <!-- Caption -->
      <div>
        <label class="block text-sm font-semibold mb-1">Caption</label>
        <textarea name="caption" rows="2"
                  class="w-full px-4 py-2 bg-neutral-500 border border-divider/40 rounded-lg resize-none
                         focus:ring-primary/30 focus:outline-none"
                  placeholder="Write a caption..."></textarea>
      </div>

      <!-- Footer -->
      <div class="flex justify-end gap-3 pt-2">
        <button type="button"
                @click="showUploadForm=false"
                class="px-4 py-2 rounded-lg border border-divider/40 bg-neutral-500 hover:bg-neutral-300">
          Cancel
        </button>
        <button type="submit"
                class="px-4 py-2 bg-primary text-primary-foreground font-semibold rounded-lg hover:opacity-90">
          Post
        </button>
      </div>
    </form>
  </div>
</div>

            </div>
          </section>

          {{-- About Tab --}}
          <section x-show="tab==='about'" x-transition class="absolute inset-0 w-full h-full overflow-y-auto pr-1">
            <div class="flex flex-col gap-6 w-full">
              <div class="bg-card border border-divider/40 p-6 rounded-2xl shadow-lg">
                <h3 class="text-lg font-semibold text-primary mb-3">About</h3>
                <p class="leading-relaxed text-foreground/90">
                  {{ $coachAbout ?? 'No information has been provided for this section.' }}
                </p>
              </div>

              <div class="bg-card border border-divider/40 p-6 rounded-2xl shadow-lg">
                <h3 class="text-lg font-semibold text-primary border-b border-divider/40 pb-2">
                  Personal Information
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4 text-foreground/90">
                  <div><span class="font-medium text-foreground">Talents:</span> {{ $coachTalents ?: '—' }}</div>
                  <div><span class="font-medium text-foreground">Date of Birth:</span> {{ $coachBirthFormatted }}</div>
                  <div class="sm:col-span-2"><span class="font-medium text-foreground">Address:</span> {{ $addressDisplay }}</div>
                  <div><span class="font-medium text-foreground">Contact Number:</span> {{ $coachContact }}</div>
                  <div><span class="font-medium text-foreground">Email Address:</span> {{ $coachEmail }}</div>
                </div>
              </div>
            </div>
          </section>

          {{-- Appointment Tab --}}
          <section x-show="tab==='appointment'" x-transition class="absolute inset-0 w-full h-full overflow-y-auto pr-1">
            <div class="bg-card border border-divider/40 p-4 rounded-2xl shadow-lg">
              <h3 class="text-lg font-semibold mb-4 border-b border-divider/40 pb-2">My Appointments</h3>

              @if (session('success'))
                <div class="bg-emerald-600/15 text-emerald-400 border border-emerald-600/30 px-4 py-2 rounded mb-4">
                  {{ session('success') }}
                </div>
              @endif
              @if (session('error'))
                <div class="bg-rose-600/15 text-rose-400 border border-rose-600/30 px-4 py-2 rounded mb-4">
                  {{ session('error') }}
                </div>
              @endif

              @if (!empty($appointments) && $appointments->count())
                @foreach ($appointments as $appointment)
                  @php
                    // ✅ SAFE end datetime (no string concatenation)
                    $endDateTime = $appointment->end_at
                      ?: ( ($appointment->date instanceof \Carbon\Carbon ? $appointment->date->copy() : \Carbon\Carbon::parse($appointment->date ?? now()))
                          ->setTimeFromTimeString($appointment->end_time ?? '00:00') );

                    $isExpired = $endDateTime->lt(now());
                    $status = $appointment->status ?? 'pending';

                    $chip =
                      $status === 'completed' ? 'bg-emerald-600/15 text-emerald-400 border border-emerald-600/30' :
                      ($status === 'pending' ? 'bg-amber-600/15 text-amber-400 border border-amber-600/30' :
                      ($status === 'confirmed' ? 'bg-blue-600/15 text-blue-400 border border-blue-600/30' :
                      ($status === 'cancelled' ? 'bg-rose-600/15 text-rose-400 border border-rose-600/30' : 'bg-zinc-600/15 text-foreground/70 border border-divider/40')));

                    $accent =
                      $status === 'completed' ? 'border-emerald-500' :
                      ($status === 'pending' ? 'border-amber-500' :
                      ($status === 'confirmed' ? 'border-blue-500' :
                      ($status === 'cancelled' ? 'border-rose-500' : 'border-divider/40')));
                  @endphp

                  <div class="flex flex-col md:flex-row md:items-center justify-between bg-layer border-l-4 {{ $accent }}
                              p-3 mb-4 rounded-xl shadow-md hover:shadow-lg transition-shadow duration-200 text-sm
                              {{ $appointment->status === 'cancelled' ? 'opacity-50' : '' }} {{ $isExpired ? 'opacity-60' : '' }}">

                    <div class="flex flex-col items-start w-16 shrink-0">
                      <span class="font-semibold text-foreground text-[10px] mb-0.5">ID</span>
                      <span class="text-[10px] text-foreground/80">{{ $appointment->appointment_id ?? 'N/A' }}</span>
                    </div>

                    <div class="flex items-center gap-2 w-40 mt-2 md:mt-0">
                      <div class="relative w-8 h-8 shrink-0">
                        @if (($appointment->client->photo ?? false))
                          <img src="{{ asset('storage/' . $appointment->client->photo) }}"
                               alt="{{ ($appointment->client->firstname ?? 'Client') . '\'s photo' }}"
                               class="w-8 h-8 rounded-full object-cover border border-divider/40">
                        @else
                          @php
                            $ci = strtoupper(
                              mb_substr($appointment->client->firstname ?? 'C', 0, 1) .
                              mb_substr($appointment->client->lastname ?? 'N', 0, 1)
                            );
                          @endphp
                          <div class="w-8 h-8 flex items-center justify-center bg-layer rounded-full text-foreground text-[10px] uppercase border border-divider/40">
                            {{ $ci }}
                          </div>
                        @endif
                      </div>
                      <div class="flex flex-col">
                        <span class="font-semibold text-foreground text-[10px]">Client</span>
                        <span class="text-[10px] text-foreground/80">
                          {{ ($appointment->client->firstname ?? 'N/A') . ' ' . ($appointment->client->lastname ?? '') }}
                        </span>
                      </div>
                    </div>

                    <div class="flex flex-col items-start w-28 mt-2 md:mt-0">
                      <span class="font-semibold text-foreground text-[10px]">Date</span>
                      <span class="text-[10px] text-foreground/80">
                        @if($appointment->date instanceof \Carbon\Carbon)
                          {{ $appointment->date->format('M d, Y') }}
                        @elseif(!empty($appointment->date))
                          {{ \Carbon\Carbon::parse($appointment->date)->format('M d, Y') }}
                        @else
                          —
                        @endif
                      </span>
                    </div>

                    <div class="flex flex-col items-start w-32 mt-2 md:mt-0">
                      <span class="font-semibold text-foreground text-[10px]">Time</span>
                      <span class="text-[10px] text-foreground/80">
                        {{ optional($appointment->start_at)->format('h:i A') ?? '—' }}
                        —
                        {{ optional($appointment->end_at)->format('h:i A') ?? '—' }}
                      </span>
                    </div>

                    <div class="flex flex-col items-start w-28 mt-2 md:mt-0">
                      <span class="font-semibold text-foreground text-[10px]">Session</span>
                      <span class="text-[10px] text-foreground/80">{{ $appointment->session_type ?? 'N/A' }}</span>
                    </div>

                    <div class="flex flex-col items-start w-28 mt-2 md:mt-0">
                      <span class="font-semibold text-foreground text-[10px]">Talent</span>
                      <span class="text-[10px] text-foreground/80">{{ ucfirst($appointment->talent ?? '—') }}</span>
                    </div>

                    <div class="flex flex-col items-start w-28 mt-2 md:mt-0">
                      <span class="font-semibold text-foreground text-[10px]">Status</span>
                      <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-semibold mt-1 {{ $chip }}">
                        {{ $isExpired && $appointment->status !== 'completed' ? 'Expired' : ucfirst($status) }}
                      </span>
                    </div>

                    <div class="mt-3 md:mt-0 flex items-center justify-end w-14">
                      <a href="{{ route('appointmentdata') }}"
                         class="inline-flex items-center justify-center w-8 h-8 rounded-full border border-divider/40 hover:border-primary hover:text-primary transition"
                         title="View details">
                        <i class="fa-solid fa-eye text-xs"></i>
                      </a>
                    </div>
                  </div>
                @endforeach
              @else
                <p class="text-foreground/70 italic text-sm">You have no appointments yet.</p>
              @endif
            </div>
          </section>

          {{-- Feedback Tab --}}
          <section x-show="tab==='feedback'" x-transition class="absolute inset-0 w-full h-full overflow-y-auto pr-1">
            <div class="bg-card border border-divider/40 p-6 rounded-2xl shadow-md">
              <h3 class="text-xl font-bold text-foreground/80 mb-4">Ratings & Feedback</h3>

              @if(!empty($coach->feedbacks) && $coach->feedbacks->count() > 0)
                <div class="p-6 rounded-3xl shadow-lg">
                  <h3 class="text-sm font-semibold text-foreground/80 mb-3">Client Feedback</h3>

                  <div class="flex gap-4 overflow-x-auto pb-3 scroll-smooth custom-scrollbar">
                    @foreach($coach->feedbacks as $feedback)
                      @php
                        $client = $feedback->user ?? null;
                        $fRating = (float) ($feedback->rating ?? 0);
                        $fullStars = floor($fRating);
                        $halfStar = ($fRating - $fullStars) >= 0.5;
                        $emptyStars = 5 - $fullStars - ($halfStar ? 1 : 0);
                        $fbId = $feedback->feedback_id ?? $feedback->id ?? uniqid('fb_');
                      @endphp

                      <div class="w-72 bg-layer/80 p-4 rounded-lg border border-divider/40 shadow-inner shrink-0">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center bg-layer text-foreground font-bold text-sm overflow-hidden mb-2 border border-divider/40">
                          @if($client && $client->photo)
                            <img src="{{ asset('storage/' . $client->photo) }}" alt="{{ $client->fullname ?? 'Client' }}" class="w-full h-full object-cover">
                          @elseif($client)
                            {{ strtoupper(mb_substr($client->firstname ?? '', 0, 1) . mb_substr($client->lastname ?? '', 0, 1)) }}
                          @else
                            NA
                          @endif
                        </div>

                        <div class="mb-2">
                          <div class="font-medium text-foreground text-sm">
                            {{ $client->fullname ?? trim(($client->firstname ?? '') . ' ' . ($client->lastname ?? '')) ?: 'Anonymous' }}
                          </div>
                          <div class="text-[10px] text-primary uppercase font-semibold">
                            {{ $client->role ?? 'Client' }}
                          </div>
                        </div>

                        <div class="flex items-center gap-1 mb-2">
                          @for ($i = 0; $i < $fullStars; $i++)
                            <svg class="w-3 h-3 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                              <path d="M10 15l-5.878 3.09L5.94 12.545.941 8.455l6.097-.91L10 2l2.962 5.545 6.097.91-4.999 4.09 1.818 5.545z"/>
                            </svg>
                          @endfor
                          @if($halfStar)
                            <svg class="w-3 h-3 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                              <defs>
                                <linearGradient id="half-{{ $fbId }}">
                                  <stop offset="50%" stop-color="currentColor" />
                                  <stop offset="50%" stop-color="transparent" />
                                </linearGradient>
                              </defs>
                              <path fill="url(#half-{{ $fbId }})"
                                    d="M10 15l-5.878 3.09L5.94 12.545.941 8.455l6.097-.91L10 2l2.962 5.545 6.097.91-4.999 4.09 1.818 5.545z"/>
                            </svg>
                          @endif
                          @for ($i = 0; $i < $emptyStars; $i++)
                            <svg class="w-3 h-3 text-foreground/30" viewBox="0 0 20 20" fill="currentColor">
                              <path d="M10 15l-5.878 3.09L5.94 12.545.941 8.455l6.097-.91L10 2l2.962 5.545 6.097.91-4.999 4.09 1.818 5.545z"/>
                            </svg>
                          @endfor
                        </div>

                        <p class="text-xs text-foreground/80 italic bg-layer/70 p-2 rounded-md">
                          “{{ $feedback->comment ?? '' }}”
                        </p>
                      </div>
                    @endforeach
                  </div>
                </div>
              @else
                <p class="text-foreground/70">No feedback yet.</p>
              @endif
            </div>
          </section>

        </div>
      </div>

      {{-- Right Column --}}
      <aside class="w-full lg:w-1/3 space-y-6">
        <div class="bg-card/90 border border-divider/40 p-4 rounded-2xl shadow-lg flex flex-col">
          @if ($portfolioUrl)
            @php
              $isImage = preg_match('/\.(png|jpe?g|webp|gif)$/i', $coachPortfolio ?? '') === 1;
            @endphp

            @if ($isImage)
              <a href="{{ $portfolioUrl }}" target="_blank"
                 class="block group relative overflow-hidden rounded-xl shadow-md w-full h-full">
                <img src="{{ $portfolioUrl }}"
                     alt="Portfolio item"
                     class="w-full h-full object-cover rounded-lg transition-transform duration-300 group-hover:scale-105">
              </a>
            @else
              <div class="flex flex-col items-center justify-center text-foreground/80 gap-2 p-6 rounded-lg border border-divider/40 w-full">
                <i class="fa-regular fa-file-pdf text-3xl"></i>
                <a href="{{ $portfolioUrl }}" target="_blank" class="underline">View Portfolio (PDF)</a>
              </div>
            @endif
          @else
            <div class="flex flex-col items-center justify-center text-foreground/60 gap-2 p-6 rounded-lg border border-divider/40 w-full h-80">
              <i class="fa-regular fa-image text-3xl"></i>
              <p class="italic">No portfolio added yet.</p>
            </div>
          @endif
        </div>
      </aside>
    </div>

  </main>

  {{-- ================= SCRIPTS ================= --}}
  <script>
    function postsManager() {
      return {
        posts: [],
        showUploadForm: false,
        showModal: false,
        modalImage: '',

        loadPosts() {
          fetch(@json(route('coach-profile-posts.fetch')))
            .then(res => res.json())
            .then(data => { this.posts = data.posts ?? data ?? []; })
            .catch(err => console.error("Error fetching posts:", err));
        },

        startPolling() { setInterval(() => this.loadPosts(), 10000); },

        deletePost(postId) {
          if (!confirm('Are you sure you want to delete this post?')) return;
          fetch(`/profile-posts/${postId}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
          })
          .then(res => res.json())
          .then(data => {
            if (data.success) this.posts = this.posts.filter(p => p.id !== postId);
            else alert(data.error || 'Failed to delete post.');
          })
          .catch(err => console.error("Error deleting post:", err));
        },

        isVideo(path) { return /\.(mp4|webm|ogg)$/i.test(path || ''); },

        formatDate(dateString) {
          const d = new Date(dateString);
          return isNaN(d.getTime()) ? '' : d.toLocaleString('en-US',{year:'numeric',month:'short',day:'numeric',hour:'numeric',minute:'2-digit'});
        },

        openImageModal(url){ this.modalImage = url; this.showModal = true; },
        closeModal(){ this.modalImage = ''; this.showModal = false; },
      };
    }
  </script>

</body>
</html>
