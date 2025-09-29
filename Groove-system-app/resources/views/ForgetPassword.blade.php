<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Groove | Password Reset</title>

  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

  <link rel="icon" href="/image/wc/logo.png" type="image/png" sizes="512x512" />
  <link rel="apple-touch-icon" href="/image/wc/logo.png" sizes="180x180" />

  <style>
    [x-cloak]{display:none!important;}
    .noise{position:fixed;inset:0;pointer-events:none;opacity:.04;background-image:url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="1200" height="600"><filter id="n"><feTurbulence type="fractalNoise" baseFrequency="0.8" numOctaves="2"/></filter><rect width="100%" height="100%" filter="url(%23n)" opacity="0.5"/></svg>');}
    .bg-grid { background:
      radial-gradient(1200px 600px at 50% -20%, rgba(255,255,255,.06), transparent 70%),
      linear-gradient(180deg, rgba(0,0,0,.35), rgba(0,0,0,.65));
    }
    @media (prefers-reduced-motion:no-preference){
      .fade-up{opacity:0;transform:translateY(16px);animation:fadeUp .5s ease-out forwards}
      @keyframes fadeUp{to{opacity:1;transform:translateY(0)}}
    }
  </style>

  @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#0b0d10] bg-grid text-white antialiased overflow-x-hidden relative">
  <div class="noise"></div>

  <main class="min-h-screen flex items-center justify-center px-4 py-10">
    <div class="w-full max-w-xl">
      <!-- Outer Card -->
      <div class="relative rounded-[28px] border border-white/10 bg-white/5 backdrop-blur-xl shadow-[0_20px_80px_-20px_rgba(0,0,0,.8),inset_0_1px_0_rgba(255,255,255,.04)] fade-up">
        <div class="px-8 pt-8 pb-7">
          <!-- LOGO -->
          <div class="flex justify-center">
            <div class="relative">
              <div class="absolute -inset-3 rounded-2xl bg-emerald-400/10 blur-xl"></div>
              <img src="{{ asset('image/bg/LOG.png') }}" alt="Groove Logo" class="relative h-16 w-16 object-contain drop-shadow-[0_6px_24px_rgba(0,0,0,.4)]" />
            </div>
          </div>

          <div class="text-center mt-5 mb-6">
            <h1 class="text-3xl font-extrabold tracking-tight">Password Reset</h1>
            <p class="mt-2 text-sm text-zinc-300/80">Move to your own rhythm.</p>
          </div>

          {{-- Flash --}}
          @if(session('status'))
            <div class="mb-5 rounded-xl border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-emerald-200" role="status" aria-live="polite">
              <div class="flex items-start gap-3">
                <svg class="mt-0.5 h-5 w-5 flex-none" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" d="M10.8 14.7l6.7-6.7 1.4 1.4-8.1 8.1L5.1 11l1.4-1.4 4.3 4.3z"/></svg>
                <p class="text-sm">{{ session('status') }}</p>
              </div>
            </div>
          @endif

          {{-- Errors --}}
          @if($errors->any())
            <div class="mb-5 rounded-xl border border-rose-500/30 bg-rose-500/10 px-4 py-3 text-rose-200" role="alert">
              <div class="flex items-start gap-3">
                <svg class="mt-0.5 h-5 w-5 flex-none" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" d="M12 2l10 18H2L12 2zm0 5a1 1 0 00-1 1v5a1 1 0 002 0V8a1 1 0 00-1-1zm0 10a1.25 1.25 0 110-2.5 1.25 1.25 0 010 2.5z"/></svg>
                <ul class="list-disc space-y-1 pl-5 text-sm">
                  @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                  @endforeach
                </ul>
              </div>
            </div>
          @endif

          {{-- Request reset --}}
          @if(!isset($token) && !isset($users))
            <form method="POST" action="{{ route('ForgetPassword.email') }}" class="space-y-5" x-data="{ loading:false }" @submit="loading=true" novalidate>
              @csrf
              <div>
                <label for="email" class="mb-2 block text-xs font-semibold tracking-wide text-zinc-300/90">EMAIL</label>
                <div class="relative">
                  <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email"
                         class="peer w-full rounded-xl border border-white/10 bg-white/7 px-4 py-3 pr-11 text-base text-fg shadow-inner shadow-black/40 placeholder:text-zinc-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/40" placeholder="y  ou@gmail.com" />
                  <svg class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 h-5 w-5 text-zinc-500 peer-focus:text-zinc-300" viewBox="0 0 24 24" fill="currentColor"><path d="M12 13L2 6.76V18a2 2 0 002 2h16a2 2 0 002-2V6.76L12 13z"/><path d="M22 6H2l10 6 10-6z"/></svg>
                </div>
              </div>

              <button type="submit" :disabled="loading"
                class="group relative inline-flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-zinc-200/15 to-zinc-200/5 px-4 py-3 font-semibold text-fg ring-1 ring-white/10 transition hover:from-zinc-200/25 hover:to-zinc-200/10 disabled:cursor-not-allowed disabled:opacity-60">
                <span>Send reset link</span>
                <svg x-show="!loading" class="h-5 w-5 transition-transform group-hover:translate-x-0.5" viewBox="0 0 24 24" fill="currentColor"><path d="M13 5l7 7-7 7M5 12h14"/></svg>
                <svg x-show="loading" x-cloak class="h-5 w-5 animate-spin" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path></svg>
              </button>
            </form>

          {{-- Reset form --}}
          @elseif(isset($token))
            <form method="POST" action="{{ route('reset-password.save') }}" class="space-y-5" x-data="resetForm()" novalidate>
              @csrf
              <input type="hidden" name="token" value="{{ $token }}" />
              <input type="hidden" name="email" value="{{ old('email', $email ?? '') }}" />
              <input type="hidden" name="user_type" value="{{ $user_type }}" />

              <div>
                <label for="password" class="mb-2 block text-xs font-semibold tracking-wide text-fg">NEW PASSWORD</label>
                <div class="relative">
                  <input id="password" x-model="password" :type="show ? 'text' : 'password'" name="password" required autocomplete="new-password"
                    class="peer w-full rounded-xl border border-white/10 bg-white/7 px-4 py-3 pr-24 text-base text-fg shadow-inner shadow-black/40 placeholder:text-zinc-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/40" placeholder="••••••••" />
                  <button type="button" @click="show = !show" :aria-pressed="show"
                    class="absolute right-2 top-1/2 -translate-y-1/2 rounded-lg px-3 py-1 text-xs font-medium text-fg ring-1 ring-white/10 hover:bg-white/10">
                    <span x-text="show ? 'Hide' : 'Show'"></span>
                  </button>
                </div>
              </div>

              <div>
                <label for="password_confirmation" class="mb-2 block text-xs font-semibold tracking-wide text-zinc-300/90">CONFIRM PASSWORD</label>
                <input id="password_confirmation" x-model="confirm" type="password" name="password_confirmation" required autocomplete="new-password"
                  class="w-full rounded-xl border border-white/10 bg-white/7 px-4 py-3 text-base text-fg shadow-inner shadow-black/40 placeholder:text-zinc-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/40" placeholder="••••••••" />
              </div>

              <div class="space-y-2 rounded-xl border border-white/10 bg-white/5 p-4">
                <div class="flex items-center justify-between text-xs text-fg">
                  <span>Password strength</span>
                  <span class="font-medium text-zinc-200" x-text="label"></span>
                </div>
                <div class="h-2 w-full overflow-hidden rounded-full bg-zinc-800/70">
                  <div class="h-full" :class="barClass" :style="`width:${score*25}%`"></div>
                </div>
                <ul class="grid gap-1 text-sm sm:grid-cols-2">
                  <li :class="password.length >= 8 ? 'text-emerald-400' : 'text-zinc-400'">• At least 8 characters</li>
                  <li :class="/[A-Z]/.test(password) ? 'text-emerald-400' : 'text-zinc-400'">• At least one uppercase</li>
                  <li :class="/[0-9]/.test(password) ? 'text-emerald-400' : 'text-zinc-400'">• At least one number</li>
                  <li :class="/[^A-Za-z0-9]/.test(password) ? 'text-emerald-400' : 'text-zinc-400'">• At least one special</li>
                  <li :class="password && confirm && password === confirm ? 'text-emerald-400' : 'text-zinc-400'" class="sm:col-span-2">• Passwords match</li>
                </ul>
              </div>

              <button type="submit" :disabled="!valid"
                class="group inline-flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-emerald-500/20 to-emerald-400/10 px-4 py-3 font-semibold text-white ring-1 ring-emerald-500/30 transition hover:from-emerald-500/30 hover:to-emerald-400/20 disabled:cursor-not-allowed disabled:opacity-60">
                <span>Reset password</span>
                <svg class="h-5 w-5 transition-transform group-hover:translate-x-0.5" viewBox="0 0 24 24" fill="currentColor"><path d="M13 5l7 7-7 7M5 12h14"/></svg>
              </button>
            </form>
          @endif

          {{-- Multiple matches --}}
          @if(isset($users))
            <div class="mt-2 space-y-3" x-data="accountPicker()" x-id="['email-sent-title']">
              <p class="mb-1 text-sm text-zinc-300/90">
                Multiple accounts found for <span class="font-semibold text-white">{{ $email }}</span>. Select the correct account:
              </p>

              @foreach($users as $user)
                @php
                  $type  = $user instanceof App\Models\Coach ? 'coach' : ($user instanceof App\Models\Client ? 'client' : 'admin');
                  $label = $type === 'coach' ? 'Coach' : ($type === 'client' ? 'Client' : 'Admin');
                  $first = $user->firstname ?? $user->name ?? '';
                  $last  = $user->lastname ?? '';
                  $initials = strtoupper(substr($first,0,1) . substr($last,0,1));
                @endphp

                <button type="button"
                        :disabled="loading"
                        @click="start(email='{{ $user->email }}', type='{{ $type }}', url='{{ route('ForgetPassword.select') }}', token='{{ csrf_token() }}')"
                        class="flex w-full items-center gap-3 rounded-xl border border-white/10 bg-white/7 p-3 text-left transition hover:bg-white/10 disabled:opacity-60 disabled:cursor-not-allowed">
                  <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-white/10 font-semibold">{{ $initials }}</span>
                  <span class="flex-1">
                    <span class="block font-semibold">{{ $first }} {{ $last }}</span>
                    <span class="block text-xs text-zinc-400">{{ $user->email }} • {{ $label }}</span>
                  </span>
                  <svg class="h-5 w-5 text-zinc-400" viewBox="0 0 24 24" fill="currentColor"><path d="M9 5l7 7-7 7"/></svg>
                </button>
              @endforeach

              <!-- DARK / MATCHING MODAL -->
              <div x-cloak x-show="open"
                   @keydown.escape.window="maybeClose()" @click.self="maybeClose()"
                   x-transition:enter="transition ease-out duration-300"
                   x-transition:enter-start="opacity-0"
                   x-transition:enter-end="opacity-100"
                   x-transition:leave="transition ease-in duration-200"
                   x-transition:leave-start="opacity-100"
                   x-transition:leave-end="opacity-0"
                   class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4"
                   role="dialog" aria-modal="true" :aria-labelledby="$id('email-sent-title')">

                <div
                  x-transition:enter="transition ease-out duration-300"
                  x-transition:enter-start="opacity-0 translate-y-6 scale-95"
                  x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                  x-transition:leave="transition ease-in duration-200"
                  x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                  x-transition:leave-end="opacity-0 translate-y-6 scale-95"
                  class="w-full max-w-sm rounded-[28px] border border-white/10 bg-white/5 backdrop-blur-xl text-white shadow-[0_20px_80px_-20px_rgba(0,0,0,.85),inset_0_1px_0_rgba(255,255,255,.04)] p-8"
                >
                  <!-- Modal header with same logo -->
                  <div class="flex justify-center mb-4">
                    <div class="relative">
                      <div class="absolute -inset-3 rounded-2xl bg-emerald-400/10 blur-xl"></div>
                      <img src="{{ asset('image/bg/LOG.png') }}" alt="Groove Logo" class="relative h-14 w-14 object-contain drop-shadow-[0_6px_24px_rgba(0,0,0,.4)]" />
                    </div>
                  </div>

                  <div class="text-center">
                    <!-- LOADING -->
                    <template x-if="state==='loading'">
                      <div class="space-y-4">
                        <svg class="mx-auto h-16 w-16 animate-spin text-amber-400" viewBox="0 0 24 24" fill="none">
                          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                        </svg>
                        <h3 :id="$id('email-sent-title')" class="text-2xl font-bold">Sending reset link…</h3>
                        <p class="text-sm text-zinc-300" x-text="`Please wait while we send the link to ${pickedEmail}`"></p>
                      </div>
                    </template>

                    <!-- SUCCESS -->
                    <template x-if="state==='success'">
                      <div class="space-y-4">
                        <svg class="mx-auto h-16 w-16 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <h3 :id="$id('email-sent-title')" class="text-2xl font-bold">Check Your Email</h3>
                        <p class="text-sm text-zinc-300" x-text="`We’ve sent a reset link to ${pickedEmail}.`"></p>
                        <a :href="gmailUrl" class="mt-2 inline-block w-full rounded-lg bg-blue-600 py-2 font-semibold text-white shadow-sm transition hover:bg-blue-700">Go to Gmail</a>
                      </div>
                    </template>

                    <!-- ERROR -->
                    <template x-if="state==='error'">
                      <div class="space-y-4">
                        <svg class="mx-auto h-16 w-16 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M12 5a7 7 0 100 14 7 7 0 000-14z"></path>
                        </svg>
                        <h3 :id="$id('email-sent-title')" class="text-2xl font-bold">Something went wrong</h3>
                        <p class="text-sm text-zinc-300" x-text="errorMsg"></p>
                        <button @click="close()" class="mt-2 inline-block w-full rounded-lg bg-zinc-900/70 ring-1 ring-white/10 py-2 font-semibold text-white shadow-sm transition hover:bg-zinc-900">Close</button>
                      </div>
                    </template>
                  </div>
                </div>
              </div>
              <!-- /DARK MODAL -->
            </div>
          @endif
        </div>

        <div class="h-2 w-full rounded-b-[28px] bg-gradient-to-r from-transparent via-white/15 to-transparent"></div>
      </div>

      <div class="mt-4 text-center text-xs text-zinc-400">
        Having trouble? <a href="/support" class="font-medium text-zinc-200 underline decoration-dotted underline-offset-4 hover:text-white">Contact support</a>
      </div>
    </div>
  </main>

  <script>
    function resetForm(){
      return {
        password:'', confirm:'', show:false,
        get score(){ let s=0; if(this.password.length>=8)s++; if(/[A-Z]/.test(this.password))s++; if(/[0-9]/.test(this.password))s++; if(/[^A-Za-z0-9]/.test(this.password))s++; return s; },
        get valid(){ return this.score===4 && this.password===this.confirm; },
        get label(){ return ['Very weak','Weak','Good','Strong','Excellent'][this.score]; },
        get barClass(){ return { 'bg-rose-500':this.score<=1, 'bg-amber-500':this.score===2, 'bg-emerald-500':this.score>=3 }; }
      }
    }

    // Modal flow: loading -> success(redirect) / error
    function accountPicker(){
      return {
        open:false, loading:false, state:'loading',
        pickedEmail:'', gmailUrl:'#', errorMsg:'',
        AUTO_REDIRECT:true,
        start(email, type, url, token){
          this.pickedEmail = email;
          this.gmailUrl = `https://mail.google.com/mail/?authuser=${encodeURIComponent(email)}#inbox`;
          this.state = 'loading'; this.loading = true; this.open = true;

          fetch(url, {
            method:'POST',
            headers:{ 'Content-Type':'application/json', 'X-CSRF-TOKEN':token },
            body: JSON.stringify({ email, user_type:type })
          })
          .then(r => r.ok ? r.json() : Promise.reject(r))
          .then(data => {
            if (data && data.success){
              this.state='success'; this.loading=false;
              if (this.AUTO_REDIRECT) setTimeout(()=>window.location.assign(this.gmailUrl), 900);
            } else {
              this.state='error'; this.loading=false;
              this.errorMsg='Unable to send the reset email. Please try again.';
            }
          })
          .catch(()=>{
            this.state='error'; this.loading=false;
            this.errorMsg='Network error. Please try again.';
          });
        },
        maybeClose(){ if (this.state!=='loading') this.open=false; },
        close(){ this.open=false; }
      }
    }
  </script>
</body>
</html>
