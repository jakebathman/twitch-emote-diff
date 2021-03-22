<?php

namespace App\Http\Remotes;

use Illuminate\Support\Facades\Http;

class BetterTTV
{
    const BASE = 'https://api.betterttv.net/3/';

    public function getEmotesForChannel($channelId)
    {
        $method = "cached/users/twitch/{$channelId}";

        return Http::get(static::BASE . $method)->json();
    }
}
