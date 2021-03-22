<?php

namespace App\Console\Commands;

use App\Clip;
use App\Http\Remotes\Twitch;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;

class GetClipsCommand extends Command
{
    protected $signature = 'clips:get {broadcaster} {perPage=20}';

    protected $description = 'Command description';

    protected $bar;

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $twitch = new Twitch;
        $broadcasterId = $this->argument('broadcaster');
        $clips = collect();
        $estClipTotal = 1200;
        $cursor = null;
        $perPage = $this->argument('perPage');
        $loops = ceil($estClipTotal/$perPage);

        $this->bar = $this->output->createProgressBar($loops);

        $this->bar->start();

        for ($i=0; $i < $loops; $i++) {
            $response = $twitch->getClips($broadcasterId, $cursor, $perPage);

            $data = collect(Arr::get($response, 'data', []));

            $clips = $clips->merge($data);
            $cursor = Arr::get($response, 'pagination.cursor');
            $this->updateProgressBar("Clip page {$i}: " . $data->count() . ' (' . $clips->count() . ' total)');

            // Save to the database
            foreach ($data as $clip) {
                Clip::updateOrCreate(
                    [
                    'clip_id' => $clip['id'],
                    ],
                    [
                    'url' => $clip['url'],
                    'broadcaster_id' => $clip['broadcaster_id'],
                    'broadcaster_name' => $clip['broadcaster_name'],
                    'creator_id' => $clip['creator_id'],
                    'creator_name' => $clip['creator_name'],
                    'video_id' => $clip['video_id'],
                    'game_id' => $clip['game_id'],
                    'title' => $clip['title'],
                    'view_count' => $clip['view_count'],
                    'created_at' => $clip['created_at'],
                    'thumbnail_url' => $clip['thumbnail_url'],
                    ]
                );
            }

            if (! $cursor) {
                $this->updateProgressBar('BREAK!');
                break;
            }
        }

        $this->bar->finish();

        $this->info('Total clips: ' . $clips->count());

        return 0;
    }

    protected function updateProgressBar($current)
    {
        $this->bar->advance();

        // Display current task context above the progress bar
        $this->bar->clear();
        $this->line("... {$current}");
        $this->bar->display();
    }
}
