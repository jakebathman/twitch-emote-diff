<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Clip extends Model
{
    public $guarded = [];

    public $incrementing = false;

    public $timestamps = false;

    protected $primaryKey = 'clip_id';

    public function clipIdWithBreaks()
    {
        $clipId = $this->clip_id;

        $words = preg_split('/(?=[A-Z])/', $clipId);
        array_shift($words);
        return implode('<wbr>', $words);
    }
}
