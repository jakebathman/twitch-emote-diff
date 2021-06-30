<?php

namespace App\Http\Remotes;

use Illuminate\Support\Facades\Http;

class FrankerFaceZ
{
    const BASE = 'https://api.frankerfacez.com/v1/';

    public static function getEmotesForChannel($channelId)
    {
        $method = "room/id/{$channelId}";

        return Http::get(static::BASE . $method)->json();
    }
}
