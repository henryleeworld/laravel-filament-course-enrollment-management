<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    /** @use HasFactory<\Database\Factories\CourseFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'class_type_id',
        'teacher_id',
        'name',
        'description',
        'price_per_student',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price_per_student' => 'decimal:2',
        ];
    }

    /**
     * Get the class type that owns the course.
     */
    public function classType(): BelongsTo
    {
        return $this->belongsTo(ClassType::class);
    }

    /**
     * Get the course classes for the course.
     */
    public function courseClasses(): HasMany
    {
        return $this->hasMany(CourseClass::class);
    }

    /**
     * Get the enrollments for the course.
     */
    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    /**
     * The students that belong to the course.
     */
    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'enrollments')
            ->withPivot('start_date', 'end_date')
            ->withTimestamps();
    }

    /**
     * Get the teacher that owns the course.
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    /**
     * Get the weekly schedules for the course.
     */
    public function weeklySchedules(): HasMany
    {
        return $this->hasMany(WeeklySchedule::class);
    }
}
