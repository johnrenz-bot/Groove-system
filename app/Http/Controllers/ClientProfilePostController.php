<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\ClientProfilePost;
use App\Models\Client;

class ClientProfilePostController extends Controller
{
    /**
     * Store a newly created post.
     */
    public function store(Request $request)
    {
        $request->validate([
            'media'   => 'required|file|mimes:jpg,jpeg,png,gif,mp4,webm,ogg|max:20480',
            'caption' => 'nullable|string|max:1000',
        ]);

        $client = Auth::guard('client')->user();
        if (!$client) { 
            return redirect()->route('login')->with('error', 'Please login first.');
        }

        // Store file in client_media disks
        $path = $request->file('media')->store('', 'client_media');

        // Save only relative path in DB
        $post = ClientProfilePost::create([
            'client_id'   => $client->client_id,
            'client_name' => $client->full_name,
            'media_path'  => $path,
            'caption'     => $request->caption,
        ]);

        return back()->with('success', 'Profile post uploaded successfully!');
    }

    /**
     * Show profile page.
     */
    public function profile()
    {
        $client = Auth::guard('client')->user();
        $unreadNotifications = $client?->unreadNotifications ?? collect();

        return view('Client.profile', compact('client'));
    }

    /**
     * Fetch posts for logged-in client (AJAX).
     */
    public function fetchPosts()
    {
        $client = Auth::guard('client')->user();

        if (!$client) {
            return response()->json([]);
        }

        $posts = ClientProfilePost::where('client_id', $client->client_id)
                     ->latest()
                     ->get()
                     ->map(function ($post) {
                         // Generate full URL for frontend
                         $post->media_url = asset('storage/client/posts/' . $post->media_path);
                         return $post;
                     });

        return response()->json($posts);
    }

    /**
     * Delete a post.
     */
    public function destroy($id)
    {
        $client = Auth::guard('client')->user();

        $post = ClientProfilePost::where('id', $id)
                     ->where('client_id', $client->client_id)
                     ->first();

        if ($post) {
            // Optionally delete media file too
            if (Storage::disk('client_media')->exists($post->media_path)) {
                Storage::disk('client_media')->delete($post->media_path);
            }

            $post->delete();
            return response()->json(['success' => true]);
        }

        return response()->json(['error' => 'Post not found'], 404);
    }
}