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

        /** @var \App\Models\Room */
        $videoRoom = Room::firstOrCreate([
            'title' => '動漫觀影室',
        ], [
            'type' => RoomType::Video,
            'auto_play' => false,
            'auto_remove' => false,
            'note' => '備註文字欄...',
        ]);

        if (! $videoRoom->playlist_items()
            ->where('type', PlayerType::Video)
            ->exists()
        ) {
            $videoRoom->playlist_items()->create([
                'type' => PlayerType::Video,
                'title' => '色違いの翼',
                'url' => '/media/色違いの翼.mp4',
                'thumbnail' => '/media/色違いの翼.jpg',
            ]);
        }

        if (! $videoRoom->playlist_items()
            ->where('type', PlayerType::YouTube)
            ->where('title', '春日影')
            ->exists()
        ) {
            $videoRoom->playlist_items()->create([
                'type' => PlayerType::YouTube,
                'title' => '春日影',
                'url' => 'https://www.youtube.com/watch?v=W8DCWI_Gc9c',
            ]);
        }

        if (! $videoRoom->playlist_items()
            ->where('type', PlayerType::YouTube)
            ->where('title', '詩超絆')
            ->exists()
        ) {
            $videoRoom->playlist_items()->create([
                'type' => PlayerType::YouTube,
                'title' => '詩超絆',
                'url' => 'https://www.youtube.com/watch?v=wWf9k4e2xEo',
            ]);
        }

        /** @var \App\Models\Room */
        $audioRoom = Room::firstOrCreate([
            'title' => '動漫音樂廳',
        ], [
            'type' => RoomType::Audio,
            'auto_play' => false,
            'auto_remove' => false,
            'note' => '備註文字欄...',
        ]);

        if (! $audioRoom->playlist_items()
            ->where('type', PlayerType::Audio)
            ->exists()
        ) {
            $audioRoom->playlist_items()->create([
                'type' => PlayerType::Audio,
                'title' => '色違いの翼',
                'url' => '/media/色違いの翼.mp3',
            ]);
        }

        if (! $audioRoom->playlist_items()
            ->where('type', PlayerType::YouTube)
            ->where('title', '色違いの翼')
            ->exists()
        ) {
            $audioRoom->playlist_items()->create([
                'type' => PlayerType::YouTube,
                'title' => '色違いの翼',
                'url' => 'https://www.youtube.com/watch?v=Melo0YFiDSY',
            ]);
        }

        if (! $audioRoom->playlist_items()
            ->where('type', PlayerType::YouTube)
            ->where('title', '詩超絆')
            ->exists()
        ) {
            $audioRoom->playlist_items()->create([
                'type' => PlayerType::YouTube,
                'title' => '詩超絆',
                'url' => 'https://www.youtube.com/watch?v=wWf9k4e2xEo',
            ]);
        }
    }
}
