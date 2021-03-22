<?php

namespace App\Http\Controllers;

use App\Emote;
use App\EmoteSetSnapshot;
use App\Http\Remotes\TwitchEmotes;
use App\Plan;
use App\TwitchChannel;
use Illuminate\Support\Facades\Cache;

class ChannelEmotesController extends Controller
{
    public function index($channelName)
    {
        return view('emotes.channel', [
            'channel' => TwitchChannel::with('plans.emotes')->where('name', $channelName)->first(),
        ]);
    }
}
