<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DefaultRoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $createRoomPermission = Permission::create(['name' => 'create-room']);

        $adminRole = Role::create(['name' => 'admin']);
        $adminRole->givePermissionTo($createRoomPermission);
    }
}
