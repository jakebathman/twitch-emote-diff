<?php

namespace App\Http\Controllers;

use App\TwitchChannel;

class HomeController extends Controller
{
    public function index()
    {
        // Get each channel and their current snapshot
        $channels = TwitchChannel::with('plans.emotes')->get();

        return view('home', [
            'channels' => $channels,
        ]);
    }
}
