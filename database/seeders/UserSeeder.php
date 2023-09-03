<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (! User::where('email', 'admin@example.com')->exists()) {
            /** @var \App\Models\User */
            $user = User::factory()->create([
                'name' => 'Admin',
                'email' => 'admin@example.com',
            ]);

            $user->assignRole('admin');
        }

        if (! User::where('email', 'soyo@example.com')->exists()) {
            User::factory()->create([
                'name' => 'Soyo',
                'email' => 'soyo@example.com',
            ]);
        }
    }
}
