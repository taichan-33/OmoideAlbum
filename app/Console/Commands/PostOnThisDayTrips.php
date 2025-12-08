<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Models\Trip;
use Carbon\Carbon;
use Illuminate\Console\Command;

class PostOnThisDayTrips extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'timeline:post-on-this-day';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Post "On This Day" trips to the timeline';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::today();
        $trips = Trip::onThisDay()->get();

        $this->info("Found {$trips->count()} trips for today.");

        if ($trips->isEmpty()) {
            return;
        }

        // Find the Bot user
        $botUser = \App\Models\User::where('email', 'bot@omoide-album.com')->first();

        if (!$botUser) {
            $this->error('Bot user not found. Please run seeders.');
            return;
        }

        foreach ($trips as $trip) {
            // Check if a post already exists for this trip today to avoid duplicates
            $exists = Post::where('attachment_type', Trip::class)
                ->where('attachment_id', $trip->id)
                ->whereDate('created_at', $today)
                ->exists();

            if ($exists) {
                $this->info("Post already exists for trip: {$trip->title}");
                continue;
            }

            $yearsAgo = $today->year - $trip->start_date->year;
            $content = "{$yearsAgo}年前の今日、{$trip->user->name}さんは「{$trip->title}」に行きました！\n懐かしい思い出です。 #思い出 #{$yearsAgo}年前";

            Post::create([
                'user_id' => $botUser->id,  // Post as Bot
                'content' => $content,
                'attachment_type' => Trip::class,
                'attachment_id' => $trip->id,
            ]);

            $this->info("Posted for trip: {$trip->title}");
        }
    }
}
