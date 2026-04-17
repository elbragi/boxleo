<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CourseEnrollment extends Model
{
    protected $fillable = [
        'course_id', 'user_id', 'enrolled_at', 'completed_at', 'status',
    ];

    protected $casts = [
        'enrolled_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function lessonCompletions(): HasMany
    {
        return $this->hasMany(LessonCompletion::class, 'enrollment_id');
    }

    public function certificate(): HasOne
    {
        return $this->hasOne(CourseCertificate::class, 'enrollment_id');
    }

    public function getProgressPercentageAttribute(): int
    {
        $totalLessons = $this->course->lessons()->count();
        if ($totalLessons === 0) return 0;
        $completed = $this->lessonCompletions()->count();
        return (int) round(($completed / $totalLessons) * 100);
    }
}
