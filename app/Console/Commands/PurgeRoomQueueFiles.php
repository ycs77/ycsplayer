<?php

namespace App\Console\Commands;

use App\Models\QueueRoomFile;
use Illuminate\Console\Command;

class PurgeRoomQueueFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'room:queue-file:purge';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Purge the expired queue files.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        QueueRoomFile::query()
            ->expired()
            ->delete();

        $this->components->info('Room queue files purge successfully.');
    }
}
