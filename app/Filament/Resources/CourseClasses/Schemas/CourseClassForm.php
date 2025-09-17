<?php

namespace App\Filament\Resources\CourseClasses\Schemas;

use App\Filament\Schemas\Components\TimeSelect;
use App\Models\Attendance;
use App\Models\CourseClass;
use App\Models\Student;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class CourseClassForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Schedule details'))
                    ->schema([
                        Select::make('course_id')
                            ->label(__('Course'))
                            ->relationship('course', 'name')
                            ->searchable()
                            ->required(),
                        Grid::make(2)
                            ->schema([
                                DatePicker::make('scheduled_date')
                                    ->label(__('Scheduled date'))
                                    ->native(false)
                                    ->required(),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TimeSelect::make('start_time')
                                    ->label(__('Start time'))
                                    ->required(),
                                TimeSelect::make('end_time')
                                    ->label(__('End time'))
                                    ->required(),
                            ]),
                        Grid::make(2)
                            ->schema([
                                Select::make('substitute_teacher_id')
                                    ->label(__('Substitute teacher'))
                                    ->relationship('substituteTeacher', 'name')
                                    ->searchable()
                                    ->nullable(),
                            ]),
                    ]),
                Section::make(__('Attendance'))
                    ->schema([
                        CheckboxList::make('attendance_student_ids')
                            ->label(__('Students'))
                            ->columns(2)
                            ->columnSpanFull()
                            ->options(function (Get $get): array {
                                $courseId = $get('course_id');
                                $scheduledDate = $get('scheduled_date');

                                if (! $courseId || ! $scheduledDate) {
                                    return [];
                                }

                                return Student::query()
                                    ->whereHas('courses', function ($query) use ($courseId, $scheduledDate) {
                                        $query->where('courses.id', $courseId)
                                            ->where('enrollments.start_date', '<=', $scheduledDate)
                                            ->where(function ($q) use ($scheduledDate) {
                                                $q->whereNull('enrollments.end_date')
                                                    ->orWhere('enrollments.end_date', '>=', $scheduledDate);
                                            });
                                    })
                                    ->orderBy('first_name')
                                    ->orderBy('last_name')
                                    ->get()
                                    ->pluck('name', 'id')
                                    ->toArray();
                            })
                            ->afterStateHydrated(function (Set $set, ?CourseClass $record): void {
                                if (! $record) {
                                    $set('attendance_student_ids', []);

                                    return;
                                }

                                $set('attendance_student_ids', $record->attendances()->pluck('student_id')->all());
                            })
                            ->dehydrated(false)
                            ->afterStateUpdated(function (Set $set, $state, ?CourseClass $record): void {
                                if (! $record) {
                                    return;
                                }

                                static::syncAttendanceRecords($record, $state ?? []);

                                static::recalculatePayTotals($record);
                            }),
                    ])
                    ->hidden(fn (?CourseClass $record) => $record === null),
            ]);
    }

    protected static function syncAttendanceRecords(CourseClass $courseClass, array $studentIds): void
    {
        $currentAttendance = $courseClass->attendances()->pluck('student_id')->toArray();

        $studentsToAdd = array_diff($studentIds, $currentAttendance);

        $studentsToRemove = array_diff($currentAttendance, $studentIds);

        foreach ($studentsToAdd as $studentId) {
            Attendance::create([
                'course_class_id' => $courseClass->id,
                'student_id' => $studentId,
            ]);
        }

        if (! empty($studentsToRemove)) {
            $courseClass->attendances()
                ->whereIn('student_id', $studentsToRemove)
                ->delete();
        }
    }

    protected static function recalculatePayTotals(CourseClass $courseClass): void
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
}
