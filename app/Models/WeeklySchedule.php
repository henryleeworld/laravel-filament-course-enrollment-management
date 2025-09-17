<?php

namespace App\Models;

use App\Enums\DayOfWeek;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WeeklySchedule extends Model
{
    /** @use HasFactory<\Database\Factories\WeeklyScheduleFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'course_id',
        'day_of_week',
        'start_time',
        'end_time',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            //
        ];
    }

    /**
     * Get the course that owns the weekly schedule.
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function getDayNameAttribute(): string
    {
        $dayOfWeek = DayOfWeek::fromValue($this->day_of_week);

        return $dayOfWeek?->label() ?? 'Unknown';
    }

    /**
     * Get the course classes for the weekly schedule.
     */
    public function courseClasses(): HasMany
    {
        return $this->hasMany(CourseClass::class);
    }

    /**
     * Get the substitute teacher that owns the weekly schedule.
     */
    public function substituteTeacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'substitute_teacher_id');
    }

    /**
     * Get the teacher that owns the weekly schedule.
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }
}
