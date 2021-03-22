<?php

namespace App\Http\Controllers;

use App\Clip;

class ClipsController extends Controller
{
    public function index($broadcasterId)
    {
        $clips = Clip::where('broadcaster_id', $broadcasterId)->get();
        $clipsByDate = $clips->sortByDesc('created_at')->values();
        $clipsByViews = $clips->sortByDesc('view_count')->values();

        return view('clips', [
            'clipsByDate' => $clipsByDate,
            'clipsByViews' => $clipsByViews,
            ]);
    }
}
