<?php

namespace App\Http\Controllers;

use App\Models\CommunityPost;
use App\Models\Comment;
use App\Models\PostReact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CommunityPostController extends Controller
{
public function store(Request $request)
{
    $request->validate([
        'caption' => 'required|string|max:255',
        'media'   => 'nullable|file|mimes:jpg,jpeg,png,mp4|max:10240',
        'talent'  => 'required|in:Dance,Singing,Acting,Theater',
    ]);

    $client = Auth::guard('client')->user();
    $coach  = Auth::guard('coach')->user();

    if (!$client && !$coach) {
        if ($request->wantsJson()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        return back()->withErrors('You must be logged in to post.');
    }

    $mediaPath = null;
    if ($request->hasFile('media')) {
        $filePath  = $request->file('media')->store('community/posts', 'public');
        $mediaPath = asset('storage/' . $filePath);
    }

    $postData = [
        'caption'    => $request->caption,
        'media_path' => $mediaPath,
        'talent'     => $request->talent,
    ];

    if ($client) {
        $postData['client_id'] = $client->client_id ?? $client->id;
    } else {
        $postData['coach_id'] = $coach->coach_id ?? $coach->id;
    }

    CommunityPost::create($postData);

    // Keep user on the community view for the selected talent
    $redirectTalent = $request->input('redirect_talent', $request->talent);
    $redirectView   = $request->input('redirect_view', 'community');

    // ✅ Use the *existing* route names (case matters!)
    // 'talent'  -> defined for client in web.php
    // 'Talent'  -> defined for coach in web.php
    $redirectRoute = $client ? 'talent' : 'Talent';

    if ($request->wantsJson()) {
        return response()->json([
            'message'      => 'Post shared successfully!',
            'redirect_url' => route($redirectRoute, [
                'talent' => $redirectTalent,
                'view'   => $redirectView,
            ]),
        ]);
    }

    return redirect()->route($redirectRoute, [
        'talent' => $redirectTalent,
        'view'   => $redirectView,
    ])->with('success', 'Post shared successfully!');
}

    public function index(Request $request)
    {
        $talent = $request->get('talent');
        if (!$talent) {
            return response()->json([]);
        }

        $clientId = Auth::guard('client')->user()?->client_id ?? Auth::guard('client')->id();
        $coachId  = Auth::guard('coach')->user()?->coach_id ?? Auth::guard('coach')->id();

        $posts = CommunityPost::with(['client', 'coach'])
            ->withCount('comments')
            ->whereRaw('LOWER(talent) = ?', [strtolower($talent)])
            ->latest()
            ->get()
            ->map(function ($post) use ($clientId, $coachId) {
                $fullname  = 'Unknown User';
                $firstname = 'Unknown';
                $photo     = null;
                $role      = null;

                if ($post->client) {
                    $fullname  = trim($post->client->firstname . ' ' . ($post->client->middlename ? $post->client->middlename . ' ' : '') . $post->client->lastname);
                    $firstname = $post->client->firstname;
                    $photo     = $post->client->photo ? asset('storage/' . $post->client->photo) : null;
                    $role      = 'client';
                } elseif ($post->coach) {
                    $fullname  = trim($post->coach->firstname . ' ' . ($post->coach->middlename ? $post->coach->middlename . ' ' : '') . $post->coach->lastname);
                    $firstname = $post->coach->firstname;
                    $photo     = $post->coach->photo ? asset('storage/' . $post->coach->photo) : null;
                    $role      = 'coach';
                }

                $totalReacts = PostReact::where('post_id', $post->id)->count();

                $reacted = false;
                if ($clientId) {
                    $reacted = PostReact::where('post_id', $post->id)
                        ->where('reactor_type', 'client')
                        ->where('reactor_id', $clientId)
                        ->exists();
                } elseif ($coachId) {
                    $reacted = PostReact::where('post_id', $post->id)
                        ->where('reactor_type', 'coach')
                        ->where('reactor_id', $coachId)
                        ->exists();
                }

                $isOwner = ($clientId && $post->client_id == $clientId)
                        || ($coachId && $post->coach_id == $coachId);

                return [
                    'id'               => $post->id,
                    'caption'          => $post->caption,
                    'media_path'       => $post->media_path,
                    'created_at'       => $post->created_at->toDateTimeString(),
                    'poster_name'      => $fullname,
                    'poster_firstname' => $firstname,
                    'poster_photo'     => $photo,
                    'poster_role'      => $role,
                    'reacts'           => $totalReacts,
                    'reacted'          => $reacted,
                    'is_owner'         => $isOwner,
                    'comments_count'   => $post->comments_count,
                ];
            });

        return response()->json($posts);
    }

    public function react($id)
    {
        $post = CommunityPost::findOrFail($id);

        $client = Auth::guard('client')->user();
        $coach  = Auth::guard('coach')->user();

        if (!$client && !$coach) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $reactorType = $client ? 'client' : 'coach';
        $reactorId   = $client ? ($client->client_id ?? $client->id) : ($coach->coach_id ?? $coach->id);

        $existing = PostReact::where('post_id', $post->id)
            ->where('reactor_type', $reactorType)
            ->where('reactor_id', $reactorId)
            ->first();

        if ($existing) {
            $existing->delete();
            $reacted = false;
        } else {
            PostReact::create([
                'post_id'      => $post->id,
                'reactor_type' => $reactorType,
                'reactor_id'   => $reactorId,
            ]);
            $reacted = true;
        }

        $totalReacts = PostReact::where('post_id', $post->id)->count();

        return response()->json([
            'reacts'  => $totalReacts,
            'reacted' => $reacted,
        ]);
    }

    public function addComment(Request $request, $postId)
    {
        $request->validate([
            'comment' => 'required|string|max:1000',
        ]);

        $post   = CommunityPost::findOrFail($postId);
        $client = Auth::guard('client')->user();
        $coach  = Auth::guard('coach')->user();

        if (!$client && !$coach) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $data = [
            'post_id' => $post->id,
            'body'    => $request->comment,
        ];

        if ($client) {
            $data['client_id'] = $client->client_id ?? $client->id;
        } else {
            $data['coach_id'] = $coach->coach_id ?? $coach->id;
        }

        $comment = Comment::create($data);

        return response()->json([
            'message' => 'Comment added successfully.',
            'comment' => $comment->load(['client', 'coach']),
        ]);
    }

    public function getComments($postId)
    {
        $comments = Comment::with(['client', 'coach'])
            ->where('post_id', $postId)
            ->latest()
            ->get()
            ->map(function ($c) {
                $name = $c->client
                    ? $c->client->firstname . ' ' . $c->client->lastname
                    : ($c->coach ? $c->coach->firstname . ' ' . $c->coach->lastname : 'Unknown');

                $photo = $c->client
                    ? ($c->client->photo ? asset('storage/' . $c->client->photo) : null)
                    : ($c->coach && $c->coach->photo ? asset('storage/' . $c->coach->photo) : null);

                if ($c->client) {
                    $firstInitial = $c->client->firstname ? strtoupper(substr($c->client->firstname, 0, 1)) : '';
                    $lastInitial  = $c->client->lastname ? strtoupper(substr($c->client->lastname, 0, 1)) : '';
                    $initial      = trim($firstInitial . $lastInitial) ?: 'U';
                } elseif ($c->coach) {
                    $firstInitial = $c->coach->firstname ? strtoupper(substr($c->coach->firstname, 0, 1)) : '';
                    $lastInitial  = $c->coach->lastname ? strtoupper(substr($c->coach->lastname, 0, 1)) : '';
                    $initial      = trim($firstInitial . $lastInitial) ?: 'U';
                } else {
                    $initial = 'U';
                }

                return [
                    'id'      => $c->id,
                    'name'    => $name,
                    'photo'   => $photo,
                    'body'    => $c->body,
                    'date'    => $c->created_at->diffForHumans(),
                    'initial' => $initial,
                ];
            })
            ->values()
            ->all();

        return response()->json($comments);
    }

    public function destroy($id)
    {
        $post = CommunityPost::findOrFail($id);

        $client = auth('client')->user();
        $coach  = auth('coach')->user();

        $isOwner = ($client && $post->client_id === ($client->client_id ?? $client->id))
                || ($coach && $post->coach_id === ($coach->coach_id ?? $coach->id));

        if (!$isOwner) {
            if (request()->wantsJson()) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
            return back()->with('error', 'You are not allowed to delete this post.');
        }

        if ($post->media_path) {
            $file = str_replace('/storage/', '', $post->media_path);
            Storage::disk('public')->delete($file);
        }

        $post->delete();

        return response()->json(['message' => 'Post deleted successfully.']);
    }
}
