<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\TwitchChannel;
use Faker\Generator as Faker;

$factory->define(TwitchChannel::class, function (Faker $faker) {
    return [
        'name' => $faker->userName,
    ];
});
