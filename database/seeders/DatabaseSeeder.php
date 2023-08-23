<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Enums\PlayerType;
use App\Enums\RoomType;
use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        if (! User::where('email', 'yangchenshin77@gmail.com')->exists()) {
            User::factory()->create([
                'name' => 'Lucas Yang',
                'email' => 'yangchenshin77@gmail.com',
            ]);
        }

        if (! Room::where('title', '動漫觀影室')->exists()) {
            /** @var \App\Models\Room */
            $room = Room::create([
                'type' => RoomType::Video,
                'title' => '動漫觀影室',
                'auto_play' => false,
                'auto_remove' => false,
                'note' => '備註文字欄...',
            ]);

            $room->playlistItems()->create([
                'type' => PlayerType::Video,
                'title' => '色違いの翼',
                'url' => '/media/色違いの翼.mp4',
                'thumbnail' => '/media/色違いの翼.jpg',
            ]);

            $room->playlistItems()->create([
                'type' => PlayerType::YouTube,
                'title' => '色違いの翼',
                'url' => 'https://www.youtube.com/watch?v=Melo0YFiDSY',
            ]);
        }

        if (! Room::where('title', '動漫音樂廳')->exists()) {
            $room = Room::create([
                'type' => RoomType::Audio,
                'title' => '動漫音樂廳',
                'auto_play' => false,
                'auto_remove' => false,
                'note' => '備註文字欄...',
            ]);

            $room->playlistItems()->create([
                'type' => PlayerType::Audio,
                'title' => '色違いの翼',
                'url' => '/media/色違いの翼.mp3',
            ]);

            $room->playlistItems()->create([
                'type' => PlayerType::Audio,
                'title' => '色違いの翼',
                'url' => 'https://www.youtube.com/watch?v=Melo0YFiDSY',
            ]);
        }
    }
}
