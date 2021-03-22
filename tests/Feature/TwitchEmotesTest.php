<?php

namespace Tests\Feature;

use App\Http\Remotes\TwitchEmotes;
use App\TwitchChannel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TwitchEmotesTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /** @test */
    function it_gets_emotes_list_for_twitch_channel()
    {
        $channel = TwitchChannel::create([
            'name' => 'drlupo',
            'twitch_channel_id' => 29829912,
        ]);

        $emotes = TwitchEmotes::getEmotesForChannel($channel->twitch_channel_id);

        $this->assertIsArray($emotes);
        $this->assertEquals('drlupo', $emotes['channel_name']);
        $this->assertArrayHasKey('plans', $emotes);
        $this->assertArrayHasKey('emotes', $emotes);
        $this->assertArrayHasKey('subscriber_badges', $emotes);
        $this->assertArrayHasKey('bits_badges', $emotes);
        $this->assertArrayHasKey('cheermotes', $emotes);
    }

    /** @test */
    function it_gets_emote_info_by_emote_ids()
    {
        $emoteIds = [25];

        // Get a channel emote to test against (29829912 is drlupo)
        $emoteIds[] = TwitchEmotes::getEmotesForChannel('29829912')['emotes'][0]['id'];

        $emoteInfo = TwitchEmotes::getEmotes($emoteIds);

        $this->assertCount(2, $emoteInfo);
        $this->assertEquals(
            [
                'code' => 'Kappa',
                'emoticon_set' => 0,
                'id' => 25,
                'channel_id' => null,
                'channel_name' => null,
            ],
            $emoteInfo[0]
        );
        $this->assertEquals(
            [
                'code' => 'lupoK',
                'emoticon_set' => 16547,
                'id' => 128428,
                'channel_id' => '29829912',
                'channel_name' => 'drlupo',
            ],
            $emoteInfo[1]
        );
    }

    /** @test */
    function it_gets_emote_info_with_over_100_emotes()
    {
        list($ids, $data) = $this->generateEmoteData();

        $chunkData = collect($data)->chunk(100);
        $chunkIds = collect($data)->chunk(100);

        Http::fake([
            'api.twitchemotes.com/*'  => Http::sequence()
                ->push($chunkData[0])
                ->push($chunkData[1])
                ->pushStatus(404),
        ]);

        $emoteInfo = TwitchEmotes::getEmotes($ids);

        $this->assertEquals($emoteInfo, $data);
    }

    function generateEmoteData($count = 150) {
        $channelId = $this->faker->randomNumber(8);
        $channelName = $this->faker->word;
        $set = $this->faker->randomNumber();

        // Make an array of fake, unique ids
        for ($i=0; $i < $count; $i++) {
            $id = $this->faker->unique()->randomNumber();
            $ids[] = $id;
            $data[] = [
                'code' => $this->faker->unique()->word,
                'emoticon_set' => $set,
                'id' => $id,
                'channel_id' => $channelId,
                'channel_name' => $channelName,
            ];
        }

        return [$ids, $data];
    }
}
