<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\CourseClass;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class CourseClassSeeder extends Seeder
{
    /**
     * Run the database seeders.
     */
    public function run(): void
    {
        $courses = Course::all();
        $teachers = User::whereHas('role', function ($query) {
            $query->where('name', 'Teacher');
        })->get();

        $workdays = [];
        $now = now();
        for ($day = 1; $day <= 30; $day++) {
            $date = Carbon::create($now->format('Y'), 9, $day);
            if ($date->isWeekday()) {
                $workdays[] = $date->format('Y-m-d');
            }
        }

        $timeSlots = [
            ['start' => '09:00:00', 'end' => '10:30:00'],
            ['start' => '11:00:00', 'end' => '12:30:00'],
            ['start' => '13:00:00', 'end' => '14:30:00'],
            ['start' => '15:00:00', 'end' => '16:30:00'],
            ['start' => '17:00:00', 'end' => '18:30:00'],
        ];

        foreach ($courses as $course) {
            $meetingsPerWeek = fake()->numberBetween(2, 3);
            $weekCount = 4;

            for ($week = 0; $week < $weekCount; $week++) {
                for ($meeting = 0; $meeting < $meetingsPerWeek; $meeting++) {
                    $dayOffset = $meeting * fake()->numberBetween(1, 2);
                    $weekStart = $week * 7;
                    $dayIndex = $weekStart + $dayOffset;

                    if (isset($workdays[$dayIndex])) {
                        $timeSlot = fake()->randomElement($timeSlots);

                        CourseClass::create([
                            'course_id' => $course->id,
                            'scheduled_date' => $workdays[$dayIndex],
                            'start_time' => $timeSlot['start'],
                            'end_time' => $timeSlot['end'],
                            'teacher_id' => $teachers->random()->id,
                            'substitute_teacher_id' => fake()->boolean(15) ? $teachers->random()->id : null,
                        ]);
                    }
                }
            }
        }
    }
}
