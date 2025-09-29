<?php

namespace App\Http\Controllers;

use App\Models\Announcement;

class AnnouncementViewController extends Controller
{
    public function index()
    {
        // Get latest 50 announcements
        $announcements = Announcement::latest()->take(50)->get();

        return response()->json($announcements);
    }
}
