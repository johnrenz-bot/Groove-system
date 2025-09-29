<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>Confirm Address</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    .container{max-width:46rem}
    @keyframes spin{to{transform:rotate(360deg)}}
    .spinner{animation:spin 1s linear infinite}
    .soft-card{box-shadow:0 1px 2px rgba(2,6,23,.04),0 8px 24px rgba(2,6,23,.06)}
    .ring-focus:focus{outline:none; box-shadow:0 0 0 3px rgb(59 130 246 / .35)}
    .kbd{font-variant:all-small-caps;border:1px solid rgba(100,116,139,.35);border-bottom-width:2px;border-radius:.375rem;padding:.125rem .375rem}
  </style>
</head>
<body class="bg-slate-50 text-slate-800">
  <div class="container mx-auto px-4 py-10">
    <!-- Header -->
    <div class="mb-6 flex items-start justify-between gap-4">
      <div>
        <h1 class="text-3xl font-semibold tracking-tight text-slate-900">Confirm Address</h1>
        <p class="text-sm text-slate-500 mt-1">Select your region down to barangay, or use your current location.</p>
      </div>
      <div class="hidden sm:flex items-center gap-2 text-xs text-slate-500">
        <span class="kbd">Tab</span><span>to move</span>
        <span class="kbd">Shift</span><span>+</span><span class="kbd">Tab</span><span>back</span>
      </div>
    </div>

    {{-- Feedback --}}
    @if ($errors->any())
      <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 p-3 text-sm text-rose-700 soft-card">
        <ul class="list-disc pl-5">
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <form method="POST" action="{{ route('confirm.address.store') }}" class="space-y-6 bg-white p-6 md:p-7 rounded-2xl border border-slate-200 soft-card" novalidate>
      @csrf

      <!-- Progress / Step Pills -->
      <ol class="flex flex-wrap gap-2 text-xs" aria-label="Selection progress">
        <li class="px-2.5 py-1 rounded-full bg-slate-100 text-slate-700" id="pill-region">1. Region</li>
        <li class="px-2.5 py-1 rounded-full bg-slate-100 text-slate-500" id="pill-province">2. Province</li>
        <li class="px-2.5 py-1 rounded-full bg-slate-100 text-slate-500" id="pill-city">3. City/Municipality</li>
        <li class="px-2.5 py-1 rounded-full bg-slate-100 text-slate-500" id="pill-brgy">4. Barangay</li>
      </ol>

      <!-- Grid layout -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
        <!-- Region -->
        <div>
          <label class="block text-xs font-semibold text-slate-700 mb-1" for="region">Region <span class="text-rose-600">*</span></label>
          <div class="relative">
            <select id="region" name="region_code" required autocomplete="address-level1"
              class="appearance-none pr-10 w-full h-11 px-3 bg-white text-slate-800 border border-slate-300 rounded-xl ring-focus disabled:bg-slate-100 disabled:text-slate-400">
              <option value="">Select Region</option>
            </select>
            <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-slate-500">
              <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z"/>
              </svg>
            </div>
          </div>
          <p id="regionHelp" class="mt-1 text-xs text-slate-500">Start here. Provinces will load based on your region.</p>
        </div>

        <!-- Province -->
        <div>
          <label class="block text-xs font-semibold text-slate-700 mb-1" for="province">Province <span class="text-rose-600">*</span></label>
          <div class="relative">
            <select id="province" name="province_code" required disabled aria-disabled="true" autocomplete="address-level1"
              class="appearance-none pr-10 w-full h-11 px-3 bg-white text-slate-800 border border-slate-300 rounded-xl ring-focus disabled:bg-slate-100 disabled:text-slate-400">
              <option value="">Select Province</option>
            </select>
            <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-slate-400">
              <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z"/>
              </svg>
            </div>
          </div>
        </div>

        <!-- City / Municipality -->
        <div>
          <label class="block text-xs font-semibold text-slate-700 mb-1" for="city">City / Municipality <span class="text-rose-600">*</span></label>
          <div class="relative">
            <select id="city" name="city_code" required disabled aria-disabled="true" autocomplete="address-level2"
              class="appearance-none pr-10 w-full h-11 px-3 bg-white text-slate-800 border border-slate-300 rounded-xl ring-focus disabled:bg-slate-100 disabled:text-slate-400">
              <option value="">Select City/Municipality</option>
            </select>
            <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-slate-400">
              <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z"/>
              </svg>
            </div>
          </div>
        </div>

        <!-- Barangay -->
        <div>
          <label class="block text-xs font-semibold text-slate-700 mb-1" for="barangay">Barangay <span class="text-rose-600">*</span></label>
          <div class="relative">
            <select id="barangay" name="barangay_code" required disabled aria-disabled="true"
              class="appearance-none pr-10 w-full h-11 px-3 bg-white text-slate-800 border border-slate-300 rounded-xl ring-focus disabled:bg-slate-100 disabled:text-slate-400">
              <option value="">Select Barangay</option>
            </select>
            <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-slate-400">
              <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z"/>
              </svg>
            </div>
          </div>
        </div>

        <!-- Street -->
        <div class="sm:col-span-2">
          <label class="block text-xs font-semibold text-slate-700 mb-1" for="street">Street / House No.</label>
          <input id="street" name="street" maxlength="120" autocomplete="address-line1"
            class="w-full h-11 px-3 bg-white text-slate-800 border border-slate-300 rounded-xl ring-focus"
            placeholder="e.g., 123 Mabini St, Brgy. Muzon" value="{{ old('street') }}" />
          <p class="mt-1 text-xs text-slate-500">Auto-fills when using current location. You can edit anytime.</p>
        </div>

        <!-- Selected Address -->
        <div class="sm:col-span-2">
          <label class="block text-xs font-semibold text-slate-700 mb-1" for="addressSummary">Selected Address</label>
          <input id="addressSummary" type="text" readonly
            class="w-full h-11 px-3 bg-slate-50 text-slate-600 border border-slate-200 rounded-xl"
            placeholder="Street, Barangay, City/Municipality, Province, Region" />
        </div>

        <!-- Postal Code -->
        <div class="sm:col-span-2">
          <div class="flex items-center justify-between">
            <label class="block text-xs font-semibold text-slate-700 mb-1" for="postal">Postal Code</label>
            <span class="text-[11px] text-slate-400">PH only</span>
          </div>
          <!-- FIX: pattern should use a single backslash -->
          <input id="postal" name="postal_code" inputmode="numeric" maxlength="10" pattern="\d{4,10}" autocomplete="postal-code"
            class="w-full h-11 px-3 bg-white text-slate-800 border border-slate-300 rounded-xl ring-focus"
            placeholder="e.g., 1000" value="{{ old('postal_code') }}" />
          <p id="postalHelp" class="mt-1 text-xs text-slate-500">Auto-fills when possible. You can edit if needed.</p>
          <p id="postalStatus" class="mt-1 text-xs text-slate-500 hidden" aria-live="polite" role="status"></p>
        </div>
      </div>

      <!-- Hidden text names -->
      <input type="hidden" name="region_name" id="region_name" />
      <input type="hidden" name="province_name" id="province_name" />
      <input type="hidden" name="city_name" id="city_name" />
      <input type="hidden" name="barangay_name" id="barangay_name" />
      <input type="hidden" name="street_name" id="street_name" />

      <!-- Buttons -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        <button type="button" id="btnLocate"
          class="h-11 inline-flex items-center justify-center gap-2 rounded-xl border border-blue-600 text-blue-700 bg-white hover:bg-blue-50 active:bg-blue-100 transition disabled:opacity-60 disabled:cursor-not-allowed"
          aria-busy="false" aria-live="polite">
          <svg data-spinner class="hidden h-5 w-5 spinner" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" opacity="0.25" />
            <path d="M22 12a10 10 0 0 1-10 10" stroke="currentColor" stroke-width="4" stroke-linecap="round" />
          </svg>
          <span data-label>Use My Current Location</span>
        </button>

        <button type="button" id="btnReset"
          class="h-11 inline-flex items-center justify-center rounded-xl border border-slate-300 text-slate-700 bg-white hover:bg-slate-50 active:bg-slate-100 transition">
          Reset
        </button>

        <button type="submit"
          class="h-11 inline-flex items-center justify-center rounded-xl bg-blue-600 text-white hover:bg-blue-700 active:bg-blue-800 transition">
          Save Address
        </button>
      </div>

      <p id="loadError" class="text-xs text-rose-600 mt-1 hidden" aria-live="assertive" role="alert"></p>

      <p class="text-[11px] text-slate-400">Data sources: PSGC (regions/provinces/cities/municipalities) & OpenStreetMap Nominatim (postal/geo).</p>
    </form>
  </div>

  <script>
  // --- API roots (PSGC public) ---
  const API = {
    regions: 'https://psgc.gitlab.io/api/regions/',
    provinces: 'https://psgc.gitlab.io/api/provinces/',
    cities: 'https://psgc.gitlab.io/api/cities/',
    municipalities: 'https://psgc.gitlab.io/api/municipalities/',
    cityBarangays: (cityCode) => `https://psgc.gitlab.io/api/cities/${cityCode}/barangays/`,
    muniBarangays: (munCode) => `https://psgc.gitlab.io/api/municipalities/${munCode}/barangays/`,
  };

  // --- Elements ---
  const regionEl = document.getElementById('region');
  const provinceEl = document.getElementById('province');
  const cityEl = document.getElementById('city');
  const brgyEl = document.getElementById('barangay');
  const streetEl = document.getElementById('street');
  const postalEl = document.getElementById('postal');
  const postalStatusEl = document.getElementById('postalStatus');

  const summaryEl = document.getElementById('addressSummary');
  const regionNameEl = document.getElementById('region_name');
  const provinceNameEl = document.getElementById('province_name');
  const cityNameEl = document.getElementById('city_name');
  const brgyNameEl = document.getElementById('barangay_name');
  const streetNameEl = document.getElementById('street_name');
  const loadErrorEl = document.getElementById('loadError');
  const btnLocate = document.getElementById('btnLocate');
  const btnReset = document.getElementById('btnReset');

  // Progress pills
  const pillRegion = document.getElementById('pill-region');
  const pillProvince = document.getElementById('pill-province');
  const pillCity = document.getElementById('pill-city');
  const pillBrgy = document.getElementById('pill-brgy');

  // --- Helpers ---
  const clearSelect = (el, placeholder) => {
    el.innerHTML = '';
    const opt = document.createElement('option');
    opt.value = '';
    opt.textContent = placeholder;
    el.appendChild(opt);
    el.selectedIndex = 0;
  };

  const enable = (el, on=true) => {
    el.disabled = !on;
    if (!on) el.setAttribute('aria-disabled','true'); else el.removeAttribute('aria-disabled');
  };

  const setOptions = (el, items, getVal, getLabel) => {
    const frag = document.createDocumentFragment();
    items.forEach(it => {
      const opt = document.createElement('option');
      opt.value = getVal(it);
      opt.textContent = getLabel(it);
      frag.appendChild(opt);
    });
    el.appendChild(frag);
  };

  const selectedText = (el) => {
    const idx = el.selectedIndex;
    if (idx === -1) return '';
    const o = el.options[idx];
    return o ? (o.textContent || '').trim() : '';
  };

  // --- CLEAN function (normalize & sanitize display-only text) ---
  const clean = (str) => {
    if (typeof str !== 'string') return '';
    // Collapse whitespace, strip control chars, limit length for safety in UI mirroring
    const s = str.replace(/[\u0000-\u001F\u007F]/g, '').replace(/\s+/g, ' ').trim();
    return s.length > 160 ? s.slice(0, 160) : s;
  };

  const updateSummary = () => {
    const street = clean(streetEl.value || '');
    const parts = [
      street,
      selectedText(brgyEl),
      selectedText(cityEl),
      selectedText(provinceEl),
      selectedText(regionEl),
    ].filter(Boolean);
    summaryEl.value = parts.join(', ');

    // Mirror clean text into hidden fields
    regionNameEl.value = selectedText(regionEl);
    provinceNameEl.value = selectedText(provinceEl);
    cityNameEl.value = selectedText(cityEl);
    brgyNameEl.value = selectedText(brgyEl);
    streetNameEl.value = street;

    // Update progress pills
    pillRegion.className = 'px-2.5 py-1 rounded-full ' + (regionEl.value ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-700');
    pillProvince.className = 'px-2.5 py-1 rounded-full ' + (provinceEl.value ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-500');
    pillCity.className = 'px-2.5 py-1 rounded-full ' + (cityEl.value ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-500');
    pillBrgy.className = 'px-2.5 py-1 rounded-full ' + (brgyEl.value ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-500');
  };

  // safer fetch with small timeout
  const safeFetch = async (url, opts={}) => {
    const controller = new AbortController();
    const id = setTimeout(() => controller.abort(), opts.timeoutMs ?? 15000);
    try {
      const res = await fetch(url, {
        cache: 'no-store',
        headers: { 'Accept': 'application/json' },
        signal: controller.signal
      });
      if (!res.ok) throw new Error(`HTTP ${res.status} for ${url}`);
      return res.json();
    } finally {
      clearTimeout(id);
    }
  };

  const setPostal = (code, msg=null, isError=false) => {
    if (typeof code === 'string' && code.trim()) postalEl.value = code.trim();
    if (msg) {
      postalStatusEl.textContent = msg;
      postalStatusEl.classList.remove('hidden');
      postalStatusEl.classList.toggle('text-rose-600', !!isError);
      postalStatusEl.classList.toggle('text-slate-500', !isError);
    } else {
      postalStatusEl.classList.add('hidden');
      postalStatusEl.textContent = '';
    }
  };

  // Debounce helper
  const debounce = (fn, wait=400) => { let t; return (...args) => { clearTimeout(t); t = setTimeout(()=>fn(...args), wait); }; };

  // --- Postal lookup based on current selection ---
  const tryFetchPostalFromSelection = debounce(async () => {
    const brgy = selectedText(brgyEl);
    const cityLabel = selectedText(cityEl);
    const prov = selectedText(provinceEl);

    if (!cityLabel && !prov) { setPostal('', null); return; }

    const q1 = [brgy, cityLabel, prov, 'Philippines'].filter(Boolean).join(', ');

    setPostal(null, 'Looking up postal code…');
    try {
      let url = `https://nominatim.openstreetmap.org/search?format=jsonv2&limit=1&countrycodes=ph&q=${encodeURIComponent(q1)}`;
      let data = await safeFetch(url);
      let postcode = data?.[0]?.address?.postcode;

      if (!postcode && (cityLabel || prov)) {
        const q2 = [cityLabel, prov, 'Philippines'].filter(Boolean).join(', ');
        url = `https://nominatim.openstreetmap.org/search?format=jsonv2&limit=1&countrycodes=ph&q=${encodeURIComponent(q2)}`;
        data = await safeFetch(url);
        postcode = data?.[0]?.address?.postcode;
      }

      if (postcode) setPostal(postcode, 'Postal code detected.');
      else setPostal('', 'Could not auto-detect postal code. You can type it manually.');
    } catch (e) {
      console.error(e);
      setPostal('', 'Postal lookup failed. You can type it manually.', true);
    }
  }, 500);

  // --- Caches to avoid repeat calls ---
  let allProvinces = null, allCities = null, allMunicipalities = null;

  // --- Load Regions on boot ---
  async function loadRegions() {
    try {
      clearSelect(regionEl, 'Loading regions…');
      enable(provinceEl, false); enable(cityEl, false); enable(brgyEl, false);

      const regions = await safeFetch(API.regions);
      regions.sort((a,b)=> a.name.localeCompare(b.name, 'en', { sensitivity:'base' }));
      clearSelect(regionEl, 'Select Region');
      setOptions(regionEl, regions, r => r.code, r => r.name);
      loadErrorEl.classList.add('hidden'); loadErrorEl.textContent = '';
    } catch (e) {
      clearSelect(regionEl, 'Failed to load regions');
      loadErrorEl.textContent = 'Could not load regions: ' + e.message;
      loadErrorEl.classList.remove('hidden'); loadErrorEl.classList.add('text-rose-600');
      console.error(e);
    }
  }

  // --- Locate Button Loading State ---
  function setLocateLoading(on) {
    const spinner = btnLocate.querySelector('[data-spinner]');
    const label = btnLocate.querySelector('[data-label]');
    btnLocate.disabled = !!on; btnLocate.setAttribute('aria-busy', String(!!on));
    if (spinner) spinner.classList.toggle('hidden', !on);
    if (label) label.textContent = on ? 'Locating…' : 'Use My Current Location';
  }

  // Promise wrapper for geolocation
  function getCurrentPositionPromise(options) {
    return new Promise((resolve, reject) => {
      if (!navigator.geolocation) { reject(new Error('Geolocation is not supported by this browser.')); return; }
      navigator.geolocation.getCurrentPosition(resolve, reject, options);
    });
  }

  // --- Handlers ---
  regionEl.addEventListener('change', async () => {
    const regionCode = regionEl.value;

    clearSelect(provinceEl, 'Select Province');
    clearSelect(cityEl, 'Select City/Municipality');
    clearSelect(brgyEl, 'Select Barangay');
    enable(provinceEl, !!regionCode);
    enable(cityEl, false); enable(brgyEl, false);
    updateSummary(); setPostal('', null);

    if (!regionCode) return;

    try {
      allProvinces = allProvinces || await safeFetch(API.provinces);
      const provinces = allProvinces
        .filter(p => p.regionCode === regionCode)
        .sort((a,b)=> a.name.localeCompare(b.name, 'en', { sensitivity:'base' }));

      setOptions(provinceEl, provinces, p => p.code, p => p.name);
    } catch (e) {
      clearSelect(provinceEl, 'Failed to load provinces');
      console.error(e);
    }
  });

  provinceEl.addEventListener('change', async () => {
    const provinceCode = provinceEl.value;

    clearSelect(cityEl, 'Select City/Municipality');
    clearSelect(brgyEl, 'Select Barangay');
    enable(cityEl, !!provinceCode); enable(brgyEl, false);
    updateSummary(); setPostal('', null);

    if (!provinceCode) return;

    try {
      allCities = allCities || await safeFetch(API.cities);
      allMunicipalities = allMunicipalities || await safeFetch(API.municipalities);

      const cities = allCities.filter(c => c.provinceCode === provinceCode)
        .map(c => ({ code: c.code, name: c.name, kind: 'city' }));

      const munis = allMunicipalities.filter(m => m.provinceCode === provinceCode)
        .map(m => ({ code: m.code, name: m.name, kind: 'municipality' }));

      const combined = [...cities, ...munis]
        .sort((a,b)=> a.name.localeCompare(b.name, 'en', { sensitivity:'base' }));

      setOptions(cityEl, combined, x => `${x.kind}:${x.code}`, x => x.name);

      tryFetchPostalFromSelection();
    } catch (e) {
      clearSelect(cityEl, 'Failed to load cities/municipalities');
      console.error(e);
    }
  });

  cityEl.addEventListener('change', async () => {
    const val = cityEl.value; // "city:CODE" or "municipality:CODE"
    clearSelect(brgyEl, 'Select Barangay');
    enable(brgyEl, !!val);
    updateSummary();

    // Trigger postal lookup now that city/municipality is known
    tryFetchPostalFromSelection();

    if (!val) return;
    const [kind, code] = val.split(':');

    try {
      let list = [];
      if (kind === 'city') {
        list = await safeFetch(API.cityBarangays(code));
      } else {
        try { list = await safeFetch(API.muniBarangays(code)); }
        catch (inner) { console.warn('Municipality barangays not found for', code, inner); list = []; }
      }

      list.sort((a,b)=> a.name.localeCompare(b.name, 'en', { sensitivity:'base' }));
      setOptions(brgyEl, list, b => b.code, b => b.name);
    } catch (e) {
      clearSelect(brgyEl, 'Failed to load barangays');
      console.error(e);
    }
  });

  brgyEl.addEventListener('change', async () => { updateSummary(); tryFetchPostalFromSelection(); });
  streetEl.addEventListener('input', updateSummary);

  // --- Reset (full clean) ---
  btnReset.addEventListener('click', () => {
    // Clear visible selects
    clearSelect(regionEl, 'Select Region');
    clearSelect(provinceEl, 'Select Province');
    clearSelect(cityEl, 'Select City/Municipality');
    clearSelect(brgyEl, 'Select Barangay');

    // Disable dependent selects
    enable(provinceEl, false); enable(cityEl, false); enable(brgyEl, false);

    // Clear text inputs
    streetEl.value = '';
    postalEl.value = '';
    setPostal('', null);

    // Clear hidden mirrors & summary
    regionNameEl.value = provinceNameEl.value = cityNameEl.value = brgyNameEl.value = streetNameEl.value = '';
    summaryEl.value = '';

    // Clear error
    loadErrorEl.textContent = '';
    loadErrorEl.classList.add('hidden');

    // Reload regions fresh
    loadRegions().then(updateSummary);
  });

  // --- "Use My Current Location" (PH only) with loading state ---
  btnLocate.addEventListener('click', async () => {
    setLocateLoading(true);
    try {
      const pos = await getCurrentPositionPromise({ enableHighAccuracy: true, timeout: 15000, maximumAge: 0 });
      const { latitude, longitude } = pos.coords;
      const res = await fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${latitude}&lon=${longitude}`);
      const data = await res.json();
      if (data.address && data.address.country_code === 'ph') {
        const regionName = (data.address.region || '').toLowerCase();
        const provinceName = (data.address.state || '').toLowerCase();
        const cityName = (data.address.city || data.address.municipality || data.address.town || '').toLowerCase();
        const barangayName = (data.address.suburb || data.address.village || data.address.neighbourhood || '').toLowerCase();
        const detectedPostcode = data.address.postcode || '';

        // Build street string (house_number + road)
        const houseNo = data.address.house_number || '';
        const road = data.address.road || data.address.residential || '';
        const streetFull = clean([houseNo, road].filter(Boolean).join(' '));
        if (streetFull) streetEl.value = streetFull;
        updateSummary();

        // Simple waits as options populate
        const wait = (ms) => new Promise(r=>setTimeout(r, ms));

        // Select region -> province -> city/municipality -> barangay
        const regionOpt = [...regionEl.options].find(o=>o.textContent.toLowerCase().includes(regionName));
        if(regionOpt){
          regionEl.value = regionOpt.value; regionEl.dispatchEvent(new Event('change'));
          await wait(800);
          const provinceOpt = [...provinceEl.options].find(o=>o.textContent.toLowerCase().includes(provinceName));
          if(provinceOpt){
            provinceEl.value = provinceOpt.value; provinceEl.dispatchEvent(new Event('change'));
            await wait(900);
            const cityOpt = [...cityEl.options].find(o=>o.textContent.toLowerCase().includes(cityName));
            if(cityOpt){
              cityEl.value = cityOpt.value; cityEl.dispatchEvent(new Event('change'));
              await wait(900);
              const brgyOpt = [...brgyEl.options].find(o=>o.textContent.toLowerCase().includes(barangayName));
              if(brgyOpt) brgyEl.value = brgyOpt.value;
              updateSummary();
              if (detectedPostcode) setPostal(detectedPostcode, 'Postal code detected from your location.');
              else tryFetchPostalFromSelection();
            } else {
              if (detectedPostcode) setPostal(detectedPostcode, 'Postal code detected from your location.');
              else tryFetchPostalFromSelection();
            }
          } else {
            if (detectedPostcode) setPostal(detectedPostcode, 'Postal code detected from your location.');
            else tryFetchPostalFromSelection();
          }
        } else {
          alert('Could not match your location to a PSGC region.');
          if (detectedPostcode) setPostal(detectedPostcode, 'Postal code detected from your location.');
        }
      } else {
        alert('Location is outside the Philippines or could not be determined.');
      }
    } catch (err) {
      console.error(err);
      alert(err?.message || 'Failed to detect location.');
    } finally {
      setLocateLoading(false);
    }
  });

  // --- Boot ---
  document.addEventListener('DOMContentLoaded', () => { loadRegions().then(updateSummary); });
  </script>
</body>
</html>
