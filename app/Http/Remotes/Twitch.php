<?php

namespace App\Http\Remotes;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class Twitch
{
    const BASE = 'https://api.twitch.tv/helix/';

    protected $clientId;
    protected $clientSecret;
    protected $redisTokenCacheKey = 'twitch::app-token';
    protected $redisDataCachePrefix = 'twitch::channels::';

    private $token;

    public function __construct()
    {
        $this->clientId = config('services.twitch.client_id');
        $this->clientSecret = config('services.twitch.client_secret');
        $this->token = Redis::get($this->redisTokenCacheKey) ?: 'missing';

        $this->validateAppToken();
        $this->authHeaders = [
            'Authorization' => "Bearer {$this->token}",
            'Client-ID' => $this->clientId,
        ];
    }

    public function validateAppToken()
    {
        $url = 'https://id.twitch.tv/oauth2/validate';

        $response = Http::withHeaders([
                'Authorization' => 'OAuth ' . $this->token,
            ])
            ->get($url)
            ->json();

        if (($response['status'] ?? null) == 401) {
            return $this->refreshAppToken();
        }

        return json_encode($response);
    }

    public function refreshAppToken()
    {
        $url = "https://id.twitch.tv/oauth2/token?client_id={$this->clientId}&client_secret={$this->clientSecret}&grant_type=client_credentials";

        $response = Http::post($url)->json();

        if ($response['access_token']) {
            // Set to expire from redis way earlier than the twitch expiration
            $expirationSeconds = (int)($response['expires_in'] / 10) ?? 604800;
            Redis::setEx(
                $this->redisTokenCacheKey,
                $expirationSeconds,
                $response['access_token']
            );

            return true;
        }

        Log::error('Error refreshing Twitch app token');
        Log::info(json_encode($response));

        return;
    }

    public function getChannelInfo($username, $useCache = true)
    {
        $cacheKey = "{$this->redisDataCachePrefix}{$username}";
        if ($useCache && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $method = 'users';

        $response = Http::withHeaders($this->authHeaders)
            ->get(static::BASE . $method, [
                'login' => $username,
            ])
            ->json();

        $channelInfo = $response['data'][0];
        Cache::put($cacheKey, $channelInfo, now()->addWeek());

        return $channelInfo;
    }

    public function getClips($broadcasterId, $cursor = null, $perPage = 20)
    {
        $clips = collect();

        $method = 'clips';

        $payload = [
            'broadcaster_id' => $broadcasterId,
            'first' => $perPage,
        ];

        if ($cursor) {
            $payload['after'] = $cursor;
        }

        $response = Http::withHeaders($this->authHeaders)
            ->get(static::BASE . $method, $payload)
            ->json();

        return $response;
    }

    public function getClipsDate($broadcasterId, $start, $end)
    {
        $clips = collect();

        $method = 'clips';

        $payload = [
            'broadcaster_id' => $broadcasterId,
            'started_at' => $start,
            'ended_at' => $end,
        ];

        $response = Http::withHeaders($this->authHeaders)
            ->get(static::BASE . $method, $payload)
            ->json();

        return $response;
    }
}
