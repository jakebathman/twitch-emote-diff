<?php

namespace App\Http\Remotes;

use Illuminate\Support\Facades\Http;

class TwitchEmotes
{
    public const BASE = 'https://api.twitchemotes.com/api/v4/';

    public static function getEmotesForChannel($channelId)
    {
        $method = "channels/{$channelId}";

        return Http::get(static::BASE . $method)->json();
    }

    public static function getEmotes(array $emoteIds)
    {
        $method = 'emotes';
        $emoteIds = collect($emoteIds);
        $data = collect();

        // This endpoint only accepts 100 at a time, so paginate if needed
        return $emoteIds->chunk(100)->flatMap(function ($chunk) use ($method) {
            return Http::get(static::BASE . $method, [
                'id' => $chunk->implode(','),
                ])
                ->json();
        })
        ->values()
        ->toArray();
    }
}
