<?php

namespace App\Http\Controllers;

use App\Models\Coach;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Models\CoachProfilePost;
use App\Models\ClientProfilePost;
use App\Models\Appointment;

use App\Models\User;

class PageController extends Controller
{
    public function index()
    {
        // Adjust filters/columns to what your app needs
        $coaches = Coach::query()
            // ->where('status', 'approved')
            // ->latest()
            ->get();

        // Use the same case as the folder: "Client"
        return view('Client.talent', compact('coaches'));
    }

    public function terms()
    {
        return view('termsandcon'); 
    }

    public function About()
    {
        if (auth()->guard('coach')->check()) {
            // If a coach is logged in
            return view('coach.about');
        } elseif (auth()->guard('client')->check()) {
            // If a client is logged in
            return view('client.about');
        }

        // If no one is logged in, redirect to login or a default view
        return redirect()->route('login')->with('error', 'Please login first.');
    }

    public function showProfile($id)
    {
        // Determine viewer role (client, coach, or guest)
        $viewerRole = session('client') ? 'client' : (session('coach') ? 'coach' : 'guest');

        $user = Client::find($id) ?? Coach::find($id);

        if (!$user) {
            abort(404, 'User not found.');
        }

        $posts = collect();

        if ($user instanceof Client) {
            $posts = ClientProfilePost::with('client')
                ->where('client_id', $id)
                ->get()
                ->map(function($post) {
                    return (object)[
                        'id' => $post->id,
                        'media_path' => $post->media_path,
                        'media_url' => asset('storage/client/posts/' . $post->media_path), 
                        'caption' => $post->caption,
                        'user_name' => $post->client->firstname . ' ' . $post->client->lastname,
                        'role' => 'client',
                        'created_at' => $post->created_at,
                    ];
                });
       } elseif ($user instanceof Coach) {
    $posts = CoachProfilePost::with('coach')
        ->where('coach_id', $id)
        ->get()
        ->map(function($post) {
            return (object)[
                'id'        => $post->id,
                'media_path'=> $post->media_path,
                'media_url' => asset('storage/coach/posts/' . $post->media_path),
                'caption'   => $post->caption,
                'user_name' => $post->coach->firstname . ' ' . $post->coach->lastname,
                'role'      => 'coach',
                'created_at'=> $post->created_at,
            ];
        });
}


        // Sort posts by latest first
        $posts = $posts->sortByDesc('created_at');

        // Return the profile view
        return view("{$viewerRole}.otherprofile", [
            'user' => $user,
            'posts' => $posts,
        ]);
    }
}
