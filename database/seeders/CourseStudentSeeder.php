<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Student;
use Illuminate\Database\Seeder;

class CourseStudentSeeder extends Seeder
{
    /**
     * Run the database seeders.
     */
    public function run(): void
    {
        $students = Student::all();
        $courses = Course::all();
        $now = now();
        foreach ($courses as $course) {
            $numberOfStudents = fake()->numberBetween(3, 8);
            $enrolledStudents = $students->random($numberOfStudents);

            foreach ($enrolledStudents as $student) {
                if (!$course->students()->where('student_id', $student->id)->exists()) {
                    $course->students()->attach($student->id, [
                        'start_date' => $now->format('Y') . '-09-01',
                        'end_date' => $now->format('Y') . '-12-20',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }
        }

        $additionalEnrollments = fake()->numberBetween(10, 20);
        for ($i = 0; $i < $additionalEnrollments; $i++) {
            $student = $students->random();
            $course = $courses->random();

            if (!$course->students()->where('student_id', $student->id)->exists()) {
                $course->students()->attach($student->id, [
                    'start_date' => fake()->dateTimeBetween($now->format('Y') . '-09-01', $now->format('Y') . '-09-15')->format('Y-m-d'),
                    'end_date' => fake()->dateTimeBetween($now->format('Y') . '-11-01', $now->format('Y') . '-12-20')->format('Y-m-d'),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }
}
