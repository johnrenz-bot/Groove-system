<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Feedback;
use Illuminate\Support\Facades\Auth;

class FeedbackController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'coach_id' => 'required|exists:coaches,coach_id',
            'rating'   => 'required|integer|min:1|max:5',
            'comment'  => 'required|string|max:500',
        ]);

        $clientId = Auth::guard('client')->id();

        Feedback::create([
            'coach_id' => $request->coach_id,
            'user_id'  => $clientId,
            'rating'   => $request->rating,
            'comment'  => $request->comment,
        ]);

        return back()->with('success', 'Na-submit na ang iyong feedback!');
    }
}
