{{-- resources\views\termsandcon.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Groove – Terms and Conditions</title>

  <!-- Fonts & libs -->
  <link rel="preconnect" href="https://fonts.bunny.net" />
  <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://kit.fontawesome.com/43f9926b04.js" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

  <link rel="icon" href="/image/wc/logo.png" type="image/png" sizes="512x512">
  <link rel="apple-touch-icon" href="/image/wc/logo.png" sizes="180x180">

  <style>
    html, body { font-family: 'Instrument Sans', ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, 'Helvetica Neue', Arial; }
    /* Smooth anchor scrolling */
    html { scroll-behavior: smooth; }
    /* Light scrollbar */
    ::-webkit-scrollbar { width: 10px; height: 10px; }
    ::-webkit-scrollbar-track { background: #e5e7eb; border-radius: 8px; }
    ::-webkit-scrollbar-thumb { background-color: #cbd5e1; border-radius: 8px; border: 2px solid #e5e7eb; }
    ::-webkit-scrollbar-thumb:hover { background-color: #94a3b8; }
    * { scrollbar-width: thin; scrollbar-color: #cbd5e1 #e5e7eb; }
  </style>
</head>

<body class="min-h-screen bg-slate-50 text-slate-800">
  <!-- Sticky Top Bar -->
  <header class="sticky top-0 z-20 backdrop-blur bg-white/70 border-b border-slate-200">
    <div class="mx-auto max-w-6xl px-4 h-14 flex items-center justify-between">
      <div class="flex items-center gap-3">
        <img src="/image/wc/logo.png" alt="Groove" class="h-7 w-7 rounded-md" />
        <span class="font-semibold text-slate-900">Groove</span>
      </div>

      <div class="flex items-center gap-2">
        <a href="{{ url()->previous() }}"
           class="inline-flex items-center gap-2 h-9 px-3 rounded-lg border border-slate-300 bg-white text-slate-700 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
          <i class="fas fa-arrow-left"></i>
          <span class="hidden sm:inline">Back</span>
        </a>
        <button type="button" onclick="window.print()"
                class="inline-flex items-center gap-2 h-9 px-3 rounded-lg bg-blue-600 text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
          <i class="fas fa-print"></i>
          <span class="hidden sm:inline">Print</span>
        </button>
      </div>
    </div>
  </header>

  <!-- Page -->
  <main class="mx-auto max-w-6xl px-4 py-10">
    <!-- Title Card -->
    <section class="mb-6">
      <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h1 class="text-3xl md:text-4xl font-semibold tracking-tight text-slate-900">Terms & Conditions</h1>
        <p class="mt-2 text-sm text-slate-500">
          Last updated: <span class="font-medium text-slate-700">{{ now()->format('F d, Y') }}</span>
        </p>
      </div>
    </section>

    <!-- Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
      <!-- TOC -->
      <aside class="lg:col-span-4 xl:col-span-3">
        <nav class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sticky top-20">
          <h2 class="text-sm font-semibold text-slate-700 mb-3">On this page</h2>
          <ol class="space-y-2 text-sm text-slate-600">
            <li><a href="#acceptance" class="hover:text-blue-700">1. Acceptance of Terms</a></li>
            <li><a href="#definitions" class="hover:text-blue-700">2. Definitions</a></li>
            <li><a href="#accounts" class="hover:text-blue-700">3. User Accounts</a></li>
            <li><a href="#conduct" class="hover:text-blue-700">4. User Conduct</a></li>
            <li><a href="#payments" class="hover:text-blue-700">5. Booking & Payments</a></li>
            <li><a href="#smartchat" class="hover:text-blue-700">6. Smart Chat Support</a></li>
            <li><a href="#ip" class="hover:text-blue-700">7. Intellectual Property</a></li>
            <li><a href="#disclaimer" class="hover:text-blue-700">8. Disclaimer & Liability</a></li>
            <li><a href="#privacy" class="hover:text-blue-700">9. Privacy</a></li>
            <li><a href="#changes" class="hover:text-blue-700">10. Changes to Terms</a></li>
            <li><a href="#termination" class="hover:text-blue-700">11. Termination</a></li>
            <li><a href="#law" class="hover:text-blue-700">12. Governing Law</a></li>
            <li><a href="#contact" class="hover:text-blue-700">13. Contact</a></li>
          </ol>
        </nav>
      </aside>

      <!-- Body -->
      <article class="lg:col-span-8 xl:col-span-9">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm space-y-8 leading-relaxed text-slate-800">
          <!-- Sections -->
          <section id="acceptance">
            <h2 class="text-xl md:text-2xl font-semibold text-slate-900 mb-2">1. Acceptance of Terms</h2>
            <p>By accessing or using the Groove website (“Platform”), you agree to be bound by these Terms. If you do not agree, do not use the Platform.</p>
          </section>

          <section id="definitions">
            <h2 class="text-xl md:text-2xl font-semibold text-slate-900 mb-2">2. Definitions</h2>
            <ul class="list-disc list-inside space-y-1 ml-2">
              <li><strong>Platform:</strong> Groove website and services.</li>
              <li><strong>User:</strong> Anyone accessing the Platform.</li>
              <li><strong>Client:</strong> User booking artists or studios.</li>
              <li><strong>Artist:</strong> Choreographer, coach, or performer using the Platform.</li>
              <li><strong>Content:</strong> Text, images, videos, audio, profiles, posts, or other materials uploaded.</li>
            </ul>
          </section>

          <section id="accounts">
            <h2 class="text-xl md:text-2xl font-semibold text-slate-900 mb-2">3. User Accounts</h2>
            <p>Registration is required for some features. Keep your password safe. Artists may undergo verification. Must be 18+ or have parental consent.</p>
          </section>

          <section id="conduct">
            <h2 class="text-xl md:text-2xl font-semibold text-slate-900 mb-2">4. User Conduct</h2>
            <p>Post legal and safe content only. No harassment, spam, or disruption of Platform services.</p>
          </section>

          <section id="payments">
            <h2 class="text-xl md:text-2xl font-semibold text-slate-900 mb-2">5. Booking & Payments</h2>
            <p>Payments occur directly between Client and Artist. Groove is not part of transactions. Users handle contracts and disputes themselves.</p>
          </section>

          <section id="smartchat">
            <h2 class="text-xl md:text-2xl font-semibold text-slate-900 mb-2">6. Smart Chat Support</h2>
            <p>AI-powered, provides info from Artists. Not legal advice. Accuracy not guaranteed.</p>
          </section>

          <section id="ip">
            <h2 class="text-xl md:text-2xl font-semibold text-slate-900 mb-2">7. Intellectual Property</h2>
            <p>Groove owns Platform IP. Users retain their Content IP. Posting grants Groove a non-exclusive license to display Content.</p>
          </section>

          <section id="disclaimer">
            <h2 class="text-xl md:text-2xl font-semibold text-slate-900 mb-2">8. Disclaimer & Liability</h2>
            <p>Platform is “as-is.” Groove not liable for damages from use, transactions, or content.</p>
          </section>

          <section id="privacy">
            <h2 class="text-xl md:text-2xl font-semibold text-slate-900 mb-2">9. Privacy</h2>
            <p>Data collection is per our Privacy Policy. Using the Platform means consent to it.</p>
          </section>

          <section id="changes">
            <h2 class="text-xl md:text-2xl font-semibold text-slate-900 mb-2">10. Changes to Terms</h2>
            <p>Groove may update Terms anytime. Continued use = acceptance.</p>
          </section>

          <section id="termination">
            <h2 class="text-xl md:text-2xl font-semibold text-slate-900 mb-2">11. Termination</h2>
            <p>Accounts may be suspended/terminated for violations or harmful conduct.</p>
          </section>

          <section id="law">
            <h2 class="text-xl md:text-2xl font-semibold text-slate-900 mb-2">12. Governing Law</h2>
            <p>Philippine laws govern these Terms.</p>
          </section>

          <section id="contact">
            <h2 class="text-xl md:text-2xl font-semibold text-slate-900 mb-2">13. Contact</h2>
            <p>Questions? Contact: <a href="mailto:support@groove-ph.com" class="text-blue-700 hover:underline">support@groove-ph.com</a><br>
              By using Groove, you accept these Terms.</p>
          </section>

          <!-- Footer actions -->
          <div class="pt-2 flex flex-wrap gap-2">
            <a href="#top" class="inline-flex items-center gap-2 h-10 px-3 rounded-lg border border-slate-300 bg-white text-slate-700 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
              <i class="fas fa-arrow-up"></i> Back to top
            </a>
            <button type="button" onclick="window.print()"
                    class="inline-flex items-center gap-2 h-10 px-3 rounded-lg bg-blue-600 text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
              <i class="fas fa-print"></i> Print
            </button>
          </div>
        </div>
      </article>
    </div>
  </main>

  <!-- Print styles -->
  <style>
    @media print {
      header, aside nav, .sticky, .shadow-sm, .shadow, .border-slate-200 { box-shadow: none !important; }
      header, .sticky { position: static !important; }
      body { background: white !important; }
      a[href]:after { content: ""; } /* hide link URLs */
      button { display: none !important; }
    }
  </style>
</body>
</html>
