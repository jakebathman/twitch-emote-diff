<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Emote extends Model
{
    const TYPE_TWITCH = 'twitch';
    const TYPE_BTTV = 'bttv';
    const TYPE_FFZ = 'ffz';

    protected $guarded = [];

    public static function makeFromTwitchApi($data)
    {
        return static::create([
            'emote_id' => $data['id'],
            'code' => $data['code'],
            'type' => self::TYPE_TWITCH,
            'plan_id' => $data['emoticon_set'],
        ]);
    }

    public static function makeFromBttvApi($data)
    {
        return static::create([
            'emote_id' => $data['id'],
            'code' => $data['code'],
            'type' => self::TYPE_BTTV,
            'plan_id' => null,
        ]);
    }

    public static function makeFromFfzApi($data)
    {
        return static::create([
            'emote_id' => $data['id'],
            'code' => $data['code'],
            'type' => self::TYPE_FFZ,
            'plan_id' => null,
        ]);
    }

    public function getImageUrl()
    {
        switch ($this->type) {
            case self::TYPE_TWITCH:
                return "https://static-cdn.jtvnw.net/emoticons/v1/{$this->emote_id}/2.0";
                break;

            case self::TYPE_BTTV:
                return "https://cdn.betterttv.net/emote/{$this->emote_id}/2x";
                break;

            case self::TYPE_FFZ:
                return "https://cdn.frankerfacez.com/emote/{$this->emote_id}/1";
                break;

            default:
                return null;
                break;
        }
    }
}
