<?php

namespace Database\Seeders;

use App\Models\ClassType;
use App\Models\Course;
use Illuminate\Database\Seeder;

class CourseSeeder extends Seeder
{
    /**
     * Run the database seeders.
     */
    public function run(): void
    {
        $groupType = ClassType::where('name', 'Group')->first();
        $oneOnOneType = ClassType::where('name', 'One on one')->first();

        Course::factory()->count(8)->create([
            'class_type_id' => $groupType->id,
        ]);

        Course::factory()->count(5)->create([
            'class_type_id' => $oneOnOneType->id,
        ]);
    }
}
