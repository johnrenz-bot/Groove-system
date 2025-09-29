@php
    use Illuminate\Support\Facades\Storage;
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Groove · Admin · Transactions</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Icons / Fonts --}}
    <link rel="icon" href="/image/white.png" type="image/png" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
    <script src="https://kit.fontawesome.com/43f9926b04.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    {{-- PWA icons --}}
  <link rel="icon" href="/image/bg/LOG.png" type="image/png" sizes="512x512">


    {{-- Your compiled Tailwind + THEME TOKENS --}}
@vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak]{display:none!important}
        body{font-family:"Instrument Sans",ui-sans-serif,system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial}
    </style>
</head>

<body class="min-h-screen antialiased theme-{{ $appTheme }} bg-surface text-foreground">
<div class="flex min-h-screen">

    {{-- SIDEBAR (themed) --}}
    <header class="w-64 h-screen bg-card border-r border-divider/40 text-foreground flex flex-col justify-between shadow-sm">
        <div class="flex flex-col p-6 space-y-8">
            <div class="flex items-center gap-3">
        <img src="/image/bg/LOG.png" alt="Logo" class="h-12 w-auto object-contain select-none" />
            </div>

                <nav class="flex flex-col space-y-1 text-sm font-medium p-3">
            <nav class="flex flex-col space-y-1 text-sm font-medium p-3">
            <a href="dashboard"
                class="flex items-center px-4 py-2 rounded-lg text-foreground/80 hover:text-foreground hover:bg-layer">
                <i class="fas fa-home mr-3 w-5 opacity-70"></i> Dashboard
            </a>
            <a href="{{ route('admin.users') }}"
                class="flex items-center px-4 py-2 rounded-lg text-foreground/80 hover:text-foreground hover:bg-layer">
                <i class="fas fa-users mr-3 w-5 opacity-70"></i> Users
            </a>
            <a href="{{ route('admin.control') }}"
                class="flex items-center px-4 py-2 rounded-lg text-foreground/80 hover:text-foreground hover:bg-layer">
                <i class="fas fa-layer-group mr-3 w-5 opacity-70"></i> Control
            </a>
            <a href="{{ route('admin.transaction') }}"
                    class="flex items-center px-4 py-2 rounded-lg text-foreground bg-layer shadow-inner border border-divider/50">
                <i class="fas fa-user-friends mr-3 w-5 opacity-70"></i> Transactions
            </a>

            {{-- ✅ NEW: Tickets --}}
    <a href="{{ route('admin.tickets') }}"
        class="flex items-center px-4 py-2 rounded-lg
                {{ request()->routeIs('admin.tickets') ? 'text-foreground bg-layer shadow-inner border border-divider/50' : 'text-foreground/80 hover:text-foreground hover:bg-layer' }}">
        <i class="fas fa-ticket-alt mr-3 w-5 opacity-70"></i> Tickets
    </a>
            </nav>
        </div>

        <div class="p-6 border-t border-divider/40 space-y-4">
            <div x-data="{ open: false }" class="w-full">
                <button @click="open = !open"
                        class="flex items-center gap-3 px-4 py-2 bg-layer rounded-xl w-full hover:opacity-95 hover:shadow-sm transition border border-divider/40">
                    <div class="flex-1 text-left">
                        <p class="text-sm font-semibold truncate">{{ Auth::guard('admin')->user()->name }}</p>
                        <p class="text-xs text-foreground/60 truncate">{{ Auth::guard('admin')->user()->email }}</p>
                    </div>
                    <i class="fas fa-chevron-down text-xs opacity-70 transition-transform duration-200" :class="{ 'rotate-180': open }"></i>
                </button>

                <div x-show="open" x-transition x-cloak
                     class="mt-2 bg-card border border-divider/40 rounded-xl overflow-hidden shadow-lg">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                                class="w-full text-left px-4 py-2 text-sm text-[crimson] hover:bg-layer transition flex items-center gap-2">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    {{-- MAIN --}}
    <main class="w-full px-8 py-6 bg-surface"
          x-data="{
              tab: 'agreement',
              openModal: false,
              selectedCoachId: null,
              selectedTx: null,
              openAppointmentModal: false,
              selectedAppointment: null
          }">

        <h1 class="text-2xl font-bold mb-6">Transactions</h1>

        {{-- Tab Switch --}}
        <div class="flex flex-col space-y-2 w-full sm:w-1/3 md:w-1/4 mb-5">
            <div class="flex bg-layer rounded-md p-1 border border-divider/40">
                <button @click="tab = 'agreement'"
                        :class="tab === 'agreement' ? 'bg-card text-foreground' : 'text-foreground/70 hover:bg-layer/80'"
                        class="flex-1 text-center py-2 px-4 rounded-md text-sm font-medium transition">
                    AGREEMENT
                </button>
                <button @click="tab = 'appointment'"
                        :class="tab === 'appointment' ? 'bg-card text-foreground' : 'text-foreground/70 hover:bg-layer/80'"
                        class="flex-1 text-center py-2 px-4 rounded-md text-sm font-medium transition">
                    APPOINTMENT
                </button>
            </div>
        </div>

        {{-- ================= AGREEMENTS ================= --}}
        <div x-show="tab === 'agreement'" class="p-4 bg-card border border-divider/40 rounded-xl" x-cloak>
            {{-- Coach List --}}
            <div x-show="!selectedCoachId" class="overflow-x-auto">
                <table class="min-w-full bg-card border border-divider/40 rounded-lg overflow-hidden">
                    <thead class="bg-layer text-foreground/80">
                        <tr>
                            <th class="px-4 py-3 text-left">#</th>
                            <th class="px-4 py-3 text-left">Coach Profile</th>
                            <th class="px-4 py-3 text-left">Coach ID</th>
                            <th class="px-4 py-3 text-left">Coach Name</th>
                            <th class="px-4 py-3 text-left">Total Sessions</th>
                            <th class="px-4 py-3 text-left">Total Agreements</th>
                            <th class="px-4 py-3 text-left">View</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-divider/40">
                        @php $coaches = $transactions->groupBy('coach_id'); @endphp

                        @forelse($coaches as $coachId => $coachTxs)
                            @php $coach = $coachTxs->first()->coach; @endphp
                            <tr class="hover:bg-layer transition">
                                <td class="px-4 py-3">{{ $loop->iteration }}</td>

                                {{-- Coach Profile --}}
                                <td class="px-4 py-3">
                                    @if($coach && $coach->photo)
                                        <img src="{{ asset('storage/' . $coach->photo) }}" class="w-10 h-10 rounded-full object-cover border border-divider/40">
                                    @elseif($coach)
                                        <div class="w-10 h-10 rounded-full bg-layer flex items-center justify-center font-bold border border-divider/40">
                                            {{ strtoupper(substr($coach->firstname,0,1)) }}{{ strtoupper(substr($coach->lastname,0,1)) }}
                                        </div>
                                    @else
                                        <span class="text-[crimson]">No coach relation ({{ $coachId }})</span>
                                    @endif
                                </td>

                                <td class="px-4 py-3">{{ $coachId }}</td>

                                {{-- Coach Name --}}
                                <td class="px-4 py-3">
                                    @if($coach)
                                        {{ $coach->firstname }} {{ $coach->lastname }}
                                    @else
                                        <span class="text-[crimson]">No coach relation ({{ $coachId }})</span>
                                    @endif
                                </td>

                                {{-- Sessions / Agreements --}}
                                <td class="px-4 py-3">{{ $coachTxs->count() }}</td>
                                <td class="px-4 py-3">{{ $coachTxs->count() }}</td>

                                {{-- Action --}}
                                <td class="px-4 py-3">
                                    @if($coach)
                                        <i class="fa-solid fa-eye cursor-pointer"
                                           style="color: var(--color-primary)"
                                           @click="selectedCoachId = {{ $coachId }}"></i>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-6 text-foreground/60">No coaches found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Agreements per Coach --}}
            @foreach($coaches as $coachId => $coachTxs)
                @php $coach = \App\Models\Coach::find($coachId); @endphp
                <template x-if="selectedCoachId == {{ $coachId }}">
                    <div class="space-y-4">
                        {{-- Coach Info --}}
                        <div class="bg-card p-4 rounded-xl flex justify-between items-center border border-divider/40">
                            <div class="p-4 rounded-xl border border-divider/40 bg-layer flex items-center gap-4 w-full max-w-md">
                                {{-- Coach Photo / Initials --}}
                                <div class="flex-shrink-0">
                                    @if($coach && $coach->photo)
                                        <img src="{{ asset('storage/' . $coach->photo) }}"
                                             class="w-16 h-16 rounded-full object-cover border border-divider/40">
                                    @elseif($coach)
                                        <div class="w-16 h-16 rounded-full bg-layer flex items-center justify-center font-bold text-xl border border-divider/40">
                                            {{ strtoupper(substr($coach->firstname,0,1)) }}{{ strtoupper(substr($coach->lastname,0,1)) }}
                                        </div>
                                    @else
                                        <div class="w-16 h-16 rounded-full bg-[crimson] flex items-center justify-center text-card font-bold text-xl">
                                            !
                                        </div>
                                    @endif
                                </div>

                                {{-- Coach Info --}}
                                <div class="flex-1">
                                    @if($coach)
                                        <h3 class="font-semibold text-lg">
                                            {{ $coach->firstname }} {{ $coach->lastname }}
                                        </h3>
                                        <p class="text-foreground/70 text-sm">{{ $coach->role ?? 'Coach' }}</p>
                                        <div class="flex gap-4 mt-2 text-foreground/80 text-sm">
                                            <span>Sessions: {{ count($coachTxs) }}</span>
                                            <span>Agreements: {{ $coachTxs->count() }}</span>
                                        </div>
                                    @else
                                        <p class="text-[crimson] font-semibold">No coach relation ({{ $coachId }})</p>
                                    @endif
                                </div>
                            </div>

                            <button @click="selectedCoachId = null"
                                    class="px-3 py-1 bg-layer rounded-md border border-divider/40 hover:opacity-90">
                                Back
                            </button>
                        </div>

                        {{-- Agreements Table --}}
                        <div class="overflow-x-auto">
                            <table class="min-w-full bg-card border border-divider/40 rounded-lg overflow-hidden text-sm">
                                <thead class="bg-layer">
                                    <tr>
                                        <th class="px-4 py-3 text-left">#</th>
                                        <th class="px-4 py-3 text-left">Client</th>
                                        <th class="px-4 py-3 text-left">Date</th>
                                        <th class="px-4 py-3 text-left">Price</th>
                                        <th class="px-4 py-3 text-left">Payment</th>
                                        <th class="px-4 py-3 text-center">View</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-divider/40">
                                    @forelse($coachTxs as $index => $tx)
                                        <tr class="hover:bg-layer">
                                            <td class="px-4 py-3">{{ $index+1 }}</td>
                                            <td class="px-4 py-3">
                                                @if($tx->client)
                                                    {{ $tx->client->full_name }}
                                                @else
                                                    <span class="text-[crimson]">No client relation</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3">{{ $tx->agreement_date ?? 'N/A' }}</td>
                                            <td class="px-4 py-3">₱{{ $tx->appointment_price ?? 'N/A' }}</td>
                                            <td class="px-4 py-3">{{ $tx->payment_method ?? 'N/A' }}</td>
                                            <td class="px-4 py-3 text-center">
                                                <button
                                                    @click="selectedTx = @js($tx->load(['client','coach'])); openModal = true"
                                                    class="font-medium"
                                                    style="color: var(--color-primary)">
                                                    <i class="fa-solid fa-eye"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-6 text-foreground/60">No agreements found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </template>
            @endforeach

            {{-- Agreement Details Modal --}}
            <div x-show="openModal"
                 x-transition
                 class="fixed inset-0 bg-black/30 flex items-center justify-center z-50 p-4"
                 x-cloak>
                <div class="bg-card text-foreground w-full max-w-3xl p-6 md:p-8 border border-divider/40 shadow-2xl flex flex-col max-h-[85vh] rounded-2xl relative overflow-y-auto">
                    <button @click="openModal = false"
                            class="absolute top-4 right-4 text-foreground/70 hover:text-foreground transition-colors duration-200">
                        <i class="fa-solid fa-xmark text-xl"></i>
                    </button>

                    <h2 class="text-center underline text-2xl font-bold mb-6 tracking-wide">
                        <span x-text="(selectedTx.coach?.role || 'COACH').toUpperCase()"></span> AGREEMENT CONTRACT
                    </h2>

                    <div class="mb-4 space-y-1 text-foreground/80">
                        <p><strong>Coach:</strong> <span x-text="selectedTx.coach ? selectedTx.coach.firstname + ' ' + selectedTx.coach.lastname : 'N/A'"></span></p>
                        <p><strong>Client:</strong> <span x-text="selectedTx.client ? selectedTx.client.firstname + ' ' + selectedTx.client.lastname : 'N/A'"></span></p>
                        <p><strong>Date:</strong> <span x-text="selectedTx.agreement_date ? selectedTx.agreement_date : new Date().toLocaleDateString()"></span></p>
                    </div>

                    <div class="space-y-4 text-foreground/80 text-sm">
                        <div>
                            <p class="font-semibold uppercase mb-1 text-foreground">Purpose of Agreement</p>
                            <p class="text-justify">
                                This Agreement outlines the terms under which the
                                <strong x-text="selectedTx.coach?.role || 'COACH'"></strong>
                                will provide professional services to the Client. It ensures clarity, safety, mutual understanding, and accountability in the coaching relationship.
                            </p>
                        </div>

                        <div>
                            <p class="font-semibold uppercase mb-1 text-foreground">Terms of Service</p>
                            <p class="text-justify mb-1">
                                The Client agrees to attend scheduled sessions on time, communicate openly, and take responsibility for their decisions.
                                Services provided include: <strong x-text="selectedTx.coach?.talent || 'N/A'"></strong>.
                                Each session lasts <strong x-text="selectedTx.coach?.session_duration || 'N/A'"></strong> at a fee of ₱<strong x-text="selectedTx.appointment_price || 'N/A'"></strong>.
                                Payment method: <strong x-text="selectedTx.payment_method || 'N/A'"></strong>.
                            </p>
                            <p class="text-justify">
                                Sessions must be scheduled in advance. Minimum notice:
                                <strong x-text="selectedTx.coach?.notice_hours || 0"></strong> hours /
                                <strong x-text="selectedTx.coach?.notice_days || 0"></strong> days.
                                Cancellation notice must be provided via <strong x-text="selectedTx.coach?.cancellation_method || 'N/A'"></strong>.
                                Same-day cancellations incur 25% of the fee. No-shows are charged in full.
                            </p>
                        </div>

                        <div>
                            <p class="font-semibold uppercase mb-1 text-foreground">Commitment</p>
                            <p class="text-justify">
                                By entering into this Agreement, both the Coach and the Client acknowledge their commitment to a respectful, professional, and productive relationship.
                                This Agreement protects both parties and ensures services are delivered with integrity and accountability.
                            </p>
                        </div>
                    </div>

                    <div class="mt-6 space-y-6">
                        <div class="flex flex-col md:flex-row justify-between gap-4">
                            <div class="w-full md:w-[45%]">
                                <p class="font-semibold text-sm text-foreground">Coach Signature:</p>
                                <img :src="selectedTx.coach_signature || '/images/placeholder-sign.png'"
                                     class="mt-1 h-12 border border-divider/40 rounded bg-card">
                            </div>
                            <div class="w-full md:w-[45%]">
                                <p class="font-semibold text-sm text-foreground">Date:</p>
                                <div class="border-b border-divider/40 mt-1 h-8 flex items-center px-2 text-sm"
                                     x-text="selectedTx.agreement_date || new Date().toLocaleDateString()"></div>
                            </div>
                        </div>

                        <div class="flex flex-col md:flex-row justify-between gap-4">
                            <div class="w-full md:w-[45%]">
                                <p class="font-semibold text-sm text-foreground">Client Signature:</p>
                                <img :src="selectedTx.client_signature || '/images/placeholder-sign.png'"
                                     class="mt-1 h-12 border border-divider/40 rounded bg-card">
                            </div>
                            <div class="w-full md:w-[45%]">
                                <p class="font-semibold text-sm text-foreground">Date:</p>
                                <div class="border-b border-divider/40 mt-1 h-8 flex items-center px-2 text-sm"
                                     x-text="selectedTx.agreement_date || new Date().toLocaleDateString()"></div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        {{-- ================= APPOINTMENTS ================= --}}
        <div x-show="tab === 'appointment'" class="p-4 bg-card border border-divider/40 rounded-xl" x-cloak>
            <table class="min-w-full bg-card border border-divider/40 rounded-lg overflow-hidden">
                <thead class="bg-layer text-foreground/80">
                    <tr>
                        <th class="px-4 py-3">#</th>
                        <th class="px-4 py-3">Client</th>
                        <th class="px-4 py-3">Coach</th>
                        <th class="px-4 py-3">Date</th>
                        <th class="px-4 py-3">Time</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">View</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-divider/40">
                    @forelse($appointments as $index => $app)
                        <tr class="hover:bg-layer">
                            <td class="px-4 py-3">{{ $index+1 }}</td>
                            <td class="px-4 py-3">{{ $app->client->fullname }}</td>
                            <td class="px-4 py-3">{{ $app->coach->fullname }}</td>
                            <td class="px-4 py-3">{{ $app->date }}</td>
                            <td class="px-4 py-3">{{ $app->start_time }} - {{ $app->end_time }}</td>
                            <td class="px-4 py-3">
                                @php
                                    $status = strtolower($app->status);
                                    $map = [
                                        'confirmed' => 'color:#16a34a',
                                        'pending'   => 'color:#ca8a04',
                                        'completed' => 'color:#2563eb',
                                        'cancelled' => 'color:#dc2626',
                                    ];
                                @endphp
                                <span class="font-semibold" style="{{ $map[$status] ?? '' }}">
                                    {{ ucfirst($app->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <button
                                    @click="selectedAppointment = @js($app); openAppointmentModal = true"
                                    class="font-medium"
                                    style="color: var(--color-primary)">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-6 text-foreground/60">No appointments found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Appointment Modal --}}
        <div x-show="openAppointmentModal" x-cloak x-transition.opacity
             class="fixed inset-0 flex items-center justify-center bg-black/30 z-50 p-4">
            <div @click.away="openAppointmentModal=false"
                 class="bg-card text-foreground max-w-3xl w-full rounded-2xl p-8 shadow-2xl border border-divider/40">
                <h2 class="text-2xl font-bold mb-6 border-b border-divider/40 pb-3 flex items-center gap-3">
                    <i class="fa-solid fa-calendar-check opacity-70"></i>
                    Appointment Details
                </h2>

                <template x-if="selectedAppointment">
                    <div class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="bg-layer p-4 rounded-xl border border-divider/40 flex items-center gap-3">
                                <i class="fa-solid fa-user opacity-70 text-xl"></i>
                                <div>
                                    <p class="text-xs uppercase font-semibold opacity-70">Client</p>
                                    <p class="font-medium"
                                       x-text="selectedAppointment.client.firstname + ' ' + selectedAppointment.client.lastname"></p>
                                </div>
                            </div>

                            <div class="bg-layer p-4 rounded-xl border border-divider/40 flex items-center gap-3">
                                <i class="fa-solid fa-chalkboard-user opacity-70 text-xl"></i>
                                <div>
                                    <p class="text-xs uppercase font-semibold opacity-70">Coach</p>
                                    <p class="font-medium"
                                       x-text="selectedAppointment.coach.firstname + ' ' + selectedAppointment.coach.lastname"></p>
                                </div>
                            </div>

                            <div class="bg-layer p-4 rounded-xl border border-divider/40 flex items-center gap-3">
                                <i class="fa-solid fa-calendar-days opacity-70 text-xl"></i>
                                <div>
                                    <p class="text-xs uppercase font-semibold opacity-70">Date</p>
                                    <p class="font-medium" x-text="selectedAppointment.date"></p>
                                </div>
                            </div>

                            <div class="bg-layer p-4 rounded-xl border border-divider/40 flex items-center gap-3">
                                <i class="fa-solid fa-clock opacity-70 text-xl"></i>
                                <div>
                                    <p class="text-xs uppercase font-semibold opacity-70">Time</p>
                                    <p class="font-medium"
                                       x-text="selectedAppointment.start_time + ' - ' + selectedAppointment.end_time"></p>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="bg-layer p-4 rounded-xl border border-divider/40 flex items-center gap-3">
                                <i class="fa-solid fa-circle-check text-xl" style="color:#16a34a"></i>
                                <div>
                                    <p class="text-xs uppercase font-semibold opacity-70">Status</p>
                                    <p class="font-medium"
                                       :class="{
                                         'text-[color:#16a34a]': selectedAppointment.status === 'confirmed',
                                         'text-[color:#ca8a04]': selectedAppointment.status === 'pending',
                                         'text-[color:#2563eb]': selectedAppointment.status === 'completed',
                                         'text-[color:#dc2626]': selectedAppointment.status === 'cancelled'
                                       }"
                                       x-text="selectedAppointment.status"></p>
                                </div>
                            </div>

                            <div class="bg-layer p-4 rounded-xl border border-divider/40 flex items-center gap-3">
                                <i class="fa-solid fa-clipboard-list opacity-70 text-xl"></i>
                                <div>
                                    <p class="text-xs uppercase font-semibold opacity-70">Purpose</p>
                                    <p class="font-medium" x-text="selectedAppointment.purpose || 'N/A'"></p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-layer p-4 rounded-xl border border-divider/40">
                            <p class="text-xs uppercase font-semibold mb-2 flex items-center gap-2 opacity-70">
                                <i class="fa-solid fa-message"></i>
                                Message / Notes
                            </p>
                            <p class="text-foreground/90" x-text="selectedAppointment.message || 'No additional notes provided.'"></p>
                        </div>

                        <div class="bg-layer p-4 rounded-xl border border-divider/40 border-l-4"
                             style="--tw-border-opacity:1;border-left-color: var(--color-primary)">
                            <p class="text-xs uppercase font-semibold mb-2 flex items-center gap-2 opacity-70">
                                <i class="fa-solid fa-lightbulb"></i>
                                Reminder
                            </p>
                            <p class="text-foreground/90">
                                Please ensure to confirm your attendance at least 24 hours before the scheduled time. Any changes or cancellations
                                should be communicated promptly to avoid conflicts or fees. Arrive on time and bring any required materials.
                            </p>
                        </div>

                        <div class="text-right">
                            <button @click="openAppointmentModal=false"
                                    class="px-6 py-2 bg-card border border-divider/40 hover:bg-layer font-semibold rounded-lg shadow-sm transition">
                                Close
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </main>
</div>
</body>
</html>
