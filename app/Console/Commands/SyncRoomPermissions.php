<?php

namespace App\Console\Commands;

use App\Models\Room;
use Illuminate\Console\Command;

class SyncRoomPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'room:sync-permissions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync rooms permissions and roles.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Room::all(['id'])
            ->each(fn (Room $room) => $room->syncRoomPermissions());

        $this->components->info('Rooms permissions sync successfully.');
    }
}
