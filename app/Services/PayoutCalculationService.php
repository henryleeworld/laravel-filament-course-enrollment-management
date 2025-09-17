<?php

namespace App\Services;

use App\Models\CourseClass;
use App\Models\TeacherPayout;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PayoutCalculationService
{
    public function calculatePayoutsForMonth(string $month): Collection
    {
        $monthStart = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $monthEnd = Carbon::createFromFormat('Y-m', $month)->endOfMonth();

        $courseClasses = CourseClass::whereBetween('scheduled_date', [$monthStart, $monthEnd])
            ->with(['teacher', 'substituteTeacher', 'attendances', 'course'])
            ->get();

        foreach ($courseClasses as $courseClass) {
            $this->updateCourseClassPayoutCalculations($courseClass);
        }

        return $this->generateTeacherPayoutSummary($month);
    }

    protected function updateCourseClassPayoutCalculations(CourseClass $courseClass): void
    {
        $attendanceCount = $courseClass->attendances()->count();
        $basePay = config('teacher_pay.base_pay', 50.00);
        $bonusPerStudent = config('teacher_pay.bonus_per_student', 2.50);

        $bonusPay = $attendanceCount * $bonusPerStudent;
        $totalPay = $basePay + $bonusPay;

        $updateData = [
            'student_count' => $attendanceCount,
            'teacher_base_pay' => $basePay,
            'teacher_bonus_pay' => $bonusPay,
            'teacher_total_pay' => $totalPay,
        ];

        if ($courseClass->substitute_teacher_id) {
            $updateData['substitute_base_pay'] = $basePay;
            $updateData['substitute_bonus_pay'] = $bonusPay;
            $updateData['substitute_total_pay'] = $totalPay;
        }

        $courseClass->update($updateData);
    }

    protected function generateTeacherPayoutSummary(string $month): Collection
    {
        $monthStart = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $monthEnd = Carbon::createFromFormat('Y-m', $month)->endOfMonth();

        $teacherCourseClasses = CourseClass::whereBetween('scheduled_date', [$monthStart, $monthEnd])
            ->with(['teacher', 'substituteTeacher', 'course'])
            ->get();

        $teacherPayouts = collect();

        $teacherTotals = $teacherCourseClasses->groupBy('teacher_id');
        foreach ($teacherTotals as $teacherId => $courseClasses) {
            $teacher = $courseClasses->first()->teacher;
            $totalPay = $courseClasses->sum('teacher_total_pay');

            $teacherPayouts->push([
                'teacher_id' => $teacherId,
                'teacher_name' => $teacher->name,
                'month' => $month,
                'total_pay' => $totalPay,
                'class_count' => $courseClasses->count(),
                'total_students' => $courseClasses->sum('student_count'),
            ]);
        }

        $substituteTotals = $teacherCourseClasses->where('substitute_teacher_id', '!=', null)
            ->groupBy('substitute_teacher_id');

        foreach ($substituteTotals as $teacherId => $courseClasses) {
            $teacher = $courseClasses->first()->substituteTeacher;
            $totalPay = $courseClasses->sum('substitute_total_pay');

            $existingIndex = $teacherPayouts->search(function ($item) use ($teacherId) {
                return $item['teacher_id'] == $teacherId;
            });

            if ($existingIndex !== false) {
                $existing = $teacherPayouts[$existingIndex];
                $existing['total_pay'] += $totalPay;
                $existing['substitute_class_count'] = $courseClasses->count();
                $existing['substitute_students'] = $courseClasses->sum('student_count');
                $teacherPayouts[$existingIndex] = $existing;
            } else {
                $teacherPayouts->push([
                    'teacher_id' => $teacherId,
                    'teacher_name' => $teacher->name,
                    'month' => $month,
                    'total_pay' => $totalPay,
                    'class_count' => 0,
                    'total_students' => 0,
                    'substitute_class_count' => $courseClasses->count(),
                    'substitute_students' => $courseClasses->sum('student_count'),
                ]);
            }
        }

        return $teacherPayouts;
    }

    public function generatePayoutsForMonth(string $month): array
    {
        $calculatedPayouts = $this->calculatePayoutsForMonth($month);

        $results = [
            'generated' => 0,
            'updated' => 0,
            'skipped' => 0,
            'errors' => [],
        ];

        DB::transaction(function () use ($calculatedPayouts, $month, &$results) {
            foreach ($calculatedPayouts as $payoutData) {
                try {
                    $existingPayout = TeacherPayout::where([
                        'teacher_id' => $payoutData['teacher_id'],
                        'month' => $month,
                    ])->first();

                    if ($existingPayout) {
                        if (! $existingPayout->is_paid) {
                            $existingPayout->update([
                                'total_pay' => $payoutData['total_pay'],
                            ]);
                            $results['updated']++;
                        } else {
                            $results['skipped']++;
                        }
                    } else {
                        TeacherPayout::create([
                            'teacher_id' => $payoutData['teacher_id'],
                            'month' => $month,
                            'total_pay' => $payoutData['total_pay'],
                        ]);
                        $results['generated']++;
                    }
                } catch (\Exception $e) {
                    $results['errors'][] = "Error processing payout for teacher {$payoutData['teacher_name']}: ".$e->getMessage();
                }
            }
        });

        return $results;
    }

    public function getMonthlyPayoutSummary(string $month): array
    {
        $monthStart = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $monthEnd = Carbon::createFromFormat('Y-m', $month)->endOfMonth();

        $existingPayouts = TeacherPayout::where('month', $month)->get();
        $calculatedPayouts = $this->calculatePayoutsForMonth($month);

        return [
            'month' => $month,
            'month_formatted' => $monthStart->format('F Y'),
            'existing_payouts_count' => $existingPayouts->count(),
            'existing_total_amount' => $existingPayouts->sum('total_pay'),
            'calculated_payouts_count' => $calculatedPayouts->count(),
            'calculated_total_amount' => $calculatedPayouts->sum('total_pay'),
            'unique_teachers_count' => $calculatedPayouts->pluck('teacher_id')->unique()->count(),
        ];
    }
}