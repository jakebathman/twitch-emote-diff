<?php

namespace App\Console\Commands;

use App\Emote;
use App\EmoteSetSnapshot;
use App\Http\Remotes\Twitch;
use App\Http\Remotes\TwitchEmotes;
use App\Plan;
use App\SnapshotChanges;
use App\TwitchChannel;
use Illuminate\Console\Command;
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
        //First, update the channels with their metadata if it's missing
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
            $emotesInSet = collect();

            // Get all of their twitch emotes
            $channelData = TwitchEmotes::getEmotesForChannel($twitchChannel->twitch_channel_id);

            // Create any missing plans first
            $channelPlans = [];
            $this->info('  Getting plans...');
            foreach ($channelData['plans'] as $label => $id) {
                $this->line("    {$label}");
                $plan = Plan::updateOrCreate(
                    [
                        'plan_id' => $id,
                        'twitch_channel_id' => $channelData['channel_id'],
                    ],
                    ['plan_label' => $label]
                );

                $channelPlans[$id] = $plan->id;
            }

            // Get any specific emote data that's missing
            $this->info('  Getting emotes...');
            foreach ($channelData['emotes'] as $channelEmote) {
                $emote = Emote::where('emote_id', $channelEmote['id'])
                ->where('type', 'twitch')
                ->where('plan_id', $channelPlans[$channelEmote['emoticon_set']])
                ->where('code', $channelEmote['code'])
                ->first();

                if (! $emote) {
                    // This is new, so let's get its detailed data
                    $emoteInfo = $this->getEmoteInfo($channelEmote['id']);
                    $emote = Emote::create([
                        'emote_id' => $emoteInfo['id'],
                        'code' => $emoteInfo['code'],
                        'type' => 'twitch',
                        'plan_id' => $channelPlans[$channelEmote['emoticon_set']],
                    ]);
                }

                $emotesInSet->push($emote);
            }
            $this->line('    Found emotes: ' . count($channelData['emotes']));


            $added = [];
            $removed = [];

            // If there's not a current set snapshot, make this it
            $emoteIds = $emotesInSet->pluck('id')->sort();
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
            $this->question('    Added:   ' . count($added));
            $this->error('    Removed: ' . count($removed));
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
}
