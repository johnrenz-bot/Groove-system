@php
    $initial = [
        'firstname' => old('firstname', ''),
        'middlename'=> old('middlename', ''),
        'lastname'  => old('lastname', ''),
        'birth_year'=> old('birth_year', ''),
        'birth_month'=> old('birth_month', ''),
        'birth_day' => old('birth_day', ''),
        'contact'   => old('contact', ''),
        'email'     => old('email', ''),
        'username'  => old('username', ''),
        // never prefill passwords from old()
        'password'  => '',
        'password_confirmation' => '',
        'terms'     => (bool) old('terms', false),
    ];

    // Build address summary if a selection exists
    $summary = null;
    if (!empty($selected)) {
        $parts = [
            $selected['street'] ?? null,
            $selected['barangay_name'] ?? null,
            $selected['city_name'] ?? null,
            $selected['province_name'] ?? null,
            $selected['region_name'] ?? null,
        ];
        $summary = implode(', ', array_filter($parts));
    }
@endphp
<!DOCTYPE html>
<html lang="en" x-data='registrationForm(@json($initial))' x-init="init()">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Groove - Client Registration</title>

  <link rel="preconnect" href="https://fonts.bunny.net" />
  <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://kit.fontawesome.com/43f9926b04.js" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

  <link rel="icon" href="/image/bg/LOG.png" type="image/png" sizes="512x512">

  <style>
    [x-cloak] { display: none !important; }
    html, body { font-family: 'Instrument Sans', ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, 'Helvetica Neue', Arial; }
    ::-webkit-scrollbar { width: 10px; height: 10px; }
    ::-webkit-scrollbar-track { background: #e5e7eb; border-radius: 8px; }
    ::-webkit-scrollbar-thumb { background-color: #cbd5e1; border-radius: 8px; border: 2px solid #e5e7eb; }
    ::-webkit-scrollbar-thumb:hover { background-color: #94a3b8; }
    * { scrollbar-width: thin; scrollbar-color: #cbd5e1 #e5e7eb; }
    .error-message { color: #dc2626; font-size: 0.75rem; margin-top: 0.25rem; }
    label.req-dynamic::after {
      content:"*"; display:inline-flex; align-items:center; justify-content:center; margin-left:6px;
      width:.95rem; height:.95rem; background:#fee2e2; color:#b91c1c; border:1px solid #fecaca; border-radius:9999px;
      font-weight:800; font-size:.7rem; transform:translateY(-1px); opacity:0; transition:opacity .15s ease;
    }
    label.req-dynamic.is-missing::after { opacity: 1; }
    .focus-glow:focus { box-shadow: 0 0 0 4px rgba(59,130,246,.1), 0 0 0 1px rgba(59,130,246,.4); }
    .tooltip { transform: translateX(0); opacity: 0; transition: all .25s ease; }
    .group:hover .tooltip { opacity: 1; transform: translateX(4px); }
  </style>

  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-slate-50 text-slate-800 min-h-screen flex flex-col relative">
  @if(session('success'))
    <div class="text-green-600 text-center mt-4">{{ session('success') }}</div>
  @endif
  @if(session('error'))
    <div class="text-rose-600 text-center mt-4">{{ session('error') }}</div>
  @endif

  <div class="absolute top-5 left-5 group z-10">
    <a href="{{ route('login') }}" class="relative flex items-center">
      <span class="text-3xl font-extrabold text-slate-900 transition-transform duration-200 group-hover:-translate-x-1 group-hover:text-blue-600">&lt;</span>
      <span class="tooltip absolute left-6 top-1.5 whitespace-nowrap bg-white/90 backdrop-blur border border-slate-200 text-slate-700 text-xs font-semibold px-3 py-1 rounded-lg shadow">
        Back to Login
      </span>
    </a>
  </div>

  <main class="flex flex-col justify-center flex-grow px-4 py-10 max-w-4xl mx-auto w-full">
    <header class="w-full flex flex-col justify-center items-center relative py-8 px-4 max-w-4xl mx-auto">
      <img src="/image/bg/LOG.png" alt="Logo" class="mb-4 h-14 w-auto" />
      <p class="font-bold text-4xl tracking-tight text-slate-900">REGISTRATION</p>
      <p class="font-semibold text-base tracking-wide text-blue-700">CLIENT</p>
      <p class="max-w-2xl text-center text-slate-600 text-sm px-5 py-4 mx-auto border border-slate-200 rounded-xl bg-white shadow-sm mt-4">
        Join Groove’s Client Registration! Connect with coaches, book sessions, and explore top talents and studios in Bulacan.
      </p>
    </header>

    <form id="registrationForm"
          action="{{ route('ClientStore') }}"
          method="POST"
          enctype="multipart/form-data"
          novalidate
          @submit="handleSubmit"
          class="w-full flex flex-col gap-6 bg-white p-6 sm:p-8 rounded-2xl border border-slate-200 shadow-sm">
      @csrf

      @if (session('addressConfirmed'))
        <div class="rounded-lg border border-green-200 bg-green-50 p-3 text-sm text-green-700">
          Address saved.
        </div>
      @endif

      <!-- Names -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div>
          <label for="firstname" class="block text-xs font-semibold text-slate-700 mb-1 req-dynamic">Firstname</label>
          <input type="text" name="firstname" id="firstname" placeholder="Firstname"
                 class="w-full h-11 px-3 bg-white text-slate-900 border rounded-lg focus:outline-none focus-glow"
                 x-model.trim="form.firstname" @input="validate('firstname')"
                 :class="inputClass('firstname')" required />
          <div class="error-message" x-show="touched.firstname" x-text="errors.firstname"></div>
          @error('firstname')<p class="text-rose-600 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
          <label for="middlename" class="block text-xs font-semibold text-slate-700 mb-1">Middlename (optional)</label>
          <input type="text" name="middlename" id="middlename" placeholder="Middlename"
                 class="w-full h-11 px-3 bg-white text-slate-900 border rounded-lg focus:outline-none focus-glow"
                 x-model.trim="form.middlename" @input="validate('middlename')"
                 :class="inputClass('middlename')" />
          <div class="error-message" x-show="touched.middlename" x-text="errors.middlename"></div>
          @error('middlename')<p class="text-rose-600 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
          <label for="lastname" class="block text-xs font-semibold text-slate-700 mb-1 req-dynamic">Lastname</label>
          <input type="text" name="lastname" id="lastname" placeholder="Lastname"
                 class="w-full h-11 px-3 bg-white text-slate-900 border rounded-lg focus:outline-none focus-glow"
                 x-model.trim="form.lastname" @input="validate('lastname')"
                 :class="inputClass('lastname')" required />
          <div class="error-message" x-show="touched.lastname" x-text="errors.lastname"></div>
          @error('lastname')<p class="text-rose-600 text-xs mt-1">{{ $message }}</p>@enderror
        </div>
      </div>

      <!-- Birthdate -->
      <div>
        <label class="block text-xs font-semibold text-slate-700 mb-2 req-dynamic" :class="errors.birthdate ? 'is-missing' : ''">Date of Birth</label>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <select id="birth_year" name="birth_year"
                  x-model="form.birth_year" @change="validate('birthdate'); updateDays()"
                  class="w-full h-11 px-3 bg-white text-slate-900 border rounded-lg focus:outline-none focus-glow"
                  :class="inputClass('birthdate')" required>
            <option value="" disabled>Year</option>
            <template x-for="y in years" :key="y">
              <option :value="y" x-text="y"></option>
            </template>
          </select>

          <select id="birth_month" name="birth_month"
                  x-model="form.birth_month" @change="validate('birthdate'); updateDays()"
                  class="w-full h-11 px-3 bg-white text-slate-900 border rounded-lg focus:outline-none focus-glow"
                  :class="inputClass('birthdate')" required>
            <option value="" disabled>Month</option>
            <template x-for="(m, i) in months" :key="i">
              <option :value="i+1" x-text="m"></option>
            </template>
          </select>

          <select id="birth_day" name="birth_day"
                  x-model="form.birth_day" @change="validate('birthdate')"
                  class="w-full h-11 px-3 bg-white text-slate-900 border rounded-lg focus:outline-none focus-glow"
                  :class="inputClass('birthdate')" required>
            <option value="" disabled>Day</option>
            <template x-for="d in daysInMonth" :key="d">
              <option :value="d" x-text="d"></option>
            </template>
          </select>
        </div>
        <div class="error-message" x-show="touched.birthdate" x-text="errors.birthdate"></div>
        @error('birth_date')<div class="text-rose-600 text-xs mt-1">{{ $message }}</div>@enderror
      </div>

      <!-- Address -->
      <div>
        <label class="block text-xs font-semibold text-slate-700 mb-1 req-dynamic">
          Address (Region, Province, City/Municipality, Barangay)
        </label>

        <div class="w-full h-11 px-3 bg-slate-50 text-slate-700 border border-slate-300 rounded-lg flex items-center justify-between">
          <span class="truncate">
            {{ $summary ? $summary : 'Region, Province, City/Municipality, Barangay' }}
            @if (!empty($selected['postal_code']))
              — {{ $selected['postal_code'] }}
            @endif
          </span>
          <button type="button"
                  class="ml-3 inline-flex items-center justify-center rounded-md border border-blue-600 text-blue-700 bg-white px-3 h-8 hover:bg-blue-50"
                  onclick="window.location.href='{{ route('confirm.address') }}'">
            Pick / Edit
          </button>
        </div>

        {{-- Hidden fields to persist address --}}
        <input type="hidden" name="region_code"   value="{{ old('region_code', $selected['region_code'] ?? '') }}">
        <input type="hidden" name="province_code" value="{{ old('province_code', $selected['province_code'] ?? '') }}">
        <input type="hidden" name="city_code"     value="{{ old('city_code', $selected['city_code'] ?? '') }}">
        <input type="hidden" name="barangay_code" value="{{ old('barangay_code', $selected['barangay_code'] ?? '') }}">
        <input type="hidden" name="region_name"   value="{{ old('region_name', $selected['region_name'] ?? '') }}">
        <input type="hidden" name="province_name" value="{{ old('province_name', $selected['province_name'] ?? '') }}">
        <input type="hidden" name="city_name"     value="{{ old('city_name', $selected['city_name'] ?? '') }}">
        <input type="hidden" name="barangay_name" value="{{ old('barangay_name', $selected['barangay_name'] ?? '') }}">
        <input type="hidden" name="street"        value="{{ old('street', $selected['street'] ?? '') }}">
        <input type="hidden" name="postal_code"   value="{{ old('postal_code', $selected['postal_code'] ?? '') }}">

        {{-- Server-side address errors --}}
        @error('region_code')   <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
        @error('province_code') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
        @error('city_code')     <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
        @error('barangay_code') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
      </div>

      <!-- Contact & Email -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label for="contact" class="block text-xs font-semibold text-slate-700 mb-1 req-dynamic">Contact</label>
          <div class="flex w-full">
            <span class="inline-flex items-center gap-2 px-3 min-w-[100px] h-11 bg-slate-50 text-slate-700 border border-slate-300 rounded-l-lg border-r-0">
              <img src="https://flagcdn.com/w40/ph.png" alt="PH Flag" class="h-4 w-auto rounded-sm" />
              +63
            </span>
            <input type="text" name="contact" id="contact" placeholder="9XXXXXXXXX"
                   maxlength="10" inputmode="numeric"
                   x-model.trim="form.contact" @input="digitsOnly('contact'); validate('contact')"
                   class="flex-1 h-11 px-3 bg-white text-slate-900 border rounded-r-lg border-l-0 focus:outline-none focus-glow"
                   :class="inputClass('contact')" required />
          </div>
          <div class="error-message" x-show="touched.contact" x-text="errors.contact"></div>
          @error('contact')<p class="text-rose-600 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
          <label for="email" class="block text-xs font-semibold text-slate-700 mb-1 req-dynamic">Email</label>
          <div class="relative">
            <i class="fa-regular fa-envelope absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
            <input type="email" name="email" id="email" placeholder="example@gmail.com"
                   class="w-full h-11 pl-9 pr-10 bg-white text-slate-900 border rounded-lg focus:outline-none focus-glow"
                   x-model.trim="form.email" @input.debounce.300ms="validate('email')"
                   :class="inputClass('email')" required />
          </div>
          <div class="error-message" x-show="touched.email" x-text="errors.email"></div>
          @error('email')<p class="text-rose-600 text-xs mt-1">{{ $message }}</p>@enderror
        </div>
      </div>

      <!-- Username -->
      <div>
        <label for="username" class="block text-xs font-semibold text-slate-700 mb-1 req-dynamic">Username</label>
        <div class="relative">
          <i class="fa-regular fa-at absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
          <input type="text" name="username" id="username" placeholder="Username"
                 class="w-full h-11 pl-9 pr-3 bg-white text-slate-900 border rounded-lg focus:outline-none focus-glow"
                 x-model.trim="form.username" @input.debounce.300ms="validate('username')"
                 :class="inputClass('username')" required />
        </div>
        <div class="error-message" x-show="touched.username" x-text="errors.username"></div>
        @error('username')<p class="text-rose-600 text-xs mt-1">{{ $message }}</p>@enderror
      </div>

      <!-- Passwords -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label for="password" class="block text-xs font-semibold text-slate-700 mb-1 req-dynamic">Password</label>
          <div class="relative">
            <i class="fa-regular fa-lock absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
            <input type="password" name="password" id="password" placeholder="••••••••"
                   class="w-full h-11 pl-9 pr-3 bg-white text-slate-900 border rounded-lg focus:outline-none focus-glow"
                   x-model="form.password" @input="validate('password')"
                   :class="inputClass('password')" required />
          </div>
          <div class="error-message" x-show="touched.password" x-text="errors.password"></div>
          @error('password')<p class="text-rose-600 text-xs mt-1">{{ $message }}</p>@enderror

          <ul class="text-[11px] mt-2 space-y-1">
            <li :class="ruleClass(passwordRules.len)"> 8 characters</li>
            <li :class="ruleClass(passwordRules.upper)">At least 1 uppercase letter</li>
            <li :class="ruleClass(passwordRules.special)">At least 1 special character</li>
          </ul>
        </div>

        <div>
          <label for="password_confirmation" class="block text-xs font-semibold text-slate-700 mb-1 req-dynamic">Confirm Password</label>
          <div class="relative">
            <i class="fa-regular fa-shield-halved absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
            <input type="password" name="password_confirmation" id="password_confirmation" placeholder="••••••••"
                   class="w-full h-11 pl-9 pr-3 bg-white text-slate-900 border rounded-lg focus:outline-none focus-glow"
                   x-model="form.password_confirmation" @input="validate('password_confirmation')"
                   :class="inputClass('password_confirmation')" required />
          </div>
          <div class="error-message" x-show="touched.password_confirmation" x-text="errors.password_confirmation"></div>
        </div>
      </div>

      <!-- SINGLE ID: Any ID (PDF/JPG/PNG) -->
      <div>
        <label class="block text-xs font-semibold text-slate-700 mb-1 req-dynamic" :class="idError ? 'is-missing' : ''">
          Upload any valid ID (PDF/JPG/PNG)
        </label>
        <div :class="dropzoneClass(idFile)" @click="pick('id_document')">
          <template x-if="!idFile.file && !idFile.preview">
            <div class="flex flex-col items-center gap-2">
              <i class="fa-regular fa-id-card text-3xl text-slate-400"></i>
              <p class="text-slate-600 text-sm">Click to upload (PDF/JPG/PNG)</p>
            </div>
          </template>

          <!-- Image preview -->
          <template x-if="idFile.preview && idFile.kind==='image'">
            <div class="w-full">
              <div class="relative w-full rounded-xl overflow-hidden border border-slate-200 bg-white">
                <div class="aspect-[4/3] w-full">
                  <img :src="idFile.preview" alt="ID preview" class="w-full h-full object-contain bg-slate-900/5">
                </div>
              </div>
              <div class="flex justify-end w-full mt-2 gap-3">
                <button type="button" @click.stop="pick('id_document')" class="text-blue-700 hover:text-blue-800 text-sm font-semibold">Change</button>
                <button type="button" @click.stop="removeId()" class="text-rose-600 hover:text-rose-700 text-sm font-semibold">Remove</button>
              </div>
            </div>
          </template>

          <!-- Non-image (PDF) simple bar -->
          <template x-if="idFile.file && idFile.kind==='file' && !idFile.preview">
            <div class="flex items-center justify-between w-full bg-slate-100 rounded px-3 py-2 border border-slate-300">
              <span class="text-slate-800 text-sm truncate" x-text="idFile.name"></span>
              <span class="text-[11px] text-slate-500 uppercase" x-text="idFile.ext"></span>
            </div>
          </template>

          <input class="hidden" x-ref="id_document" type="file" name="id_document"
                 accept=".pdf,.jpg,.jpeg,.png"
                 @change="onIdChange($event)">
        </div>
        <div class="error-message" x-text="idError"></div>
        @error('id_document')<p class="text-rose-600 text-xs mt-1">{{ $message }}</p>@enderror
      </div>

      <!-- Terms -->
      <div class="flex items-start gap-3">
        <input type="checkbox" name="terms" id="terms" class="mt-0.5 h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500 focus:ring-offset-0"
               x-model="form.terms" @change="validate('terms')" {{ old('terms') ? 'checked' : '' }} />
        <label for="terms" class="text-xs text-slate-700">I have read and accept the
          <a href="{{ route('terms') }}" class="font-semibold text-blue-700 hover:underline">Terms and Conditions</a>
        </label>
      </div>
      <div class="error-message" x-show="touched.terms" x-text="errors.terms"></div>
      @error('terms')<p class="text-rose-600 text-xs mt-1">{{ $message }}</p>@enderror

      <!-- Submit -->
      <div class="pt-2">
        <button type="submit"
                :disabled="!formValid || isSubmitting || !idOk"
                :aria-busy="isSubmitting ? 'true' : 'false'"
                class="w-full sm:w-[40%] h-11 inline-flex items-center justify-center gap-2 rounded-lg
                       bg-blue-600 text-white font-semibold shadow-sm
                       hover:bg-blue-700 active:bg-blue-800 transition
                       focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-blue-600
                       disabled:opacity-50 disabled:cursor-not-allowed">
          <svg x-show="isSubmitting" x-cloak class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke-width="4"></circle>
            <path class="opacity-75" stroke-width="4" d="M4 12a8 8 0 018-8"></path>
          </svg>
          <span x-text="isSubmitting ? 'Submitting…' : 'Create Account'"></span>
        </button>
        <p class="text-[11px] text-slate-500 mt-2">By continuing, you agree to our Terms & Privacy Policy.</p>
      </div>
    </form>

    {{-- Email Sent Modal --}}
    <div x-cloak x-show="@json(session('emailSent'))"
         x-transition:enter="transition ease-out duration-400"
         x-transition:enter-start="opacity-0 translate-y-6 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-300"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 translate-y-6 scale-95"
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm p-4">
      <div class="bg-white p-8 rounded-2xl shadow-2xl max-w-sm w-full mx-4 border border-slate-200">
        <div class="text-center">
          <svg class="w-16 h-16 text-amber-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6 0a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
          <h3 class="mt-4 text-2xl font-bold text-slate-900">Check Your Email</h3>
          <p class="mt-2 text-sm text-slate-600">We’ve sent a verification email. Please confirm your email to proceed.</p>
          <a href="https://mail.google.com/mail/u/0/#inbox"
             class="mt-6 inline-block w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 rounded-lg shadow-sm transition">
            Go to Gmail
          </a>
        </div>
      </div>
    </div>

    <!-- Submitting overlay -->
    <div x-cloak x-show="isSubmitting"
         class="fixed inset-0 z-40 bg-black/40 backdrop-blur-sm flex items-center justify-center p-4"
         x-transition.opacity>
      <div class="bg-white border border-slate-200 rounded-2xl shadow-2xl px-6 py-5 text-center max-w-sm w-full">
        <svg class="w-10 h-10 mx-auto animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke-width="4"></circle>
          <path class="opacity-75" stroke-width="4" d="M4 12a8 8 0 018-8"></path>
        </svg>
        <p class="mt-3 font-semibold text-slate-900">Processing your submission…</p>
        <p class="mt-1 text-sm text-slate-600">Please wait while we create your account and send the email.</p>
      </div>
    </div>

    <p class="mt-6 text-center text-xs text-slate-500">© {{ date('Y') }} Groove. All rights reserved.</p>
  </main>

  <script>
    function registrationForm(initial) {
      const now = new Date();
      const MIN_YEAR = 1900;
      const MAX_YEAR = now.getFullYear() - 13;

      const nameRegex = /^[A-Za-zÀ-ÿ\s'-]+$/u;
      const usernameRegex = /^[A-Za-z0-9._-]{3,20}$/;
      const gmailRegex = /^[A-Za-z0-9._%+\-]+@gmail\.com$/i;
      const contactRegex = /^9\d{9}$/;

      return {
        // Core
        isSubmitting: false,
        form: {
          firstname: initial.firstname, middlename: initial.middlename, lastname: initial.lastname,
          birth_year: initial.birth_year, birth_month: initial.birth_month, birth_day: initial.birth_day,
          contact: initial.contact, email: initial.email, username: initial.username,
          password: '', password_confirmation: '',
          terms: !!initial.terms
        },
        errors: {
          firstname: '', middlename: '', lastname: '',
          birthdate: '', contact: '', email: '', username: '',
          password: '', password_confirmation: '', terms: ''
        },
        touched: {},
        years: [], months: "January February March April May June July August September October November December".split(" "),
        daysInMonth: [],
        formValid: false,
        passwordRules: { len: false, upper: false, special: false },

        // Single ID state
        idFile: { file: null, name: "", ext: "", preview: "", kind: "" }, // kind: 'image' or 'file'
        idError: "",

        init() {
          this.years = Array.from({ length: MAX_YEAR - MIN_YEAR + 1 }, (_, i) => MAX_YEAR - i);
          this.updateDays();
          this.$nextTick(() => this.recomputeFormValid());
          this.setupDynamicAsterisks();
          window.addEventListener('pageshow', () => { this.isSubmitting = false; });
        },

        // Helpers
        inputClass(field) {
          return {
            'border-slate-300': !this.errors[field],
            'border-rose-500': !!this.errors[field] && this.touched[field],
            'border-green-500': !this.errors[field] && this.touched[field] && (this.form[field] !== '' || field === 'birthdate' || field === 'terms')
          };
        },
        ruleClass(ok) { return ok ? 'text-green-600' : 'text-slate-500'; },
        digitsOnly(field) { this.form[field] = (this.form[field] || '').replace(/[^0-9]/g, ''); },
        dropzoneClass(obj) {
          return [
            "w-full border-2 border-dashed rounded-xl bg-white/60 hover:bg-white transition p-4 cursor-pointer",
            "min-h-[120px] grid place-items-center text-center",
            obj.file ? "border-blue-300" : "border-slate-300"
          ].join(" ");
        },
        pick(ref) { this.$refs[ref]?.click(); },

        // ID file change
        async onIdChange(e) {
          const file = e.target.files?.[0];
          if (!file) return;

          const ext = (file.name.split('.').pop() || '').toLowerCase();
          const isImage = ['jpg','jpeg','png'].includes(ext);
          const isPdf = ext === 'pdf';

          if (!isImage && !isPdf) {
            this.idFile = { file: null, name: "", ext: "", preview: "", kind: "" };
            this.idError = "Please upload a PDF or image (JPG/PNG).";
            return;
          }

          let preview = "";
          if (isImage) {
            preview = await new Promise((resolve) => {
              const reader = new FileReader();
              reader.onload = (ev) => resolve(ev.target.result);
              reader.readAsDataURL(file);
            });
          }

          this.idFile = {
            file,
            name: file.name,
            ext: ext.toUpperCase(),
            preview: preview,       // only for images
            kind: isImage ? 'image' : 'file'
          };
          this.idError = "";
        },
        removeId() {
          this.idFile = { file: null, name: "", ext: "", preview: "", kind: "" };
          if (this.$refs.id_document) this.$refs.id_document.value = "";
          this.idError = "This ID is required.";
        },

        // Birthdate helpers
        updateDays() {
          const y = +this.form.birth_year;
          const m = +this.form.birth_month;
          if (!y || !m) { this.daysInMonth = []; return; }
          const days = new Date(y, m, 0).getDate();
          this.daysInMonth = Array.from({ length: days }, (_, i) => i + 1);
          if (+this.form.birth_day > days) this.form.birth_day = '';
        },

        // Validation
        validate(field) {
          this.touched[field] = true;

          if (['firstname','middlename','lastname'].includes(field)) {
            const val = (this.form[field] || '').trim();
            if (field !== 'middlename' && !val) {
              this.errors[field] = 'This field is required.';
            } else if (val && !nameRegex.test(val)) {
              this.errors[field] = 'Only letters, spaces, apostrophes, and dashes are allowed.';
            } else {
              this.errors[field] = '';
            }
          }

          if (field === 'birthdate' || ['birth_year','birth_month','birth_day'].includes(field)) {
            const y = +this.form.birth_year, m = +this.form.birth_month, d = +this.form.birth_day;
            this.touched.birthdate = true;
            if (!y || !m || !d) {
              this.errors.birthdate = 'Please select Year, Month, and Day.';
            } else {
              const maxDay = new Date(y, m, 0).getDate();
              if (d > maxDay) {
                this.errors.birthdate = 'Invalid date for the selected month.';
              } else {
                const birth = new Date(y, m - 1, d);
                const age13 = new Date(y + 13, m - 1, d);
                if (birth > new Date()) {
                  this.errors.birthdate = 'Birthdate cannot be in the future.';
                } else if (new Date() < age13) {
                  this.errors.birthdate = 'You must be at least 13 years old to register.';
                } else {
                  this.errors.birthdate = '';
                }
              }
            }
          }

          if (field === 'contact') {
            const v = (this.form.contact || '').trim();
            if (!v) this.errors.contact = 'Contact is required.';
            else if (!/^9\d{9}$/.test(v)) this.errors.contact = 'Use PH mobile format: 9XXXXXXXXX (10 digits).';
            else this.errors.contact = '';
          }

          if (field === 'email') {
            const v = (this.form.email || '').trim();
            if (!v) this.errors.email = 'Email is required.';
            else if (!gmailRegex.test(v)) this.errors.email = 'Email must be a valid @gmail.com address.';
            else this.errors.email = '';
          }

          if (field === 'username') {
            const v = (this.form.username || '').trim();
            if (!v) this.errors.username = 'Username is required.';
            else if (!usernameRegex.test(v)) this.errors.username = '3–20 chars: letters, numbers, dot, underscore, dash.';
            else this.errors.username = '';
          }

          if (field === 'password') {
            const v = this.form.password || '';
            this.passwordRules.len = v.length >= 8;
            this.passwordRules.upper = /[A-Z]/.test(v);
            this.passwordRules.special = /[!@#$%^&*()_\-+=\[\]{};:'",.<>\/?`~\\|]/.test(v);

            if (!v) this.errors.password = 'Password is required.';
            else if (!(this.passwordRules.len && this.passwordRules.upper && this.passwordRules.special)) {
              this.errors.password = 'Password should be at least 8 characters, with an uppercase letter, and special character.';
            } else {
              this.errors.password = '';
            }

            if (this.touched.password_confirmation) this.validate('password_confirmation');
          }

          if (field === 'password_confirmation') {
            const v = this.form.password_confirmation || '';
            if (!v) this.errors.password_confirmation = 'Please confirm your password.';
            else if (v !== this.form.password) this.errors.password_confirmation = 'Passwords do not match.';
            else this.errors.password_confirmation = '';
          }

          if (field === 'terms') {
            this.touched.terms = true;
            this.errors.terms = this.form.terms ? '' : 'You must accept the terms.';
          }

          this.recomputeFormValid();
        },

        recomputeFormValid() {
          const requiredFilled = [
            'firstname','lastname','birth_year','birth_month','birth_day',
            'contact','email','username','password','password_confirmation'
          ].every(k => (this.form[k] !== '' && this.form[k] !== null));

          const noErrors = Object.values(this.errors).every(e => !e);
          this.formValid = requiredFilled && noErrors && !!this.form.terms;
        },

        get idOk() {
          return !!(this.idFile.file || this.idFile.preview);
        },

        handleSubmit(e) {
          e.preventDefault();

          // Validate all fields
          ['firstname','middlename','lastname','birthdate','contact','email','username','password','password_confirmation','terms'].forEach(f => this.validate(f));
          if (!this.idOk) this.idError = "This ID is required.";

          this.recomputeFormValid();

          if (this.formValid && this.idOk) {
            this.isSubmitting = true;
            requestAnimationFrame(() => e.target.submit());
          } else {
            const firstErrorField =
              Object.keys(this.errors).find(k => this.errors[k]) ||
              (!this.idOk ? 'id_document' : null);
            if (firstErrorField) {
              const el = document.getElementById(firstErrorField) || document.querySelector(`[name="${firstErrorField}"]`);
              if (el && el.scrollIntoView) el.scrollIntoView({ behavior: 'smooth', block: 'center' });
              if (el && el.focus) el.focus();
            }
          }
        },

        setupDynamicAsterisks() {
          document.querySelectorAll('form [required]').forEach((el) => {
            const label = document.querySelector(`label[for="${el.id}"]`);
            if (!label) return;
            label.classList.add('req-dynamic');
            const update = () => {
              const missing = el.type === 'checkbox' ? !el.checked : !el.value?.trim();
              label.classList.toggle('is-missing', missing);
            };
            update();
            el.addEventListener('input', update);
            el.addEventListener('change', update);
            el.addEventListener('blur', update);
          });
        },
      };
    }
  </script>
</body>
</html>
