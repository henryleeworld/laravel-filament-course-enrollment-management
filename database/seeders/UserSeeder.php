<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeders.
     */
    public function run(): void
    {
        $ownerRole = Role::where('name', 'Owner')->first();
        $adminRole = Role::where('name', 'Admin')->first();
        $teacherRole = Role::where('name', 'Teacher')->first();

        User::factory()->create([
            'role_id' => $ownerRole->id,
            'name' => __('Business owner'),
            'email' => 'owner@admin.com',
        ]);

        User::factory()->count(2)->create([
            'role_id' => $adminRole->id,
        ]);

        User::factory()->count(8)->create([
            'role_id' => $teacherRole->id,
        ]);
    }
}
