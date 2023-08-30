<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class AppAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:admin {user} {--remove}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add or remove admin role for a user.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        /** @var \App\Models\User */
        $user = User::find($this->argument('user'));

        if ($this->option('remove')) {
            $user->removeRole('admin');

            $this->components->info("Removed admin role for user \"{$user->name}\" successfully.");
        } else {
            $user->assignRole('admin');

            $this->components->info("Added admin role for user \"{$user->name}\" successfully.");
        }
    }
}
