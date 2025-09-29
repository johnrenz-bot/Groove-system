<!DOCTYPE html>
<html lang="en" class="bg-zinc-600">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>GROOVE — Performing Arts</title>

  <!-- SEO & Social -->
  <meta name="description" content="Groove is the pulse of the performing arts community — discover talents, studios, services, and more." />
  <meta property="og:title" content="GROOVE — Performing Arts" />
  <meta property="og:description" content="Where every beat, movement, and melody converge to create magic." />
  <meta property="og:type" content="website" />
  <meta property="og:image" content="{{ asset('image/wc/logo.png') }}" />
  <meta name="theme-color" content="#0b0b0c" />

  <link rel="icon" href="{{ asset('image/bg/LOG.png') }}" type="image/png" sizes="512x512">
  <link rel="apple-touch-icon" href="{{ asset('image/wc/logo.png') }}" sizes="180x180">

  <!-- Fonts & scripts -->
  <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
  <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
  <script src="https://kit.fontawesome.com/43f9926b04.js" crossorigin="anonymous" defer></script>

@vite(['resources/css/app.css', 'resources/js/app.js'])

  <style>
    :root{
      --ease-out: cubic-bezier(.22,1,.36,1);
      --color-primary: #a78bfa; /* violet-400 */
    }
    html { scroll-behavior: smooth; }
    body { font-family: 'Instrument Sans', ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, 'Apple Color Emoji','Segoe UI Emoji'; }

    /* Motion-safe reveal & effects */
    @media (prefers-reduced-motion: no-preference) {
      .reveal { opacity: 0; transform: translateY(16px); transition: opacity .8s var(--ease-out), transform .8s var(--ease-out); }
      .reveal.is-visible { opacity: 1; transform: translateY(0); }
      .logo-float { animation: logoFloat 6s ease-in-out infinite; }
      @keyframes logoFloat { 0%,100%{ transform: translateY(0) } 50%{ transform: translateY(-8px) } }
      .spin-on-hover:hover{ transform: rotate(8deg) scale(1.04); }
    }

    /* Logo watermark for sections */
    .logo-watermark{ position:absolute; inset: 0; pointer-events:none; display:grid; place-items:center; opacity: .06; filter: drop-shadow(0 4px 30px rgba(0,0,0,.4)); }

    /* Soft glass card */
    .glass { backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); background: rgba(255,255,255,.05); border:1px solid rgba(255,255,255,.1); }

    /* Gradient text helper */
    .text-gradient { background: linear-gradient(90deg,#fff 0%,#c7d2fe 25%,#93c5fd 50%,#a78bfa 75%,#f9a8d4 100%); -webkit-background-clip:text; background-clip:text; color:transparent; }
  </style>
</head>

<body class="min-h-screen flex flex-col text-white bg-gradient-to-br from-black via-zinc-900 to-black selection:bg-white/10 selection:text-white">

  <!-- ===== HEADER / NAV ===== -->
  <header id="home" class="relative min-h-[100svh] w-full overflow-hidden">
    <!-- Background accents -->
    <div aria-hidden="true" class="pointer-events-none absolute inset-0">
      <div class="absolute -top-24 -left-16 h-72 w-72 rounded-full bg-fuchsia-500/10 blur-3xl"></div>
      <div class="absolute bottom-0 right-0 h-96 w-96 rounded-full bg-cyan-400/10 blur-3xl"></div>
      <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top,rgba(255,255,255,.06),transparent_60%)]"></div>
    </div>

    <nav id="navbar" class="fixed top-0 left-0 w-full z-50 transition-all duration-500">
      <div class="mx-auto max-w-7xl px-4 md:px-6">
        <div class="flex h-16 md:h-[72px] items-center justify-between">
          <!-- Logo -->
          <a href="#home" class="inline-flex items-center gap-3 group">
            <img src="{{ asset('image/bg/LOG.png') }}" alt="GROOVE" class="h-9 w-auto object-contain select-none transition-transform duration-300 group-hover:scale-105 spin-on-hover" />
            <span class="hidden sm:inline text-sm font-semibold tracking-wide opacity-90 group-hover:opacity-100">GROOVE</span>
            <span class="sr-only">GROOVE Home</span>
          </a>

          <!-- Desktop menu -->
          <ul class="hidden md:flex items-center gap-6 text-sm font-medium">
            <li><a href="#talent" class="opacity-90 hover:opacity-100 hover:text-zinc-200 transition">Talents</a></li>
            <li><a href="#Studio" class="opacity-90 hover:opacity-100 hover:text-zinc-200 transition">Studios</a></li>
            <li><a href="#about" class="opacity-90 hover:opacity-100 hover:text-zinc-200 transition">About</a></li>
            <li><a href="#Services" class="opacity-90 hover:opacity-100 hover:text-zinc-200 transition">Services</a></li>
          </ul>

          <!-- CTA -->
          <div class="hidden md:flex items-center gap-3">
            <a href="{{ route('login') }}" class="inline-flex items-center gap-2 rounded-xl border border-white/15 bg-white/5 px-4 py-2 text-sm font-semibold hover:bg-white/10 hover:shadow-lg hover:shadow-cyan-500/10 transition">Sign In</a>
          </div>

          <!-- Mobile menu toggle -->
          <button id="mobile-menu-button" class="md:hidden inline-flex items-center justify-center rounded-xl border border-white/10 bg-white/5 p-2 hover:bg-white/10 focus:outline-none focus-visible:ring-2 focus-visible:ring-cyan-400" aria-expanded="false" aria-controls="mobile-menu" aria-label="Open menu">
            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6h16M4 12h16m-7 6h7"/></svg>
          </button>
        </div>
      </div>

      <!-- Mobile sheet -->
      <div id="mobile-menu" class="md:hidden fixed inset-x-0 top-16 origin-top scale-95 opacity-0 pointer-events-none transition-all duration-300">
        <div class="mx-3 rounded-2xl border border-white/10 bg-zinc-900/90 backdrop-blur-xl p-3 shadow-2xl">
          <ul class="grid gap-1 text-base">
            <li><a class="block rounded-xl px-4 py-3 hover:bg-white/5" href="#talent">Talents</a></li>
            <li><a class="block rounded-xl px-4 py-3 hover:bg-white/5" href="#Studio">Studios</a></li>
            <li><a class="block rounded-xl px-4 py-3 hover:bg-white/5" href="#about">About</a></li>
            <li><a class="block rounded-xl px-4 py-3 hover:bg-white/5" href="#Services">Services</a></li>
            <li class="pt-2"><a href="{{ route('login') }}" class="block rounded-xl border border-white/15 bg-white/5 px-4 py-3 font-semibold text-center hover:bg-white/10">Sign In</a></li>
          </ul>
        </div>
      </div>
    </nav>

    <!-- ===== HERO ===== -->
    <section class="relative z-0 flex min-h-[100svh] items-center">
      <!-- big faint logo watermark -->
      <div aria-hidden="true" class="logo-watermark">
        <img src="{{ asset('image/bg/LOG.png') }}" class="w-[56vw] max-w-[720px] logo-float" alt="">
      </div>

      <div class="mx-auto max-w-7xl px-4 md:px-6">
        <div class="grid items-center gap-10 md:grid-cols-12 pt-24 md:pt-28">
          <!-- Copy -->
          <div class="md:col-span-6 lg:col-span-7 space-y-5 reveal">
            <span class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-3 py-1 text-[11px] uppercase tracking-widest text-white/80">
              <i class="fa-solid fa-bolt"></i> Performing Arts Platform
            </span>
            <h1 class="text-balance text-4xl sm:text-5xl md:text-6xl lg:text-7xl font-extrabold leading-[1.05]">
              <span class="bg-clip-text text-transparent bg-gradient-to-r from-white via-indigo-300 to-pink-300">GROOVE</span> — Your Performing Arts Hub
            </h1>
            <p class="max-w-2xl text-pretty text-base sm:text-lg text-white/80">
              Built from our capstone research in San Jose del Monte, Bulacan, GROOVE is a web-based hub for performers and coaches—with 24/7 smart chat support and a studio locator—so you can find the right mentors, spaces, and opportunities in one place.
            </p>
            <div class="flex flex-wrap items-center gap-3 pt-2">
              <a href="#talent" class="inline-flex items-center justify-center rounded-full bg-white px-6 py-3 text-sm font-semibold text-black hover:scale-[1.02] active:scale-[.98] transition">Discover Talents & Studios</a>
              <a href="#about" class="inline-flex items-center justify-center rounded-full border border-white/15 bg-white/5 px-6 py-3 text-sm font-semibold hover:bg-white/10 transition">Learn More</a>
            </div>
            <!-- Small logo pill badge -->
            <div class="mt-6 inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-3 py-1 text-xs backdrop-blur">
              <img src="{{ asset('image/wc/logo.png') }}" class="h-5 w-5 rounded-full" alt="Groove"> Powered by the Groove community
            </div>
          </div>

          <!-- Media -->
          <div class="md:col-span-6 lg:col-span-5 md:justify-self-end w-full reveal">
            <figure class="group relative w-full overflow-hidden rounded-3xl border border-white/10 bg-white/5 shadow-2xl">
              <div class="relative aspect-[3/4]">
                <video id="grooveHeroVideo" class="absolute inset-0 h-full w-full object-cover" muted playsinline loop preload="metadata" poster="{{ asset('image/bg/LOG.png') }}">
                  <source src="{{ asset('media/groove-feature-vid.mp4') }}" type="video/mp4" />
                  Your browser does not support the video tag.
                </video>
                <div class="pointer-events-none absolute inset-0 bg-gradient-to-t from-black/40 via-black/10 to-transparent"></div>
                <figcaption class="absolute top-3 left-3 inline-flex items-center gap-2 rounded-full bg-black/50 backdrop-blur px-3 py-1 text-[10px] uppercase tracking-widest md:hidden">HD Preview</figcaption>
              </div>
            </figure>
          </div>
        </div>
      </div>
    </section>
  </header>

  <!-- ===== LOGIN NOTICE MODAL ===== -->
  <div id="loginNoticeModal" class="fixed inset-0 z-[60] hidden place-items-center bg-black/70 p-4">
    <div role="dialog" aria-modal="true" aria-labelledby="loginNoticeTitle" class="w-full max-w-sm rounded-2xl border border-white/15 bg-zinc-900/95 p-6 shadow-2xl">
      <h2 id="loginNoticeTitle" class="text-xl font-bold">Hold Up!</h2>
      <p class="mt-2 text-sm text-white/80">Please register or log in first to access this feature and connect with our talented community.</p>
      <div class="mt-5 flex items-center justify-end gap-3">
        <button data-close-modal class="rounded-lg border border-white/15 bg-white/5 px-4 py-2 text-sm hover:bg-white/10">Got it</button>
        <a href="{{ route('login') }}" class="rounded-lg bg-white px-4 py-2 text-sm font-semibold text-black hover:opacity-90">Sign In</a>
      </div>
    </div>
  </div>

  <main id="main" class="flex-1">

    <!-- ===== TALENTS ===== -->
    <section id="talent" class="py-16 md:py-24 relative">
      <div aria-hidden="true" class="absolute inset-0 -z-10">
        <div class="absolute -top-8 left-1/2 -translate-x-1/2 opacity-10">
          <img src="{{ asset('image/bg/LOG.png') }}" class="h-40 w-auto" alt="">
        </div>
      </div>
      <div class="mx-auto max-w-7xl px-4 md:px-6">
        <header class="reveal text-center">
          <h2 class="text-3xl md:text-5xl font-extrabold tracking-tight">Talents</h2>
          <p class="mx-auto mt-3 max-w-2xl text-white/70">Connect and collaborate across dance, singing, acting, and theater.</p>
        </header>

        <div class="mt-10 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
          <!-- Dance -->
          <button type="button" class="group relative aspect-[4/5] w-full overflow-hidden rounded-2xl border border-white/10 bg-white/5 text-left focus:outline-none focus-visible:ring-2 focus-visible:ring-cyan-400" data-open-login>
            <img src="{{ asset('image/wc/dance.jpg') }}" alt="Dance" class="h-full w-full object-cover transition duration-500 group-hover:scale-105" loading="lazy" decoding="async" />
            <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/30 to-transparent"></div>
            <div class="absolute inset-x-0 bottom-0 p-4">
              <p class="text-lg font-extrabold">Dance</p>
              <p class="text-sm text-white/80">Connect and grow with others passionate about dance.</p>
            </div>
          </button>
          <!-- Singing -->
          <button type="button" class="group relative aspect-[4/5] w-full overflow-hidden rounded-2xl border border-white/10 bg-white/5 text-left focus:outline-none focus-visible:ring-2 focus-visible:ring-cyan-400" data-open-login>
            <img src="{{ asset('image/wc/singg.png') }}" alt="Singing" class="h-full w-full object-cover transition duration-500 group-hover:scale-105" loading="lazy" decoding="async" />
            <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/30 to-transparent"></div>
            <div class="absolute inset-x-0 bottom-0 p-4">
              <p class="text-lg font-extrabold">Singing</p>
              <p class="text-sm text-white/80">Find your voice and collaborate with fellow singers.</p>
            </div>
          </button>
          <!-- Acting -->
          <button type="button" class="group relative aspect-[4/5] w-full overflow-hidden rounded-2xl border border-white/10 bg-white/5 text-left focus:outline-none focus-visible:ring-2 focus-visible:ring-cyan-400" data-open-login>
            <img src="{{ asset('image/wc/acting.jpg') }}" alt="Acting" class="h-full w-full object-cover transition duration-500 group-hover:scale-105" loading="lazy" decoding="async" />
            <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/30 to-transparent"></div>
            <div class="absolute inset-x-0 bottom-0 p-4">
              <p class="text-lg font-extrabold">Acting</p>
              <p class="text-sm text-white/80">Join actors and coaches to refine your craft.</p>
            </div>
          </button>
          <!-- Theater -->
          <button type="button" class="group relative aspect-[4/5] w-full overflow-hidden rounded-2xl border border-white/10 bg-white/5 text-left focus:outline-none focus-visible:ring-2 focus-visible:ring-cyan-400" data-open-login>
            <img src="{{ asset('image/wc/theater.jpg') }}" alt="Theater" class="h-full w-full object-cover transition duration-500 group-hover:scale-105" loading="lazy" decoding="async" />
            <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/30 to-transparent"></div>
            <div class="absolute inset-x-0 bottom-0 p-4">
              <p class="text-lg font-extrabold">Theater</p>
              <p class="text-sm text-white/80">Step into the spotlight with our vibrant community.</p>
            </div>
          </button>
        </div>
      </div>
    </section>

    <!-- ===== STUDIOS (Map) ===== -->
    <section id="Studio" class="py-16 md:py-24">
      <div class="mx-auto max-w-7xl px-4 md:px-6">
        <header class="reveal text-center">
          <h2 class="text-3xl md:text-5xl font-extrabold tracking-tight text-zinc-300">Studios</h2>
          <p class="mx-auto mt-3 max-w-2xl text-white/70">Find rehearsal studios near you with our location-based map tool.</p>
        </header>
        <div class="reveal mt-8 rounded-3xl border border-white/10 bg-white/5 p-2 shadow-2xl">
          <div class="relative overflow-hidden rounded-2xl" style="aspect-ratio: 16/9;">
            <iframe title="Dance Studios near San Jose del Monte" src="https://www.google.com/maps/embed?pb=!1m16!1m12!1m3!1d61730.37468240366!2d120.99412321212131!3d14.760667026083794!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!2m1!1sDANCE%20studio!5e0!3m2!1sen!2sph!4v1747613518621!5m2!1sen!2sph" width="100%" height="100%" style="border:0;" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
          </div>
        </div>
      </div>
    </section>

    <!-- ===== ABOUT (Expanded) ===== -->
    <section id="about" class="py-16 md:py-24 relative">
      <div class="absolute inset-0 -z-10 opacity-[0.05] flex items-center justify-center">
        <img src="{{ asset('image/bg/LOG.png') }}" alt="" class="max-w-[420px] w-[56vw]">
      </div>
      <div class="mx-auto max-w-7xl px-4 md:px-6">
        <!-- Hero -->
        <div class="text-center mb-16 reveal">
          <img src="{{ asset('image/wc/logo.png') }}" alt="Groove Logo" class="h-16 w-auto mx-auto mb-4 drop-shadow-[0_0_24px_rgba(255,255,255,.15)] logo-float">
          <h2 class="text-4xl md:text-6xl font-extrabold tracking-tight"> About <span class="text-gradient">Groove</span> </h2>
          <p class="mt-4 text-lg text-white/70 max-w-3xl mx-auto"> San Jose Del Monte Bulacan's Web-Based Performing Arts Hub with Smart Chat Support and Studio Locator </p>
        </div>

        <!-- Project Context -->
        <section class="mb-16 reveal">
          <div class="grid md:grid-cols-3 gap-6 mt-8">
            <div class="glass p-5 rounded-xl">
              <div class="mb-3"><i class="fa-solid fa-magnifying-glass text-xl" style="color: var(--color-primary)"></i></div>
              <h3 class="text-xl font-semibold mb-2">Finding Coaches</h3>
              <p class="text-white/70"> 78.9% of artists in San Jose Del Monte Bulacan had difficulty finding available and qualified coaches or choreographers. </p>
            </div>
            <div class="glass p-5 rounded-xl">
              <div class="mb-3"><i class="fa-solid fa-comments text-xl" style="color: var(--color-primary)"></i></div>
              <h3 class="text-xl font-semibold mb-2">Communication Issues</h3>
              <p class="text-white/70"> 82.2% of artists experience delays or difficulty receiving responses when inquiring about availability, rates, or scheduling. </p>
            </div>
            <div class="glass p-5 rounded-xl">
              <div class="mb-3"><i class="fa-solid fa-location-dot text-xl" style="color: var(--color-primary)"></i></div>
              <h3 class="text-xl font-semibold mb-2">Studio Access</h3>
              <p class="text-white/70"> 86.8% of artists reported difficulty finding nearby studios in their area for rehearsals and practice. </p>
            </div>
          </div>
        </section>

        <!-- Mission / Vision -->
        <section class="mb-16 reveal">
          <div class="grid md:grid-cols-2 gap-8 mt-6">
            <div class="glass p-6 rounded-xl border border-white/10">
              <h3 class="text-xl font-semibold mb-4" style="color: var(--color-primary)">Our Mission</h3>
              <p class="text-white/80"> To create opportunities for artists to offer their service and showcase their talents by addressing the challenges that artists face such as the difficulty of finding coaches and choreographers, delays in communication, limited access to nearby studios, and the right platform to share their work with other performers. </p>
            </div>
            <div class="glass p-6 rounded-xl border border-white/10">
              <h3 class="text-xl font-semibold mb-4" style="color: var(--color-primary)">Our Vision</h3>
              <p class="text-white/80"> To develop a more accessible and efficient support system for the performing arts community that improves the overall experience and opportunities for performing artists, enabling them to thrive creatively and professionally. </p>
            </div>
          </div>
        </section>

        <!-- Key Features -->
        <section class="mb-16 reveal">
          <h3 class="text-3xl font-bold mb-6" style="color: var(--color-primary)">Key Features</h3>
          <div class="grid md:grid-cols-2 gap-8">
            <div class="glass p-6 rounded-xl">
              <div class="flex items-center mb-4">
                <i class="fa-solid fa-robot text-xl mr-4" style="color: var(--color-primary)"></i>
                <h4 class="text-xl font-semibold">Smart Chat Support</h4>
              </div>
              <p class="text-white/80"> Uses artificial intelligence to provide 24/7 responses to inquiries, ensuring faster and more efficient communication between clients and artists. </p>
            </div>
            <div class="glass p-6 rounded-xl">
              <div class="flex items-center mb-4">
                <i class="fa-solid fa-users text-xl mr-4" style="color: var(--color-primary)"></i>
                <h4 class="text-xl font-semibold">Artist Directory</h4>
              </div>
              <p class="text-white/80"> Allows clients to search for and connect with coaches or choreographers based on specific genres or expertise, reducing the difficulty of finding suitable matches. </p>
            </div>
            <div class="glass p-6 rounded-xl">
              <div class="flex items-center mb-4">
                <i class="fa-solid fa-map-location-dot text-xl mr-4" style="color: var(--color-primary)"></i>
                <h4 class="text-xl font-semibold">Studio Locator</h4>
              </div>
              <p class="text-white/80"> Helps users find nearby rehearsal spaces, addressing the issue of the difficulty of finding studios within a preferred distance. </p>
            </div>
            <div class="glass p-6 rounded-xl">
              <div class="flex items-center mb-4">
                <i class="fa-solid fa-handshake text-xl mr-4" style="color: var(--color-primary)"></i>
                <h4 class="text-xl font-semibold">Community Platform</h4>
              </div>
              <p class="text-white/80"> Enables artists to upload and present their work, offering a platform for visibility and collaboration within the performing arts community. </p>
            </div>
          </div>
        </section>

   
    <!-- ===== SERVICES ===== -->
    <section id="Services" class="py-16 md:py-24">
    
        <!-- CTA Banner -->
        <div class="reveal mt-12 rounded-3xl border border-white/10 bg-gradient-to-r from-fuchsia-600/20 via-purple-600/20 to-cyan-500/20 p-6 md:p-10 text-center relative overflow-hidden">
          <div class="absolute -right-6 -top-6 opacity-10">
            <img src="{{ asset('image/bg/LOG.png') }}" class="h-32 w-auto" alt="">
          </div>
          <h3 class="text-2xl md:text-3xl font-bold">Ready to find your groove?</h3>
          <p class="mx-auto mt-2 max-w-2xl text-white/80">Create an account and start connecting with artists, coaches, and studios near you.</p>
          <div class="mt-6 flex flex-wrap items-center justify-center gap-3">
            <a href="#about" class="inline-flex items-center justify-center rounded-full bg-white px-6 py-3 text-sm font-semibold text-black hover:scale-[1.02] active:scale-[.98] transition">Get Started</a>
            <a href="{{ route('login') }}" class="inline-flex items-center justify-center rounded-full border border-white/15 bg-white/5 px-6 py-3 text-sm font-semibold hover:bg-white/10 transition">Sign In</a>
          </div>
        </div>
      </div>
    </section>
  </main>

  <!-- ===== FOOTER ===== -->
  <footer class="border-t border-white/10 py-8 text-center text-white/70">
    <div class="mx-auto max-w-7xl px-4 md:px-6">
      <div class="flex flex-col items-center justify-between gap-4 md:flex-row">
        <p class="text-sm">&copy; <span id="year"></span> GROOVE. All rights reserved.</p>
        <div class="flex items-center gap-4">
          <a href="mailto:info@groove.com" class="hover:text-white transition">Groo1152000@gmail.com</a>
          <a class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-white/10 hover:bg-white/10" href="#home" aria-label="Back to top">
            <i class="fa-solid fa-arrow-up"></i>
          </a>
        </div>
      </div>
    </div>
  </footer>

  <noscript>
    <div class="mx-auto max-w-3xl p-4 text-center text-sm text-white/70">For the best experience, please enable JavaScript.</div>
  </noscript>

  <!-- ===== SCRIPTS ===== -->
  <script>
    // Navbar background on scroll
    const navbar = document.getElementById('navbar');
    const onScroll = () => {
      if (window.scrollY > 12) {
        navbar.classList.add('backdrop-blur-xl','bg-zinc-950/70','border-b','border-white/10','shadow-lg');
      } else {
        navbar.classList.remove('backdrop-blur-xl','bg-zinc-950/70','border-b','border-white/10','shadow-lg');
      }
    };
    document.addEventListener('scroll', onScroll, { passive: true });
    onScroll();

    // Reveal on intersection (motion-safe)
    const items = document.querySelectorAll('.reveal');
    const io = new IntersectionObserver((entries) => {
      entries.forEach(e => { if (e.isIntersecting) e.target.classList.add('is-visible'); });
    }, { threshold: 0.12 });
    items.forEach(el => io.observe(el));

    // Mobile menu toggle
    const mobileBtn = document.getElementById('mobile-menu-button');
    const mobileMenu = document.getElementById('mobile-menu');
    mobileBtn?.addEventListener('click', () => {
      const expanded = mobileBtn.getAttribute('aria-expanded') === 'true';
      mobileBtn.setAttribute('aria-expanded', String(!expanded));
      mobileMenu.classList.toggle('opacity-0');
      mobileMenu.classList.toggle('scale-95');
      mobileMenu.classList.toggle('pointer-events-none');
    });
    // Close mobile menu on link click
    document.querySelectorAll('#mobile-menu a').forEach(a => a.addEventListener('click', () => {
      mobileBtn.setAttribute('aria-expanded', 'false');
      mobileMenu.classList.add('opacity-0','scale-95','pointer-events-none');
    }));

    // Login modal
    const modal = document.getElementById('loginNoticeModal');
    const openers = document.querySelectorAll('[data-open-login]');
    const closer = document.querySelector('[data-close-modal]');
    openers.forEach(btn => btn.addEventListener('click', () => modal.classList.remove('hidden')));
    closer?.addEventListener('click', () => modal.classList.add('hidden'));
    modal?.addEventListener('click', (e) => { if (e.target === modal) modal.classList.add('hidden'); });

    // Current year
    document.getElementById('year').textContent = new Date().getFullYear();
  </script>

  <!-- Accessibility & performance-conscious video behavior -->
  <script>
    (function(){
      const video = document.getElementById('grooveHeroVideo');
      if (!video) return;
      const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

      function updateToggleBtn(isPlaying){
        const toggleBtn = document.querySelector('[data-action="toggle"]');
        if (!toggleBtn) return;
        const icon = toggleBtn.querySelector('i');
        const label = toggleBtn.querySelector('span');
        if (isPlaying){
          toggleBtn.setAttribute('aria-pressed', 'true');
          toggleBtn.setAttribute('aria-label', 'Pause video');
          if (icon) icon.className = 'fa-solid fa-pause';
          if (label) label.textContent = 'Pause';
        } else {
          toggleBtn.setAttribute('aria-pressed', 'false');
          toggleBtn.setAttribute('aria-label', 'Play video');
          if (icon) icon.className = 'fa-solid fa-play';
          if (label) label.textContent = 'Play';
        }
      }

      const tryPlay = () => {
        if (prefersReduced) return; // user prefers no motion
        video.muted = true;
        video.play().then(() => updateToggleBtn(true)).catch(() => updateToggleBtn(false));
      };

      const visIO = new IntersectionObserver((entries) => {
        entries.forEach(e => {
          if (e.isIntersecting) {
            tryPlay();
          } else {
            video.pause();
            updateToggleBtn(false);
          }
        });
      }, { threshold: 0.25 });
      visIO.observe(video);

      // Optional buttons (if you decide to add controls later)
      const buttons = document.querySelectorAll('.heroVidBtn');
      buttons.forEach(btn => btn.addEventListener('click', () => {
        const action = btn.getAttribute('data-action');
        if (action === 'toggle') {
          if (video.paused) { video.play(); updateToggleBtn(true); }
          else { video.pause(); updateToggleBtn(false); }
        }
        if (action === 'mute') {
          video.muted = !video.muted;
          const pressed = btn.getAttribute('aria-pressed') === 'true';
          btn.setAttribute('aria-pressed', String(!pressed));
          btn.setAttribute('title', video.muted ? 'Mute' : 'Unmute');
          const icon = btn.querySelector('i');
          if (icon) icon.className = video.muted ? 'fa-solid fa-volume-xmark' : 'fa-solid fa-volume-high';
        }
        if (action === 'replay') {
          video.currentTime = 0;
          video.play();
          updateToggleBtn(true);
        }
      }));

      video.addEventListener('play', () => updateToggleBtn(true));
      video.addEventListener('pause', () => updateToggleBtn(false));
    })();
  </script>

  <!-- Optional: light reveal animation for [data-reveal] elements -->
  <style>
    @media (prefers-reduced-motion: no-preference){
      [data-reveal]{ opacity: 0; transform: translateY(16px); transition: opacity .8s cubic-bezier(.22,1,.36,1), transform .8s cubic-bezier(.22,1,.36,1); }
      [data-reveal].is-visible{ opacity:1; transform:none; }
    }
  </style>
  <script>
    (function(){
      const els = document.querySelectorAll('[data-reveal]');
      if (!('IntersectionObserver' in window)) return els.forEach(el => el.classList.add('is-visible'));
      const io2 = new IntersectionObserver((entries)=>{ entries.forEach(e=>{ if(e.isIntersecting) e.target.classList.add('is-visible'); }); },{threshold:0.12});
      els.forEach(el=>io2.observe(el));
    })();
  </script>

</body>
</html>