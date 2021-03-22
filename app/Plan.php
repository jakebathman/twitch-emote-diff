<?php

namespace App;

use App\Emote;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $guarded = [];

    public function emotes()
    {
        return $this->hasMany(Emote::class);
    }
}
