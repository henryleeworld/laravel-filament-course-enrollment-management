<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\CourseClass;
use App\Models\Student;
use Illuminate\Database\Seeder;

class AttendanceSeeder extends Seeder
{
    /**
     * Run the database seeders.
     */
    public function run(): void
    {
        $courseClasses = CourseClass::all();
        $students = Student::all();

        Attendance::factory()->count(80)->create([
            'course_class_id' => $courseClasses->random()->id,
            'student_id' => $students->random()->id,
        ]);
    }
}
