<?php

namespace App;

use App\Plan;
use Illuminate\Database\Eloquent\Model;

class TwitchChannel extends Model
{
    protected $guarded = [];

    public function getImageUrl()
    {
        return $this->profile_image_url;
    }

    public function plans()
    {
        return $this->hasMany(Plan::class, 'twitch_channel_id', 'twitch_channel_id');
    }
}
