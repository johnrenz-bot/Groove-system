<!DOCTYPE html>
<html lang="en" x-data="coachRegistrationForm()" x-init="init()">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Groove – Coach Registration</title>

  <!-- Fonts / Icons / JS -->
  <link rel="preconnect" href="https://fonts.bunny.net" />
  <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://kit.fontawesome.com/43f9926b04.js" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

  <!-- Favicon -->
  <link rel="icon" href="/image/bg/LOG.png" type="image/png" sizes="512x512">

  <!-- App assets -->
  @vite(['resources/css/app.css', 'resources/js/app.js'])

  <style>
    [x-cloak]{display:none!important}
    html,body{font-family:'Instrument Sans',ui-sans-serif,system-ui,-apple-system,Segoe UI,Roboto,'Helvetica Neue',Arial}
    ::-webkit-scrollbar{width:10px;height:10px}
    ::-webkit-scrollbar-track{background:#e5e7eb;border-radius:8px}
    ::-webkit-scrollbar-thumb{background-color:#cbd5e1;border-radius:8px;border:2px solid #e5e7eb}
    ::-webkit-scrollbar-thumb:hover{background-color:#94a3b8}
    .focus-glow:focus{box-shadow:0 0 0 4px rgba(29,78,216,.1),0 0 0 1px rgba(29,78,216,.4)}
    .error-message{color:#dc2626;font-size:.75rem;margin-top:.25rem}

    /* Required asterisk that appears only when the field is missing */
    label.req-dynamic::after{
      content:"*";display:inline-flex;align-items:center;justify-content:center;margin-left:6px;
      width:.95rem;height:.95rem;background:#fee2e2;color:#b91c1c;border:1px solid #fecaca;border-radius:9999px;
      font-weight:800;font-size:.7rem;transform:translateY(-1px);opacity:0;transition:opacity .15s ease
    }
    label.req-dynamic.is-missing::after{opacity:1}

    /* Tag pills */
    .tag{border:1px solid #cbd5e1}
    .tag.active{background:#e0e7ff;border-color:#6366f1;color:#1f2937}

    /* Tiny hover tooltip for “Back to Login” */
    .tooltip{transform:translateX(0);opacity:0;transition:all .25s ease}
    .group:hover .tooltip{opacity:1;transform:translateX(4px)}
  </style>
</head>

<body class="bg-slate-50 text-slate-800 min-h-screen flex flex-col relative">
  @if(session('success'))
    <div class="text-green-600 text-center mt-4" role="status">{{ session('success') }}</div>
  @endif
  @if(session('error'))
    <div class="text-rose-600 text-center mt-4" role="alert">{{ session('error') }}</div>
  @endif

  <!-- Back -->
  <div class="absolute top-5 left-5 group z-10">
    <a href="{{ route('login') }}" class="relative flex items-center" aria-label="Back to Login">
      <span class="text-3xl font-extrabold text-slate-900 transition-transform duration-200 group-hover:-translate-x-1 group-hover:text-indigo-600">&lt;</span>
      <span class="tooltip absolute left-6 top-1.5 whitespace-nowrap bg-white/90 backdrop-blur border border-slate-200 text-slate-700 text-xs font-semibold px-3 py-1 rounded-lg shadow">
        Back to Login
      </span>
    </a>
  </div>

  <main class="flex flex-col justify-center flex-grow px-4 py-10 max-w-4xl mx-auto w-full">
    <!-- Header -->
    <header class="w-full flex flex-col justify-center items-center relative py-8 px-4 max-w-4xl mx-auto">
      <img src="/image/bg/LOG.png" alt="Groove Logo" class="mb-4 h-14 w-auto" />
      <p class="font-bold text-4xl tracking-tight text-slate-900">REGISTRATION</p>
      <p class="font-semibold text-base tracking-wide text-indigo-700">CHOREOGRAPHER / COACH</p>
      <p class="max-w-2xl text-center text-slate-600 text-sm px-5 py-4 mx-auto border border-slate-200 rounded-xl bg-white shadow-sm mt-4">
        Join Groove’s Coaches &amp; Choreographers Registration! Connect with talented artists, share your expertise, book sessions, and grow your network in Bulacan.
      </p>
    </header>

    <!-- Stepper -->
    <div class="flex items-center gap-3 justify-center mb-5" aria-label="Progress">
      <button type="button" @click="step = 1" class="w-10 h-10 rounded-xl grid place-items-center font-semibold transition-colors" :class="step===1 ? 'bg-indigo-600 text-white shadow' : 'bg-slate-200 text-slate-600'">1</button>
      <div class="h-1 w-16 rounded" :class="step>=2 ? 'bg-indigo-600' : 'bg-slate-200'"></div>
      <button type="button" @click="step = 2" class="w-10 h-10 rounded-xl grid place-items-center font-semibold transition-colors" :class="step===2 ? 'bg-indigo-600 text-white shadow' : 'bg-slate-200 text-slate-600'">2</button>
    </div>

    <!-- Form -->
    <form
      id="coachForm"
      action="{{ route('CoachStore') }}"
      method="POST"
      enctype="multipart/form-data"
      novalidate
      @submit="handleSubmit"
      class="w-full flex flex-col gap-6 bg-white p-6 sm:p-8 rounded-2xl border border-slate-200 shadow-sm"
    >
      @csrf
      <input type="hidden" name="talents_json" x-ref="talentsJsonField">
      <input type="hidden" name="genres_map_json" x-ref="genresJsonField">

      <!-- ===========================
           STEP 1 — Personal Details
           =========================== -->
      <section x-show="step===1" x-transition class="space-y-5" aria-label="Step 1">
        <!-- Name -->
        <div class="grid grid-cols-1 sm:grid-cols-[1fr_1fr_1fr_auto] gap-4">
          <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1 req-dynamic" :class="req('firstname')">Firstname</label>
            <input type="text" name="firstname" placeholder="Firstname" class="w-full h-11 px-3 bg-white text-slate-900 border rounded-lg focus:outline-none focus-glow" x-model.trim="form.firstname" @input="validate('firstname')" :class="inputClass('firstname')" required>
            <div class="error-message" x-text="errors.firstname" role="alert"></div>
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Middlename</label>
            <input type="text" name="middlename" placeholder="Middlename" class="w-full h-11 px-3 bg-white text-slate-900 border rounded-lg focus:outline-none focus-glow" x-model.trim="form.middlename">
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1 req-dynamic" :class="req('lastname')">Lastname</label>
            <input type="text" name="lastname" placeholder="Lastname" class="w-full h-11 px-3 bg-white text-slate-900 border rounded-lg focus:outline-none focus-glow" x-model.trim="form.lastname" @input="validate('lastname')" :class="inputClass('lastname')" required>
            <div class="error-message" x-text="errors.lastname" role="alert"></div>
          </div>

          <div class="max-w-[10rem]">
            <label class="block text-xs font-semibold text-slate-700 mb-1">Suffix (optional)</label>
            <div class="relative">
              <select name="suffix" x-model="form.suffix" class="w-full h-11 px-3 pr-9 bg-white border rounded-lg focus:outline-none focus-glow appearance-none">
                <option value="" disabled selected>Select suffix</option>
                <option value="Jr.">Jr.</option>
                <option value="Sr.">Sr.</option>
                <option value="II">II</option>
                <option value="III">III</option>
                <option value="IV">IV</option>
              </select>
              <svg class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
            </div>
          </div>
        </div>

        <!-- Birthdate -->
        <div>
          <label class="block text-xs font-semibold text-slate-700 mb-2 req-dynamic" :class="req('birthdate')">Date of Birth</label>
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <select name="birth_year" x-model="form.birth_year" @change="updateDays(); validate('birthdate')" class="w-full h-11 px-3 bg-white text-slate-900 border rounded-lg focus:outline-none focus-glow" :class="inputClass('birthdate')" required>
              <option value="" disabled>Year</option>
              <template x-for="y in years" :key="y"><option :value="y" x-text="y"></option></template>
            </select>
            <select name="birth_month" x-model="form.birth_month" @change="updateDays(); validate('birthdate')" class="w-full h-11 px-3 bg-white text-slate-900 border rounded-lg focus:outline-none focus-glow" :class="inputClass('birthdate')" required>
              <option value="" disabled>Month</option>
              <template x-for="(m,i) in months" :key="i"><option :value="i+1" x-text="m"></option></template>
            </select>
            <select name="birth_day" x-model="form.birth_day" @change="validate('birthdate')" class="w-full h-11 px-3 bg-white text-slate-900 border rounded-lg focus:outline-none focus-glow" :class="inputClass('birthdate')" required>
              <option value="" disabled>Day</option>
              <template x-for="d in daysInMonth" :key="d"><option :value="d" x-text="d"></option></template>
            </select>
          </div>
          <div class="error-message" x-text="errors.birthdate" role="alert"></div>
        </div>

        <!-- Address (comes from address picker page) -->
        <div>
          <label class="block text-xs font-semibold text-slate-700 mb-1 req-dynamic" :class="req('address')">Address (Region, Province, City/Municipality, Barangay)</label>
          <div class="w-full h-11 px-3 bg-slate-50 text-slate-700 border border-slate-300 rounded-lg flex items-center justify-between">
            <span class="truncate">{{ $summary ? $summary : 'Region, Province, City/Municipality, Barangay' }}</span>
            <a href="{{ route('confirm.address', ['return' => 'coach']) }}" class="ml-3 inline-flex items-center justify-center rounded-md border border-blue-600 text-blue-700 bg-white px-3 h-8 hover:bg-blue-50">Pick / Edit</a>
          </div>

          <!-- Hidden fields set by address picker -->
          <input type="hidden" name="region_code"   value="{{ old('region_code',   $selected['region_code']   ?? '') }}">
          <input type="hidden" name="province_code" value="{{ old('province_code', $selected['province_code'] ?? '') }}">
          <input type="hidden" name="city_code"     value="{{ old('city_code',     $selected['city_code']     ?? '') }}">
          <input type="hidden" name="barangay_code" value="{{ old('barangay_code', $selected['barangay_code'] ?? '') }}">
          <input type="hidden" name="region_name"   value="{{ old('region_name',   $selected['region_name']   ?? '') }}">
          <input type="hidden" name="province_name" value="{{ old('province_name', $selected['province_name'] ?? '') }}">
          <input type="hidden" name="city_name"     value="{{ old('city_name',     $selected['city_name']     ?? '') }}">
          <input type="hidden" name="barangay_name" value="{{ old('barangay_name', $selected['barangay_name'] ?? '') }}">
          <input type="hidden" name="street"        value="{{ old('street',        $selected['street']        ?? '') }}">
          <input type="hidden" name="postal_code"   value="{{ old('postal_code',   $selected['postal_code']   ?? '') }}">

          <div class="error-message" x-text="errors.address" role="alert"></div>
        </div>

        <!-- Contact + Email -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1 req-dynamic" :class="req('contact')">Contact</label>
            <div class="flex w-full">
              <span class="inline-flex items-center gap-2 px-3 min-w-[100px] h-11 bg-slate-50 text-slate-700 border border-slate-300 rounded-l-lg border-r-0">
                <img src="https://flagcdn.com/w40/ph.png" alt="Philippine flag" class="h-4 w-auto rounded-sm" />
                +63
              </span>
              <input
                type="text"
                name="contact"
                placeholder="9XXXXXXXXX"
                maxlength="10"
                inputmode="numeric"
                class="flex-1 h-11 px-3 bg-white text-slate-900 border rounded-r-lg border-l-0 focus:outline-none focus-glow"
                x-model.trim="form.contact"
                @input="digitsOnly('contact'); validate('contact')"
                :class="inputClass('contact')"
                required
                aria-describedby="ph-contact-help"
              >
            </div>
            <p id="ph-contact-help" class="text-[11px] text-slate-500 mt-1">Starts with 9 · 10 digits</p>
            <div class="error-message" x-text="errors.contact" role="alert"></div>
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1 req-dynamic" :class="req('email')">Email</label>
            <div class="relative">
              <i class="fa-regular fa-envelope absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
              <input
                type="email"
                name="email"
                placeholder="example@gmail.com"
                inputmode="email"
                autocomplete="email"
                pattern="^[a-zA-Z0-9._%+-]+@gmail\.com$"
                title="Please enter a @gmail.com address"
                class="w-full h-11 pl-9 pr-3 bg-white text-slate-900 border rounded-lg focus:outline-none focus-glow"
                x-model.trim="form.email"
                @input.debounce.300ms="validate('email')"
                :class="inputClass('email')"
                required
              >
            </div>
            <div class="error-message" x-text="errors.email" role="alert"></div>
          </div>
        </div>

        <!-- Username -->
        <div>
          <label class="block text-xs font-semibold text-slate-700 mb-1 req-dynamic" :class="req('username')">Username</label>
          <div class="relative">
            <i class="fa-regular fa-at absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
            <input
              type="text"
              name="username"
              placeholder="Username"
              class="w-full h-11 pl-9 pr-3 bg-white text-slate-900 border rounded-lg focus:outline-none focus-glow"
              x-model.trim="form.username"
              @input.debounce.300ms="validate('username')"
              :class="inputClass('username')"
              required
            >
          </div>
          <div class="error-message" x-text="errors.username" role="alert"></div>
        </div>

        <!-- Password + Confirm -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4" x-data="{ showPwd:false, showPwd2:false }">
          <!-- Password -->
          <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1 req-dynamic" :class="req('password')">Password</label>
            <div class="relative">
              <i class="fa-regular fa-lock absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
              <input
                :type="showPwd ? 'text' : 'password'"
                id="password"
                name="password"
                placeholder="••••••••"
                class="w-full h-11 pl-9 pr-10 bg-white text-slate-700 border rounded-lg focus:outline-none focus-glow"
                x-model="form.password"
                @input="validate('password')"
                :class="inputClass('password')"
                required
              >
              <button
                type="button"
                class="absolute inset-y-0 right-2 my-auto h-8 w-8 grid place-items-center rounded-md hover:bg-slate-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-300"
                @click="showPwd = !showPwd"
                :aria-pressed="showPwd.toString()"
                :title="showPwd ? 'Hide password' : 'Show password'"
                aria-controls="password"
              >
                <i class="fa-regular" :class="showPwd ? 'fa-eye-slash' : 'fa-eye'"></i>
                <span class="sr-only" x-text="showPwd ? 'Hide password' : 'Show password'"></span>
              </button>
            </div>

            <div class="error-message" x-text="errors.password" role="alert"></div>

            <ul class="text-[11px] mt-2 space-y-1" aria-live="polite">
              <li :class="ruleClass(passwordRules.len)">8 characters</li>
              <li :class="ruleClass(passwordRules.upper)">At least 1 uppercase letter</li>
              <li :class="ruleClass(passwordRules.special)">At least 1 special character</li>
            </ul>
          </div>

          <!-- Confirm -->
          <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1 req-dynamic" :class="req('password_confirmation')">Confirm Password</label>
            <div class="relative">
              <i class="fa-regular fa-shield-halved absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
              <input
                :type="showPwd2 ? 'text' : 'password'"
                id="password_confirmation"
                name="password_confirmation"
                placeholder="••••••••"
                class="w-full h-11 pl-9 pr-10 bg-white text-slate-700 border rounded-lg focus:outline-none focus-glow"
                x-model="form.password_confirmation"
                @input="validate('password_confirmation')"
                :class="inputClass('password_confirmation')"
                required
              >
              <button
                type="button"
                class="absolute inset-y-0 right-2 my-auto h-8 w-8 grid place-items-center rounded-md hover:bg-slate-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-300"
                @click="showPwd2 = !showPwd2"
                :aria-pressed="showPwd2.toString()"
                :title="showPwd2 ? 'Hide password' : 'Show password'"
                aria-controls="password_confirmation"
              >
                <i class="fa-regular" :class="showPwd2 ? 'fa-eye-slash' : 'fa-eye'"></i>
                <span class="sr-only" x-text="showPwd2 ? 'Hide password' : 'Show password'"></span>
              </button>
            </div>
            <div class="error-message" x-text="errors.password_confirmation" role="alert"></div>
          </div>
        </div>

        <!-- Bio -->
        <div>
          <label class="block text-xs font-semibold text-slate-700 mb-1 req-dynamic" :class="req('about')">About You (Bio)</label>
          <textarea name="about" rows="4" placeholder="Tell us about your background..." class="w-full px-3 py-2 bg-white text-slate-900 border rounded-lg focus:outline-none focus-glow" x-model.trim="form.about" @input="validate('about')" :class="inputClass('about')" required></textarea>
          <div class="error-message" x-text="errors.about" role="alert"></div>
        </div>

        <!-- Talents & Genres -->
        <div class="space-y-3">
          <div class="border-b border-slate-200 pb-2">
            <h2 class="text-xl font-bold text-indigo-700">Talents &amp; Genres</h2>
            <p class="text-slate-600 text-sm">
              Pili ng <b>Talent</b>, pumili ng <b>Genres</b>, tapos i-click <b>Confirm</b>.
              Lalabas lang ang pinili mo. Pwede kang magdagdag ng 2 o higit pang talents.
            </p>
          </div>

          <!-- Talent picker -->
          <div class="flex gap-2 items-end">
            <div class="flex-1">
              <label class="block mb-2 text-slate-700 font-semibold req-dynamic" :class="req('talents')">Select Talent</label>
              <select x-model="talentToAdd" class="w-full border border-slate-300 rounded-lg p-2 focus:ring focus:ring-indigo-300">
                <option value="" disabled selected>Select a talent</option>
                <template x-for="(genres, talent) in talentCatalog" :key="talent">
                  <option
                    :value="talent"
                    x-text="talent"
                    :disabled="selectedTalents.includes(talent) || selectedTalents.length >= maxTalents">
                  </option>
                </template>
              </select>
              <p class="text-xs text-slate-500 mt-1" x-text="`Selected: ${selectedTalents.length}/${maxTalents}`"></p>
            </div>
            <button
              type="button"
              @click="addTalent()"
              class="h-10 px-4 rounded-lg bg-indigo-600 text-white font-semibold shadow-sm hover:bg-indigo-700 disabled:opacity-50"
              :disabled="!talentToAdd || selectedTalents.includes(talentToAdd) || selectedTalents.length >= maxTalents">
              Add
            </button>
          </div>
          <div class="error-message mt-1" x-text="errors.talents" role="alert"></div>

          <!-- Selected talents -->
          <template x-for="t in selectedTalents" :key="t">
            <div class="border border-slate-200 rounded-lg p-4 space-y-3">
              <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                  <h3 class="font-semibold text-slate-800" x-text="t"></h3>
                  <span class="text-[11px] px-2 py-0.5 rounded-full border"
                        :class="(finalGenres[t]?.length||0) ? 'border-indigo-200 text-indigo-700 bg-indigo-50' : 'border-slate-200 text-slate-500 bg-slate-50'">
                    <span x-text="(finalGenres[t]||[]).length"></span>/<span x-text="maxGenresPerTalent"></span> genres
                  </span>
                </div>

                <!-- Suggest custom genre -->
                <div class="flex items-center gap-2 mb-2">
                  <input type="text"
                         class="h-9 w-full max-w-[220px] px-3 border rounded-lg"
                         placeholder="Suggest a genre…"
                         x-model.trim="newGenreText[t]"
                         @keydown.enter.prevent="addSuggestedGenre(t)">
                  <button type="button"
                          class="h-9 px-3 rounded-lg border border-slate-300 bg-white hover:bg-slate-50 text-sm"
                          @click="addSuggestedGenre(t)">
                    Add suggestion
                  </button>
                </div>

                <div class="flex items-center gap-3">
                  <button type="button" class="text-slate-700 text-sm font-semibold hover:underline"
                          @click="toggleEdit(t)"
                          x-text="isEditing[t] ? 'Cancel' : (finalGenres[t]?.length ? 'Edit' : 'Choose genres')"></button>
                  <button type="button" class="text-rose-600 text-sm font-semibold hover:underline" @click="removeTalent(t)">Remove</button>
                </div>
              </div>

              <!-- Confirmed chips (click to remove) -->
              <div x-show="!isEditing[t]" class="chips flex flex-wrap gap-2" x-cloak>
                <template x-if="(finalGenres[t] || []).length === 0">
                  <span class="text-xs text-slate-500">No genres selected.</span>
                </template>
                <template x-for="g in (finalGenres[t] || [])" :key="g">
                  <button type="button"
                          class="chip inline-flex items-center gap-1 text-sm px-2 py-1 rounded-full border border-slate-300 hover:bg-slate-100"
                          @click="removeGenreChip(t, g)">
                    <span x-text="g"></span>
                    <span aria-hidden="true" class="text-slate-500 text-xs">✕</span>
                  </button>
                </template>
              </div>

              <!-- Editing UI -->
              <div x-show="isEditing[t]" x-cloak>
                <div class="flex items-center gap-3 mb-2">
                  <p class="text-sm text-slate-500">Select genres for <strong x-text="t"></strong>:</p>
                  <span class="text-[11px] px-2 py-0.5 rounded-full border border-slate-200 text-slate-600 bg-slate-50"
                        x-text="`${(draftGenres[t]||[]).length}/${maxGenresPerTalent}`"></span>
                  <div class="ml-auto relative">
                    <input type="text" placeholder="Search genres…" class="h-9 w-44 px-3 border rounded-lg"
                           x-model.trim="genreFilter[t]">
                    <span class="absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 text-xs">⌘K</span>
                  </div>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 max-h-40 overflow-y-auto pr-2">
                  <template x-for="g in filteredGenres(t)" :key="g">
                    <label class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border tag cursor-pointer"
                           :class="(draftGenres[t] || []).includes(g) ? 'active' : ''"
                           @keydown.meta.k.stop.prevent="genreFilter[t]=''">
                      <input type="checkbox"
                             :value="g"
                             :disabled="!isChecked(t,g) && (draftGenres[t]?.length||0) >= maxGenresPerTalent"
                             @change="toggleDraftCheck(t, g, $event.target.checked)"
                             :checked="isChecked(t,g)"
                             class="h-4 w-4 text-indigo-600 border-slate-300 rounded">
                      <span class="text-sm" x-text="g"></span>
                    </label>
                  </template>
                </div>

                <div class="flex items-center justify-end gap-3 mt-3">
                  <button type="button" class="text-sm px-3 py-2 rounded-lg border border-slate-300 hover:bg-slate-50" @click="clearDraft(t)">Clear</button>
                  <button type="button" class="text-sm px-3 py-2 rounded-lg border border-slate-300 hover:bg-slate-50" @click="selectAllDraft(t)">Select all</button>
                  <button type="button" class="text-sm px-4 py-2 rounded-lg bg-indigo-600 text-white font-semibold shadow-sm hover:bg-indigo-700"
                          :disabled="(draftGenres[t]?.length||0)===0"
                          @click="confirmGenres(t)">Confirm</button>
                </div>
              </div>
            </div>
          </template>
        </div>

        <!-- Next -->
        <div class="pt-2 flex justify-end">
          <button type="button"
            @click="goStep(2)"
            :disabled="!step1CanAdvance"
            class="inline-flex items-center justify-center gap-2 h-11 px-5 rounded-lg bg-indigo-600 text-white font-semibold shadow-sm hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed">
            Next
          </button>
        </div>
      </section>

      <!-- ===========================
           STEP 2 — Agreement & Uploads
           =========================== -->
      <section x-show="step===2" x-transition class="space-y-5" aria-label="Step 2">
        <div class="border-b border-slate-200 pb-3">
          <h2 class="text-2xl font-bold text-indigo-700">Create Your Agreement Form</h2>
          <p class="text-slate-600 text-sm">Set terms and expectations prior to confirming appointments and processing transactions. Fill out the form below so both sides have a clear understanding.</p>
        </div>

        <div>
          <label class="block text-xs font-semibold text-slate-700 mb-1">Role</label>
          <div class="w-full h-11 px-3 flex items-center bg-slate-50 text-slate-700 border border-slate-300 rounded-lg">Choreographer/Coach</div>
          <input type="hidden" name="role" value="Choreographer/Coach">
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
          <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1 req-dynamic" :class="req2('service_fee')">Service Fee</label>
            <p class="text-xs text-slate-500 mb-1">Please indicate your standard rate for each session.</p>
            <input type="text" name="service_fee" placeholder="e.g., 500" inputmode="numeric" @input="digitsOnly2('service_fee', 5); validate2('service_fee')" x-model="form2.service_fee" class="w-full h-11 px-3 bg-white text-slate-900 border rounded-lg focus:outline-none focus-glow" :class="inputClass2('service_fee')" required>
            <div class="error-message" x-text="errors2.service_fee" role="alert"></div>
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1 req-dynamic" :class="req2('duration')">Session Duration</label>
            <p class="text-xs text-slate-500 mb-1">How long does one session usually take?</p>
            <select name="duration" x-model="form2.duration" @change="validate2('duration')" class="w-full h-11 px-3 bg-white text-slate-900 border rounded-lg focus:outline-none focus-glow" :class="inputClass2('duration')" required>
              <option value="" disabled selected>Select duration</option>
              <template x-for="n in 12" :key="n">
                <option :value="n===1 ? '1 hour' : (n+' hours')" x-text="n===1 ? '1 hour' : (n+' hours')"></option>
              </template>
            </select>
            <div class="error-message" x-text="errors2.duration" role="alert"></div>
          </div>
        </div>

        <!-- Payment -->
        <div>
          <label class="block text-xs font-semibold text-slate-700 mb-1 req-dynamic" :class="req2('payment')">Payment Method</label>
          <p class="text-xs text-slate-500 mb-1">Select how clients can pay you.</p>

          <select name="payment" x-model="form2.payment" @change="validate2('payment')" class="w-full h-11 px-3 bg-white text-slate-900 border rounded-lg focus:outline-none focus-glow" :class="inputClass2('payment')" required>
            <option value="" disabled>Select Payment Method</option>
            <option value="cash">Cash</option>
            <option value="online">Online Payment</option>
          </select>
          <div class="error-message" x-text="errors2.payment" role="alert"></div>

          <!-- Provider / Handle -->
          <div x-show="form2.payment==='online'" x-cloak class="mt-3 space-y-3">
            <div>
              <label class="block text-xs font-semibold text-slate-700 mb-1 req-dynamic" :class="req2('payment_provider')">Online Provider</label>
              <select name="payment_provider" x-model="form2.payment_provider" @change="validate2('payment_provider')"
                      class="w-full h-11 px-3 bg-white text-slate-900 border rounded-lg focus:outline-none focus-glow"
                      :class="inputClass2('payment_provider')" required>
                <option value="" disabled selected>Select provider</option>
                <option value="gcash">GCash</option>
                <option value="maya">Maya</option>
                <option value="paypal">PayPal</option>
              </select>
              <div class="error-message" x-text="errors2.payment_provider" role="alert"></div>
            </div>

            <div>
              <label class="block text-xs font-semibold text-slate-700 mb-1 req-dynamic" :class="req2('payment_handle')" x-text="paymentHandleLabel"></label>
           <input
  type="text"
  name="payment_handle"
  placeholder="Enter up to 11 digits"
  class="w-full h-11 px-3 bg-white text-slate-900 border rounded-lg focus:outline-none focus-glow"
  :class="inputClass2('payment_handle')"
  x-model.trim="form2.payment_handle"
  @input.debounce.300ms="validate2('payment_handle')"
  @input="form2.payment_handle = form2.payment_handle.replace(/\D/g,'').slice(0,11)"
  maxlength="11"
  required
>

              <p class="text-[11px] text-slate-500 mt-1" x-text="paymentHandleHelp"></p>
              <div class="error-message" x-text="errors2.payment_handle" role="alert"></div>
            </div>
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
          <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Minimum Notice Required</label>
            <p class="text-xs text-slate-500 mb-2">How many hours or days in advance should clients notify you?</p>
            <div class="flex gap-4 items-center">
              <div class="flex items-center gap-2">
                <input type="number" name="notice_hours" class="w-24 h-11 px-3 bg-white text-slate-900 border rounded-lg focus:outline-none focus-glow" x-model.number="form2.notice_hours" @input="validate2('notice_hours')" min="0" max="99">
                <span class="text-slate-600 text-sm">Hours</span>
              </div>
              <div class="flex items-center gap-2">
                <input type="number" name="notice_days" class="w-24 h-11 px-3 bg-white text-slate-900 border rounded-lg focus:outline-none focus-glow" x-model.number="form2.notice_days" @input="validate2('notice_days')" min="0" max="30">
                <span class="text-slate-600 text-sm">Days</span>
              </div>
            </div>
            <div class="error-message" x-show="form2.notice_hours>99">Maximum is 99 hours</div>
            <div class="error-message" x-show="form2.notice_days>30">Maximum is 30 days</div>
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1 req-dynamic" :class="req2('method')">Cancellation / Rescheduling Method</label>
            <input
              type="email"
              name="method"
              placeholder="@gmail.com"
              inputmode="email"
              autocomplete="email"
              pattern="^[a-zA-Z0-9._%+-]+@gmail\.com$"
              title="Please enter a @gmail.com address"
              class="w-full h-11 px-3 bg-white text-slate-900 border rounded-lg focus:outline-none focus-glow"
              x-model.trim="form2.method"
              @input="validate2('method')"
              :class="inputClass2('method')"
              required
            >
            <div class="error-message" x-text="errors2.method" role="alert"></div>
          </div>
        </div>

        <!-- Uploads -->
        <div class="space-y-8">
          <!-- Portfolio -->
          <div class="space-y-2">
            <label class="block text-xs font-semibold text-slate-700 mb-1 req-dynamic" :class="req2('portfolio')">Upload Portfolio</label>
            <div class="flex flex-wrap items-center justify-between gap-2">
              <p class="text-[12px] text-slate-500">Accepted: PDF, JPG, PNG</p>
              <div class="flex items-center gap-2" x-show="portfolio.name">
                <span class="text-[12px] text-slate-500">Preview style:</span>
                <div class="flex items-center gap-1">
                  <label class="inline-flex items-center gap-1 text-[12px]"><input type="radio" class="accent-indigo-600" x-model="portfolio.mode" value="frame"> Frame</label>
                  <label class="inline-flex items-center gap-1 text-[12px]"><input type="radio" class="accent-indigo-600" x-model="portfolio.mode" value="edge"> Edge-to-edge</label>
                  <label class="inline-flex items-center gap-1 text-[12px]"><input type="radio" class="accent-indigo-600" x-model="portfolio.mode" value="plain"> Plain image</label>
                </div>
              </div>
            </div>

            <div :class="dropzoneClass(portfolio)" @click="pick('portfolio')">
              <template x-if="!portfolio.name">
                <div class="flex flex-col items-center gap-2">
                  <i class="fas fa-cloud-upload-alt text-3xl text-slate-400"></i>
                  <p class="text-slate-600 text-sm">Drag &amp; Drop or Click to Upload</p>
                </div>
              </template>

              <template x-if="portfolio.name">
                <div class="w-full">
                  <template x-if="portfolio.preview && portfolio.mode !== 'plain'">
                    <div class="relative w-full rounded-xl overflow-hidden border border-slate-200 bg-white">
                      <div class="aspect-[4/3] w-full">
                        <img :src="portfolio.preview" :alt="portfolio.name" class="w-full h-full" :class="portfolio.mode==='edge' ? 'object-cover' : 'object-contain bg-slate-900/5'">
                      </div>
                    </div>
                  </template>

                  <template x-if="portfolio.preview && portfolio.mode === 'plain'">
                    <img :src="portfolio.preview" :alt="portfolio.name" class="max-w-full rounded-lg border" />
                  </template>

                  <template x-if="!portfolio.preview">
                    <div class="flex items-center justify-between w-full bg-slate-100 rounded px-3 py-2 border border-slate-300">
                      <span class="text-slate-800 text-sm truncate" x-text="portfolio.name"></span>
                      <span class="text-[11px] text-slate-500 uppercase" x-text="portfolio.ext"></span>
                    </div>
                  </template>

                  <div class="flex justify-end w-full mt-2 gap-3">
                    <button type="button" @click.stop="pick('portfolio')" class="text-indigo-700 hover:text-indigo-800 text-sm font-semibold">Change</button>
                    <button type="button" @click.stop="remove('portfolio'); validate2('portfolio')" class="text-rose-600 hover:text-rose-700 text-sm font-semibold">Remove</button>
                  </div>
                </div>
              </template>

              <input class="hidden" x-ref="portfolio" type="file" name="portfolio" accept=".pdf,.jpg,.jpeg,.png" @change="onFile('portfolio', {imageOnly:false}); validate2('portfolio')" required>
            </div>
            <div class="error-message" x-text="errors2.portfolio" role="alert"></div>
          </div>

          <!-- Valid ID -->
          <div class="space-y-2">
            <label class="block text-xs font-semibold text-slate-700 mb-1 req-dynamic" :class="req2('valid_id')">Valid Government ID (image)</label>
            <div class="flex items-center justify-end gap-2" x-show="valid_id.preview">
              <span class="text-[12px] text-slate-500">Preview style:</span>
              <label class="inline-flex items-center gap-1 text:[12px]"><input type="radio" class="accent-indigo-600" x-model="valid_id.mode" value="frame"> Frame</label>
              <label class="inline-flex items-center gap-1 text:[12px]"><input type="radio" class="accent-indigo-600" x-model="valid_id.mode" value="edge"> Edge-to-edge</label>
              <label class="inline-flex items-center gap-1 text:[12px]"><input type="radio" class="accent-indigo-600" x-model="valid_id.mode" value="plain"> Plain image</label>
            </div>

            <div :class="dropzoneClass(valid_id)" @click="pick('valid_id')">
              <template x-if="!valid_id.preview">
                <div class="flex flex-col items-center gap-2">
                  <i class="fa-regular fa-id-card text-3xl text-slate-400"></i>
                  <p class="text-slate-600 text-sm">Click to upload your ID (front)</p>
                  <p class="text-slate-500 text-xs">Accepted: JPG, PNG</p>
                </div>
              </template>

              <template x-if="valid_id.preview">
                <div class="w-full">
                  <template x-if="valid_id.mode !== 'plain'">
                    <div class="relative w-full rounded-xl overflow-hidden border border-slate-200 bg-white">
                      <div class="aspect-[4/3] w-full">
                        <img :src="valid_id.preview" alt="ID preview" class="w-full h-full" :class="valid_id.mode==='edge' ? 'object-cover' : 'object-contain bg-slate-900/5'">
                      </div>
                    </div>
                  </template>

                  <template x-if="valid_id.mode === 'plain'">
                    <img :src="valid_id.preview" alt="ID preview" class="max-w-full rounded-lg border" />
                  </template>

                  <div class="flex justify-end w-full mt-2 gap-3">
                    <button type="button" @click.stop="pick('valid_id')" class="text-indigo-700 hover:text-indigo-800 text-sm font-semibold">Change</button>
                    <button type="button" @click.stop="remove('valid_id'); validate2('valid_id')" class="text-rose-600 hover:text-rose-700 text-sm font-semibold">Remove</button>
                  </div>
                </div>
              </template>

              <input class="hidden" x-ref="valid_id" type="file" name="valid_id" accept=".jpg,.jpeg,.png" @change="onFile('valid_id', {imageOnly:true}); validate2('valid_id')" required>
            </div>
            <div class="error-message" x-text="errors2.valid_id" role="alert"></div>
          </div>

          <!-- Selfie with ID (guide toggle) -->
          <div class="space-y-2" x-data="{ showGuide:false, today: (new Date()).toLocaleDateString('en-US', { year:'numeric', month:'long', day:'numeric' }) }">
            <label class=" text-xs font-semibold text-slate-700 mb-1 req-dynamic flex items-center gap-2" :class="req2('id_selfie')" :aria-describedby="showGuide ? 'id-selfie-guide' : null">
              Photo of You Holding the Same ID (image)
              <button type="button" @click="showGuide = !showGuide" class="inline-flex items-center gap-1 text-[11px] font-normal px-2 py-1 rounded-md border border-slate-200 hover:border-slate-300 text-slate-600 hover:text-slate-700" :aria-expanded="showGuide" aria-controls="id-selfie-guide">
                <i class="fa-regular fa-circle-question text-[13px]"></i>
                Label guide
              </button>
            </label>

            <div id="id-selfie-guide" x-show="showGuide" x-transition class="rounded-lg border border-slate-200 bg-slate-50/60 p-3 text-[12px] leading-5 text-slate-700">
              <ol class="list-decimal pl-5 space-y-1">
                <li>Take a clear selfie holding your valid ID <em>and</em> a paper with <strong x-text="today"></strong> signature and your full name written on it.</li>
                <li>Your face must be fully visible (no mask, hat, or sunglasses).</li>
                <li>The photo must be clear and readable.</li>
                <li>Photos are used only for ID verification.</li>
              </ol>
            </div>

            <div class="flex items-center justify-end gap-2" x-show="id_selfie.preview">
              <span class="text:[12px] text-slate-500">Preview style:</span>
              <label class="inline-flex items-center gap-1 text:[12px]"><input type="radio" class="accent-indigo-600" x-model="id_selfie.mode" value="frame"> Frame</label>
              <label class="inline-flex items-center gap-1 text:[12px]"><input type="radio" class="accent-indigo-600" x-model="id_selfie.mode" value="edge"> Edge-to-edge</label>
              <label class="inline-flex items-center gap-1 text:[12px]"><input type="radio" class="accent-indigo-600" x-model="id_selfie.mode" value="plain"> Plain image</label>
            </div>

            <div :class="dropzoneClass(id_selfie)" @click="pick('id_selfie')">
              <template x-if="!id_selfie.preview">
                <div class="flex flex-col items-center gap-2">
                  <i class="fa-regular fa-image text-3xl text-slate-400"></i>
                  <p class="text-slate-600 text-sm">Click to upload your selfie with the ID</p>
                  <p class="text-slate-500 text-xs">Accepted: JPG, PNG</p>
                </div>
              </template>

              <template x-if="id_selfie.preview">
                <div class="w-full">
                  <template x-if="id_selfie.mode !== 'plain'">
                    <div class="relative w-full rounded-xl overflow-hidden border border-slate-200 bg-white">
                      <div class="aspect-[4/3] w-full">
                        <img :src="id_selfie.preview" alt="Selfie preview" class="w-full h-full" :class="id_selfie.mode==='edge' ? 'object-cover' : 'object-contain bg-slate-900/5'">
                      </div>
                    </div>
                  </template>

                  <template x-if="id_selfie.mode === 'plain'">
                    <img :src="id_selfie.preview" alt="Selfie preview" class="max-w-full rounded-lg border" />
                  </template>

                  <div class="flex justify-end w-full mt-2 gap-3">
                    <button type="button" @click.stop="pick('id_selfie')" class="text-indigo-700 hover:text-indigo-800 text-sm font-semibold">Change</button>
                    <button type="button" @click.stop="remove('id_selfie'); validate2('id_selfie')" class="text-rose-600 hover:text-rose-700 text-sm font-semibold">Remove</button>
                  </div>
                </div>
              </template>

              <input class="hidden" x-ref="id_selfie" type="file" name="id_selfie" accept=".jpg,.jpeg,.png" @change="onFile('id_selfie', {imageOnly:true}); validate2('id_selfie')" required>
            </div>
            <div class="error-message" x-text="errors2.id_selfie" role="alert"></div>
          </div>
        </div>

        <!-- Terms + Submit -->
        <div class="flex items-start gap-3">
          <input type="checkbox" name="terms" id="terms" class="mt-0.5 h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 focus:ring-offset-0" x-model="form2.terms" @change="validate2('terms')">
          <label for="terms" class="text-xs text-slate-700 req-dynamic" :class="req2('terms')">I accept the <a href="{{ route('terms') }}" class="font-semibold text-indigo-700 hover:underline">Terms and Conditions</a></label>
        </div>
        <div class="error-message" x-text="errors2.terms" role="alert"></div>

        <div class="pt-1 flex items-center justify-between">
          <button type="button" @click="goStep(1)" class="inline-flex items-center justify-center gap-2 h-11 px-4 rounded-lg border border-slate-300 bg-white hover:bg-slate-50 text-slate-700">Back</button>
          <button
            type="submit"
            :disabled="!step2Valid || isSubmitting"
            :aria-busy="isSubmitting ? 'true' : 'false'"
            class="inline-flex items-center justify-center gap-2 h-11 px-5 rounded-lg bg-indigo-600 text-white font-semibold shadow-sm hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <svg x-show="isSubmitting" x-cloak class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke-width="4"></circle>
              <path class="opacity-75" stroke-width="4" d="M4 12a8 8 0 018-8"></path>
            </svg>
            <span x-text="isSubmitting ? 'Submitting…' : 'Submit'"></span>
          </button>
        </div>
      </section>
    </form>

    <!-- Success modal (server controlled) -->
    <div x-cloak x-show="@json(session('emailSent'))" x-transition:enter="transition ease-out duration-400" x-transition:enter-start="opacity-0 translate-y-6 scale-95" x-transition:enter-end="opacity-100 translate-y-0 scale-100" x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100 translate-y-0 scale-100" x-transition:leave-end="opacity-0 translate-y-6 scale-95" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm p-4" role="dialog" aria-modal="true" aria-label="Verification sent">
      <div class="bg-white p-8 rounded-2xl shadow-2xl max-w-sm w-full mx-4 border border-slate-200">
        <div class="text-center">
          <svg class="w-16 h-16 text-amber-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6 0a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
          <h3 class="mt-4 text-2xl font-bold text-slate-900">Thanks{{ session('coachName') ? ', '.e(session('coachName')) : '' }}! Check Your Email</h3>
          <p class="mt-2 text-sm text-slate-600">We’ve sent a verification email. Please confirm your email to activate your account.</p>
          <a href="https://mail.google.com/mail/u/0/#inbox" class="mt-6 inline-block w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 rounded-lg shadow-sm transition">Go to Gmail</a>
        </div>
      </div>
    </div>

    <!-- Submitting overlay -->
    <div x-cloak x-show="isSubmitting" class="fixed inset-0 z-40 bg-black/40 backdrop-blur-sm flex items-center justify-center p-4" x-transition.opacity aria-hidden="true">
      <div class="bg-white border border-slate-200 rounded-2xl shadow-2xl px-6 py-5 text-center max-w-sm w-full">
        <svg class="w-10 h-10 mx-auto animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke-width="4"></circle>
          <path class="opacity-75" stroke-width="4" d="M4 12a8 8 0 018-8"></path>
        </svg>
        <p class="mt-3 font-semibold text-slate-900">Processing your submission…</p>
        <p class="mt-1 text-sm text-slate-600">Please wait while we verify and send the email.</p>
      </div>
    </div>

    <p class="mt-6 text-center text-xs text-slate-500">© {{ date('Y') }} Groove. All rights reserved.</p>
  </main>
  
<script>
  // helpers
  const isEmail = (v) =>
    /^[^\s@]+@[^\s@]+$/.test(String(v || "").trim()) &&
    /\.[A-Za-z]{2,}$/.test(String(v || "").trim());
  const onlyDigits = (v) => (v || "").replace(/\D+/g, "");
  const hasUpper = (v) => /[A-Z]/.test(v || "");
  const hasSpecial = (v) => /[^A-Za-z0-9]/.test(v || "");
  const isGmail = (v) => /^[a-z0-9._%+-]+@gmail\.com$/i.test(String(v || "").trim());

  function coachRegistrationForm () {
    return {
      step: 1,
      isSubmitting: false, // loading state
      form: {
        firstname: "", middlename: "", lastname: "", suffix: "",
        birth_year: "", birth_month: "", birth_day: "",
        contact: "", email: "", username: "",
        password: "", password_confirmation: "",
        about: ""
      },
      form2: {
        role: "Choreographer/Coach",
        service_fee: "", duration: "", payment: "",
        notice_hours: null, notice_days: null, method: "",
        terms: false,

        // Payment additions (no QR):
        payment_provider: "",
        payment_handle: ""
      },

      // ===== Talents & Genres data =====
      talentCatalog: {
        "Dance": ["Hip-hop","Breaking","Popping","Locking","Krump","House","Waacking","Voguing","Tutting","Animation","Litefeet","Memphis Jookin","Urban","Street","Choreography","Lyrical","Contemporary","Modern","Jazz","Theatre Jazz","Heels","Commercial","K-pop","J-pop","Ballet","Classical Ballet","Neoclassical","Pointe","Character","Ballroom","Waltz","Tango","Viennese Waltz","Foxtrot","Quickstep","Latin Ballroom","Cha-cha","Rumba","Samba","Paso Doble","Jive","Swing","Lindy Hop","Charleston","Balboa","West Coast Swing","East Coast Swing","Salsa (On1)","Salsa (On2)","Bachata (Sensual)","Bachata (Dominican)","Kizomba","Zouk","Afrobeats","Amapiano","Azonto","Dancehall","Reggaeton","Bollywood","Bhangra","Garba","Kathak Fusion","Tap","Irish","Flamenco","Belly Dance (Raqs Sharqi)","Hula","Tahitian","Cheer","Pom","Majorette","Drill","Freestyle","Experimental","Contact Improvisation","Capoeira","Folk/Traditional (incl. Tinikling, Cariñosa)"],
        "Singing": ["Pop","K-pop","J-pop","OPM","R&B","Contemporary R&B","Neo-Soul","Soul","Funk","Gospel","Ballad","Power Ballad","Acoustic","Singer-Songwriter","Indie","Alternative","Rock","Pop Rock","Alt Rock","Classic Rock","Punk","New Wave","Metal","Metalcore","Hard Rock","Hip-hop/Rap","Trap","Boom Bap","Spoken Word","EDM","House","Techno","Trance","Drum & Bass","Dubstep","Electropop","Dance","Country","Bluegrass","Folk","Americana","Blues","Jazz","Swing","Big Band","Bossa Nova","Latin","Reggaeton","Salsa","Bachata","Bolero","Mariachi","Reggae","Ska","Afrobeats/Amapiano (Vocal)","World","Classical","Opera","Art Song","Oratorio","Musical Theater","A Cappella","Barbershop","Choral","Lullaby/Children","Lo-fi","Ambient","Experimental","Holiday"],
        "Theater": ["Stage Acting","Musical","Shakespearean","Classical Greek/Roman","Period/Farce","Comedy","Drama","Melodrama","Improvisation","Devised Theater","Physical Theatre","Movement-Based","Mask Work","Pantomime","Commedia dell’arte","Absurdist","Epic/Brechtian","Realism/Naturalism","Expressionism","Site-Specific/Immersive","Monologue","Reader’s Theater","Puppetry","Shadow Play","Children’s Theatre","Experimental/Avant-garde"],
        "Acting": ["Film Acting","TV Acting","Web Series/Streaming","Teleserye/Soap","Commercial/Advert","Hosting/Presenting","Model/Print","Comedy/Sketch","Sitcom (Multi-cam)","Single-cam Drama","Action/Thriller","Rom-com","Period Piece","Voice Acting","Animation VO","Video Game VO","ADR/Dubbing","Narration/Documentary","Audiobook","Green Screen","Motion Capture/Performance Capture","Stunt/Action Basics","Audition Technique","Cold Reading","On-Camera Technique","Method Acting","Meisner Technique","Chekhov Technique","Classical Technique","Improvisation for Actors"]
      },
      talentToAdd: "",
      selectedTalents: [],

      // === config & helpers ===
      maxTalents: 8,
      maxGenresPerTalent: 12,
      genreFilter: {}, // { [talent]: 'search text' }

      // custom/suggested genres per-talent
      customGenres: {},   // { [talent]: string[] }
      newGenreText: {},   // { [talent]: string }

      // Editing state + genre stores
      isEditing: {},        // { [talent]: boolean }
      finalGenres: {},      // { [talent]: string[] }
      draftGenres: {},      // { [talent]: string[] }

      // Mirrors for hidden inputs
      talentsPlain: "",
      genresPlain: "",

      // Files
      portfolio:  { file: null, name: "", ext: "", preview: "", mode: "frame" },
      valid_id:   { file: null, name: "", ext: "", preview: "", mode: "frame" },
      id_selfie:  { file: null, name: "", ext: "", preview: "", mode: "frame" },

      // Errors
      errors:  {
        firstname:"", lastname:"", birthdate:"", contact:"", email:"", username:"",
        password:"", password_confirmation:"", about:"", address:"", talents:""
      },
      errors2: {
        service_fee:"", duration:"", payment:"", method:"",
        portfolio:"", valid_id:"", id_selfie:"", terms:"",
        notice_hours:"", notice_days:"",
        payment_provider:"", payment_handle:""
      },

      passwordRules: { len:false, upper:false, special:false },
      years: [],
      months: ["January","February","March","April","May","June","July","August","September","October","November","December"],
      daysInMonth: [],

      init () {
        const now = new Date();
        const maxY = now.getFullYear() - 13;
        const minY = maxY - 80;
        this.years = [];
        for (let y = maxY; y >= minY; y--) this.years.push(y);
        this.updateDays();

        const getVal = (sel) => (document.querySelector(`[name="${sel}"]`)?.value || "").trim();

        ["firstname","middlename","lastname","suffix","email","username","about"].forEach(k => {
          const v = getVal(k); if (v) this.form[k] = v;
        });
        ["birth_year","birth_month","birth_day"].forEach(k => {
          const v = getVal(k); if (v) this.form[k] = String(v);
        });

        const c = getVal("contact");
        if (c) this.form.contact = onlyDigits(c).replace(/^63/, "").replace(/^0/, "");

        ["service_fee","duration","payment","method"].forEach(k => {
          const v = getVal(k); if (v) this.form2[k] = v;
        });
        ["payment_provider","payment_handle"].forEach(k => {
          const v = getVal(k); if (v) this.form2[k] = v;
        });

        this.evalPasswordRules();
        this.updateTalents();
        this.initLoadingWatcher(); // reset submitting state on bfcache restore
      },

      // ===== UI helpers =====
      inputClass (field) { return this.errors[field] ? "border-rose-400 bg-rose-50" : "border-slate-300"; },
      inputClass2 (field) { return this.errors2[field] ? "border-rose-400 bg-rose-50" : "border-slate-300"; },
      ruleClass (ok) { return ok ? "text-green-600" : "text-slate-500"; },
      pick (key) { this.$refs[key]?.click(); },
      dropzoneClass (obj) {
        return [
          "w-full border-2 border-dashed rounded-xl bg-white/60 hover:bg-white transition p-4 cursor-pointer",
          "min-h-[120px] grid place-items-center text-center",
          obj.file ? "border-indigo-300" : "border-slate-300"
        ].join(" ");
      },
      initLoadingWatcher () {
        window.addEventListener('pageshow', () => { this.isSubmitting = false; });
      },

      // ===== Asterisks / Required state =====
      req (field) { return this.isMissing(field) ? "is-missing" : ""; },
      req2 (field) { return this.isMissing2(field) ? "is-missing" : ""; },

      isMissing (field) {
        switch (field) {
          case "firstname": return !(this.form.firstname?.trim());
          case "lastname":  return !(this.form.lastname?.trim());
          case "birthdate": {
            const { birth_year, birth_month, birth_day } = this.form;
            if (!birth_year || !birth_month || !birth_day) return true;
            const y = +birth_year, m = +birth_month, d = +birth_day;
            const dt = new Date(y, m - 1, d);
            const valid = dt && (dt.getMonth() + 1) === m && dt.getDate() === d && dt.getFullYear() === y;
            if (!valid) return true;
            const today = new Date(); let age = today.getFullYear() - y;
            const mDelta = (today.getMonth() + 1) - m; const dDelta = today.getDate() - d;
            if (mDelta < 0 || (mDelta === 0 && dDelta < 0)) age--;
            return age < 13;
          }
          case "address": {
            const region = document.querySelector('[name="region_code"]')?.value || "";
            const province = document.querySelector('[name="province_code"]')?.value || "";
            const city = document.querySelector('[name="city_code"]')?.value || "";
            const brgy = document.querySelector('[name="barangay_code"]')?.value || "";
            return !(region && province && city && brgy);
          }
          case "contact": return !(onlyDigits(this.form.contact).length === 10 && /^9/.test(this.form.contact));
          case "email":   return !isGmail(this.form.email);
          case "username":return !(this.form.username?.length >= 3);
          case "password": {
            const p = this.form.password || "";
            return !(p.length >= 8 && hasUpper(p) && hasSpecial(p));
          }
          case "password_confirmation":
            return !(this.form.password_confirmation && this.form.password_confirmation === this.form.password);
          case "about":   return !((this.form.about || "").trim().length >= 10);
          case "talents": return !(this.selectedTalents.length > 0);
          default: return false;
        }
      },

      isMissing2 (field) {
        switch (field) {
          case "service_fee": {
            if (!this.form2.service_fee) return true;
            const v = Number(this.form2.service_fee);
            return !(Number.isFinite(v) && v >= 1 && v <= 10000);
          }
          case "duration": return !(this.form2.duration?.trim().length);
          case "payment":  return !this.form2.payment;
          case "method":   return !isGmail(this.form2.method);
          case "portfolio":
          case "valid_id":
          case "id_selfie": {
            const obj = this[field];
            return !(obj && (obj.file || obj.preview));
          }
          case "terms":    return !this.form2.terms;

          // When payment is online
          case "payment_provider":
            return this.form2.payment === "online" && !this.form2.payment_provider;

          case "payment_handle": {
            if (this.form2.payment !== "online" || !this.form2.payment_provider) return false;
            const v = (this.form2.payment_handle || "").trim();
            if (!v) return true;
            if (["gcash","maya"].includes(this.form2.payment_provider)) {
              const digits = v.replace(/\D+/g, "");
              return !(digits.length === 11 && /^09/.test(digits));
            }
            if (this.form2.payment_provider === "paypal") {
              return !/^[^\s@]+@[^\s@]+\.[A-Za-z]{2,}$/.test(v);
            }
            return false;
          }

          default: return false;
        }
      },

      // ===== File handling =====
      async onFile (key, { imageOnly }) {
        const input = this.$refs[key];
        const file = input?.files?.[0];
        if (!file) return;
        const ext = (file.name.split(".").pop() || "").toLowerCase();
        if (imageOnly && !["jpg","jpeg","png"].includes(ext)) {
          this[key] = { file:null, name:"", ext:"", preview:"", mode:"frame" };
          alert("Please upload a JPG or PNG image.");
          if (["portfolio","valid_id","id_selfie"].includes(key)) {
            this.errors2[key] = "This file is required.";
          }
          return;
        }
        let preview = "";
        if (["jpg","jpeg","png"].includes(ext)) {
          preview = await new Promise((resolve) => {
            const reader = new FileReader();
            reader.onload = (e) => resolve(e.target.result);
            reader.readAsDataURL(file);
          });
        }
        this[key] = { file, name:file.name, ext:ext.toUpperCase(), preview, mode:this[key]?.mode || "frame" };
        if (["portfolio","valid_id","id_selfie"].includes(key)) this.errors2[key] = "";
      },
      remove (key) {
        this[key] = { file:null, name:"", ext:"", preview:"", mode:"frame" };
        if (this.$refs[key]) this.$refs[key].value = "";
        if (["portfolio","valid_id","id_selfie"].includes(key)) {
          this.errors2[key] = "This file is required.";
        }
      },

      // ===== Data transforms & validation =====
      digitsOnly (key) { this.form[key] = onlyDigits(this.form[key]).slice(0, 10); },
      digitsOnly2 (key, maxLen = 9) {
        let raw = onlyDigits(this.form2[key]).slice(0, maxLen);
        if (raw.length) raw = String(parseInt(raw, 10));
        if (raw === "0") raw = "";
        this.form2[key] = raw;
      },
      updateDays () {
        const y = parseInt(this.form.birth_year);
        const m = parseInt(this.form.birth_month);
        let days = 31;
        if (m) {
          if ([4,6,9,11].includes(m)) days = 30;
          else if (m === 2) {
            const leap = y && ((y % 4 === 0 && y % 100 !== 0) || (y % 400 === 0));
            days = leap ? 29 : 28;
          }
        }
        this.daysInMonth = Array.from({ length: days }, (_, i) => i + 1);
        const d = parseInt(this.form.birth_day);
        if (d && d > days) this.form.birth_day = String(days);
      },
      evalPasswordRules () {
        const p = this.form.password || "";
        this.passwordRules = { len: (p.length >= 8), upper: hasUpper(p), special: hasSpecial(p) };
      },

      // ===== Talents & Genres =====
      addTalent () {
        const t = this.talentToAdd;
        if (!t) return;
        if (this.selectedTalents.includes(t)) return;
        if (this.selectedTalents.length >= this.maxTalents) return;

        this.selectedTalents = [...this.selectedTalents, t];

        if (!Array.isArray(this.finalGenres[t]))  this.finalGenres  = { ...this.finalGenres,  [t]: [] };
        if (!Array.isArray(this.draftGenres[t]))  this.draftGenres  = { ...this.draftGenres,  [t]: [] };
        if (!Array.isArray(this.customGenres[t])) this.customGenres = { ...this.customGenres, [t]: [] };
        if (typeof this.isEditing[t]      !== "boolean") this.isEditing   = { ...this.isEditing,   [t]: false };
        if (typeof this.genreFilter[t]    !== "string")  this.genreFilter = { ...this.genreFilter, [t]: "" };
        if (typeof this.newGenreText[t]   !== "string")  this.newGenreText= { ...this.newGenreText,[t]: "" };

        this.isEditing = { ...this.isEditing, [t]: true };
        this.talentToAdd = "";
        this.updateTalents();
        this.validate("talents");
      },
      removeTalent (t) {
        this.selectedTalents = this.selectedTalents.filter(x => x !== t);
        const fg = { ...this.finalGenres }; delete fg[t]; this.finalGenres = fg;
        const dg = { ...this.draftGenres }; delete dg[t]; this.draftGenres = dg;
        const cg = { ...this.customGenres}; delete cg[t]; this.customGenres= cg;
        const ie = { ...this.isEditing   }; delete ie[t]; this.isEditing   = ie;
        const gf = { ...this.genreFilter }; delete gf[t]; this.genreFilter = gf;
        const ng = { ...this.newGenreText}; delete ng[t]; this.newGenreText= ng;

        this.updateTalents();
        this.validate("talents");
      },
      toggleEdit (t) {
        const next = !this.isEditing[t];
        this.isEditing = { ...this.isEditing, [t]: next };
        if (next) {
          const seed = Array.isArray(this.finalGenres[t]) ? [...this.finalGenres[t]] : [];
          this.draftGenres = { ...this.draftGenres, [t]: seed.slice(0, this.maxGenresPerTalent) };
        }
      },

      isChecked (t, g) {
        return Array.isArray(this.draftGenres[t]) && this.draftGenres[t].includes(g);
      },
      toggleDraftCheck (t, g, checked) {
        const list = Array.isArray(this.draftGenres[t]) ? [...this.draftGenres[t]] : [];
        if (checked) {
          if (!list.includes(g)) {
            if (list.length >= this.maxGenresPerTalent) return;
            list.push(g);
          }
        } else {
          const i = list.indexOf(g);
          if (i > -1) list.splice(i, 1);
        }
        this.draftGenres = { ...this.draftGenres, [t]: list };
      },
      clearDraft (t) {
        this.draftGenres = { ...this.draftGenres, [t]: [] };
      },
      selectAllDraft (t) {
        const all = Array.isArray(this.talentCatalog[t]) ? this.talentCatalog[t] : [];
        const merged = this._mergeGenres(t, all);
        const capped = merged.slice(0, this.maxGenresPerTalent);
        this.draftGenres = { ...this.draftGenres, [t]: capped };
      },
      confirmGenres (t) {
        const chosen = Array.isArray(this.draftGenres[t]) ? this.draftGenres[t] : [];
        this.finalGenres = { ...this.finalGenres, [t]: chosen.slice(0, this.maxGenresPerTalent) };
        this.isEditing = { ...this.isEditing, [t]: false };
        this.updateTalents();
      },
      removeGenreChip (t, g) {
        const cur = Array.isArray(this.finalGenres[t]) ? [...this.finalGenres[t]] : [];
        const idx = cur.indexOf(g);
        if (idx > -1) {
          cur.splice(idx, 1);
          this.finalGenres = { ...this.finalGenres, [t]: cur };
          if (Array.isArray(this.draftGenres[t])) {
            this.draftGenres = { ...this.draftGenres, [t]: this.draftGenres[t].filter(x => x !== g) };
          }
          this.updateTalents();
          this.validate("talents");
        }
      },

      _mergeGenres (t, list) {
        const seen = new Set();
        const out = [];
        for (const g of list || []) {
          const key = String(g || "").trim();
          if (!key) continue;
          if (!seen.has(key)) {
            seen.add(key);
            out.push(key);
          }
        }
        const custom = Array.isArray(this.customGenres[t]) ? this.customGenres[t] : [];
        for (const g of custom) {
          if (!seen.has(g)) {
            seen.add(g);
            out.push(g);
          }
        }
        return out;
      },

      filteredGenres (t) {
        const base = Array.isArray(this.talentCatalog[t]) ? this.talentCatalog[t] : [];
        const custom = Array.isArray(this.customGenres[t]) ? this.customGenres[t] : [];
        const merged = this._mergeGenres(t, [...base, ...custom]);
        const q = (this.genreFilter[t] || "").toLowerCase().trim();
        if (!q) return merged;
        return merged.filter(g => g.toLowerCase().includes(q));
      },

      addSuggestedGenre (t) {
        const raw = (this.newGenreText[t] || "").trim();
        if (!raw) return;
        const pretty = raw.replace(/\s+/g, " ").replace(/\s*-\s*/g, "-");
        const all = new Set(this._mergeGenres(t, this.talentCatalog[t] || []));
        if (!all.has(pretty)) {
          const cur = Array.isArray(this.customGenres[t]) ? [...this.customGenres[t]] : [];
          if (!cur.includes(pretty)) {
            this.customGenres = { ...this.customGenres, [t]: [...cur, pretty] };
          }
        }
        if (this.isEditing[t]) {
          const draft = Array.isArray(this.draftGenres[t]) ? [...this.draftGenres[t]] : [];
          if (!draft.includes(pretty) && draft.length < this.maxGenresPerTalent) {
            this.draftGenres = { ...this.draftGenres, [t]: [...draft, pretty] };
          }
        }
        this.newGenreText = { ...this.newGenreText, [t]: "" };
        this.updateTalents();
      },

      // Mirror to hidden inputs
      updateTalents () {
        const talentsArr = [...this.selectedTalents];
        const genresMap = {};
        for (const t of talentsArr) {
          genresMap[t] = Array.isArray(this.finalGenres[t]) ? [...this.finalGenres[t]] : [];
        }
        this.talentsPlain = talentsArr.join(", ");
        this.genresPlain = Object.entries(genresMap).map(([t, arr]) => `${t}: ${arr.join(", ")}`).join(" | ");

        try {
          if (this.$refs.talentsJsonField) {
            this.$refs.talentsJsonField.value = JSON.stringify(talentsArr);
          }
          if (this.$refs.genresJsonField) {
            this.$refs.genresJsonField.value = JSON.stringify(genresMap);
          }
        } catch (e) { /* noop */ }
      },

      // -------- Validation (Step 1) --------
      validate (field) {
        const set = (k, msg) => (this.errors = { ...this.errors, [k]: msg });
        switch (field) {
          case "firstname":
            set("firstname", this.isMissing("firstname") ? "Firstname is required." : "");
            break;
          case "lastname":
            set("lastname", this.isMissing("lastname") ? "Lastname is required." : "");
            break;
          case "birthdate":
            set("birthdate", this.isMissing("birthdate") ? "Enter a valid birthdate (13+ years old)." : "");
            break;
          case "address":
            set("address", this.isMissing("address") ? "Please pick a complete address." : "");
            break;
          case "contact":
            set("contact", this.isMissing("contact") ? "Enter a valid PH mobile (9XXXXXXXXX)." : "");
            break;
          case "email":
            set("email", this.isMissing("email") ? "Please use a valid @gmail.com address." : "");
            break;
          case "username":
            set("username", this.isMissing("username") ? "Username must be at least 3 characters." : "");
            break;
          case "password":
            this.evalPasswordRules();
            set("password", this.isMissing("password") ? "Password must be 8+ chars, 1 uppercase, 1 special." : "");
            if (this.form.password_confirmation) this.validate("password_confirmation");
            break;
          case "password_confirmation":
            set("password_confirmation", this.isMissing("password_confirmation") ? "Passwords must match." : "");
            break;
          case "about":
            set("about", this.isMissing("about") ? "Tell us a bit more (min 10 characters)." : "");
            break;
          case "talents":
            set("talents", this.isMissing("talents") ? "Add at least one talent and pick genres." : "");
            break;
          default: break;
        }
        return this.step1CanAdvance;
      },

      // -------- Validation (Step 2) --------
      validate2 (field) {
        const set = (k, msg) => (this.errors2 = { ...this.errors2, [k]: msg });
        switch (field) {
          case "service_fee":
            if (this.isMissing2("service_fee")) set("service_fee", "Enter a fee between 1 and 10000.");
            else set("service_fee", "");
            break;
          case "duration":
            set("duration", this.isMissing2("duration") ? "Please select a session duration." : "");
            break;
          case "payment":
            set("payment", this.isMissing2("payment") ? "Please choose a payment method." : "");
            // re-validate provider/handle when toggling cash/online
            if (this.form2.payment !== "online") {
              set("payment_provider", ""); set("payment_handle", "");
            } else {
              this.validate2("payment_provider");
              if (this.form2.payment_handle) this.validate2("payment_handle");
            }
            break;
          case "method":
            set("method", this.isMissing2("method") ? "Provide a valid @gmail.com for cancellations." : "");
            break;
          case "portfolio":
            set("portfolio", this.isMissing2("portfolio") ? "Portfolio file is required (PDF/JPG/PNG)." : "");
            break;
          case "valid_id":
            set("valid_id", this.isMissing2("valid_id") ? "Government ID image is required." : "");
            break;
          case "id_selfie":
            set("id_selfie", this.isMissing2("id_selfie") ? "Selfie with ID is required." : "");
            break;
          case "terms":
            set("terms", this.isMissing2("terms") ? "You must accept the Terms to continue." : "");
            break;
          case "notice_hours":
            set("notice_hours", (this.form2.notice_hours ?? null) > 99 ? "Maximum is 99 hours." : "");
            break;
          case "notice_days":
            set("notice_days", (this.form2.notice_days ?? null) > 30 ? "Maximum is 30 days." : "");
            break;

          case "payment_provider":
            set("payment_provider",
                this.isMissing2("payment_provider") ? "Please choose a provider." : "");
            if (this.form2.payment_handle) this.validate2("payment_handle");
            break;

          case "payment_handle": {
            if (this.isMissing2("payment_handle")) {
              let msg = "Enter a valid account/handle.";
              if (["gcash","maya"].includes(this.form2.payment_provider)) msg = "Enter a valid PH mobile (09XXXXXXXXX).";
              else if (this.form2.payment_provider === "paypal") msg = "Enter a valid PayPal email.";
              set("payment_handle", msg);
            } else set("payment_handle", "");
            break;
          }

          default: break;
        }
        return this.step2Valid;
      },

      // -------- Computed helpers for payment handle UI --------
      get paymentHandleLabel () {
        switch (this.form2.payment_provider) {
          case "gcash": return "GCash Number (09XXXXXXXXX)";
          case "maya":  return "Maya Number (09XXXXXXXXX)";
          case "paypal":return "PayPal Email";
          default:      return "Account / Handle";
        }
      },
      get paymentHandlePlaceholder () {
        return this.paymentHandleLabel;
      },
      get paymentHandleType () {
        return this.form2.payment_provider === "paypal" ? "email" : "text";
      },
      get paymentHandleHelp () {
        switch (this.form2.payment_provider) {
          case "gcash":
          case "maya":
            return "Use your registered mobile number (Philippines: 09XXXXXXXXX).";
          case "paypal":
            return "Use the PayPal email where you receive payments.";
          default:
            return "";
        }
      },

      // -------- Computed gates --------
      get step1CanAdvance () {
        const required = [
          "firstname","lastname","birthdate","address",
          "contact","email","username","password","password_confirmation","about","talents"
        ];
        const anyErr = Object.values(this.errors).some(Boolean);
        if (anyErr) return false;
        for (const f of required) {
          if (this.isMissing(f)) return false;
        }
        for (const t of this.selectedTalents) {
          if (!Array.isArray(this.finalGenres[t]) || this.finalGenres[t].length === 0) return false;
        }
        return true;
      },

      get step2Valid () {
        const required = ["service_fee","duration","payment","method","portfolio","valid_id","id_selfie","terms"];
        if (this.form2.payment === "online") {
          required.push("payment_provider","payment_handle");
        }
        const anyErr = Object.values(this.errors2).some(Boolean);
        if (anyErr) return false;
        for (const f of required) {
          if (this.isMissing2(f)) return false;
        }
        if ((this.form2.notice_hours ?? 0) > 99) return false;
        if ((this.form2.notice_days ?? 0) > 30) return false;
        return true;
      },

      // -------- Step navigation --------
      goStep (n) {
        if (n === 2) {
          ["firstname","lastname","birthdate","address","contact","email","username","password","password_confirmation","about","talents"].forEach(f => this.validate(f));
          if (!this.step1CanAdvance) return;
        }
        this.step = n;
      },

      // -------- Submit handler with loading state --------
      handleSubmit (e) {
        e.preventDefault();

        ["firstname","lastname","birthdate","address","contact","email","username","password","password_confirmation","about","talents"].forEach(f => this.validate(f));
        ["service_fee","duration","payment","method","portfolio","valid_id","id_selfie","terms","notice_hours","notice_days","payment_provider","payment_handle"].forEach(f => this.validate2(f));

        if (!this.step1CanAdvance) {
          this.step = 1;
          return;
        }
        if (!this.step2Valid) {
          this.step = 2;
          return;
        }

        // Ensure hidden JSON fields are fresh
        this.updateTalents();

        // Submit with loading state
        this.isSubmitting = true;
        requestAnimationFrame(() => {
          e.target.submit();
        });
      }
    }
  }
</script>
