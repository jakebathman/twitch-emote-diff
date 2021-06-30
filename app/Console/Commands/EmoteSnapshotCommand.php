<?php

namespace App\Console\Commands;

use App\Emote;
use App\EmoteSetSnapshot;
use App\Http\Remotes\BetterTTV;
use App\Http\Remotes\FrankerFaceZ;
use App\Http\Remotes\Twitch;
use App\Http\Remotes\TwitchEmotes;
use App\Plan;
use App\SnapshotChanges;
use App\TwitchChannel;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;

class EmoteSnapshotCommand extends Command
{
    protected $signature = 'emotes:snapshot {usernames?}';

    protected $description = 'Get all emotes and create a new snapshot (if changed)';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        // First, update the channels with their metadata if it's missing
        $twitch = new Twitch;
        $existing = TwitchChannel::all()->pluck('name');

        foreach ($existing->merge($this->argument('usernames')) as $channelName) {
            // Get their channel info for use on the site
            $this->info("Getting channel info for {$channelName}");
            $info = $twitch->getChannelInfo($channelName);
            // dd($info);
            TwitchChannel::updateOrCreate(
                ['name' => $channelName],
                [
                    'display_name' => $info['display_name'],
                    'twitch_channel_id' => $info['id'],
                    'profile_image_url' => $info['profile_image_url'],
                    'broadcaster_type' => $info['broadcaster_type'],
                    'description' => $info['description'],
                ]
            );
        }

        foreach (TwitchChannel::all() as $twitchChannel) {
            $this->info('Channel: ' . $twitchChannel->name);
            $channelEmotes = collect();

            // Get all of their twitch emotes
            $twitch = new Twitch;
            $twitchEmotes = $twitch->getEmotesForChannel($twitchChannel->twitch_channel_id);

            // Create any missing plans first
            $channelPlans = [];
            $this->info('  Getting plans...');

            foreach ($twitchEmotes as $emote) {
                $planLabel = implode('_', [$emote['emote_type'], $emote['tier']]);

                $plan = Plan::updateOrCreate(
                    [
                        'plan_id' => $planLabel,
                        'twitch_channel_id' => $twitchChannel->twitch_channel_id,
                    ],
                    ['plan_label' => $planLabel]
                );

                $channelPlans[$planLabel] = $plan->id;
            }

            // Get any specific emote data that's missing
            $this->info('  Getting emotes...');

            foreach ($twitchEmotes as $emote) {
                $planLabel = implode('_', [$emote['emote_type'], $emote['tier']]);

                $dbEmote = Emote::where('emote_id', $emote['id'])
                    ->where('type', 'twitch')
                    ->where('plan_id', $channelPlans[$planLabel])
                    ->where('code', $emote['name'])
                    ->first();

                if (! $dbEmote) {
                    $dbEmote = Emote::create([
                        'emote_id' => $emote['id'],
                        'code' => $emote['name'],
                        'type' => Emote::TYPE_TWITCH,
                        'plan_id' => $channelPlans[$planLabel],
                        // 'image_type' => 'png',
                    ]);
                }
                $channelEmotes->push($dbEmote);
            }
            $this->line('    Found Twitch emotes: ' . count($twitchEmotes));

            // Get BTTV emotes for this channel
            $bttvEmotes = BetterTTV::getEmotesForChannel($twitchChannel->twitch_channel_id);

            // Combine channel and shared into one array
            $bttvEmotes = array_merge(
                Arr::get($bttvEmotes, 'channelEmotes', []),
                Arr::get($bttvEmotes, 'sharedEmotes', [])
            );

            // Create or get Emote entity for each and add to overall set for channel
            foreach ($bttvEmotes as $bttvEmote) {
                $emote = Emote::firstOrCreate([
                    'emote_id' => $bttvEmote['id'],
                    'code' => $bttvEmote['code'],
                    'image_type' => $bttvEmote['imageType'],
                    'type' => Emote::TYPE_BTTV,
                ]);

                $channelEmotes->push($emote);
            }

            $this->line('    Found BTTV emotes: ' . count($bttvEmotes));

            // Get FFZ emotes for this channel
            $ffzEmotes = FrankerFaceZ::getEmotesForChannel($twitchChannel->twitch_channel_id);

            // Get array of just emotes
            $ffzSetId = Arr::get($ffzEmotes, 'room.set');
            if ($ffzSetId) {
                $ffzEmotes = Arr::get($ffzEmotes, "sets.{$ffzSetId}.emoticons", []);

                // Create or get Emote entity for each and add to overall set for channel
                foreach ($ffzEmotes as $ffzEmote) {
                    $emote = Emote::firstOrCreate([
                        'emote_id' => $ffzEmote['id'],
                        'code' => $ffzEmote['name'],
                        'image_type' => null,
                        'type' => Emote::TYPE_FFZ,
                    ]);

                    $channelEmotes->push($emote);
                }

                $this->line('    Found FFZ emotes: ' . count($ffzEmotes));
            }

            $added = [];
            $removed = [];

            // If there's not a current set snapshot, make this it
            $emoteIds = $channelEmotes->pluck('id')->sort();
            $emoteIdsJson = $emoteIds->values()->toJson();
            $this->info('  Diffing...');
            if (! $twitchChannel->current_snapshot) {
                $this->createSnapshot($twitchChannel, $emoteIdsJson);
            } else {
                // Compare the current snapshot with these emotes
                $snapshot = EmoteSetSnapshot::find($twitchChannel->current_snapshot);
                if ($emoteIdsJson != $snapshot->emote_ids) {
                    // New snapshot
                    $old = collect(json_decode($snapshot->emote_ids, true));
                    $new = collect($emoteIds);
                    $snapshot = $this->createSnapshot($twitchChannel, $emoteIdsJson);

                    $added = $new->diff($old)->values()->toArray();
                    $removed = $old->diff($new)->values()->toArray();
                    // Add this snapshot diff to the table
                    SnapshotChanges::create([
                        'twitch_channel_id' => $twitchChannel->twitch_channel_id,
                        'snapshot_id' => $snapshot->id,
                        'emote_ids_added' => json_encode($added),
                        'emote_ids_removed' => json_encode($removed),
                        ]);
                }
            }

            $this->line('');

            $addedEmotes = Emote::whereIn('id', array_values($added))->get(['id','emote_id','code','type']);
            $removedEmotes = Emote::whereIn('id', $removed)->get(['id','emote_id','code','type']);

            $this->question('    Added:   ' . count($added));
            $this->table(['ID','Emote ID','Code','Type'], $addedEmotes->toArray());

            $this->line('');

            $this->error('    Removed: ' . count($removed));
            $this->table(['ID','Emote ID','Code','Type'], $removedEmotes->toArray());

            $this->line('');
        }

        return 0;
    }

    public function getEmoteInfo($emoteId)
    {
        return Cache::remember("emote-info::{$emoteId}", now()->addDay(), function () use ($emoteId) {
            return TwitchEmotes::getEmotes([$emoteId])[0];
        });
    }

    public function createSnapshot($channel, $emoteIdsJson)
    {
        $snapshot = EmoteSetSnapshot::create([
            'twitch_channel_id' => $channel->twitch_channel_id,
            'type' => 'twitch',
            'emote_ids' => $emoteIdsJson,
        ]);

        $channel->current_snapshot = $snapshot->id;
        $channel->save();

        return $snapshot;
    }

    public function diffSnapshots($current, $prior)
    {
    }
}
