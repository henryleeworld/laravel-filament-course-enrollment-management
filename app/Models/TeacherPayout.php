<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class TeacherPayout extends Model
{
    /** @use HasFactory<\Database\Factories\TeacherPayoutFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'teacher_id',
        'month',
        'total_pay',
        'is_paid',
        'paid_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'total_pay' => 'integer', // 'decimal:2'
            'is_paid' => 'boolean',
            'paid_at' => 'datetime',
        ];
    }

    /**
     * Get the class schedules for the teacher payout.
     */
    public function courseClasses(): HasMany
    {
        return $this->hasMany(CourseClass::class, 'teacher_id', 'teacher_id')
            ->whereBetween('scheduled_date', [
                Carbon::createFromFormat('Y-m', $this->month)->startOfMonth(),
                Carbon::createFromFormat('Y-m', $this->month)->endOfMonth(),
            ]);
    }

    /**
     * Get the teacher that owns the teacher payout.
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }
}
