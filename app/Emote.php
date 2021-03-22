<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Emote extends Model
{
    protected $guarded = [];

    public static function makeFromApi($data, $type = 'twitch')
    {
        return static::create([
            'emote_id' => $data['id'],
            'code' => $data['code'],
            'type' => $type,
            'plan_id' => $data['emoticon_set'],
        ]);
    }

    public function getImageUrl()
    {
        return "https://static-cdn.jtvnw.net/emoticons/v1/{$this->emote_id}/2.0";
    }
}
