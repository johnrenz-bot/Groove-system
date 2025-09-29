<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\CoachProfilePost;
use App\Models\Appointment;
use App\Models\Coach;

class CoachProfilePostController extends Controller
{
    /**
     * Store a new coach post
     */
    public function store(Request $request)
    {
        $request->validate([
            'media'   => 'required|file|mimes:jpg,jpeg,png,gif,mp4,webm,ogg|max:20480',
            'caption' => 'nullable|string|max:1000',
        ]);

        $coach = Auth::guard('coach')->user();
        if (!$coach) {
            return $request->ajax()
                ? response()->json(['error' => 'Unauthenticated'], 401)
                : redirect()->route('coach.login')->with('error', 'Please login first.');
        }

        $path = $request->file('media')->store('coach/posts', 'public');
        $filename = basename($path);

        $post = CoachProfilePost::create([
            'coach_id'   => $coach->coach_id,
            'coach_name' => $coach->full_name,
            'media_path' => $filename,
            'caption'    => $request->caption,
        ]);

        $responseData = [
            'id'         => $post->id,
            'media_url'  => asset('storage/coach/posts/' . $filename),
            'caption'    => $post->caption,
            'coach_name' => $post->coach_name,
            'created_at' => $post->created_at->toDateTimeString(),
        ];

 return redirect()
            ->route('Profile')  
            ->with('success', 'Post uploaded successfully!');
    }

    /**
     * Delete a coach post
     */
    public function destroy($id)
    {
        $coach = Auth::guard('coach')->user();
        if (!$coach) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $post = CoachProfilePost::where('id', $id)
            ->where('coach_id', $coach->coach_id)
            ->first();

        if ($post) {
            Storage::disk('public')->delete('coach/posts/' . $post->media_path);
            $post->delete();
            return response()->json(['success' => true]);
        }

        return response()->json(['error' => 'Post not found'], 404);
    }

    /**
     * Fetch all posts for the authenticated coach
     */
    public function fetchPosts()
    {
        $coach = Auth::guard('coach')->user();
        if (!$coach) {
            return response()->json(['posts' => []]);
        }

        $posts = CoachProfilePost::where('coach_id', $coach->coach_id)
            ->latest()
            ->get()
            ->map(function ($post) {
                return [
                    'id'         => $post->id,
                    'media_url'  => asset('storage/coach/posts/' . $post->media_path),
                    'caption'    => $post->caption,
                    'coach_name' => $post->coach_name,
                    'created_at' => $post->created_at->toDateTimeString(),
                ];
            });

        return response()->json(['posts' => $posts]);
    }

    /**
     * Show the coach profile (own or by ID)
     */
  public function profile($id = null)
    {
        if ($id) {
            // If viewing another coach by ID
            $coach = Coach::with(['feedbacks.user'])->findOrFail($id);
        } else {
            // Viewing authenticated coach
            $authCoach = Auth::guard('coach')->user();
            if (!$authCoach) {
                return redirect()->route('coach.login')->with('error', 'Please login first.');
            }
            $coach = Coach::with(['feedbacks.user'])->findOrFail($authCoach->coach_id);
        }

        // Get appointments for this coach
        $appointments = Appointment::with('client')
            ->where('coach_id', $coach->coach_id)
            ->whereIn('status', ['pending', 'confirmed', 'completed'])
            ->orderBy('date', 'desc')
            ->orderBy('start_time', 'asc')
            ->get();

        return view('Coach.Profile', [
            'coach'         => $coach,
            'appointments'  => $appointments,
            'averageRating' => $coach->rating ?? 0,
            'ratingCount'   => $coach->rating_count ?? 0,
            'latestComment' => $coach->comments ?? null,
        ]);
    }

    /**
     * Fetch authenticated coach photo
     */
    public function fetchPhoto()
    {
        $coach = Auth::guard('coach')->user();
        if (!$coach) {
            return response()->json(['photo_url' => '']);
        }

        return response()->json([
            'photo_url' => $coach->photo ? asset('storage/' . $coach->photo) : ''
        ]);
    }
}
