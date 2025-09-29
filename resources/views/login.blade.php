{{-- resources/views/login.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Groove | Sign In</title>

  <meta name="csrf-token" content="{{ csrf_token() }}">
@vite(['resources/css/app.css', 'resources/js/app.js'])

  <link rel="icon" href="{{ asset('image/bg/LOG.png') }}" type="image/png" sizes="512x512"/>

  <style>
    /* Animations */
    @keyframes float { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-8px)} }
    @keyframes fadeUp { 0%{opacity:0; transform:translateY(12px)} 100%{opacity:1; transform:translateY(0)} }
    .animate-float { animation: float 4s ease-in-out infinite; }
    .animate-fadeUp { animation: fadeUp 1s ease-out both; }

    /* Glass effect */
    .glass {
      background: rgba(18,18,18,0.5);
      border: 1px solid rgba(255,255,255,0.08);
      backdrop-filter: blur(14px);
      -webkit-backdrop-filter: blur(14px);
      box-shadow:0 10px 35px rgba(0,0,0,0.45), inset 0 1px 0 rgba(255,255,255,0.06);
    }

    /* Modal transition */
    .modal-hidden { opacity:0; pointer-events:none; transform: translateY(10px); }
    .modal-visible { opacity:1; pointer-events:auto; transform: translateY(0); transition: all .28s ease; }
  </style>
</head>

<body class="min-h-screen flex items-center justify-center bg-gradient-to-br from-zinc-950 via-zinc-800 to-black text-white font-[instrument-sans] antialiased selection:bg-zinc-200 selection:text-black">

  <div class="w-[95%] md:w-[92%] xl:w-[86%] h-auto md:h-[90vh] grid grid-cols-1 md:grid-cols-2 overflow-hidden rounded-3xl shadow-2xl border border-zinc-800/70">

    {{-- Left: Sign In --}}
    <div class="flex flex-col justify-center px-4 sm:px-10 md:px-14 py-8 bg-zinc-950/40">
      <div class="glass rounded-3xl w-full max-w-md mx-auto p-7 sm:p-9">

        {{-- Logo --}}
        <div class="flex justify-center">
          <img src="{{ asset('image/bg/LOG.png') }}" alt="Groove Logo"
               class="w-24 sm:w-28 md:w-32 drop-shadow-[0_0_28px_rgba(255,255,255,0.25)] animate-float hover:scale-105 transition-transform duration-300"/>
        </div>

        <h1 class="mt-6 text-3xl sm:text-4xl font-extrabold text-center bg-gradient-to-r from-zinc-50 via-zinc-200 to-zinc-400 bg-clip-text text-transparent tracking-tight">
          Welcome Back
        </h1>
        <p class="mt-2 text-center text-sm text-zinc-400">Move to your own rhythm.</p>

        {{-- Errors --}}
        @if ($errors->any())
          <div id="errorBox" class="mt-6 rounded-xl bg-red-500/10 border border-red-500/40 text-red-200 p-4">
            <ul class="list-disc list-inside space-y-1">
              @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
              @endforeach
            </ul>
          </div>
        @endif
        @if (session('error'))
          <div class="mt-6 rounded-xl bg-red-500/10 border border-red-500/40 text-red-200 p-4">
            {{ session('error') }}
          </div>
        @endif

        {{-- Form --}}
        <form action="{{ route('login.post') }}" method="POST" class="mt-8 space-y-5">
          @csrf
          <div>
            <label class="block text-xs uppercase tracking-wider text-zinc-400">Username</label>
            <input type="text" name="username" value="{{ old('username') }}" autofocus autocomplete="username"
                   class="mt-2 w-full h-12 rounded-xl bg-white/5 border border-white/10 px-4 text-zinc-100 placeholder-zinc-500 outline-none focus:ring-2 focus:ring-zinc-300/70 focus:border-zinc-300/70 transition"
                   placeholder="Enter your username" required/>
          </div>

          <div>
            <div class="flex items-center justify-between">
              <label class="block text-xs uppercase tracking-wider text-zinc-400">Password</label>
              <a href="{{ route('ForgetPassword') }}" class="text-xs text-zinc-300 hover:text-white underline-offset-4 hover:underline">
                Forgot Password?
              </a>
            </div>

            <div class="mt-2 relative">
              <input id="passwordInput" type="password" name="password" autocomplete="current-password"
                     class="w-full h-12 rounded-xl bg-white/5 border border-white/10 pl-4 pr-12 text-zinc-100 placeholder-zinc-500 outline-none focus:ring-2 focus:ring-zinc-300/70 focus:border-zinc-300/70 transition"
                     placeholder="••••••••" required/>
              <button type="button" id="togglePassword" aria-label="Toggle password visibility"
                      class="absolute right-3 top-1/2 -translate-y-1/2 p-2 rounded-lg hover:bg-white/5 focus:outline-none focus:ring-2 focus:ring-zinc-300/60 transition">
                <svg id="eyeOpen" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6"
                        d="M2.25 12s3.75-6.75 9.75-6.75S21.75 12 21.75 12s-3.75 6.75-9.75 6.75S2.25 12 2.25 12z"/>
                  <circle cx="12" cy="12" r="3.25" stroke-width="1.6"></circle>
                </svg>
                <svg id="eyeClosed" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6"
                        d="M3 3l18 18M10.58 10.58A3 3 0 0012 15a3 3 0 002.42-4.42M6.11 6.11C3.94 7.56 2.25 10 2.25 12c0 0 3.75 6.75 9.75 6.75 1.79 0 3.41-.43 4.83-1.12M17.89 17.89C20.06 16.44 21.75 14 21.75 12c0 0-3.75-6.75-9.75-6.75-1.08 0-2.1.16-3.06.45"/>
                </svg>
              </button>
            </div>
          </div>

          <button type="submit"
                  class="group w-full h-12 rounded-xl bg-gradient-to-r from-zinc-200 via-zinc-400 to-zinc-600 text-black font-semibold tracking-wide shadow-lg hover:shadow-[0_0_24px_rgba(200,200,200,0.45)] transition-transform hover:-translate-y-0.5 active:translate-y-0">
            <span class="inline-flex items-center justify-center gap-2">
              Sign in
              <span class="inline-block transition-transform group-hover:translate-x-0.5">→</span>
            </span>
          </button>
        </form>

        {{-- Create Account link --}}
        <div class="mt-7 text-center text-sm text-zinc-400">
          Don’t have an account?
          <button id="showChoiceBtn" class="ml-1 font-medium text-zinc-200 hover:text-white underline underline-offset-4">
            Create Account
          </button>
        </div>

        {{-- Back to Home button --}}
        <div class="mt-6 flex justify-center">
          <a href="{{ route('wc') }}"
             class="inline-flex items-center gap-2 px-5 py-2 rounded-xl bg-zinc-800/50 border border-zinc-700 text-zinc-100 text-sm font-medium hover:bg-zinc-700 hover:border-zinc-500 hover:text-white transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 19l-7-7 7-7" />
            </svg>
            Back to Home
          </a>
        </div>

      </div>
    </div>

    {{-- Right side illustration --}}
    <div class="relative hidden md:block">
      <img src="{{ asset('image/login/bright.jpeg') }}" alt="Groove Illustration" class="w-full h-full object-cover brightness-[.75]"/>
      <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/40 to-transparent"></div>
      <div class="absolute inset-0 flex flex-col justify-end items-center pb-16 xl:pb-28 text-center px-6">
        <img src="{{ asset('image/bg/LOG.png') }}" alt="Groove Logo" class="h-16 md:h-20 opacity-95 drop-shadow-xl animate-float"/>
        <p class="relative font-semibold tracking-wide text-base md:text-lg italic bg-gradient-to-r from-yellow-200 via-white to-yellow-300 bg-clip-text text-transparent mt-2 space-y-1">
          <span class="block animate-fadeUp" style="animation-delay:.05s">Move to your own rhythm.</span>
          <span class="block text-zinc-200 animate-fadeUp" style="animation-delay:.25s">Believe in your journey, even when the path feels uncertain.</span>
          <span class="block text-zinc-300 animate-fadeUp" style="animation-delay:.45s">Create your own path with courage and passion.</span>
        </p>
        <div class="mt-4 w-14 h-[3px] bg-gradient-to-r from-zinc-300 to-zinc-500 rounded-full mx-auto animate-pulse"></div>
      </div>
    </div>
  </div>

  {{-- Role Choice Modal --}}
  <div id="choiceOverlay" class="fixed inset-0 z-50 bg-black/70 backdrop-blur-sm flex items-center justify-center modal-hidden transition-all duration-200" aria-hidden="true" role="dialog" aria-labelledby="choiceTitle">
    <div id="choicePanel" class="relative w-[92%] max-w-6xl glass rounded-3xl p-5 sm:p-8">
      <button id="closeChoice" class="absolute top-3 right-3 text-3xl leading-none text-white/90 hover:text-white focus:outline-none focus:ring-2 focus:ring-zinc-300/70 rounded-lg px-2" aria-label="Close">&times;</button>

      <div class="flex justify-center mb-6">
        <h2 id="choiceTitle" class="text-2xl sm:text-3xl md:text-4xl font-bold text-white tracking-wide text-center">
          Choose Your Role
        </h2>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 sm:gap-8">
        <a href="{{ route('CoachRegister') }}"
           class="group relative h-[180px] sm:h-[380px] md:h-[460px] rounded-2xl overflow-hidden border border-zinc-700/70 shadow-xl bg-gradient-to-br from-zinc-900 via-zinc-800 to-zinc-900 hover:scale-[1.02] hover:border-zinc-400 transition-all duration-300">
          <img src="{{ asset('image/login/choreo.jpg') }}" class="absolute inset-0 w-full h-full object-cover opacity-60 group-hover:opacity-90 transition duration-500" alt="Choreographer"/>
          <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/50 to-transparent"></div>
          <div class="absolute bottom-0 w-full px-5 py-5 text-center bg-black/30 backdrop-blur-md">
            <h3 class="text-xl sm:text-2xl md:text-3xl font-bold text-white">Choreographer</h3>
            <p class="hidden sm:block text-gray-300 text-sm md:text-base leading-relaxed mt-1">
              Showcase your skills, build your portfolio, and connect with clients.
            </p>
          </div>
        </a>

        <a href="{{ route('Clientregister') }}"
           class="group relative h-[180px] sm:h-[380px] md:h-[460px] rounded-2xl overflow-hidden border border-zinc-700/70 shadow-xl bg-gradient-to-br from-zinc-900 via-zinc-800 to-zinc-900 hover:scale-[1.02] hover:border-zinc-400 transition-all duration-300">
          <img src="{{ asset('image/login/arti.jpg') }}" class="absolute inset-0 w-full h-full object-cover opacity-60 group-hover:opacity-90 transition duration-500" alt="Client"/>
          <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/50 to-transparent"></div>
          <div class="absolute bottom-0 w-full px-5 py-5 text-center bg-black/30 backdrop-blur-md">
            <h3 class="text-xl sm:text-2xl md:text-3xl font-bold text-white">Client</h3>
            <p class="hidden sm:block text-gray-300 text-sm md:text-base leading-relaxed mt-1">
              Find choreographers, book sessions seamlessly, and manage your dance journey.
            </p>
          </div>
        </a>
      </div>
    </div>
  </div>

  {{-- JS --}}
  <script>
    const errorBox = document.getElementById('errorBox');
    if (errorBox) setTimeout(() => errorBox.remove(), 3000);

    const pwdInput = document.getElementById('passwordInput');
    const toggleBtn = document.getElementById('togglePassword');
    const eyeOpen = document.getElementById('eyeOpen');
    const eyeClosed = document.getElementById('eyeClosed');
    toggleBtn?.addEventListener('click', () => {
      const isHidden = pwdInput.type === 'password';
      pwdInput.type = isHidden ? 'text' : 'password';
      eyeOpen.classList.toggle('hidden', !isHidden);
      eyeClosed.classList.toggle('hidden', isHidden);
    });

    const overlay = document.getElementById('choiceOverlay');
    const openBtn  = document.getElementById('showChoiceBtn');
    const closeBtn = document.getElementById('closeChoice');
    function openModal() {
      overlay.classList.remove('modal-hidden');
      overlay.classList.add('modal-visible');
      overlay.setAttribute('aria-hidden', 'false');
    }
    function closeModal() {
      overlay.classList.add('modal-hidden');
      overlay.classList.remove('modal-visible');
      overlay.setAttribute('aria-hidden', 'true');
    }
    openBtn?.addEventListener('click', openModal);
    closeBtn?.addEventListener('click', closeModal);
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeModal(); });
    overlay?.addEventListener('click', (e) => { if (e.target === overlay) closeModal(); });
  </script>
</body>
</html>
