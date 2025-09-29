{{-- resources/views/Coach/Messenger.blade.php --}}
@php
    // Auth context (safe)
    $authUser  = $authUser ?? null; // from controller
    $authRole  = $authRole ?? 'client';
    
    $authId    = $authRole === 'client' ? ($authUser->client_id ?? null) : ($authUser->coach_id ?? null);
    $authType  = $authRole === 'client' ? 'client' : 'coach';

    // Use $current for header/avatar to avoid null errors (works for client or coach)
    $current   = $authUser;
    $currentId = $authRole === 'client' ? ($current->client_id ?? null) : ($current->coach_id ?? null);
    $currentStatus = ucfirst($current->status ?? 'Offline');


    // Notifications
    $unreadNotifications = $authUser ? $authUser->unreadNotifications : collect();
@endphp

<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Groove Messenger</title>
<meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Vite (Tailwind + App JS) --}}
@vite(['resources/css/app.css', 'resources/js/app.js'])

    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    {{-- Axios --}}
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

    {{-- Icons --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>

  <link rel="icon" href="/image/bg/LOG.png" type="image/png" sizes="512x512">

    <style>
        [x-cloak]{display:none !important;}
        .custom-scrollbar { scrollbar-width: thin; scrollbar-color: #6b7280 transparent; }
        .custom-scrollbar:hover { scrollbar-color:#71717a transparent; }
        .custom-scrollbar::-webkit-scrollbar{ width:8px; height:8px; }
        .custom-scrollbar::-webkit-scrollbar-track{ background:transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb{ background:#6b7280; border-radius:6px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover{ background:#4b5563; }
    </style>

    
</head>

<body class="min-h-screen antialiased theme-{{ $appTheme }} bg-surface text-foreground">
<header
    x-data="{ scrolled: false }"
    x-init="window.addEventListener('scroll', () => { scrolled = window.scrollY > 10 })"
    class="w-full py-4 px-8 fixed top-0 left-0 z-50 transition duration-300"
    :class="scrolled
        ? 'bg-zinc-800/70 backdrop-blur-sm shadow-lg shadow-purple-900/30 border-b border-zinc-700'
        : 'bg-transparent'">
    <div class="flex justify-between items-center max-w-7xl mx-auto">
        <!-- Logo -->
        <div class="flex items-center gap-3">
            <img src="/image/bg/LOG.png" alt="Logo" class="h-12 w-auto object-contain select-none" />
        </div>

        <!-- Nav -->
        <nav class="flex space-x-4 text-sm font-medium">
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
           class="relative  border border-divider/50 shadow-inner px-4 py-2 rounded-xl text-foreground/70 hover:text-foreground hover:bg-layer hover:border hover:border-divider/40 hover:shadow-md transition-all duration-300">
          Messages
        </a>
        </nav>

        <!-- Right: Notifications + Profile -->
        <div class="flex items-center gap-4">
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
            <!-- Profile w/ status (works for client or coach) -->
            <div
                x-data="{
                    open: false,
                    openStatus: false,
                    status: '{{ $currentStatus }}',
                    changeStatus(newStatus) {
                        this.status = newStatus;
                        this.openStatus = false;
                        updateUserStatus('{{ $authRole }}', newStatus);
                    }
                }"
                class="relative">
                <button @click="open = !open"
                        class="flex items-center gap-x-3 px-3 py-2 bg-card text-fg rounded-full
                               hover:shadow-[0_4px_12px_rgba(147,51,234,0.5)] transition duration-200 focus:outline-none">

                    <div class="relative w-8 h-8">
                        @if ($current && !empty($current->photo))
                            <img src="{{ asset('storage/' . $current->photo) }}" alt="Avatar"
                                 class="w-8 h-8 rounded-full object-cover border border-neutral-400 shadow-sm">
                        @else
                            <div class="w-8 h-8 flex items-center justify-center bg-card rounded-full text-sm font-bold uppercase text-fg border border-neutral-400 shadow-sm">
                                {{ strtoupper(substr(($current->firstname ?? 'U'), 0, 1)) }}
                            </div>
                        @endif
                        <div class="absolute bottom-0 right-0 w-2.5 h-2.5 rounded-full border-2 border-zinc-800 z-10"
                             :class="{
                               'bg-green-500': status === 'Online',
                               'bg-yellow-400': status === 'Away',
                               'bg-red-500': status === 'Busy',
                               'bg-zinc-500': status === 'Offline'
                             }"></div>
                    </div>

                    <div class="flex items-center space-x-3 text-xs leading-none">
                        <span class="capitalize">
                            {{ strtolower($current->firstname ?? ($authRole === 'coach' ? 'coach' : 'client')) }}
                            {{ strtolower($current->middlename ?? '') }}
                        </span>
                        <i class="fa-solid fa-caret-down"></i>
                    </div>
                </button>

                <div x-show="open" @click.away="open = false" x-transition
                     class="absolute mt-2 w-60 rounded-xl bg-card ring-1 ring-purple-700/10 z-50 text-fg">
                    <!-- Info -->
                    <div class="px-4 py-3 bg-card backdrop-blur-sm border-b border-zinc-700 text-center rounded-t-xl">
                        <p class="text-sm font-semibold">
                            {{ $current->firstname ?? 'User' }} {{ $current->middlename ?? '' }}
                        </p>
                        <p class="text-xs mt-0.5">
                            #{{ $currentId ?? '0000' }} • {{ ucfirst($authRole) }}
                        </p>
                    </div>

                    <!-- Menu -->
                    <div class="flex flex-col px-3 py-2 space-y-1">
                        <a href="{{ route('profile') }}"
                           class="flex items-center gap-2  px-3 py-1.5 rounded-md transition">
                            <i class="fa-regular fa-user text-neutral-400 text-sm"></i>
                            <span class="text-sm">Profile</span>
                        </a>

                           <a href="/client/profile/EDIT"
                 class="flex items-center gap-2 hover:bg-layer px-3 py-1.5 rounded-xl transition">
                <i class="fa-solid fa-gear opacity-70 text-sm"></i><span class="text-sm">Settings</span>
              </a>


                        <!-- Status (only show toggle if role has endpoint; we'll allow both, endpoint picks based on role) -->
                        <div class="relative">
                            <button @click="openStatus = !openStatus"
                                    class="flex items-center justify-between w-full px-3 py-1.5 text-sm rounded-md transition">
                                <div class="flex items-center gap-2">
                                    <i class="fa-solid fa-circle text-xs"
                                       :class="{
                                         'text-green-400': status === 'Online',
                                         'text-yellow-400': status === 'Away',
                                         'text-red-500': status === 'Busy',
                                         'text-zinc-500': status === 'Offline'
                                       }"></i>
                                    <span x-text="status"></span>
                                </div>
                                <i class="fa-solid fa-chevron-down text-xs ml-auto"></i>
                            </button>

                            <div x-show="openStatus" x-transition @click.away="openStatus = false"
                                 class="absolute top-full left-0 mt-1 w-full bg-card border text-fg border-zinc-700 rounded-lg shadow-xl z-50">
                                <ul class="text-sm  py-1">
                                    <template x-for="opt in ['Online','Busy','Away','Offline']" :key="opt">
                                        <li>
                                            <button @click="changeStatus(opt)"
                                                    class="w-full px-4 py-2 text-left  flex items-center gap-2  hover:bg-layer hover:border hover:border-divider/40 p-2 rounded-lg">
                                                <i class="fa-solid fa-circle text-xs "
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
                    </div>

                    <!-- Logout -->
                    <div class="border-t border-zinc-700 px-3 py-2 rounded-b-xl">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                    class="w-full flex items-center gap-2 text-red-500 hover:text-red-600 hover:bg-red-600/10 px-3 py-1.5 rounded-xl text-sm transition">
                                <i class="fa-solid fa-arrow-right-from-bracket text-sm"></i>
                                <span>Logout</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div> <!-- /right -->
    </div>
</header>

<div class="flex h-screen overflow-hidden pt-[80px] p-2" x-data="{ openAgreementModal: false }">
    <!-- Sidebar: Users -->
    <aside class="w-1/4 bg-card border border-zinc-700 p-5 me-5 overflow-y-auto rounded-3xl custom-scrollbar">
        <h2 class="text-2xl font-bold text-fg mb-1">Groove Messenger</h2>
        <p class="text-sm text-fg-2 mb-6">Chat with other users</p>

        @forelse($users as $user)
            @php
                $chatUserId = $user->client_id ?? $user->coach_id ?? $user->getKey();
                $roleType   = $user instanceof \App\Models\Client ? 'client' : 'coach';
                $userStatus = ucfirst($user->status ?? 'Offline');
            @endphp

            <a href="{{ route('messages.index', ['with_id' => $chatUserId, 'with_type' => $roleType]) }}"
               class="flex items-center gap-3 p-3 mb-2 rounded-xl bg-card border border-divider/50 shadow-inner transition bg-card-1 hover:bg-neutral-800/20 {{ request('with_id') == $chatUserId ? 'bg-purple-700/20' : '' }}">
                <!-- Avatar + Status -->
                <div x-data="{ status: '{{ $userStatus }}' }" class="relative w-10 h-10">
                    @if(!empty($user->photo))
                        <img src="{{ asset('storage/' . $user->photo) }}"
                             alt="User Photo"
                             class="w-10 h-10 rounded-full object-cover border-2 border-purple-500 shadow-sm">
                    @else
                        <div class="w-10 h-10 rounded-full bg-card flex items-center justify-center text-fg font-bold">
                            {{ strtoupper(substr($user->firstname ?? 'U', 0, 1)) }} {{ strtoupper(substr($user->lastname ?? '', 0, 1)) }}
                        </div>
                    @endif
                    <div class="absolute bottom-0 right-0 w-2.5 h-2.5 rounded-full border-2 border-zinc-800 z-10"
                         :class="{
                           'bg-green-500': status === 'Online',
                           'bg-yellow-400': status === 'Away',
                           'bg-red-500': status === 'Busy',
                           'bg-zinc-500': status === 'Offline'
                         }"></div>
                </div>

                <div>
                    <p class="text-sm t font-semibold">{{ trim(($user->firstname ?? '').' '.($user->lastname ?? '')) ?: ucfirst($roleType) }}</p>
                    <p class="text-xs text-fg-2 capitalize">{{ $roleType }}</p>
                </div>
            </a>
        @empty
            <p class="text-sm text-fg-2">No users available.</p>
        @endforelse
    </aside>

    <!-- Main: Chat -->
    <main class="flex-1 flex flex-col bg-card border border-zinc-700 rounded-3xl overflow-hidden">
        <!-- Chat Header -->
     <div class="p-4 border-b border-zinc-800 bg-card flex items-center min-h-[70px] rounded-t-3xl">
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
                <div class="w-10 h-10 bg-neutral-700 rounded-full flex items-center justify-center text-fg font-bold">
                    {{ strtoupper(substr($chatWith->firstname ?? 'U', 0, 1)) }}{{ strtoupper(substr($chatWith->lastname ?? '', 0, 1)) }}
                </div>
            @endif
            <!-- Status dot -->
            <div class="absolute bottom-0 right-0 w-3 h-3 rounded-full border-2 border-zinc-900 {{ $statusColor }}"></div>
        </div>

        <div class="ml-3 w-full flex justify-between items-center">
            <div class="flex flex-col">
                <p class="text-base font-semibold text-fg">
                    {{ trim(($chatWith->firstname ?? '').' '.($chatWith->lastname ?? '')) ?: ($chatWith instanceof \App\Models\Coach ? 'Coach' : 'Client') }}
                </p>
                <p class="text-xs text-zinc-400">
                    {{ $chatWith instanceof \App\Models\Coach ? 'Coach' : 'Client' }}
                </p>
            </div>
            <!-- Status badge -->
            <span class="px-2 py-0.5 text-xs  rounded-md capitalize {{ $statusColor }}">
                {{ $status }}
            </span>
        </div>
    @else
        <p class="text-fg-2 text-sm">Select a user to start chatting</p>
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


                <div class="flex items-center justify-between px-4 py-3 rounded-2xl bg-fg backdrop-blur border">
                    <div>
                        <h2 class="text-base font-semibold text-fg">Groove Assistant</h2>
                        <p class="text-xs text-fg-2">Ask about skills, availability, rates, and prep tips.</p>
                    </div>
                    <span class="inline-flex items-center gap-2 rounded-full border border-zinc-700 bg-card px-3 py-1 text-xs text-fg">
                      <span class="w-2.5 h-2.5 rounded-full {{ $statusColor }}"></span>
                      {{ ucfirst($status) }}
                    </span>
                </div>

                <!-- Suggestions -->
                <div class="mt-3 flex flex-wrap gap-2">
                    <template x-for="(s, i) in suggestions" :key="i">
                        <button type="button" @click="input=s; $nextTick(()=>{$refs.msg?.focus()})"
                                class="px-3 py-1.5 rounded-full text-xs border border-zinc-700/80 text-fg hover:text-fg-2 hover:border-zinc-600 bg-zinc-900/60">
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
                                           class="flex-1 px-4 py-3 bg-transparent outline-none text-fg placeholder-zinc-500" required>
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
                <!-- Chat form -->
                <form method="POST" action="{{ route('messages.send') }}" enctype="multipart/form-data"
                      x-data="messagingFeatures()" x-init="init()"
                      class="p-3 border-t border-zinc-800 flex flex-col gap-2 rounded-b-3xl relative">
                    @csrf
                    <input type="hidden" name="receiver_id" value="{{ $chatWith->client_id ?? $chatWith->coach_id ?? $chatWith->getKey() }}">
                    <input type="hidden" name="receiver_type" value="{{ $chatWith instanceof \App\Models\Client ? 'client' : 'coach' }}">
                    <input type="hidden" name="location_url" :value="locationUrl">

                    <!-- Media preview -->
                    <div class="flex gap-2 mb-2 overflow-x-auto px-3 pb-1 custom-scrollbar" x-show="mediaPreview.length > 0" x-cloak>
                        <template x-for="(file, index) in mediaPreview" :key="index">
                            <div class="relative flex-shrink-0 w-20 h-20 border border-neutral-600 rounded overflow-hidden">
                                <img x-show="file.type.startsWith('image')" :src="file.url" class="object-cover w-full h-full">
                                <video x-show="file.type.startsWith('video')" :src="file.url" class="object-cover w-full h-full" muted></video>
                                <button type="button" @click="removeMedia(index)"
                                        class="absolute top-1 right-1 bg-red-600 text-fg text-xs rounded-full w-5 h-5 flex items-center justify-center">
                                    ✕
                                </button>
                            </div>
                        </template>
                    </div>

                    <!-- Input row -->
                    <div class="w-full rounded-2xl border border-zinc-700">
                        <div class="flex items-center gap-3 px-3 py-2 rounded-xl">

                            <!-- Location -->
                            <div class="relative flex items-center justify-center">
                                <button type="button" @click="openLocationPicker"
                                        class="w-10 h-10 flex items-center justify-center rounded-full text-fg hover:bg-neutral-800 transition">
                                    <i class="fa-solid fa-map-pin text-lg"></i>
                                </button>
                            </div>

                            <!-- Media upload -->
                            <div class="relative flex items-center justify-center">
                                <label class="w-10 h-10 flex items-center justify-center rounded-full text-fg hover:bg-neutral-700/50 cursor-pointer transition">
                                    <i class="fa-solid fa-cloud-arrow-up text-lg"></i>
                                    <input type="file" name="media[]" class="hidden" multiple accept="image/*,video/*"
                                           @change="previewMedia($event)">
                                </label>
                            </div>


                            <!-- Text + location preview -->
                            <div class="flex-1 flex flex-col">
                                <input type="text" name="message" placeholder="Type a message..."
                                       class="h-10 bg-transparent text-sm text-fg placeholder:text-zinc-500 focus:outline-none px-3 w-full"/>
                                <template x-if="locationUrl">
                                    <a :href="locationUrl" target="_blank"
                                       class="text-[11px] text-blue-400 underline mt-1 px-3 hover:text-blue-300 truncate">
                                        📍 Location ready: <span x-text="locationUrl"></span>
                                    </a>
                                </template>
                            </div>

                            <!-- Send -->
                            <button type="submit"
                                    class="w-10 h-10 flex items-center justify-center rounded-full text-fg hover:bg-neutral-700/50 transition">
                                <i class="fa-solid fa-paper-plane text-lg"></i>
                            </button>
                        </div>
                    </div>
                </form>
            @endif
        @endif
    </main>


{{-- MODAL: Agreement --}}
<div
  x-data="clientAgreementModal(@js([
      'hasExistingClientSignature' => (bool) optional($agreement)->client_signature,
      'existingClientSignatureUrl' => optional($agreement)->client_signature && file_exists(public_path('storage/' . optional($agreement)->client_signature)) ? asset('storage/' . optional($agreement)->client_signature) : ''
  ]))"
  x-trap.noscroll="openAgreementModal"
  x-show="openAgreementModal"
  x-cloak
  @keydown.escape.window="openAgreementModal = false"
  class="fixed inset-0 z-50"
  aria-live="polite"
  aria-busy="false"
>
  <!-- Overlay -->
  <div class="absolute inset-0 bg-black/70" x-show="openAgreementModal" x-transition.opacity aria-hidden="true"></div>

  <!-- Dialog -->
  <div
    class="relative mx-auto h-full w-full max-w-4xl p-4 md:p-6 flex items-center justify-center"
    role="dialog"
    aria-modal="true"
    aria-labelledby="agreement-title"
  >
    <div
      class="bg-card w-full max-h-[92vh] overflow-y-auto rounded-2xl shadow-xl ring-1 ring-black/5 print:max-h-full print:shadow-none print:ring-0 custom-scrollbar"
      x-show="openAgreementModal"
      x-transition.scale.origin.center
    >
      <!-- Sticky Header -->
      <div class="sticky top-0 z-10 bg-card backdrop-blur supports-[backdrop-filter]:bg-white/75 border-b border-zinc-100">
        <div class="flex items-center justify-between px-5 py-4">
          <h2 id="agreement-title" class="text-gray-900 text-base md:text-lg font-semibold uppercase tracking-wide">
            Agreement
          </h2>
          <button
            type="button"
            @click="openAgreementModal = false"
            class="inline-flex items-center justify-center size-9 rounded-lg hover:bg-zinc-100 text-gray-900 text-xl font-bold transition print:hidden"
            aria-label="Close agreement"
            title="Close"
          >×</button>
        </div>
      </div>

      <!-- Form body -->
      <div class="px-5 md:px-8 pb-6 md:pb-8">

        @php
            use App\Models\Client;
            use App\Models\Coach;
            use Illuminate\Support\Facades\Storage;
            use Illuminate\Support\Str;

            // Partner (may be null)
            $partner = $chatWith ?? null;

            // Receiver fields (null-safe)
            $receiverId = null;
            $receiverType = null;
            if ($partner instanceof Client) {
                $receiverId   = $partner->client_id ?? null;
                $receiverType = 'client';
            } elseif ($partner instanceof Coach) {
                $receiverId   = $partner->coach_id ?? null;
                $receiverType = 'coach';
            }

            // Coach information (null-safe)
            $coachIdForPaths = $partner instanceof Coach ? ($partner->coach_id ?? null) : null;
            $coachRole = $partner->role ?? 'Coach';
            $coachFullName = trim(collect([
                optional($partner)->firstname,
                optional($partner)->middlename,
                optional($partner)->lastname,
            ])->filter()->implode(' '));

            // Dates
            $agreementDateStr = optional($agreement)->agreement_date
                ? \Carbon\Carbon::parse($agreement->agreement_date)->format('F d, Y')
                : now()->format('F d, Y');

            // Signature resolver
            $resolveSignature = function ($value) {
                if (!$value || !is_string($value)) return null;

                // data URL or absolute URL
                if (Str::startsWith($value, ['data:image', 'http://', 'https://', '//'])) {
                    return $value;
                }

                // normalize "storage/..." or leading "/"
                $rel = ltrim($value, '/');
                if (Str::startsWith($rel, 'storage/')) {
                    $rel = Str::after($rel, 'storage/');
                }

                // public disk first
                if (Storage::disk('public')->exists($rel)) {
                    return Storage::url($rel); // "/storage/{rel}"
                }

                // physical file under public/storage
                $abs = public_path('storage/' . $rel);
                if (file_exists($abs)) {
                    return asset('storage/' . $rel);
                }

                return null;
            };

            // Coach signature: agreement -> profile
            $coachSigSrc = $resolveSignature(optional($agreement)->coach_signature)
                        ?: $resolveSignature(optional($partner)->signature);
        @endphp

        <!-- Hidden fields inside the modal (local-only; we’ll forward to the hidden POST form) -->
        <input type="hidden" name="coach_id" value="{{ $coachIdForPaths ?? '' }}">
        <input type="hidden" name="client_signature_data" x-ref="clientSignatureInput">

        <!-- Header / Parties -->
        <div class="text-center mt-6">
          <h3 class="font-extrabold text-2xl text-black md:text-3xl underline tracking-wide">
            {{ strtoupper($coachRole) }} AGREEMENT CONTRACT
          </h3>
        </div>

        <!-- Parties & Date panel -->
        <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4 md:gap-6">
          <div class="rounded-xl border border-zinc-200 p-4">
            <p class="text-xs font-medium text-zinc-500 uppercase">Coach</p>
            <p class="mt-1 font-semibold text-zinc-900">{{ $coachFullName }}</p>
            <p class="text-sm text-zinc-600">
              Role: <span class="font-medium">{{ $coachRole }}</span>
            </p>
          </div>
          <div class="rounded-xl border border-zinc-200 p-4">
            <p class="text-xs font-medium text-zinc-500 uppercase">Client</p>
            <p class="mt-1 font-semibold text-zinc-900">
              {{ trim(($authUser->firstname ?? '') . ' ' . ($authUser->middlename ?? '') . ' ' . ($authUser->lastname ?? '')) ?: '______________________' }}
            </p>
          </div>
          <div class="rounded-xl border border-zinc-200 p-4">
            <p class="text-xs font-medium text-zinc-500 uppercase">Date</p>
            <p class="mt-1 font-semibold text-zinc-900">{{ $agreementDateStr }}</p>
          </div>
        </div>

        <!-- Agreement Content -->
        <div class="mt-8 prose prose-sm max-w-none text-slate-700">
          <p>
            This Agreement outlines the terms under which the
            <strong>{{ $coachRole }}</strong>
            will provide professional services to the Client. It is designed to ensure clarity, safety, mutual
            understanding, and accountability in the coaching relationship. The {{ $coachRole }} agrees
            to provide a safe, supportive, and professional environment, to maintain the confidentiality of all client
            communications, and to deliver services to the best of their ability within the agreed scope.
          </p><br>

          <p>
            The Client agrees to attend scheduled sessions on time, communicate openly and honestly, and take full
            responsibility for their own decisions, actions, and results. The services provided under this Agreement
            include: <strong>{{ $chatWith->talents ?? '__________' }}</strong>. The service fee is ₱<strong>{{ $chatWith->service_fee ?? '______' }}</strong> per session or package. Each session will last for
            <strong>{{ $chatWith->duration ?? '______' }}</strong>. Payment will be made by
            <strong>{{ $chatWith->payment ?? '__________' }}</strong>. Payment must be made in advance of the session or package unless otherwise agreed.
          </p><br>

          <p>
            All sessions must be scheduled in advance. A minimum notice of
            <strong>{{ $chatWith->notice_hours ?? 0 }}</strong> hours /
            <strong>{{ $chatWith->notice_days ?? 0 }}</strong> days
            is required for cancellations or rescheduling. Notice of cancellation must be provided by
            <strong>{{ $chatWith->method ?? '__________' }}</strong>. If the Client cancels on the same day of the scheduled
            session, the Client agrees to pay <strong>25% of the service fee</strong>. Failure to provide any notice (“no show”)
            will result in the session being charged in full.
          </p><br>

          <p class="mb-0">
            By entering into this Agreement, both the {{ $coachRole }} and the Client acknowledge their
            shared commitment to a respectful, professional, and productive working relationship. This Agreement is
            intended to protect both parties and ensure that services are delivered with integrity and accountability.
            By signing below, both parties confirm that they have read, understood, and agreed to the terms outlined in
            this Agreement.
          </p>
        </div>

        <!-- Signatures Section -->
        <div class="mt-10 space-y-8">

          <!-- Coach Signature + Date -->
          <div class="rounded-2xl border border-zinc-200 p-4 md:p-6">
            <h4 class="text-sm font-semibold text-zinc-700 tracking-wide uppercase">Coach Signature</h4>
            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <div class="min-h-16 flex items-center">
                  @if($coachSigSrc)
                    <img src="{{ $coachSigSrc }}" alt="Coach Signature" class="h-16 object-contain">
                  @else
                    <div class="border-b border-black h-10 w-full"></div>
                  @endif
                </div>
                <p class="mt-2 text-xs text-zinc-500">
                  Signed by:
                  <span class="font-medium text-zinc-700">{{ $coachFullName }}</span>
                </p>
              </div>
              <div>
                <p class="font-medium text-zinc-800">Date</p>
                <div class="border-b border-black mt-2 h-10 flex items-center px-2 text-zinc-800">
                  {{ $agreementDateStr }}
                </div>
              </div>
            </div>
          </div>

          <!-- Client Signature + Date -->
          <div class="rounded-2xl border border-emerald-200 p-4 md:p-6">
            <div class="flex items-center justify-between gap-4">
              <h4 class="text-sm font-semibold text-emerald-800 tracking-wide uppercase">Client Signature</h4>

              <!-- Actions -->
              <div class="print:hidden flex items-center gap-2">
                <button
                  type="button"
                  x-show="hasSignature && !editing"
                  @click="startEditing()"
                  class="px-3 py-1.5 text-xs rounded-md border border-zinc-300 hover:bg-zinc-100"
                >Edit signature</button>

                <template x-if="editing">
                  <div class="flex items-center gap-2">
                    <button
                      type="button"
                      @click="clearClientSignature()"
                      class="px-3 py-1.5 text-xs rounded-md border border-zinc-300 hover:bg-zinc-100"
                    >Clear</button>
                    <button
                      type="button"
                      @click="saveClientSignature()"
                      class="px-3 py-1.5 text-xs rounded-md bg-emerald-600 text-fg hover:bg-emerald-700"
                    >Save Signature</button>
                  </div>
                </template>
              </div>
            </div>

            {{-- Existing stored signature preview --}}
            @if(optional($agreement)->client_signature && file_exists(public_path('storage/' . optional($agreement)->client_signature)))
              <img src="{{ asset('storage/' . optional($agreement)->client_signature) }}" alt="Client Signature" class="mt-3 h-16 object-contain">
            @endif

            {{-- Draw Canvas (hidden when hasSignature && !editing) --}}
            <div class="mt-3 rounded-lg border border-zinc-300 bg-card p-2 shadow-inner"
                 x-show="!hasSignature || editing"
                 x-transition>
              <canvas x-ref="clientCanvas" class="w-full h-40 touch-none" style="touch-action:none;" aria-label="Signature canvas"></canvas>
            </div>

            {{-- Live Preview after Save (data URL) --}}
            <img x-ref="clientSignaturePreview" class="mt-3 border rounded-lg w-full hidden" alt="Client Signature Preview">

            <p x-ref="clientSignatureError" class="text-red-600 text-xs mt-2 hidden" role="alert">
              Signature is required.
            </p>

            <!-- Client Date -->
            <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
              <div class="hidden md:block"></div>
              <div>
                <p class="font-medium text-zinc-800">Date</p>
                <div class="border-b border-black mt-2 h-10 flex items-center px-2 text-zinc-800">
                  {{ optional($agreement)->agreement_date ? \Carbon\Carbon::parse($agreement->agreement_date)->format('F d, Y') : '' }}
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div class="mt-8 flex items-center justify-end gap-3 print:hidden">
          <button
            type="button"
            @click="openAgreementModal = false"
            class="px-4 py-2 rounded-xl border border-zinc-300 text-zinc-700 hover:bg-zinc-50"
          >Cancel</button>
          <button
            type="button"
            @click="submitIfValid()"
            class="px-4 py-2 rounded-xl bg-emerald-600 text-fg hover:bg-emerald-700"
            x-bind:disabled="{{ $receiverId ? 'false' : 'true' }}"
            title="{{ $receiverId ? '' : 'Select a conversation first' }}"
          >I Agree & Sign</button>
        </div>
      </div>

      <!-- Hidden Form (message send) -->
      <form x-ref="agreementForm" method="POST" action="{{ route('messages.send') }}" class="hidden">
        @csrf
        <input type="hidden" name="receiver_id"   value="{{ $receiverId ?? '' }}">
        <input type="hidden" name="receiver_type" value="{{ $receiverType ?? '' }}">
        <input type="hidden" name="message"       value="Sent Agreement PDF / Terms">
        <input type="hidden" name="agreement"     value="1">

        {{-- Forwarded signatures (filled at runtime) --}}
        <input type="hidden" name="client_signature_data" x-ref="clientSignatureInputForward">
        <input type="hidden" name="coach_signature_data"  x-ref="coachSignatureInputForward">
      </form>
    </div>
  </div>
</div>





</div>

{{-- --- deps (load once per page) --- --}}
<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
<script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.1/dist/signature_pad.umd.min.js"></script>
<script src="https://js.pusher.com/7.2/pusher.min.js"></script>


<script src="https://cdnjs.cloudflare.com/ajax/libs/laravel-echo/1.11.3/echo.iife.min.js"></script>
<script>
const API_URL = "{{ route('chatbot.send') }}";
if (window.axios) {
  const m = document.querySelector('meta[name="csrf-token"]');
  if (m) axios.defaults.headers.common['X-CSRF-TOKEN'] = m.getAttribute('content');
}
function updateClientStatus(newStatus) {
  fetch('/client/status/update', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    },
    body: JSON.stringify({ status: newStatus })
  })
  .then(r => r.ok ? r.json() : Promise.reject('Failed to update status'))
  .then(data => console.log('Status updated:', data.status))
  .catch(err => console.error('Error updating status:', err));
}
function messagingFeatures() {
  return {
    mediaPreview: [],
    pdfUrl: '',
    openAgreementModal: false,
    locationUrl: '',
    init() {},
    previewMedia(e) {
      this.mediaPreview = [];
      for (let file of e.target.files) {
        const reader = new FileReader();
        reader.onload = ev => { this.mediaPreview.push({ name: file.name, type: file.type, url: ev.target.result }); };
        reader.readAsDataURL(file);
      }
    },
    removeMedia(i) { this.mediaPreview.splice(i, 1); },
    openLocationPicker() {
      if (!navigator.geolocation) { alert("Geolocation is not supported by your browser."); return; }
      navigator.geolocation.getCurrentPosition(pos => {
        const lat = pos.coords.latitude, lng = pos.coords.longitude;
        this.locationUrl = `https://www.google.com/maps?q=${lat},${lng}`;
        const input = document.querySelector('input[name="location_url"]');
        if (input) input.value = this.locationUrl;
        alert("Location added! You can now send it.");
      }, () => { alert("Unable to fetch your location."); });
    },
  }
}
function polish(t) {
  if (!t) return t;
  return t.replace(/(^|[\s([{<])"/g,'$1“').replace(/"/g,'”').replace(/(^|[\s([{<])'/g,'$1‘').replace(/'/g,'’').replace(/\s--\s/g,' — ').replace(/\s+([,.;:!?])/g,'$1').replace(/([—–])\s*/g,' $1 ').replace(/\s{2,}/g,' ').trim();
}
(function setupChatbot() {
  const container = document.getElementById('chatbot-container');
  if (!container) return;
  const chatEl = document.getElementById('chat');
  const formEl = document.getElementById('chat-form');
  const inputEl = formEl?.querySelector('input[name="message"]');
  const coachId = container.getAttribute('data-coach-id');
  const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
  function addBubble(role, html, { pending=false } = {}) {
    const wrap = document.createElement('div');
    const isUser = role === 'user';
    wrap.className = ['max-w-[85%] rounded-2xl px-4 py-3 leading-relaxed', isUser ? 'ml-auto bg-neutral-300 border  text-fg' : 'mr-auto bg-zinc-400 border border-zinc-800 text-fg prose prose-invert prose-sm'].join(' ');
    if (pending) {
      wrap.dataset.typing = '1';
      wrap.innerHTML = '<span class="inline-flex items-center gap-2 text-fg"><span class="relative flex h-2 w-2"><span class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-75"></span><span class="relative inline-flex rounded-full h-2 w-2"></span></span><span>Typing…</span></span>';
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
  function replaceTypingBubble(el, html) {
    if (!el) return;
    delete el.dataset.typing;
    el.innerHTML = html;
    chatEl.scrollTop = chatEl.scrollHeight;
  }
  let sending = false, lastSentAt = 0;
  function escapeHTML(s) {
    return String(s).replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/"/g,"&quot;").replace(/'/g,"&#039;");
  }
  async function sendMessage(message) {
    const now = Date.now();
    if (sending) return;
    if (now - lastSentAt < 2500) return;
    sending = true;
    lastSentAt = now;
    addBubble('user', `<div>${escapeHTML(message)}</div>`);
    const typing = addBubble('assistant', '', { pending: true });
    inputEl.disabled = true;
    const submitBtn = formEl.querySelector('button[type="submit"]');
    if (submitBtn) submitBtn.disabled = true;
    try {
      const res = await fetch(API_URL, {
        method: 'POST',
        headers: { 'Accept':'application/json', 'Content-Type':'application/json', 'X-CSRF-TOKEN': csrf },
        body: JSON.stringify({ message, coach_id: coachId })
      });
      const data = await res.json();
      if (!res.ok) throw new Error(data?.error || res.statusText);
      const raw = data?.answer || 'No response received.';
      const html = marked.parse(polish(raw));
      replaceTypingBubble(typing, html);
    } catch (err) {
      const safe = escapeHTML(err.message || String(err));
      replaceTypingBubble(typing, `<div>Sorry, something went wrong.<br><br><strong>Details:</strong> ${safe}</div>`);
    } finally {
      sending = false;
      inputEl.disabled = false;
      if (submitBtn) submitBtn.disabled = false;
      inputEl.value = '';
      inputEl.focus();
    }
  }
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
(function setupEcho(){
  const loggedInId = {{ $loggedInId ?? 'null' }};
  if(!loggedInId) return;
  window.Pusher = Pusher;
  window.Echo = new Echo({
    broadcaster: 'pusher',
    key: '{{ env("PUSHER_APP_KEY") }}',
    cluster: '{{ env("PUSHER_APP_CLUSTER") }}',
    forceTLS: true
  });
  window.Echo.private(`chat.${loggedInId}`).listen('.MessageSent', () => {
    const chatContainer = document.getElementById('chat-container');
    if (chatContainer) chatContainer.scrollTop = chatContainer.scrollHeight;
  });
})();
function clientAgreementModal(opts = {}) {
  return {
    pad: null,
    _resizeHandler: null,
    hasSignature: !!opts.hasExistingClientSignature,
    editing: false,
    existingUrl: opts.existingClientSignatureUrl || '',
    init() {
      this.editing = !this.hasSignature;
      this.$watch('openAgreementModal', open => { open ? this.$nextTick(() => this.initPadIfNeeded()) : this.destroyPad(); });
      if (this.openAgreementModal) this.$nextTick(() => this.initPadIfNeeded());
    },
    startEditing() { this.editing = true; this.$nextTick(() => this.initPadIfNeeded(true)); },
    initPadIfNeeded(force = false) { if (!this.editing && !force) return; this.initPad(); },
    initPad() {
      const canvas = this.$refs.clientCanvas;
      if (!canvas) return;
      if (typeof SignaturePad === 'undefined') { console.error('SignaturePad script is not loaded.'); return; }
      if (!this.pad) this.pad = new SignaturePad(canvas, { backgroundColor: 'rgba(255,255,255,1)', penColor: '#000', minWidth: 0.8, maxWidth: 2 });
      const sizeCanvas = (preserve = true) => {
        const ratio = Math.max(window.devicePixelRatio || 1, 1);
        let data = null;
        if (preserve && this.pad && !this.pad.isEmpty()) { try { data = this.pad.toData(); } catch (_) {} }
        const displayWidth  = canvas.clientWidth;
        const displayHeight = canvas.clientHeight;
        canvas.width  = Math.max(1, Math.floor(displayWidth  * ratio));
        canvas.height = Math.max(1, Math.floor(displayHeight * ratio));
        const ctx = canvas.getContext('2d');
        ctx.setTransform(1, 0, 0, 1, 0, 0);
        ctx.scale(ratio, ratio);
        ctx.fillStyle = '#fff';
        ctx.fillRect(0, 0, canvas.width, canvas.height);
        if (this.pad) this.pad.clear();
        if (data && data.length) { try { this.pad.fromData(data); } catch (_) {} }
      };
      sizeCanvas(false);
      let timer = null;
      this._resizeHandler = () => { clearTimeout(timer); timer = setTimeout(() => sizeCanvas(true), 120); };
      window.addEventListener('resize', this._resizeHandler, { passive: true });
      setTimeout(() => sizeCanvas(true), 0);
      canvas.style.touchAction = 'none';
    },
    destroyPad() {
      if (this._resizeHandler) { window.removeEventListener('resize', this._resizeHandler); this._resizeHandler = null; }
      if (this.pad) { try { this.pad.off(); } catch (_) {} this.pad = null; }
      const canvas = this.$refs.clientCanvas;
      if (canvas) { const ctx = canvas.getContext('2d'); ctx && ctx.clearRect(0, 0, canvas.width, canvas.height); }
    },
    clearClientSignature() {
      if (this.pad) this.pad.clear();
      if (this.$refs.clientSignatureInput) this.$refs.clientSignatureInput.value = '';
      if (this.$refs.clientSignatureInputForward) this.$refs.clientSignatureInputForward.value = '';
      if (this.$refs.coachSignatureInputForward)  this.$refs.coachSignatureInputForward.value  = '';
      this.$refs.clientSignatureError?.classList.add('hidden');
      this.$refs.clientSignaturePreview?.classList.add('hidden');
      this.hasSignature = false;
      this.editing = true;
    },
    saveClientSignature() {
      if (!this.pad || this.pad.isEmpty()) { this.$refs.clientSignatureError?.classList.remove('hidden'); return false; }
      const dataURL = this.pad.toDataURL('image/png');
      if (this.$refs.clientSignatureInput) this.$refs.clientSignatureInput.value = dataURL;
      if (this.$refs.clientSignatureInputForward) this.$refs.clientSignatureInputForward.value = dataURL;
      if (this.$refs.clientSignaturePreview) { this.$refs.clientSignaturePreview.src = dataURL; this.$refs.clientSignaturePreview.classList.remove('hidden'); }
      this.$refs.clientSignatureError?.classList.add('hidden');
      this.hasSignature = true;
      this.editing = false;
      return true;
    },
    submitIfValid() {
      if (this.editing) { const ok = this.saveClientSignature(); if (!ok) return; }
      else if (!this.hasSignature) { this.$refs.clientSignatureError?.classList.remove('hidden'); return; }
      if (this.$refs.agreementForm) { this.$refs.agreementForm.submit(); }
      else { console.error('Hidden agreement form not found.'); }
    }
  }
}
</script>



</body>
</html>
