@php
    $authUser = $authUser ?? null;
    $authRole = $authRole ?? 'client'; // fallback

    $authId = $authRole === 'client' ? ($authUser->client_id ?? null) : ($authUser->coach_id ?? null);
    $authType = $authRole === 'client' ? 'client' : 'coach';

    $unreadNotifications = $authUser ? $authUser->unreadNotifications : collect();
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Groove Messenger</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <link rel="icon" href="/image/bg/LOG.png" type="image/png" sizes="512x512">
    <link rel="apple-touch-icon" href="/image/wc/logo.png" sizes="180x180">

    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 8px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background-color: #440083; border-radius: 4px; }

        .hover-scrollbar { scrollbar-width: thin; scrollbar-color: transparent transparent; scroll-behavior: smooth; padding-right: 6px; }
        .hover-scrollbar:hover { scrollbar-color: #71717a transparent; }
        .hover-scrollbar::-webkit-scrollbar { width: 6px; }
        .hover-scrollbar::-webkit-scrollbar-thumb { background: transparent; border-radius: 6px; transition: background .3s ease; }
        .hover-scrollbar:hover::-webkit-scrollbar-thumb { background: #71717a; }

        /* Dark container scrollbars */
        .bg-zinc-900::-webkit-scrollbar { width: 8px; background: transparent; }
        .bg-zinc-900::-webkit-scrollbar-thumb { background-color: #555; border-radius: 10px; }
        .bg-zinc-900 { scrollbar-width: thin; scrollbar-color: #565050 transparent; }
    </style>

@vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen antialiased theme-{{ $appTheme }} bg-surface text-foreground">

<header x-data="{ scrolled:false, openNotif:false, openMenu:false }"
        x-init="window.addEventListener('scroll', () => { scrolled = window.scrollY > 10 })"
        class="fixed top-0 left-0 right-0 z-50 transition duration-300"
        :class="scrolled ? 'backdrop-blur-sm border-b border-ui bg-zinc-900/70' : 'bg-transparent'">
  <div class="max-w-7xl mx-auto px-4 lg:px-8 py-3">
    <div class="flex items-center gap-4">

      <!-- Logo -->
      <a href="/coach/home"
         class="flex items-center gap-3 shrink-0 focus:outline-none focus:ring-2 focus:ring-purple-500/60 rounded-xl">
        <img src="/image/bg/LOG.png" alt="Website Logo" class="h-10 w-auto object-contain select-none" />
        <span class="sr-only">Home</span>
      </a>

      <!-- Navigation -->
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

      <!-- Right Actions -->
      <div class="ml-auto flex items-center gap-3">

        <!-- Notifications -->
        <div class="relative" x-cloak>
          <button @click="openNotif = !openNotif"
                  class="w-10 h-10 flex items-center justify-center rounded-full hover:bg-layer transition relative"
                  aria-label="Notifications" aria-haspopup="true" :aria-expanded="openNotif">
            <i class="fa-regular fa-bell text-fg"></i>
            @if($unreadNotifications->count())
              <span x-show="!openNotif" class="absolute -top-1 -right-1 w-5 h-5 text-[10px] font-bold text-fg bg-layer border border-ui rounded-full grid place-items-center">
                {{ $unreadNotifications->count() }}
              </span>
            @endif
          </button>

          <div x-show="openNotif" @click.away="openNotif = false" @keydown.escape.window="openNotif=false"
               class="absolute right-0 mt-3 w-80 max-h-96 bg-card border border-ui rounded-xl shadow-ui p-4 space-y-3 z-50 overflow-y-auto"
               x-transition:enter="transition ease-out duration-150"
               x-transition:enter-start="opacity-0 translate-y-2 scale-95"
               x-transition:enter-end="opacity-100 translate-y-0 scale-100"
               x-transition:leave="transition ease-in duration-100"
               x-transition:leave-start="opacity-100 scale-100"
               x-transition:leave-end="opacity-0 scale-95"
               role="menu" aria-label="Notifications">
            <h4 class="text-base font-semibold border-b border-ui pb-2">Notifications</h4>

            @forelse ($unreadNotifications as $notif)
              <button type="button"
                      wire:click="$emit('markAsRead', '{{ $notif->id }}')"
                      class="w-full text-left bg-layer rounded-lg p-3 text-sm transition hover:ring-ui">
                <p class="font-medium">{{ $notif->data['title'] }}</p>
                <p class="text-muted text-xs mt-1">{{ $notif->data['message'] }}</p>
                <p class="text-muted text-[11px] mt-2">{{ $notif->created_at->diffForHumans() }}</p>
              </button>
            @empty
              <div class="text-center text-muted italic py-6 text-sm">You're all caught up</div>
            @endforelse
          </div>
        </div>

        <!-- Profile Dropdown -->
        <div class="relative" x-cloak>
          <button @click="openMenu = !openMenu"
                  class="flex items-center gap-x-3 px-3 py-2 bg-card rounded-full transition duration-200 border border-ui"
                  aria-label="User Profile Menu" aria-haspopup="true" :aria-expanded="openMenu">

            <!-- Avatar with Status Dot -->
            <div class="relative w-8 h-8">
              @if ($coach && !empty($coach->photo))
                <img src="{{ asset('storage/' . $coach->photo) }}" alt="Avatar"
                     class="w-8 h-8 rounded-full object-cover border border-ui">
              @else
                <div class="w-8 h-8 grid place-items-center bg-layer rounded-full text-sm font-bold uppercase border border-ui">
                  {{ strtoupper(substr($coach->firstname ?? 'C', 0, 1)) }}
                </div>
              @endif
              <div class="absolute bottom-0 right-0 w-2.5 h-2.5 rounded-full border-2 border-zinc-800 z-10"
                   :class="{
                     'bg-green-500': $store.profile.status === 'Online',
                     'bg-yellow-400': $store.profile.status === 'Away',
                     'bg-red-500': $store.profile.status === 'Busy',
                     'bg-zinc-500': $store.profile.status === 'Offline'
                   }"></div>
            </div>

            <!-- Name -->
            <span class="hidden sm:inline text-xs capitalize">
              {{ strtolower($coach->firstname ?? ($authRole === 'coach' ? 'coach' : 'client')) }}
              {{ strtolower($coach->middlename ?? '') }}
            </span>
            <i class="fa-solid fa-caret-down text-muted"></i>
          </button>

          <!-- Menu Panel -->
          <div x-show="openMenu" @click.away="openMenu=false" @keydown.escape.window="openMenu=false" x-transition
               class="absolute right-0 mt-2 w-64 bg-card border border-ui rounded-2xl shadow-ui ring-1 ring-ui z-50 overflow-hidden">

            <!-- Info -->
            <div class="px-4 py-3 bg-layer border-b border-ui text-center">
              <p class="text-sm font-semibold">{{ $coach->firstname ?? 'User' }} {{ $coach->middlename ?? '' }}</p>
              <p class="text-xs text-muted mt-0.5">#{{ $coachId ?? '0000' }} • {{ ucfirst($authRole) }}</p>
            </div>

            <!-- Status -->
            <div class="relative">
              <button @click="$store.profile.openStatus = !$store.profile.openStatus"
                      class="flex items-center justify-between w-full px-3 py-1.5 text-sm text-fg  rounded-md transition">
                <div class="flex items-center gap-2">
                  <i class="fa-solid fa-circle text-xs"
                     :class="{
                       'text-green-400': $store.profile.status === 'Online',
                       'text-yellow-400': $store.profile.status === 'Away',
                       'text-red-500': $store.profile.status === 'Busy',
                       'text-zinc-500': $store.profile.status === 'Offline'
                     }"></i>
                  <span x-text="$store.profile.status"></span>
                </div>
                <i class="fa-solid fa-chevron-down text-xs ml-auto"></i>
              </button>

              <div x-show="$store.profile.openStatus" x-cloak x-transition @click.outside="$store.profile.openStatus=false"
                   class="absolute top-full left-0 mt-1 w-full bg-zinc-900 border border-zinc-700 rounded-lg shadow-xl z-50">
                <ul class="text-sm text-zinc-300 py-1">
                  <template x-for="opt in ['Online','Busy','Away','Offline']" :key="opt">
                    <li>
                      <button @click="$store.profile.changeStatus(opt)"
                              class="w-full px-4 py-2 text-left hover:bg-zinc-800 flex items-center gap-2">
                        <i class="fa-solid fa-circle text-xs"
                           :class="{
                             'text-green-400': opt === 'Online',
                             'text-red-500': opt === 'Busy',
                             'text-yellow-400': opt === 'Away',
                             'text-zinc-500': opt === 'Offline'
                           }"></i>
                        <span x-text="opt"></span>
                      </button>
                    </li>
                  </template>
                </ul>
              </div>
            </div>

            <!-- Menu Items -->
            <div class="flex flex-col px-3 py-2">
              <a href="{{ route('Profile') }}" class="flex items-center gap-2 hover:bg-layer px-3 py-1.5 rounded-xl transition">
                <i class="fa-regular fa-user text-muted text-sm"></i>
                <span class="text-sm">Profile</span>
              </a>
              <a href="{{ route('PROFILE.EDIT') }}" class="flex items-center gap-2 hover:bg-layer px-3 py-1.5 rounded-xl transition">
                <i class="fa-solid fa-gear text-muted text-sm"></i>
                <span class="text-sm">Settings</span>
              </a>
            </div>

            <!-- Logout -->
            <div class="border-t border-ui px-3 py-2">
              <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full flex items-center gap-2 hover:bg-layer px-3 py-1.5 rounded-xl text-sm transition">
                  <i class="fa-solid fa-arrow-right-from-bracket text-sm"></i>
                  <span>Logout</span>
                </button>
              </form>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
</header>



    <div class="flex h-screen overflow-hidden pt-[80px] p-2">
        {{-- LEFT LIST --}}
        <aside class="w-1/4 border border-zinc-700 p-5 me-5 overflow-y-auto rounded-3xl">
            <h2 class="text-2xl font-bold mb-1">Groove Messenger</h2>
            <p class="text-sm text-fg-2 mb-6">Chat with other users</p>

            @forelse($users as $user)
                @php
                    $chatUserId = $user->client_id ?? $user->coach_id;
                    $roleType   = $user instanceof \App\Models\Client ? 'client' : 'coach';
                    $userStatus = ucfirst($user->status ?? 'Offline');
                @endphp

                <a href="{{ route('messages.index', ['with_id' => $chatUserId, 'with_type' => $roleType]) }}"
                   class="flex items-center gap-3 p-3 mb-2 rounded-xl transition border border-zinc-700 {{ request('with_id') == $chatUserId ? 'bg-purple-700/20' : '' }}">
                    {{-- Avatar + Status Bubble --}}
                    <div x-data="{ status: '{{ $userStatus }}' }" class="relative w-10 h-10">
                        @if($user->photo)
                            <img src="{{ asset('storage/' . $user->photo) }}"
                                 alt="User Photo"
                                 class="w-10 h-10 rounded-full object-cover border-2 border-purple-500 shadow-sm">
                        @else
                            <div class="w-10 h-10 rounded-full bg-neutral-600 flex items-center justify-center font-bold">
                                {{ strtoupper(substr($user->firstname, 0, 1)) }}{{ strtoupper(substr($user->lastname, 0, 1)) }}
                            </div>
                        @endif

                        <div class="absolute bottom-0 right-0 w-2.5 h-2.5 rounded-full border-2 border-zinc-800 z-10"
                             :class="{
                                'bg-green-500': status === 'Online',
                                'bg-yellow-400': status === 'Away',
                                'bg-red-500': status === 'Busy',
                                'bg-zinc-500': status === 'Offline'
                             }">
                        </div>
                    </div>

                    <div>
                        <p class="text-sm font-semibold">{{ $user->firstname }} {{ $user->lastname }}</p>
                        <p class="text-xs capitalize">{{ $roleType }}</p>
                    </div>
                </a>
            @empty
                <p class="text-sm text-zinc-500">No users available.</p>
            @endforelse
        </aside>

        {{-- RIGHT: Chat --}}
        <main class="flex-1 flex flex-col border border-zinc-700 rounded-3xl overflow-hidden">
            {{-- Chat Header --}}
   <div class="p-4 border-b border-zinc-800 bg-zinc-900 flex items-center min-h-[70px] rounded-t-3xl">
    @if($chatWith)
        @php
            $chatPhoto = $chatWith->photo ?? null;
            // detect status and assign correct color
            $status = strtolower($chatWith->status ?? 'offline');
            $statusColors = [
                'active'  => 'bg-green-500',
                'online'  => 'bg-green-500', // keep 'online' for backward compatibility
                'away'    => 'bg-yellow-400',
                'busy'    => 'bg-red-500',
                'offline' => 'bg-zinc-500',
            ];
            $statusColor = $statusColors[$status] ?? 'bg-zinc-500';
        @endphp

        <div class="relative">
            @if(!empty($chatPhoto))
                <img src="{{ asset('storage/' . $chatPhoto) }}"
                     alt="User Photo"
                     class="w-10 h-10 rounded-full object-cover border-2 border-zinc-600 shadow-sm">
            @else
                <div class="w-10 h-10 bg-neutral-700 rounded-full flex items-center justify-center text-white font-bold">
                    {{ strtoupper(substr($chatWith->firstname ?? 'U', 0, 1)) }}{{ strtoupper(substr($chatWith->lastname ?? '', 0, 1)) }}
                </div>
            @endif
            <!-- Status dot -->
            <div class="absolute bottom-0 right-0 w-3 h-3 rounded-full border-2 border-zinc-900 {{ $statusColor }}"></div>
        </div>

        <div class="ml-3 w-full flex justify-between items-center">
            <div class="flex flex-col">
                <p class="text-base font-semibold text-gray-200">
                    {{ trim(($chatWith->firstname ?? '').' '.($chatWith->lastname ?? '')) ?: ($chatWith instanceof \App\Models\Coach ? 'Coach' : 'Client') }}
                </p>
                <p class="text-xs text-zinc-400">
                    {{ $chatWith instanceof \App\Models\Coach ? 'Coach' : 'Client' }}
                </p>
            </div>
            <!-- Status badge -->
            <span class="px-2 py-0.5 text-xs rounded-md capitalize {{ $statusColor }}">
                {{ $status }}
            </span>
        </div>
    @else
        <p class="text-zinc-400 text-sm">Select a user to start chatting</p>
    @endif
</div>


         @if($chatWith && ($chatWith instanceof \App\Models\Coach) && in_array(strtolower($chatWith->status ?? 'offline'), ['offline','busy','away']))
            {{-- Assistant (only when coach is not actively available) --}}
         <div id="chatbot-container"
     x-data="{
       typing:false,
       suggestions:[
         'What are your specialties?',
         'When is your next available slot?',
         'How much do sessions cost?',
         'Any pre-session tips?'
       ],
       input:'',
       send(){ $dispatch('submit-chat'); },
     }"
     data-coach-id="{{ $chatWith->coach_id }}"
     class="flex-1 flex min-h-0 flex-col p-4">


                <div class="flex items-center justify-between px-4 py-3 rounded-2xl bg-zinc-900/80 backdrop-blur border border-zinc-800">
                    <div>
                        <h2 class="text-base font-semibold text-zinc-100">Groove Assistant</h2>
                        <p class="text-xs text-zinc-400">Ask about skills, availability, rates, and prep tips.</p>
                    </div>
                    <span class="inline-flex items-center gap-2 rounded-full border border-zinc-700 bg-zinc-800 px-3 py-1 text-xs text-zinc-200">
                      <span class="w-2.5 h-2.5 rounded-full {{ $statusColor }}"></span>
                      {{ ucfirst($status) }}
                    </span>
                </div>

                <!-- Suggestions -->
                <div class="mt-3 flex flex-wrap gap-2">
                    <template x-for="(s, i) in suggestions" :key="i">
                        <button type="button" @click="input=s; $nextTick(()=>{$refs.msg?.focus()})"
                                class="px-3 py-1.5 rounded-full text-xs border border-zinc-700/80 text-zinc-300 hover:text-white hover:border-zinc-600 bg-zinc-900/60">
                            <span x-text="s"></span>
                        </button>
                    </template>
                </div>

                <!-- Chat body -->
                <div class="mt-3 flex-1 min-h-0 rounded-2xl border border-zinc-800 bg-zinc-950/50 overflow-hidden flex flex-col">
                    <div id="chat" class="flex-1 min-h-0 overflow-y-auto p-4 pb-28 space-y-3 custom-scrollbar"></div>

                    <!-- Composer -->
                    <div class="border-t border-zinc-800 bg-zinc-900/80 backdrop-blur px-3 py-2 sticky bottom-0">
                        <form id="chat-form" @submit.prevent="send" class="flex items-end gap-2">
                            <label for="message" class="sr-only">Message</label>
                            <div class="flex-1">
                                <div class="flex items-center gap-2 rounded-2xl border border-zinc-700 bg-card   focus-within:ring-2 focus-within:ring-purple-500/40">
                                    <input x-model="input" x-ref="msg" id="message" name="message" type="text"
                                           placeholder="Type your question…"
                                           class="flex-1 px-4 py-3 bg-transparent outline-none text-zinc-100 placeholder-zinc-500" required>
                                </div>
                            </div>
                            <button type="submit"
                                    class="inline-flex items-center gap-2 rounded-2xl bg-card hover:to-purple-600 text-fg px-4 py-3 shadow-lg shadow-purple-900/30">
                                <span class="hidden sm:inline">Send</span>
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5">
                                    <path d="m5 12 14-7-7 14-1.5-5.5L5 12z"/>
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @else
            <!-- Normal Chat Messages -->
<div class="flex-1 overflow-y-auto p-6 space-y-5 custom-scrollbar" id="chat-container" x-data>
    @forelse($messages as $message)
        @php
            $isSender = ($message->sender_id == $authId && get_class($authUser) === $message->sender_type);
            $extension = $message->media_path ? strtolower(pathinfo($message->media_path, PATHINFO_EXTENSION)) : null;
            $isImage = in_array($extension, ['jpg','jpeg','png','gif']);
            $isVideo = in_array($extension, ['mp4','mov','avi','webm']);
            $isAudio = in_array($extension, ['mp3','wav','ogg']);
            $isPdf   = $extension === 'pdf';
        @endphp

        <div class="flex justify-{{ $isSender ? 'end' : 'start' }} items-start space-x-2 relative">

            <!-- Message Bubble -->
            <div class="max-w-[65vw] px-4 py-3 text-sm
                {{ $isSender
                    ? 'backdrop-blur-md rounded-tr-2xl rounded-tl-2xl rounded-bl-2xl shadow-lg border border-zinc-700'
                    : 'backdrop-blur-md rounded-tr-2xl rounded-tl-2xl rounded-br-2xl shadow-md border border-zinc-700'
                }} break-words">

                {{-- Text --}}
                @if ($message->message)
                    <p class="break-words">{{ $message->message }}</p>
                @endif

                {{-- Media / Attachment --}}
                @if($message->media_path)
                    <div class="mt-1">
                        @if($isImage)
                            <img src="{{ asset('storage/' . $message->media_path) }}"
                                 alt="Sent image"
                                 class="rounded max-h-60 max-w-[250px] object-cover shadow-xs">
                        @elseif($isVideo)
                            <video controls class="rounded max-h-60 max-w-[250px] object-cover shadow-xs">
                                <source src="{{ asset('storage/' . $message->media_path) }}" type="video/{{ $extension }}">
                                Your browser does not support the video tag.
                            </video>
                        @elseif($isAudio)
                            <audio controls class="w-full mt-2">
                                <source src="{{ asset('storage/' . $message->media_path) }}" type="audio/{{ $extension }}">
                                Your browser does not support the audio element.
                            </audio>
                        @elseif($isPdf)
                            <button type="button"
                                    @click="$dispatch('open-agreement', { url: @js(asset('storage/'.$message->media_path)) })"
                                    class="flex items-center gap-2 px-4 py-2 bg-neutral-700 text-white font-semibold rounded-lg shadow-sm hover:bg-neutral-600 hover:shadow-md transition">
                                📄 Agreement Form
                            </button>
                        @else
                            <a href="{{ asset('storage/' . $message->media_path) }}" download
                               class="flex items-center gap-2 px-3 py-2 rounded-xl shadow transition max-w-[65vw] border border-zinc-700">
                                📎 Download File
                            </a>
                        @endif
                    </div>
                @endif

                {{-- Location --}}
                @if($message->location_url)
                    <div class="mt-2">
                        <a href="{{ $message->location_url }}" target="_blank"
                           class="text-[10px] underline block mt-1 transition">
                           Open in Google Maps <i class="fa-solid fa-location-dot"></i>
                        </a>
                    </div>
                @endif

                {{-- Timestamp --}}
                <div class="text-[9px] text-right mt-1 opacity-70">
                    {{ $message->created_at->format('g:i A') }}
                    @if($message->edited_at)
                        (edited)
                    @endif
                </div>
            </div>

            {{-- 3-dots dropdown for sender --}}
            @if($isSender)
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open" class="transition p-1 rounded-full">
                    <i class="fa-solid fa-ellipsis-vertical"></i>
                </button>

                <div x-show="open" @click.outside="open = false"
                     x-transition
                     class="absolute right-0 mt-5 w-32 border border-zinc-700 bg-neutral-500 shadow-lg z-50 rounded-xl">
                    <div class="flex flex-col items-center text-[9px] opacity-70 p-2">
                        <span class="mb-1 h-[50%]">
                            {{ $message->created_at->format('g:i A') }}
                            @if($message->edited_at)
                                (edited)
                            @endif
                        </span>

                        <form method="POST" action="{{ route('messages.destroy', $message->id) }}" class="w-full">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                class="w-full text-left px-4 py-2 text-sm transition flex items-center gap-2">
                               Unsend
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @endif
        </div>
    @empty
        <div class="flex justify-center items-center h-full">
            <p class="text-lg opacity-70">No messages yet. Start the conversation!</p>
        </div>
    @endforelse
</div>

@if($chatWith)
{{-- Composer + Agreement --}}
<div x-data="Object.assign(messagingFeatures(), coachAgreementModal())" x-init="init()" x-cloak>
    <form method="POST" action="{{ route('messages.send') }}" enctype="multipart/form-data"
          class="p-3 border-t border-zinc-700 flex flex-col gap-2 rounded-b-3xl relative">
        @csrf
        <input type="hidden" name="receiver_id" value="{{ $chatWith->client_id ?? $chatWith->coach_id }}">
        <input type="hidden" name="receiver_type" value="{{ $chatWith instanceof \App\Models\Client ? 'client' : 'coach' }}">
        <input type="hidden" name="location_url" x-bind:value="locationUrl">

        <div class="w-full max-w-[140vh] rounded-2xl border border-zinc-700 flex flex-col">
            {{-- previews --}}
            <div class="flex gap-3 flex-wrap px-3 pt-3 pb-2"
                 :class="(mediaPreviews.length > 0 || locationUrl) ? 'border-b border-zinc-700 mb-2' : ''">
                <template x-for="(media, index) in mediaPreviews" :key="index">
                    <div class="relative">
                        <img x-show="media.isImage" :src="media.url" class="h-24 w-24 rounded-lg object-cover shadow-md">
                        <video x-show="!media.isImage" :src="media.url" class="h-24 w-24 rounded-lg object-cover shadow-md" controls></video>
                        <button type="button" @click="removeMedia(index)"
                                class="absolute -top-2 -right-2 text-xs rounded-full w-5 h-5 flex items-center justify-center shadow border border-zinc-700">
                            ✕
                        </button>
                    </div>
                </template>

                <template x-if="locationUrl">
                    <a :href="locationUrl" target="_blank"
                       class="px-3 py-2 border border-zinc-700 text-sm rounded-lg shadow transition">
                        📍 Location Selected
                    </a>
                </template>
            </div>

            {{-- input row --}}
            <div class="flex items-center gap-3 px-3 py-2 rounded-xl">
                {{-- Location --}}
                <div class="relative flex items-center justify-center group">
                    <button type="button" @click="openLocationPicker"
                            class="w-10 h-10 flex items-center justify-center rounded-full shadow-sm hover:shadow-md transition-all duration-200">
                        <i class="fa-solid fa-map-pin text-lg"></i>
                    </button>
                    <div class="absolute bottom-full mb-2 px-2 py-1 rounded text-xs whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none border border-zinc-700">
                        Open location picker
                    </div>
                </div>

                {{-- Upload --}}
                <div class="relative flex items-center justify-center group">
                    <label class="w-10 h-10 flex items-center justify-center rounded-full cursor-pointer transition relative 0">
                        <i class="fa-solid fa-cloud-arrow-up text-lg"></i>
                        <input type="file" name="media[]" class="hidden" multiple @change="handleMediaUpload">
                        <div class="absolute bottom-full mb-2 px-2 py-1 rounded text-xs whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none border border-zinc-700">
                            Upload media (images/videos/PDFs)
                        </div>
                    </label>
                </div>

                {{-- Agreement --}}
        <div class="relative flex items-center justify-center group">
  <button
    type="button"
    @click="$dispatch('open-agreement', { url: '' })"
    class="relative flex items-center gap-2 px-4 py-2 font-semibold rounded-lg shadow-sm hover:shadow-md transition"
  >
                        <!-- PDF Icon -->
                        <svg xmlns="http://www.w3.org/2000/svg"
                             viewBox="0 0 64 64"
                             role="img"
                             aria-labelledby="pdfIconTitle"
                             class="h-6 w-6">
                          <title id="pdfIconTitle">PDF document</title>
                          <defs>
                            <linearGradient id="paperShadow" x1="0" y1="0" x2="0" y2="1">
                              <stop offset="0" stop-color="#ffffff"/>
                              <stop offset="1" stop-color="#f0f3f8"/>
                            </linearGradient>
                          </defs>
                          <rect x="10" y="4" width="36" height="56" rx="4" ry="4"
                                fill="url(#paperShadow)" stroke="#d3dae6" stroke-width="1.2"/>
                          <path d="M46 18L34 6v10a2 2 0 0 0 2 2z"
                                fill="#e6ecf5" stroke="#d3dae6" stroke-width="1.2"/>
                          <g>
                            <path d="M30 42h24a4 4 0 0 1 4 4v10a4 4 0 0 1-4 4H30a4 4 0 0 1-4-4V46a4 4 0 0 1 4-4z"
                                  fill="#e11d2e"/>
                            <path d="M30 42h24a4 4 0 0 1 4 4v10a4 4 0 0 1-4 4H30a4 4 0 0 1-4-4V46a4 4 0 0 1 4-4z"
                                  fill="none" stroke="#b91c1c" stroke-width="1.2"/>
                            <text x="32" y="56"
                                  font-size="14"
                                  font-weight="800"
                                  fill="#ffffff"
                                  letter-spacing=".5"
                                  dominant-baseline="middle">PDF</text>
                          </g>
                          <g stroke="#d6dbe6" stroke-linecap="round">
                            <line x1="16" y1="26" x2="40" y2="26" stroke-width="1.4"/>
                            <line x1="16" y1="32" x2="40" y2="32" stroke-width="1.4"/>
                            <line x1="16" y1="38" x2="26" y2="38" stroke-width="1.4"/>
                          </g>
                        </svg>
                        <div class="absolute bottom-full mb-2 px-2 py-1 rounded text-xs whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none border border-zinc-700">
                          View Agreement PDF
                        </div>
                    </button>
                </div>

                {{-- Text --}}
                <input type="text" name="message" x-model="message" placeholder="Type a message..."
                       class="flex-1 h-10 bg-transparent text-sm placeholder:opacity-60 focus:outline-none px-3" />

                {{-- Send --}}
                <button type="submit"
                        class="w-10 h-10 flex items-center justify-center rounded-full transition border border-zinc-700">
                    <i class="fa-solid fa-paper-plane text-lg"></i>
                </button>
            </div>
        </div>
    </form>

<div
  x-data="coachAgreementModal()"
  x-init="initFromServer()"
  x-show="openAgreementModal"
  @open-agreement.window="pdfUrl = ($event.detail?.url || ''); openAgreementModal = true"
  x-transition.opacity
  class="fixed inset-0 z-[100] flex items-center justify-center"
  role="dialog" aria-modal="true"
>
  <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="openAgreementModal = false"></div>

  <div
    @click.away="openAgreementModal = false"
    class="relative w-[92%] md:w-5/6 lg:w-2/3 max-h-[90vh] overflow-y-auto rounded-3xl border border-zinc-700 shadow-2xl bg-gradient-to-b from-slate-50 to-slate-100 text-slate-900"
  >
    {{-- Header --}}
    <div class="sticky top-0 z-10 flex items-center justify-between px-5 py-4
                bg-gradient-to-r from-white/90 to-slate-100/90 backdrop-blur
                border-b border-zinc-200/70 rounded-t-3xl">
      <div class="flex items-center gap-2">
        <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-indigo-100 text-indigo-600">
          <i class="fa-regular fa-file-lines text-[13px]"></i>
        </span>
        <h2 class="text-base md:text-lg font-semibold tracking-tight">Agreement</h2>
      </div>
      <button
        @click="openAgreementModal = false"
        class="grid h-9 w-9 place-items-center rounded-full border border-zinc-300/80 text-zinc-700 hover:bg-zinc-100 active:scale-95 transition"
        aria-label="Close"
      >
        <i class="fa-solid fa-xmark text-lg"></i>
      </button>
    </div>

    {{-- Body --}}
    <div class="px-4 md:px-8 py-6 text-[15px] leading-6">
      <h3 class="text-center font-extrabold tracking-wide text-slate-800 text-xl md:text-2xl">
        {{ strtoupper($coach->role) }} AGREEMENT CONTRACT
      </h3>
      <div class="mx-auto mt-2 h-px w-24 rounded bg-zinc-300"></div>

      {{-- Meta strip --}}
      <div class="mt-6 grid gap-3 sm:grid-cols-3">
        <div class="rounded-xl bg-white border border-zinc-200 px-4 py-3 shadow-sm">
          <div class="text-xs uppercase tracking-wide text-zinc-500">Coach</div>
          <div class="mt-0.5 font-medium">
            {{ $coach->firstname }} {{ $coach->middlename }} {{ $coach->lastname }}
          </div>
        </div>
        <div class="rounded-xl bg-white border border-zinc-200 px-4 py-3 shadow-sm">
          <div class="text-xs uppercase tracking-wide text-zinc-500">Client</div>
          <div class="mt-0.5 font-medium">
            {{ trim(($chatWith->firstname ?? '') . ' ' . ($chatWith->middlename ?? '') . ' ' . ($chatWith->lastname ?? '')) ?: ($client_firstname ?? '______________________') }}
          </div>
        </div>
        <div class="rounded-xl bg-white border border-zinc-200 px-4 py-3 shadow-sm">
          <div class="text-xs uppercase tracking-wide text-zinc-500">Date</div>
          <div class="mt-0.5 font-medium">
            {{ $date ?? now()->format('F d, Y') }}
          </div>
        </div>
      </div>

      {{-- Content --}}
      <div class="mt-6 rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm">
        <p class="font-semibold text-slate-800 mb-3">Purpose of Agreement</p>
        <div class="prose prose-sm max-w-none text-slate-700">
          <p>
            This Agreement outlines the terms under which the <strong>{{ $coach->role }}</strong> will provide professional services to the Client.
            It is designed to ensure clarity, safety, mutual understanding, and accountability in the coaching relationship.
            The {{ $coach->role }} agrees to provide a safe, supportive, and professional environment,
            to maintain the confidentiality of all client communications, and to deliver services to the best of their ability within the agreed scope.
          </p>

          <p>
            The Client agrees to attend scheduled sessions on time, communicate openly and honestly, and take full responsibility for their own decisions, actions, and results.
            The services provided under this Agreement include: <strong>{{ $coach->talents ?? '__________' }}</strong>.
            The service fee is ₱<strong>{{ $coach->service_fee ?? '______' }}</strong> per session or package.
            Each session will last for <strong>{{ $coach->duration ?? '______' }}</strong>.
            Payment will be made by <strong>{{ $coach->payment ?? '__________' }}</strong>. Payment must be made in advance of the session or package unless otherwise agreed.
          </p>

          <p>
            All sessions must be scheduled in advance. A minimum notice of
            <strong>{{ $coach->notice_hours ?? 0 }}</strong> hours /
            <strong>{{ $coach->notice_days ?? 0 }}</strong> days
            is required for cancellations or rescheduling. Notice of cancellation must be provided by <strong>{{ $coach->method ?? '__________' }}</strong>.
            If the Client cancels on the same day of the scheduled session, the Client agrees to pay <strong>25% of the service fee</strong>.
            Failure to provide any notice (“no show”) will result in the session being charged in full.
          </p>

          <p class="mb-0">
            By entering into this Agreement, both the {{ $coach->role }} and the Client acknowledge their shared commitment to a respectful, professional, and productive working relationship.
            This Agreement is intended to protect both parties and ensure that services are delivered with integrity and accountability.
            By signing below, both parties confirm that they have read, understood, and agreed to the terms outlined in this Agreement.
          </p>
        </div>
      </div>

      {{-- Signatures --}}
      <div class="mt-6 grid gap-6 md:grid-cols-2">
        {{-- Coach Signature --}}
        <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm">

          <div class="flex items-center justify-between">
            <div class="font-semibold">Choreographer/Coach Signature</div>
            @auth('coach')
            <button
              type="button"
              @click="toggleCoachSign()"
              class="inline-flex items-center gap-1 text-xs px-3 py-1.5 rounded-full border border-zinc-300 hover:bg-zinc-100"
            >
              <i class="fa-regular fa-pen-to-square text-[11px]"></i>
              <span x-text="coachSignEnabled ? 'Use Stored' : 'Sign Now'"></span>
            </button>
            @endauth
          </div>

          {{-- Always-visible stored/current signature --}}
          <div class="mt-3">
            @php
              $hasStoredCoachSig = $coach->signature && file_exists(public_path('storage/'.$coach->signature));
              $storedCoachSigUrl = $hasStoredCoachSig ? asset('storage/'.$coach->signature) : null;
            @endphp

            <div class="rounded-xl border border-dashed border-zinc-300 bg-zinc-50/60 p-3 h-40 grid place-items-center">
              <template x-if="currentCoachSignatureUrl">
                <img :src="currentCoachSignatureUrl" alt="Coach Signature" class="h-24 object-contain" x-ref="coachSignatureImg">
              </template>
              <template x-if="!currentCoachSignatureUrl">
                <span class="text-zinc-500 text-sm">No stored signature</span>
              </template>
            </div>

            {{-- Hidden inputs to post base64/new signature back to server if needed --}}
            <input type="hidden" name="coach_signature_data" x-ref="coachSignatureInput">
            <input type="hidden" name="coach_signature_data_forward" x-ref="coachSignatureInputForward">
          </div>

          {{-- Drawing area (toggle visibility only) --}}
          @auth('coach')
          <div class="mt-4" x-show="coachSignEnabled" x-transition>
            <div class="rounded-xl border border-zinc-300 bg-white p-3 shadow-inner">
              <canvas x-ref="coachCanvas" class="w-full h-40" style="touch-action:none;"></canvas>
            </div>
            <p x-ref="coachSignatureError" class="text-red-600 text-xs mt-1 hidden">Signature is required.</p>
            <div class="mt-2 flex justify-end gap-2">
              <button type="button" @click="clearCoachSignature()" class="px-3 py-1.5 text-sm rounded-lg border border-zinc-300 hover:bg-zinc-100">
                Clear
              </button>
              <button type="button" @click="saveCoachSignature()" class="px-3 py-1.5 text-sm rounded-lg bg-emerald-600 text-white hover:bg-emerald-700 shadow">
                Save Signature
              </button>
            </div>
          </div>
          @endauth
        </div>

        {{-- Date --}}
        <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm">
          <div class="font-semibold">Date</div>
          <div class="mt-3 h-10 rounded-lg border border-dashed border-zinc-300 bg-zinc-50 grid place-items-center px-3">
            <span class="w-full text-center text-sm text-zinc-700">{{ $date ?? now()->format('F d, Y') }}</span>
          </div>
        </div>
      </div>

      {{-- Client signature (read-only if stored) --}}
      <div class="mt-6 grid gap-6 md:grid-cols-2">
        <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm">
          <div class="font-semibold">Client Signature</div>
          <div class="mt-3">
            @php
              $clientSigPath = optional($agreement)->client_signature;
              $hasClientSig = $clientSigPath && file_exists(public_path('storage/'.$clientSigPath));
            @endphp
            @if($hasClientSig)
              <img src="{{ asset('storage/'.optional($agreement)->client_signature) }}" alt="Client Signature" class="h-24 object-contain">
            @else
              <div class="h-10 rounded-lg border border-dashed border-zinc-300 bg-zinc-50"></div>
            @endif
          </div>
        </div>
        <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm">
          <div class="font-semibold">Date</div>
          <div class="mt-3 h-10 rounded-lg border border-dashed border-zinc-300 bg-zinc-50 grid place-items-center px-3">
            <span class="w-full text-center text-sm text-zinc-700">
              {{ optional($agreement)->agreement_date ? \Carbon\Carbon::parse($agreement->agreement_date)->format('F d, Y') : '' }}
            </span>
          </div>
        </div>
      </div>
    </div>


        {{-- Footer --}}
        <div class="sticky bottom-0 z-10 flex items-center justify-end gap-3 px-5 py-4
                    bg-gradient-to-r from-white/90 to-slate-100/90 backdrop-blur
                    border-t border-zinc-200/70 rounded-b-3xl">
          <button
            @click="openAgreementModal = false"
            class="px-5 py-2 rounded-full font-medium border border-zinc-300 hover:bg-zinc-100 active:scale-95 transition"
          >
            Close
          </button>

          <button
            type="button"
            @click="submitMessageForm()"
            class="px-5 py-2 rounded-full font-semibold bg-indigo-600 text-white shadow hover:bg-indigo-700 active:scale-95 transition"
          >
            Send Agreement
          </button>

          {{-- Hidden Form (message send) --}}
          <form x-ref="agreementForm" method="POST" action="{{ route('messages.send') }}">
            @csrf
            <input type="hidden" name="receiver_id" value="{{ $chatWith->client_id ?? $chatWith->coach_id }}">
            <input type="hidden" name="receiver_type" value="{{ $chatWith instanceof \App\Models\Client ? 'client' : 'coach' }}">
            <input type="hidden" name="message" value="Sent Agreement PDF / Terms">
            <input type="hidden" name="agreement" value="1">
            {{-- signature gets injected here when saved --}}
            <input type="hidden" name="coach_signature_data" x-ref="coachSignatureInputForward">
          </form>
        </div>
      </div>
    </div>


</div>
@endif
@endif
</main>
</div>

<script src="https://js.pusher.com/7.2/pusher.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/laravel-echo/1.11.3/echo.iife.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.1/dist/signature_pad.umd.min.js"></script>
<script>
  /* ===============================
   * Groove Messenger – Frontend JS
   * Alpine helpers + Messaging + Chatbot + Echo
   * =============================== */

  // ---- Agreement Modal + Signature state (Alpine) ----
  function coachAgreementModal() {
    return {
      openAgreementModal: false,
      pdfUrl: '',
      coachSignEnabled: false,
      currentCoachSignatureUrl: {{ isset($storedCoachSigUrl) ? json_encode($storedCoachSigUrl) : 'null' }},
      signaturePad: null,

      initFromServer() {
        // Placeholder: preload anything from server if needed
      },

      init() {
        // Optional: run when component mounts
      },

      toggleCoachSign() {
        this.coachSignEnabled = !this.coachSignEnabled;
        if (this.coachSignEnabled) {
          this.$nextTick(() => {
            const canvas = this.$refs.coachCanvas;
            if (!canvas) return;

            // HiDPI scaling for crisp strokes
            const ratio = Math.max(window.devicePixelRatio || 1, 1);
            canvas.width  = canvas.offsetWidth * ratio;
            canvas.height = canvas.offsetHeight * ratio;
            const ctx = canvas.getContext('2d');
            ctx.scale(ratio, ratio);

            this.signaturePad = new SignaturePad(canvas, { minWidth: 0.9, maxWidth: 2.5 });
          });
        }
      },

      clearCoachSignature() {
        this.signaturePad?.clear();
      },

      saveCoachSignature() {
        if (!this.signaturePad || this.signaturePad.isEmpty()) {
          this.$refs.coachSignatureError?.classList.remove('hidden');
          return;
        }
        this.$refs.coachSignatureError?.classList.add('hidden');

        const dataUrl = this.signaturePad.toDataURL('image/png');

        // Show in persistent preview block
        this.currentCoachSignatureUrl = dataUrl;

        // Stash on main composer form (visible section)
        if (this.$refs.coachSignatureInput)        this.$refs.coachSignatureInput.value = dataUrl;
        // Stash on hidden forward-only form (footer "Send Agreement")
        if (this.$refs.coachSignatureInputForward) this.$refs.coachSignatureInputForward.value = dataUrl;

        this.coachSignEnabled = false;
      },

      submitMessageForm() {
        // Forward signature from visible form to hidden footer form (safety)
        if (this.$refs.coachSignatureInputForward && this.$refs.coachSignatureInput) {
          this.$refs.coachSignatureInputForward.value = this.$refs.coachSignatureInput.value || '';
        }
        this.$refs.agreementForm?.submit();
        this.openAgreementModal = false;
      }
    }
  }

  // ---- Messaging features (media upload previews + location picker) ----
  function messagingFeatures() {
    return {
      // bound in the composer
      message: '',
      mediaPreviews: [],       // [{ url, isImage, isVideo, isAudio, isPdf, name, size, type }]
      locationUrl: '',

      // config
      maxFiles: 8,
      maxFileSizeMB: 50,
      // Images / videos / audio / pdf (others will still upload, but won’t preview)
      imageTypes: ['image/jpeg','image/png','image/gif','image/webp'],
      videoTypes: ['video/mp4','video/webm','video/ogg','video/quicktime','video/x-msvideo'],
      audioTypes: ['audio/mpeg','audio/ogg','audio/wav','audio/webm'],
      pdfType: 'application/pdf',

      init() {
        // no-op for now
      },

      handleMediaUpload(e) {
        const input = e.target;
        const files = Array.from(input.files || []);
        if (!files.length) return;

        // cap total selected files
        if (this.mediaPreviews.length + files.length > this.maxFiles) {
          alert(`You can attach up to ${this.maxFiles} files.`);
        }

        const remainingSlots = Math.max(0, this.maxFiles - this.mediaPreviews.length);
        const slice = files.slice(0, remainingSlots);

        slice.forEach(file => {
          const tooBig = file.size > this.maxFileSizeMB * 1024 * 1024;
          if (tooBig) {
            alert(`${file.name} is larger than ${this.maxFileSizeMB}MB and was skipped.`);
            return;
          }

          const isImage = this.imageTypes.includes(file.type);
          const isVideo = this.videoTypes.includes(file.type);
          const isAudio = this.audioTypes.includes(file.type);
          const isPdf   = file.type === this.pdfType || /\.pdf$/i.test(file.name);

          // Only images/videos get a visual preview; audio/pdf/others show as chips (no inline image)
          if (isImage || isVideo) {
            const reader = new FileReader();
            reader.onload = (ev) => {
              this.mediaPreviews.push({
                url: ev.target.result,
                isImage, isVideo, isAudio, isPdf,
                name: file.name,
                size: file.size,
                type: file.type
              });
            };
            reader.readAsDataURL(file);
          } else {
            this.mediaPreviews.push({
              url: '',
              isImage, isVideo, isAudio, isPdf,
              name: file.name,
              size: file.size,
              type: file.type
            });
          }
        });

        // Keep the underlying FileList as-is for form POST
        // If you want to allow re-selecting the same file immediately:
        // input.value = '';
      },

      removeMedia(index) {
        this.mediaPreviews.splice(index, 1);

        // If you need 1:1 sync with <input type="file">, rebuild FileList:
        /*
        const fileInput = document.querySelector('input[type="file"][name="media[]"]');
        if (fileInput && fileInput.files?.length) {
          const dt = new DataTransfer();
          Array.from(fileInput.files).forEach((f, i) => { if (i !== index) dt.items.add(f); });
          fileInput.files = dt.files;
        }
        */
      },

      openLocationPicker() {
        if (!navigator.geolocation) {
          alert("Geolocation is not supported by your browser.");
          return;
        }
        navigator.geolocation.getCurrentPosition(
          pos => {
            const lat = pos.coords.latitude.toFixed(6);
            const lng = pos.coords.longitude.toFixed(6);
            this.locationUrl = `https://www.google.com/maps?q=${lat},${lng}`;
            // reflect to hidden input
            const hidden = document.querySelector('input[name="location_url"]');
            if (hidden) hidden.value = this.locationUrl;
            alert("Location added! You can now send it.");
          },
          err => {
            console.error(err);
            alert("Unable to fetch your location.");
          },
          { enableHighAccuracy: true, timeout: 12000, maximumAge: 0 }
        );
      },
    }
  }

  // ---- Profile status store (Alpine) ----
  document.addEventListener('alpine:init', () => {
    Alpine.store('profile', {
      open: false,
      openStatus: false,
      status: @json($currentStatus ?? 'Offline'),
      async changeStatus(newStatus) {
        this.status = newStatus;
        this.openStatus = false;

        try {
          const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
          const res = await fetch('{{ route('coach.updateStatus') }}', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': token,
              'Accept': 'application/json'
            },
            body: JSON.stringify({ status: newStatus })
          });
          const data = await res.json();
          if (!res.ok || !data.success) throw new Error(data.error || 'Failed to update status');
        } catch (e) {
          console.error(e);
          // Roll back UI if server fails
          this.status = @json($currentStatus ?? 'Offline');
          alert('Could not update status. Please try again.');
        }
      }
    });
  });

  // ---- Header state helper (Alpine) ----
  function headerState() {
    return {
      scrolled: false,
      init() {
        this.scrolled = window.scrollY > 10;
        window.addEventListener('scroll', () => { this.scrolled = window.scrollY > 10; }, { passive: true });
      }
    }
  }

  // === Config / CSRF ===
  const API_URL = "{{ route('chatbot.send') }}";
  (function ensureAxiosCsrf(){
    if (window.axios) {
      const m = document.querySelector('meta[name="csrf-token"]');
      if (m) axios.defaults.headers.common['X-CSRF-TOKEN'] = m.getAttribute('content');
    }
  })();

  // === Typography polish (safe if already defined elsewhere) ===
  function polish(t) {
    if (!t) return t;
    return t
      .replace(/(^|[\s([{<])"/g,'$1“').replace(/"/g,'”')
      .replace(/(^|[\s([{<])'/g,'$1‘').replace(/'/g,'’')
      .replace(/\s--\s/g,' — ')
      .replace(/\s+([,.;:!?])/g,'$1')
      .replace(/([—–])\s*/g,' $1 ')
      .replace(/\s{2,}/g,' ')
      .trim();
  }

  // === Chat UI helpers ===
  function escapeHTML(s) {
    return String(s)
      .replace(/&/g,"&amp;").replace(/</g,"&lt;")
      .replace(/>/g,"&gt;").replace(/"/g,"&quot;")
      .replace(/'/g,"&#039;");
  }
  function addBubble(chatEl, role, html, { pending=false } = {}) {
    const wrap = document.createElement('div');
    const isUser = role === 'user';
    wrap.className = [
      'max-w-[85%] rounded-2xl px-4 py-3 leading-relaxed',
      isUser ? 'ml-auto bg-purple-600/20 border border-purple-700 text-zinc-100'
             : 'mr-auto bg-zinc-900/70 border border-zinc-800 text-zinc-100 prose prose-invert prose-sm'
    ].join(' ');
    if (pending) {
      wrap.dataset.typing = '1';
      wrap.innerHTML = '<span class="inline-flex items-center gap-2 text-zinc-300"><span class="relative flex h-2 w-2"><span class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-75"></span><span class="relative inline-flex rounded-full h-2 w-2"></span></span><span>Typing…</span></span>';
    } else {
      wrap.innerHTML = html;
    }
    const row = document.createElement('div');
    row.className = 'flex';
    row.appendChild(wrap);
    chatEl.appendChild(row);
    chatEl.scrollTop = chatEl.scrollHeight;
    return wrap;
  }
  function replaceTypingBubble(chatEl, el, html) {
    if (!el) return;
    delete el.dataset.typing;
    el.innerHTML = html;
    chatEl.scrollTop = chatEl.scrollHeight;
  }

  // === Chatbot wiring (Coach) ===
  (function setupCoachChatbot() {
    const container = document.getElementById('chatbot-container');
    if (!container) return; // no chatbot in current view

    const chatEl  = document.getElementById('chat');
    const formEl  = document.getElementById('chat-form');
    const inputEl = formEl?.querySelector('input[name="message"]');

    // Resolve coach id from either data attribute
    let coachId = container.getAttribute('data-coach-id') || null;
    if (!coachId) {
      try {
        const coachObj = JSON.parse(container.getAttribute('data-coach') || 'null');
        coachId = coachObj?.coach_id || coachObj?.id || null;
      } catch (_) {}
    }

    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    let sending = false, lastSentAt = 0;

    async function sendMessage(message) {
      const now = Date.now();
      if (sending) return;
      if (now - lastSentAt < 2500) return; // flood control
      sending = true;
      lastSentAt = now;

      addBubble(chatEl, 'user', `<div>${escapeHTML(message)}</div>`);
      const typing = addBubble(chatEl, 'assistant', '', { pending: true });

      inputEl.disabled = true;
      const submitBtn = formEl?.querySelector('button[type="submit"]');
      if (submitBtn) submitBtn.disabled = true;

      try {
        const res = await fetch(API_URL, {
          method: 'POST',
          headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrf
          },
          body: JSON.stringify({ message, coach_id: coachId })
        });
        const data = await res.json().catch(() => ({}));
        if (!res.ok) throw new Error(data?.error || res.statusText || 'Request failed');

        const raw  = data?.answer || 'No response received.';
        const html = (window.marked ? marked.parse(polish(raw)) : escapeHTML(raw));
        replaceTypingBubble(chatEl, typing, html);
      } catch (err) {
        const safe = escapeHTML(err.message || String(err));
        replaceTypingBubble(chatEl, typing, `<div>Sorry, something went wrong.<br><br><strong>Details:</strong> ${safe}</div>`);
      } finally {
        sending = false;
        inputEl.disabled = false;
        const submitBtn2 = formEl?.querySelector('button[type="submit"]');
        if (submitBtn2) submitBtn2.disabled = false;
        inputEl.value = '';
        inputEl.focus();
      }
    }

    // Event hooks
    container.addEventListener('submit-chat', () => {
      const msg = (inputEl?.value || '').trim();
      if (!msg) return;
      sendMessage(msg);
    });
    formEl?.addEventListener('submit', e => {
      e.preventDefault();
      const msg = (inputEl?.value || '').trim();
      if (!msg) return;
      sendMessage(msg);
    });
    inputEl?.addEventListener('keydown', e => {
      if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        const msg = inputEl.value.trim();
        if (msg) sendMessage(msg);
      }
    });
  })();

  // === Optional: Echo autoscroll (keeps thread pinned to latest) ===
  (function setupEcho(){
    const loggedInId = {{ $loggedInId ?? 'null' }};
    if (!loggedInId || typeof Echo === 'undefined' || typeof Pusher === 'undefined') return;

    window.Pusher = window.Pusher || Pusher;
    window.Echo   = window.Echo   || new Echo({
      broadcaster: 'pusher',
      key: '{{ env("PUSHER_APP_KEY") }}',
      cluster: '{{ env("PUSHER_APP_CLUSTER") }}',
      forceTLS: true
    });

    window.Echo.private(`chat.${loggedInId}`).listen('.MessageSent', () => {
      const chatContainer = document.getElementById('chat') || document.getElementById('chat-container');
      if (chatContainer) chatContainer.scrollTop = chatContainer.scrollHeight;
    });
  })();
</script>

</body>
</html>