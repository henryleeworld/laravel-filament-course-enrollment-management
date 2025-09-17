<?php

namespace Database\Seeders;

use App\Models\ClassType;
use Illuminate\Database\Seeder;

class ClassTypeSeeder extends Seeder
{
    /**
     * Run the database seeders.
     */
    public function run(): void
    {
        ClassType::create(['name' => 'Group']);
        ClassType::create(['name' => 'One on one']);
    }
}
