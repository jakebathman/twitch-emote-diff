<?php

use Illuminate\Support\Facades\Route;

Route::get('/', 'HomeController@index')->name('home');

Route::get('emotes/{channelName}', 'ChannelEmotesController@index')->name('emotes');

Route::get('twitch', function () {
    $client = new App\Http\Remotes\Twitch;

    return $client->getChannelInfo('jakebathman');
});

Route::get('clips/{broadcasterId}', 'ClipsController@index');
