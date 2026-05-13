<?php

namespace App\Console\Commands;

use App\Mail\StudyReminderMail;
use App\Models\CourseEnrollment;
use App\Models\LessonCompletion;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendStudyReminders extends Command
{
    protected $signature   = 'study:send-reminders {--day= : Override day of week (e.g. Monday)}';
    protected $description = 'Send weekly study reminder emails to enrolled employees who opted in';

    public function handle(): void
    {
        $day = $this->option('day') ?? now()->format('l'); // e.g. "Wednesday"

        $enrollments = CourseEnrollment::with(['user', 'course'])
            ->where('reminder_enabled', true)
            ->where('reminder_day', $day)
            ->where('status', 'active')
            ->get();

        if ($enrollments->isEmpty()) {
            $this->info("No reminders to send for {$day}.");
            return;
        }

        // Group by user so each person gets one email listing all their courses
        $byUser = $enrollments->groupBy('user_id');
        $sent = 0;

        foreach ($byUser as $userId => $userEnrollments) {
            $user = $userEnrollments->first()->user;
            if (!$user || !$user->email) continue;

            $courses = $userEnrollments->map(function ($enrollment) {
                $total     = $enrollment->course->lessons()->count();
                $completed = LessonCompletion::where('enrollment_id', $enrollment->id)->count();
                $progress  = $total > 0 ? (int) round(($completed / $total) * 100) : 0;

                return [
                    'title'    => $enrollment->course->title,
                    'category' => $enrollment->course->category,
                    'progress' => $progress,
                ];
            })->values()->toArray();

            $name = trim(($user->firstname ?? '') . ' ' . ($user->lastname ?? '')) ?: $user->name;

            Mail::to($user->email)->send(new StudyReminderMail($name, $courses));
            $sent++;
        }

        $this->info("Sent {$sent} study reminder email(s) for {$day}.");
    }
}
