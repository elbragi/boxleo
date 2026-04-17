<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\CourseLesson;
use App\Models\LessonCompletion;
use App\Models\CourseCertificate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class StaffDevelopmentApiController extends Controller
{
    // ─── Courses ─────────────────────────────────────────────────────────────

    public function courses(Request $request)
    {
        $userId = Auth::id();
        $query = Course::withCount(['lessons', 'enrollments'])
            ->where('is_active', true);

        if ($request->search) {
            $query->where('title', 'like', "%{$request->search}%");
        }
        if ($request->category && $request->category !== 'All') {
            $query->where('category', $request->category);
        }
        if ($request->level && $request->level !== 'All') {
            $query->where('level', $request->level);
        }

        $courses = $query->latest()->get()->map(function ($course) use ($userId) {
            $enrollment = CourseEnrollment::where('course_id', $course->id)
                ->where('user_id', $userId)->first();

            $progress = 0;
            if ($enrollment) {
                $totalLessons = $course->lessons_count;
                if ($totalLessons > 0) {
                    $completedLessons = LessonCompletion::where('enrollment_id', $enrollment->id)->count();
                    $progress = (int) round(($completedLessons / $totalLessons) * 100);
                }
            }

            return [
                'id' => $course->id,
                'title' => $course->title,
                'description' => $course->description,
                'category' => $course->category,
                'level' => $course->level,
                'thumbnail' => $course->thumbnail,
                'provider' => $course->provider,
                'duration_hours' => $course->duration_hours,
                'is_featured' => $course->is_featured,
                'lessons_count' => $course->lessons_count,
                'enrollments_count' => $course->enrollments_count,
                'enrolled' => $enrollment !== null,
                'enrollment_id' => $enrollment?->id,
                'enrollment_status' => $enrollment?->status,
                'progress' => $progress,
                'completed' => $enrollment?->status === 'completed',
                'created_at' => $course->created_at,
            ];
        });

        return response()->json($courses);
    }

    public function courseDetail($id)
    {
        $userId = Auth::id();
        $course = Course::withCount(['lessons', 'enrollments'])->findOrFail($id);

        $enrollment = CourseEnrollment::where('course_id', $id)
            ->where('user_id', $userId)->first();

        $completedLessonIds = [];
        $progress = 0;

        if ($enrollment) {
            $completedLessonIds = LessonCompletion::where('enrollment_id', $enrollment->id)
                ->pluck('lesson_id')->toArray();
            $totalLessons = $course->lessons_count;
            if ($totalLessons > 0) {
                $progress = (int) round((count($completedLessonIds) / $totalLessons) * 100);
            }
        }

        $lessons = $course->lessons->map(function ($lesson) use ($completedLessonIds) {
            return [
                'id' => $lesson->id,
                'title' => $lesson->title,
                'description' => $lesson->description,
                'content' => $lesson->content,
                'video_url' => $lesson->video_url,
                'duration_minutes' => $lesson->duration_minutes,
                'order' => $lesson->order,
                'is_free_preview' => $lesson->is_free_preview,
                'completed' => in_array($lesson->id, $completedLessonIds),
            ];
        });

        $certificate = null;
        if ($enrollment) {
            $cert = CourseCertificate::where('enrollment_id', $enrollment->id)->first();
            if ($cert) {
                $certificate = [
                    'id' => $cert->id,
                    'file_name' => $cert->file_name,
                    'issuer' => $cert->issuer,
                    'issue_date' => $cert->issue_date?->format('Y-m-d'),
                    'expiry_date' => $cert->expiry_date?->format('Y-m-d'),
                    'is_public' => $cert->is_public,
                    'url' => Storage::url($cert->file_path),
                ];
            }
        }

        return response()->json([
            'id' => $course->id,
            'title' => $course->title,
            'description' => $course->description,
            'category' => $course->category,
            'level' => $course->level,
            'thumbnail' => $course->thumbnail,
            'provider' => $course->provider,
            'duration_hours' => $course->duration_hours,
            'is_featured' => $course->is_featured,
            'lessons_count' => $course->lessons_count,
            'enrollments_count' => $course->enrollments_count,
            'lessons' => $lessons,
            'enrolled' => $enrollment !== null,
            'enrollment_id' => $enrollment?->id,
            'enrollment_status' => $enrollment?->status,
            'progress' => $progress,
            'completed_lesson_ids' => $completedLessonIds,
            'certificate' => $certificate,
        ]);
    }

    public function storeCourse(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|string',
            'level' => 'required|in:Beginner,Intermediate,Advanced',
            'provider' => 'nullable|string|max:255',
            'duration_hours' => 'nullable|integer|min:0',
        ]);

        $course = Course::create([
            'title' => $request->title,
            'description' => $request->description,
            'category' => $request->category,
            'level' => $request->level,
            'provider' => $request->provider,
            'duration_hours' => $request->duration_hours ?? 0,
            'is_active' => $request->boolean('is_active', true),
            'is_featured' => $request->boolean('is_featured', false),
            'created_by' => Auth::id(),
        ]);

        return response()->json(['message' => 'Course created successfully', 'course' => $course], 201);
    }

    public function updateCourse(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string',
            'level' => 'required|in:Beginner,Intermediate,Advanced',
        ]);

        $course = Course::findOrFail($id);
        $course->update($request->only([
            'title', 'description', 'category', 'level',
            'provider', 'duration_hours', 'is_active', 'is_featured',
        ]));

        return response()->json(['message' => 'Course updated successfully', 'course' => $course]);
    }

    public function deleteCourse($id)
    {
        $course = Course::findOrFail($id);
        $course->delete();
        return response()->json(['message' => 'Course deleted successfully']);
    }

    // ─── Lessons ─────────────────────────────────────────────────────────────

    public function storeLessons(Request $request, $courseId)
    {
        $request->validate([
            'lessons' => 'required|array|min:1',
            'lessons.*.title' => 'required|string|max:255',
            'lessons.*.description' => 'nullable|string',
            'lessons.*.content' => 'nullable|string',
            'lessons.*.video_url' => 'nullable|url',
            'lessons.*.duration_minutes' => 'nullable|integer|min:0',
            'lessons.*.order' => 'nullable|integer|min:0',
        ]);

        $course = Course::findOrFail($courseId);
        $course->lessons()->delete();

        foreach ($request->lessons as $index => $lessonData) {
            $course->lessons()->create([
                'title' => $lessonData['title'],
                'description' => $lessonData['description'] ?? null,
                'content' => $lessonData['content'] ?? null,
                'video_url' => $lessonData['video_url'] ?? null,
                'duration_minutes' => $lessonData['duration_minutes'] ?? 0,
                'order' => $lessonData['order'] ?? $index + 1,
                'is_free_preview' => $lessonData['is_free_preview'] ?? false,
            ]);
        }

        return response()->json(['message' => 'Lessons saved successfully']);
    }

    // ─── Enrollment ──────────────────────────────────────────────────────────

    public function enroll($courseId)
    {
        $userId = Auth::id();
        $course = Course::findOrFail($courseId);

        $existing = CourseEnrollment::where('course_id', $courseId)
            ->where('user_id', $userId)->first();

        if ($existing) {
            return response()->json(['message' => 'Already enrolled', 'enrollment' => $existing]);
        }

        $enrollment = CourseEnrollment::create([
            'course_id' => $courseId,
            'user_id' => $userId,
            'enrolled_at' => now(),
            'status' => 'active',
        ]);

        return response()->json(['message' => 'Enrolled successfully', 'enrollment' => $enrollment], 201);
    }

    public function myCourses()
    {
        $userId = Auth::id();

        $enrollments = CourseEnrollment::with(['course' => function ($q) {
            $q->withCount('lessons');
        }])->where('user_id', $userId)->latest()->get();

        $data = $enrollments->map(function ($enrollment) use ($userId) {
            $completedCount = LessonCompletion::where('enrollment_id', $enrollment->id)->count();
            $totalLessons = $enrollment->course->lessons_count ?? 0;
            $progress = $totalLessons > 0 ? (int) round(($completedCount / $totalLessons) * 100) : 0;

            $certificate = CourseCertificate::where('enrollment_id', $enrollment->id)->first();

            return [
                'enrollment_id' => $enrollment->id,
                'status' => $enrollment->status,
                'enrolled_at' => $enrollment->enrolled_at?->format('Y-m-d'),
                'completed_at' => $enrollment->completed_at?->format('Y-m-d'),
                'progress' => $progress,
                'completed_lessons' => $completedCount,
                'total_lessons' => $totalLessons,
                'has_certificate' => $certificate !== null,
                'course' => [
                    'id' => $enrollment->course->id,
                    'title' => $enrollment->course->title,
                    'description' => $enrollment->course->description,
                    'category' => $enrollment->course->category,
                    'level' => $enrollment->course->level,
                    'thumbnail' => $enrollment->course->thumbnail,
                    'duration_hours' => $enrollment->course->duration_hours,
                ],
            ];
        });

        return response()->json($data);
    }

    // ─── Lesson Completion ───────────────────────────────────────────────────

    public function completeLesson(Request $request, $lessonId)
    {
        $userId = Auth::id();
        $lesson = CourseLesson::findOrFail($lessonId);

        $enrollment = CourseEnrollment::where('course_id', $lesson->course_id)
            ->where('user_id', $userId)->firstOrFail();

        $completion = LessonCompletion::firstOrCreate([
            'enrollment_id' => $enrollment->id,
            'lesson_id' => $lessonId,
            'user_id' => $userId,
        ], ['completed_at' => now()]);

        // Check if all lessons completed → mark course as completed
        $totalLessons = $lesson->course->lessons()->count();
        $completedLessons = LessonCompletion::where('enrollment_id', $enrollment->id)->count();

        if ($totalLessons > 0 && $completedLessons >= $totalLessons) {
            $enrollment->update(['status' => 'completed', 'completed_at' => now()]);
        }

        $progress = $totalLessons > 0 ? (int) round(($completedLessons / $totalLessons) * 100) : 0;

        return response()->json([
            'message' => 'Lesson marked as complete',
            'progress' => $progress,
            'course_completed' => $completedLessons >= $totalLessons,
        ]);
    }

    public function uncompleteLesson($lessonId)
    {
        $userId = Auth::id();
        $lesson = CourseLesson::findOrFail($lessonId);

        $enrollment = CourseEnrollment::where('course_id', $lesson->course_id)
            ->where('user_id', $userId)->firstOrFail();

        LessonCompletion::where('enrollment_id', $enrollment->id)
            ->where('lesson_id', $lessonId)
            ->delete();

        if ($enrollment->status === 'completed') {
            $enrollment->update(['status' => 'active', 'completed_at' => null]);
        }

        $totalLessons = $lesson->course->lessons()->count();
        $completedLessons = LessonCompletion::where('enrollment_id', $enrollment->id)->count();
        $progress = $totalLessons > 0 ? (int) round(($completedLessons / $totalLessons) * 100) : 0;

        return response()->json(['message' => 'Lesson unmarked', 'progress' => $progress]);
    }

    // ─── Certificates ────────────────────────────────────────────────────────

    public function uploadCertificate(Request $request, $enrollmentId)
    {
        $request->validate([
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'issuer' => 'nullable|string|max:255',
            'issue_date' => 'nullable|date',
            'expiry_date' => 'nullable|date|after_or_equal:issue_date',
            'is_public' => 'nullable|boolean',
        ]);

        $enrollment = CourseEnrollment::where('id', $enrollmentId)
            ->where('user_id', Auth::id())->firstOrFail();

        // Delete old certificate if exists
        $existing = CourseCertificate::where('enrollment_id', $enrollmentId)->first();
        if ($existing) {
            Storage::delete($existing->file_path);
            $existing->delete();
        }

        $file = $request->file('file');
        $path = $file->store("certificates/{$enrollment->user_id}", 'public');

        $cert = CourseCertificate::create([
            'enrollment_id' => $enrollmentId,
            'user_id' => Auth::id(),
            'course_id' => $enrollment->course_id,
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'issuer' => $request->issuer,
            'issue_date' => $request->issue_date,
            'expiry_date' => $request->expiry_date,
            'is_public' => $request->boolean('is_public', true),
        ]);

        // Mark enrollment as completed when certificate uploaded
        $enrollment->update(['status' => 'completed', 'completed_at' => now()]);

        return response()->json([
            'message' => 'Certificate uploaded successfully',
            'certificate' => [
                'id' => $cert->id,
                'file_name' => $cert->file_name,
                'issuer' => $cert->issuer,
                'issue_date' => $cert->issue_date?->format('Y-m-d'),
                'expiry_date' => $cert->expiry_date?->format('Y-m-d'),
                'is_public' => $cert->is_public,
                'url' => Storage::url($cert->file_path),
            ],
        ], 201);
    }

    public function myCertificates()
    {
        $userId = Auth::id();
        $certificates = CourseCertificate::with('course')
            ->where('user_id', $userId)->latest()->get();

        $data = $certificates->map(function ($cert) {
            return [
                'id' => $cert->id,
                'file_name' => $cert->file_name,
                'issuer' => $cert->issuer,
                'issue_date' => $cert->issue_date?->format('Y-m-d'),
                'expiry_date' => $cert->expiry_date?->format('Y-m-d'),
                'is_public' => $cert->is_public,
                'url' => Storage::url($cert->file_path),
                'course' => [
                    'id' => $cert->course->id,
                    'title' => $cert->course->title,
                    'category' => $cert->course->category,
                ],
            ];
        });

        return response()->json($data);
    }

    public function deleteCertificate($id)
    {
        $cert = CourseCertificate::where('id', $id)
            ->where('user_id', Auth::id())->firstOrFail();

        Storage::delete($cert->file_path);
        $cert->delete();

        return response()->json(['message' => 'Certificate deleted']);
    }

    // ─── Stats ───────────────────────────────────────────────────────────────

    public function stats()
    {
        $userId = Auth::id();
        $isAdmin = Auth::user()->hasRole('admin');

        $myEnrollments = CourseEnrollment::where('user_id', $userId)->get();
        $myCompleted = $myEnrollments->where('status', 'completed')->count();
        $myInProgress = $myEnrollments->where('status', 'active')->count();
        $myCertificates = CourseCertificate::where('user_id', $userId)->count();

        $result = [
            'my_enrollments' => $myEnrollments->count(),
            'my_completed' => $myCompleted,
            'my_in_progress' => $myInProgress,
            'my_certificates' => $myCertificates,
        ];

        if ($isAdmin) {
            $result['total_courses'] = Course::where('is_active', true)->count();
            $result['total_enrollments'] = CourseEnrollment::count();
            $result['total_certificates'] = CourseCertificate::count();
            $result['completion_rate'] = CourseEnrollment::count() > 0
                ? round((CourseEnrollment::where('status', 'completed')->count() / CourseEnrollment::count()) * 100)
                : 0;
        }

        return response()->json($result);
    }

    // ─── Team Certificates (visible to HR/Admin) ─────────────────────────────

    public function teamCertificates()
    {
        $certificates = CourseCertificate::with(['course', 'user'])
            ->where('is_public', true)
            ->latest()->get();

        return response()->json($certificates->map(function ($cert) {
            return [
                'id' => $cert->id,
                'file_name' => $cert->file_name,
                'issuer' => $cert->issuer,
                'issue_date' => $cert->issue_date?->format('Y-m-d'),
                'url' => Storage::url($cert->file_path),
                'course' => ['id' => $cert->course->id, 'title' => $cert->course->title, 'category' => $cert->course->category],
                'user' => ['id' => $cert->user->id, 'name' => $cert->user->name, 'avatar' => $cert->user->avatar ?? null],
            ];
        }));
    }

    // ─── Admin: all enrollments ───────────────────────────────────────────────

    public function allEnrollments()
    {
        $enrollments = CourseEnrollment::with(['user', 'course'])->latest()->get();
        return response()->json($enrollments->map(function ($e) {
            $completed = LessonCompletion::where('enrollment_id', $e->id)->count();
            $total = $e->course->lessons()->count();
            return [
                'id' => $e->id,
                'status' => $e->status,
                'enrolled_at' => $e->enrolled_at?->format('Y-m-d'),
                'progress' => $total > 0 ? (int) round(($completed / $total) * 100) : 0,
                'user' => ['id' => $e->user->id, 'name' => $e->user->name],
                'course' => ['id' => $e->course->id, 'title' => $e->course->title],
            ];
        }));
    }
}
