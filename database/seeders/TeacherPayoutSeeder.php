<?php

namespace Database\Seeders;

use App\Models\TeacherPayout;
use App\Models\User;
use Illuminate\Database\Seeder;

class TeacherPayoutSeeder extends Seeder
{
    /**
     * Run the database seeders.
     */
    public function run(): void
    {
        $teachers = User::whereHas('role', function ($query) {
            $query->where('name', 'Teacher');
        })->get();

        if ($teachers->isEmpty()) {
            return;
        }
        $now = now();
        $months = [$now->format('Y') . '-01', $now->format('Y') . '-02', $now->format('Y') . '-03', $now->format('Y') . '-04', $now->format('Y') . '-05'];
        foreach ($teachers as $teacher) {
            foreach ($months as $month) {
                TeacherPayout::firstOrCreate(
                    [
                        'teacher_id' => $teacher->id,
                        'month' => $month,
                    ],
                    [
                        'total_pay' => fake()->randomNumber(5, true), // fake()->randomFloat(2, 50, 500)
                        'is_paid' => fake()->boolean(30),
                        'paid_at' => fake()->boolean(30) ? fake()->dateTimeThisYear() : null,
                    ]
                );
            }
        }
    }
}
