<?php

use App\TwitchChannel;
use Illuminate\Database\Seeder;

class TwitchChannelSeeder extends Seeder
{
    public function run()
    {
        factory(TwitchChannel::class)->create([
            'name' => 'drlupo',
        ]);
    }
}
