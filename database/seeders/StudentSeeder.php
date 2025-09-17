<?php

namespace Database\Seeders;

use App\Models\Student;
use Illuminate\Database\Seeder;

class StudentSeeder extends Seeder
{
    /**
     * Run the database seeders.
     */
    public function run(): void
    {
        Student::factory()->count(25)->create();
    }
}
