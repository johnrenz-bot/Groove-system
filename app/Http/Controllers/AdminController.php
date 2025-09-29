<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;

use App\Models\User;
use App\Models\Admin;
use App\Models\Client;
use App\Models\Coach;
use App\Models\CommunityPost;
use App\Models\ClientProfilePost;
use App\Models\CoachProfilePost;
use App\Models\Comment;
use App\Models\Agreement;
use App\Models\Appointment;
use App\Models\Announcement;
use App\Models\Setting;
use App\Models\Ticket;


use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use App\Notifications\AdminPasscodeNotification;
use Carbon\Carbon;


use App\Notifications\GrooveNotification;

class AdminController extends Controller
{
    /**
     * Display the admin dashboard with various metrics and data.
     */
   // Add this method (note the plural + view name matches file)
public function tickets()
{
    $user = Auth::user();
    $query = \App\Models\Ticket::query();

    if ($q = request('q')) {
        $query->where(function($w) use ($q){
            $w->where('subject', 'like', "%$q%")
              ->orWhere('name', 'like', "%$q%")
              ->orWhere('email', 'like', "%$q%");
        });
    }
    if ($s = request('status'))   $query->where('status', $s);
    if ($p = request('priority')) $query->where('priority', $p);

    $tickets = $query->latest()->paginate(20);

    return view('Admin.AdminTickets', [
        'adminName'  => $user?->name ?? 'Admin',
        'adminEmail' => $user?->email ?? '',
        'tickets'    => $tickets,
    ]);
}

    public function dashboard(Request $request)
    {
        $admin = Auth::guard('admin')->user();
        if (!$admin) {
            return redirect()->route('login')->withErrors(['login_error' => 'Unauthorized access.']);
        }

        // Announcements / Notifications
        $announcements = Announcement::latest()->take(50)->get();
        $unreadNotifications = $admin->unreadNotifications;
        $notificationsData = $unreadNotifications->map(function ($n) {
            return [
                'id'      => $n->id,
                'title'   => $n->data['title']   ?? '',
                'message' => $n->data['message'] ?? '',
                'time'    => $n->created_at->diffForHumans(),
            ];
        });
        $unreadMessages = []; // placeholder if you later wire messaging

        // Optional filter
        $filterTalent = $request->input('talent', null);

        // Headline metrics
        $totalClients        = Client::count();
        $totalCoaches        = Coach::count();
        $totalCommunityPosts = CommunityPost::count();
        $totalComments       = Comment::count();

        $activeToday = Client::whereDate('updated_at', today())->count()
                     + Coach::whereDate('updated_at', today())->count();

        $mostActiveHour = DB::table('clients')
            ->select(DB::raw('HOUR(updated_at) as hour'), DB::raw('count(*) as count'))
            ->groupBy('hour')
            ->orderByDesc('count')
            ->first();

        // ---- Talents (robust) -------------------------------------------------
        // Merge counts from clients.talent (single string) + coaches.talents (CSV)
        $coachTalentCounts  = $this->aggregateCsvColumn(Coach::query(), 'talents');
        $clientTalentCounts = $this->aggregateCsvColumn(Client::query(), 'talent');
        $allTalentCounts    = $coachTalentCounts
                                ->mergeRecursive($clientTalentCounts)
                                ->map(fn ($v) => is_array($v) ? array_sum($v) : $v)
                                ->sortDesc(); // ["Dance" => 23, "Sing" => 17, ...]

        // Top talents as [{ talent: ..., total: ... }, ...] for charts/UI convenience
        $topTalents = $allTalentCounts
            ->take(5)
            ->map(function ($count, $talent) {
                return (object) ['talent' => $talent, 'total' => $count];
            })
            ->values();

        // Talent counts for specific buckets (no 'coaches.talent' usage!)
        $buckets = ['dance', 'sing', 'acting', 'theater'];
        $talentCounts = [];
        foreach ($buckets as $bucket) {
            $clientsCount = Client::whereNotNull('talent')
                ->whereRaw('LOWER(TRIM(talent)) = ?', [strtolower($bucket)])
                ->count();

            $coachesCount = Coach::query()
                ->when(true, function ($q) use ($bucket) {
                    $this->whereCsvContains($q, 'talents', $bucket);
                })
                ->count();

            $talentCounts[$bucket] = [
                'clients' => $clientsCount,
                'coaches' => $coachesCount,
                'total'   => $clientsCount + $coachesCount,
            ];
        }

        // ---- Data with optional filter ----------------------------------------
        $clients = Client::when($filterTalent, function ($q) use ($filterTalent) {
                $q->whereNotNull('talent')
                  ->whereRaw('LOWER(TRIM(talent)) = ?', [strtolower($filterTalent)]);
            })
            ->get();

        $coaches = Coach::when($filterTalent, function ($q) use ($filterTalent) {
                // filter on CSV column `talents`
                $this->whereCsvContains($q, 'talents', $filterTalent);
            })
            ->get();

        $communityPosts = CommunityPost::when($filterTalent, function ($q) use ($filterTalent) {
                // If your posts have a single 'talent' column:
                $q->whereRaw('LOWER(TRIM(talent)) = ?', [strtolower($filterTalent)]);
            })
            ->latest()
            ->get();

        // Appointments / Agreements
        $appointments = Appointment::with(['client', 'coach'])
            ->latest()
            ->limit(5)
            ->get();

        $agreements = Agreement::with(['client', 'coach'])
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($tx) {
                $tx->client_signature = $tx->client_signature
                    ? asset('storage/' . $tx->client_signature)
                    : null;
                $tx->coach_signature = $tx->coach_signature
                    ? asset('storage/' . $tx->coach_signature)
                    : null;
                return $tx;
            });

        // System sample metrics (placeholder)
        $systemMetrics = [
            'cpu'           => 37,
            'memory'        => 68,
            'disk'          => 55,
            'uptime'        => '99.98%',
            'response_time' => '180ms',
            'error_rate'    => '0.2%',
        ];

        $maintenance = [
            'last_deploy'     => '2025-08-10 14:32',
            'bugs_fixed'      => 12,
            'security_status' => 'All checks passed',
            'last_backup'     => '2025-08-15 02:00',
        ];

        $activeUserCount  = $activeToday;
        $suspiciousLogins = 3;

        return view('Admin.AdminDashboard', compact(
            'notificationsData',
            'unreadMessages',
            'admin',
            'announcements',

            'clients',
            'coaches',
            'communityPosts',
            'appointments',
            'agreements',

            'filterTalent',
            'talentCounts',
            'topTalents',
            'allTalentCounts', // if you also chart the whole distribution

            'systemMetrics',
            'maintenance',
            'activeUserCount',
            'suspiciousLogins',

            'totalClients',
            'totalCoaches',
            'totalCommunityPosts',
            'totalComments',
            'activeToday',
            'mostActiveHour',
        ));
    }

    /**
     * Users list
     */
    public function users()
    {
        $clients = Client::all();
        $coaches = Coach::all();
        return view('Admin.Adminusers', compact('clients', 'coaches'));
    }

    /**
     * Control page
     */
    public function control()
    {
        $themes  = ['light', 'dark', 'ocean'];
        $current = Setting::get('theme', 'light');
        return view('Admin.AdminControl', compact('themes', 'current'));
    }

    public function updateTheme(Request $request)
    {
        $request->validate([
            'theme' => 'required|in:light,dark,ocean'
        ]);

        Setting::set('theme', (string) $request->input('theme'));

        return back()->with('status', 'Theme updated to ' . $request->input('theme') . '!');
    }

    /**
     * Delete a community post.
     */
    public function deleteCommunityPost($id)
    {
        $post = CommunityPost::findOrFail($id);

        if ($post->media_path) {
            $file = str_replace('/storage/', '', $post->media_path);
            Storage::disk('public')->delete($file);
        }

        $post->delete();
        return redirect()->back()->with('success', 'Community post deleted.');
    }

    /**
     * Delete a client profile post.
     */
    public function deleteClientPost($id)
    {
        $post = ClientProfilePost::findOrFail($id);
        $post->delete();
        return redirect()->back()->with('success', 'Client post deleted.');
    }

    /**
     * Delete a coach profile post.
     */
    public function deleteCoachPost($id)
    {
        $post = CoachProfilePost::findOrFail($id);
        $post->delete();
        return redirect()->back()->with('success', 'Coach post deleted.');
    }

    /**
     * Reports snapshot page (kept simple).
     */
    public function reports()
    {
        $totalClients        = Client::count();
        $totalCoaches        = Coach::count();
        $totalCommunityPosts = CommunityPost::count();
        $totalComments       = Comment::count();

        $activeToday = Client::whereDate('updated_at', today())->count()
                     + Coach::whereDate('updated_at', today())->count();

        $mostActiveHour = DB::table('clients')
            ->select(DB::raw('HOUR(updated_at) as hour'), DB::raw('count(*) as count'))
            ->groupBy('hour')
            ->orderByDesc('count')
            ->first();

        // Use merged top talents here too if you want;
        // for compatibility, keep a simple client-only fallback:
        $topTalents = Client::select('talent', DB::raw('count(*) as total'))
            ->whereNotNull('talent')
            ->groupBy('talent')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        return view('Admin.AdminDashboard', compact(
            'totalClients',
            'totalCoaches',
            'totalCommunityPosts',
            'totalComments',
            'activeToday',
            'mostActiveHour',
            'topTalents'
        ));
    }

    /**
     * Transactions page.
     */
    public function transaction()
    {
        $transactions = Agreement::with(['client', 'coach'])
            ->latest()
            ->get()
            ->map(function ($tx) {
                $tx->client_signature = $tx->client_signature
                    ? asset('storage/' . $tx->client_signature)
                    : null;
                $tx->coach_signature = $tx->coach_signature
                    ? asset('storage/' . $tx->coach_signature)
                    : null;
                return $tx;
            });

        $appointments = Appointment::with(['client', 'coach'])
            ->latest()
            ->get();

        return view('Admin.AdminTransaction', compact('transactions', 'appointments'));
    }

    // -------------------- Notifications ---------------------------------------

    public function fetchNotifications()
    {
        $admin = Auth::guard('admin')->user();
        $unreadNotifications = $admin->unreadNotifications;

        $notificationsData = $unreadNotifications->map(function ($n) {
            return [
                'id'      => $n->id,
                'title'   => $n->data['title']   ?? '',
                'message' => $n->data['message'] ?? '',
                'time'    => $n->created_at->diffForHumans(),
            ];
        });

        return response()->json([
            'notifications' => $notificationsData,
            'unreadCount'   => $unreadNotifications->count(),
        ]);
    }

    public function sendNotification(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'title'   => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        $user = User::find($request->user_id);
        $user->notify(new GrooveNotification($request->title, $request->message));

        return response()->json([
            'status'  => 'success',
            'message' => 'Notification sent successfully!',
        ]);
    }

    public function storeAnnouncement(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:500',
            'title'   => 'nullable|string|max:255',
        ]);

        $announcement = Announcement::create([
            'title'   => $request->title,
            'message' => $request->message,
            'author'  => auth('admin')->user()->name,
        ]);

        // Notify all clients
        Client::all()->each(function ($client) use ($announcement) {
            $client->notify(new GrooveNotification(
                $announcement->title ?? 'Announcement',
                $announcement->message
            ));
        });

        // Notify all coaches
        Coach::all()->each(function ($coach) use ($announcement) {
            $coach->notify(new GrooveNotification(
                $announcement->title ?? 'Announcement',
                $announcement->message
            ));
        });

        return redirect()->back()->with('success', 'Announcement sent!');
    }

    // -------------------- Helpers ---------------------------------------------

    /**
     * Aggregate a CSV (comma-separated) text column into counts.
     * - Trims values
     * - Ignores null/empty
     * - Normalizes casing (Title Case)
     */
    private function aggregateCsvColumn($eloquentQuery, string $column): Collection
    {
        $model = $eloquentQuery->getModel();
        if (!Schema::hasColumn($model->getTable(), $column)) {
            return collect();
        }

        $rows = $eloquentQuery->whereNotNull($column)->pluck($column);
        $counts = collect();

        foreach ($rows as $csv) {
            $items = is_string($csv) ? preg_split('/\s*,\s*/', $csv, -1, PREG_SPLIT_NO_EMPTY) : [];
            foreach ($items as $raw) {
                $val = trim($raw);
                if ($val === '') continue;
                // Normalize casing
                $val = mb_convert_case($val, MB_CASE_TITLE, "UTF-8");
                $counts[$val] = ($counts[$val] ?? 0) + 1;
            }
        }
        return $counts;
    }

    /**
     * Add a WHERE condition that checks if a CSV column contains a token.
     * Uses: FIND_IN_SET(needle, normalized_csv)
     */
    private function whereCsvContains($query, string $column, string $needle): void
    {
        $needle = mb_strtolower(preg_replace('/\s+/', '', $needle));              // " Hip Hop " -> "hiphop"
        // Remove spaces inside the CSV then lowercase, so "Hip Hop, Jazz" -> "hiphop,jazz"
        $raw = "FIND_IN_SET(?, REPLACE(LOWER(REPLACE($column, ' ', '')), ',,', ',')) > 0";
        $query->whereNotNull($column)->whereRaw($raw, [$needle]);
    }




    // ================= Passcode helpers =================
    private function passcodeCacheKey(Admin $admin): string
    {
        return "admin_passcode:{$admin->id}";
    }

    private function generatePasscode(): string
    {
        return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    private function ensurePasscodeIssued(Admin $admin): void
    {
        $key = $this->passcodeCacheKey($admin);
        $data = Cache::get($key);
        if ($data && Carbon::parse($data['expiresAt'])->isFuture()) {
            return; // already have a valid code
        }
        $this->issuePasscode($admin);
    }

    public function issuePasscode(Admin $admin): void
    {
        $code = $this->generatePasscode();
        $payload = [
            'hash'      => Hash::make($code),
            'expiresAt' => now()->addMinutes(10)->toDateTimeString(),
            'attempts'  => 0,
        ];
        $expiresAt = Carbon::parse($payload['expiresAt']);
        Cache::put($this->passcodeCacheKey($admin), $payload, $expiresAt);

        if ($admin->email) {
            $admin->notify(new AdminPasscodeNotification($code));
        }

        if (config('app.debug')) {
            session()->flash('debug_passcode', $code); // show once in dev if you want
        }
    }

    private function verifyPasscodeValue(Admin $admin, string $code): bool
    {
        $key = $this->passcodeCacheKey($admin);
        $data = Cache::get($key);
        if (!$data) return false;

        if (Carbon::parse($data['expiresAt'])->isPast()) {
            Cache::forget($key); return false;
        }

        if (($data['attempts'] ?? 0) >= 6) {
            Cache::forget($key); return false;
        }

        $ok = Hash::check($code, $data['hash']);
        if (!$ok) {
            $data['attempts'] = ($data['attempts'] ?? 0) + 1;
            Cache::put($key, $data, Carbon::parse($data['expiresAt']));
            return false;
        }

        Cache::forget($key);
        return true;
    }

    // ================= Public endpoints =================
    public function verifyPasscode(Request $request)
    {
        $request->validate(['code' => 'required|digits:6']);

        $admin = Auth::guard('admin')->user();
        if (!$admin) {
            return response()->json(['ok' => false, 'message' => 'Not authenticated.'], 401);
        }

        if ($this->verifyPasscodeValue($admin, $request->code)) {
            session()->forget('require_admin_passcode');
            session()->put('admin_passcode_verified', true);
            return response()->json(['ok' => true]);
        }

        return response()->json(['ok' => false, 'message' => 'Invalid or expired code.'], 422);
    }

    public function resendPasscode(Request $request)
    {
        $admin = Auth::guard('admin')->user();
        if (!$admin) {
            return response()->json(['ok' => false, 'message' => 'Not authenticated.'], 401);
        }

        $key = 'admin_passcode_resend:' . $admin->id;
        if (RateLimiter::tooManyAttempts($key, 5)) {
            return response()->json(['ok' => false, 'message' => 'Too many requests. Try again later.'], 429);
        }

        RateLimiter::hit($key, 3600);
        $this->issuePasscode($admin);

        return response()->json(['ok' => true, 'message' => 'A new code was sent to your email.']);
    }
}