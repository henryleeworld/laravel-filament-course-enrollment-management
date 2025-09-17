<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CourseClass extends Model
{
    /** @use HasFactory<\Database\Factories\CourseClassFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'course_id',
        'weekly_schedule_id',
        'scheduled_date',
        'start_time',
        'end_time',
        'teacher_id',
        'substitute_teacher_id',
        'student_count',
        'teacher_base_pay',
        'teacher_bonus_pay',
        'teacher_total_pay',
        'substitute_base_pay',
        'substitute_bonus_pay',
        'substitute_total_pay',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'scheduled_date' => 'date',
            'teacher_base_pay' => 'integer',
            'teacher_bonus_pay' => 'integer',
            'teacher_total_pay' => 'integer',
            'substitute_base_pay' => 'integer',
            'substitute_bonus_pay' => 'integer',
            'substitute_total_pay' => 'integer',
            /*
            'teacher_base_pay' => 'decimal:2',
            'teacher_bonus_pay' => 'decimal:2',
            'teacher_total_pay' => 'decimal:2',
            'substitute_base_pay' => 'decimal:2',
            'substitute_bonus_pay' => 'decimal:2',
            'substitute_total_pay' => 'decimal:2',
            */
        ];
    }

    /**
     * Get the attendances for the course class.
     */
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * Get the course that owns the course class.
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Get the substitute teacher that owns the course class.
     */
    public function substituteTeacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'substitute_teacher_id');
    }

    /**
     * Get the teacher that owns the course class.
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    /**
     * Get the weekly schedule that owns the course class.
     */
    public function weeklySchedule(): BelongsTo
    {
        return $this->belongsTo(WeeklySchedule::class);
    }
}
