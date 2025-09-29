<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Coach;
use App\Models\Appointment;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /** Redirect to proper home by guard */
    public function home()
    {
        if (auth('client')->check()) {
            return redirect()->route('client.home');
        }
        if (auth('coach')->check()) {
            return redirect()->route('coach.home');
        }
        return redirect()->route('login');
    }

    /** Coach / Client dashboard */
    public function showDashboard()
    {
        $now = Carbon::now();

        // Top talents (from clients.talent + coaches.talents)
        [$topTalentsWithCount, $topTalents] = $this->getTopTalents();

        // Top verified coaches with AVG rating (from feedbacks.rating)
        $topVerifiedCoaches = Coach::query()
            ->select('coaches.*')
            ->where('status', 'active')
            ->where('email_verified', true)
            // Compute average feedback rating and expose it as "rating"
            ->withAvg('feedbacks as rating', 'rating')
            // Highest rated first; unrated (NULL) last
            ->orderByRaw('COALESCE(rating, 0) DESC')
            ->take(5)
            ->get();

        // If logged-in coach
        if (auth('coach')->check()) {
            /** @var \App\Models\Coach $coach */
            $coach = auth('coach')->user();

            $recommendedClients = Client::all()->map(function ($u) {
                $u->role = 'client';
                $u->id   = $u->client_id;
                return $u;
            });

            $recommendedCoaches = Coach::where('coach_id', '!=', $coach->coach_id)
                ->get()
                ->map(function ($u) {
                    $u->role = 'coach';
                    $u->id   = $u->coach_id;
                    return $u;
                });

            $recommendedUsers = $recommendedClients->concat($recommendedCoaches);

            $appointments = Appointment::with('client')
                ->where('coach_id', $coach->coach_id)
                ->where('status', '!=', 'cancelled')
                ->where(function ($q) use ($now) {
                    $q->where('date', '>', $now->toDateString())
                      ->orWhere(function ($qq) use ($now) {
                          $qq->where('date', $now->toDateString())
                             ->whereTime('end_time', '>', $now->format('H:i:s'));
                      });
                })
                ->latest()
                ->get();

            return view('Coach.home', compact(
                'topTalents',
                'topTalentsWithCount',
                'recommendedUsers',
                'topVerifiedCoaches',
                'appointments',
                'coach'
            ));
        }

        // If logged-in client
        if (auth('client')->check()) {
            /** @var \App\Models\Client $client */
            $client = auth('client')->user();

            $recommendedClients = Client::where('client_id', '!=', $client->client_id)
                ->get()
                ->map(function ($u) {
                    $u->role = 'client';
                    $u->id   = $u->client_id;
                    return $u;
                });

            $recommendedCoaches = Coach::all()->map(function ($u) {
                $u->role = 'coach';
                $u->id   = $u->coach_id;
                return $u;
            });

            $recommendedUsers = $recommendedClients->concat($recommendedCoaches);

            $appointments = Appointment::with('coach')
                ->where('client_id', $client->client_id)
                ->where('status', '!=', 'cancelled')
                ->where(function ($q) use ($now) {
                    $q->where('date', '>', $now->toDateString())
                      ->orWhere(function ($qq) use ($now) {
                          $qq->where('date', $now->toDateString())
                             ->whereTime('end_time', '>', $now->format('H:i:s'));
                      });
                })
                ->latest()
                ->get();

            return view('Client.home', compact(
                'topTalents',
                'topTalentsWithCount',
                'recommendedUsers',
                'topVerifiedCoaches',
                'appointments',
                'client'
            ));
        }

        return redirect()->route('login')->with('error', 'Please log in.');
    }

    /**
     * Build a ranked list of talents.
     * Coaches: 'talents' (comma-separated)
     * Clients: 'talent'  (single/string)
     *
     * @return array [Collection $counts, Collection $topKeys]
     */
    private function getTopTalents(): array
    {
        // Common placeholders we never want to count/show
        $invalid = [
            'n/a','na','n.a.','none','null','nil','unknown','undefined',
            'not applicable','-','--'
        ];

        $clientTalents = DB::table('clients')
            ->whereNotNull('talent')
            ->where('talent', '!=', '')
            ->pluck('talent')
            ->toArray();

        $coachTalents = DB::table('coaches')
            ->whereNotNull('talents')
            ->where('talents', '!=', '')
            ->pluck('talents')
            ->toArray();

        $all = array_merge($clientTalents, $coachTalents);

        $normalized = [];
        foreach ($all as $s) {
            foreach (preg_split('/\s*,\s*/', (string) $s, -1, PREG_SPLIT_NO_EMPTY) as $piece) {
                $piece = trim($piece);
                if ($piece === '') continue;

                // Remove non-letters (keep spaces and hyphens)
                $clean = preg_replace('/[^a-z\s\-]/i', '', $piece);
                $clean = preg_replace('/\s+/', ' ', $clean);
                $clean = trim($clean, " -");

                if ($clean === '' || mb_strlen($clean) < 2) continue;

                $low = strtolower($clean);
                if (in_array($low, $invalid, true)) continue;

                // Normalize casing (e.g., "dance", "DANCE" -> "Dance")
                $normalized[] = ucwords(strtolower($clean));
            }
        }

        $counts  = collect(array_count_values($normalized))->sortDesc();
        $topKeys = $counts->keys()->take(5);

        return [$counts, $topKeys];
    }
}
