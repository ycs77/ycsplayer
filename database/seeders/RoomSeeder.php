<?php

namespace Database\Seeders;

use App\Enums\PlayerType;
use App\Enums\RoomType;
use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Seeder;

class RoomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        /** @var \App\Models\User */
        $user = User::where('email', 'admin@example.com')->first();

        /** @var \App\Models\User */
        $soyo = User::where('email', 'soyo@example.com')->first();

        /** @var \App\Models\Room */
        $videoRoom = Room::firstOrCreate([
            'name' => '動漫觀影室',
        ], [
            'type' => RoomType::Video,
            'auto_play' => false,
            'auto_remove' => true,
            'note' => '記事本文字欄...',
        ]);
        if ($videoRoom->doesntMember($user)) {
            $videoRoom->join($user, 'admin');
        }
        if ($videoRoom->doesntMember($soyo)) {
            $videoRoom->join($soyo);
        }

        if (! $videoRoom->playlistItems()
            ->where('type', PlayerType::YouTube)
            ->where('title', '春日影')
            ->exists()
        ) {
            $videoRoom->playlistItems()->create([
                'type' => PlayerType::YouTube,
                'title' => '春日影',
                'url' => 'https://www.youtube.com/watch?v=W8DCWI_Gc9c',
                'thumbnail' => 'https://img.youtube.com/vi/W8DCWI_Gc9c/default.jpg',
            ]);
        }

        if (! $videoRoom->playlistItems()
            ->where('type', PlayerType::YouTube)
            ->where('title', 'メリーゴーランド')
            ->exists()
        ) {
            $videoRoom->playlistItems()->create([
                'type' => PlayerType::YouTube,
                'title' => 'メリーゴーランド',
                'url' => 'https://www.youtube.com/watch?v=eWeSqrRk-gs',
                'thumbnail' => 'https://img.youtube.com/vi/eWeSqrRk-gs/default.jpg',
            ]);
        }

        if (! $videoRoom->playlistItems()
            ->where('type', PlayerType::YouTube)
            ->where('title', '星座になれたら')
            ->exists()
        ) {
            $videoRoom->playlistItems()->create([
                'type' => PlayerType::YouTube,
                'title' => '星座になれたら',
                'url' => 'https://www.youtube.com/watch?v=fJh5UeiULZs',
                'thumbnail' => 'https://img.youtube.com/vi/fJh5UeiULZs/default.jpg',
            ]);
        }

        if (! $videoRoom->playlistItems()
            ->where('type', PlayerType::YouTube)
            ->where('title', 'spiral')
            ->exists()
        ) {
            $videoRoom->playlistItems()->create([
                'type' => PlayerType::YouTube,
                'title' => 'spiral',
                'url' => 'https://www.youtube.com/watch?v=fE9trKOuT3Q',
                'thumbnail' => 'https://img.youtube.com/vi/fE9trKOuT3Q/default.jpg',
            ]);
        }

        /** @var \App\Models\Room */
        $audioRoom = Room::firstOrCreate([
            'name' => '動漫音樂廳',
        ], [
            'type' => RoomType::Audio,
            'auto_play' => true,
            'auto_remove' => false,
            'note' => '記事本文字欄...',
        ]);
        if ($audioRoom->doesntMember($user)) {
            $audioRoom->join($user, 'admin');
        }

        if (! $audioRoom->playlistItems()
            ->where('type', PlayerType::YouTube)
            ->where('title', '色違いの翼')
            ->exists()
        ) {
            $audioRoom->playlistItems()->create([
                'type' => PlayerType::YouTube,
                'title' => '色違いの翼',
                'url' => 'https://www.youtube.com/watch?v=Melo0YFiDSY',
                'thumbnail' => 'https://img.youtube.com/vi/Melo0YFiDSY/default.jpg',
            ]);
        }

        if (! $audioRoom->playlistItems()
            ->where('type', PlayerType::YouTube)
            ->where('title', 'メリーゴーランド')
            ->exists()
        ) {
            $audioRoom->playlistItems()->create([
                'type' => PlayerType::YouTube,
                'title' => 'メリーゴーランド',
                'url' => 'https://www.youtube.com/watch?v=P9cuGQNDf-A',
                'thumbnail' => 'https://img.youtube.com/vi/P9cuGQNDf-A/default.jpg',
            ]);
        }

        if (! $audioRoom->playlistItems()
            ->where('type', PlayerType::YouTube)
            ->where('title', '詩超絆')
            ->exists()
        ) {
            $audioRoom->playlistItems()->create([
                'type' => PlayerType::YouTube,
                'title' => '詩超絆',
                'url' => 'https://www.youtube.com/watch?v=wWf9k4e2xEo',
                'thumbnail' => 'https://img.youtube.com/vi/wWf9k4e2xEo/default.jpg',
            ]);
        }
    }
}
