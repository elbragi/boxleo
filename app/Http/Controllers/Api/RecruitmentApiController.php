<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Job;
use App\Models\JobApplicant;

class RecruitmentApiController extends Controller
{
    /**
     * Get all jobs
     */
    public function jobs()
    {
        $jobs = Job::with('department')->latest()->get();
        return response()->json($jobs);
    }

    /**
     * Get all applications
     */
    public function applications(Request $request)
    {
        $query = JobApplicant::with('job.department');

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        if ($request->has('job_id') && $request->job_id) {
            $query->where('job_id', $request->job_id);
        }

        $applications = $query->latest()->get();
        return response()->json($applications);
    }

    /**
     * Update application status
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,shortlisted,rejected,hired',
        ]);

        $application = JobApplicant::findOrFail($id);
        $application->status = $request->status;
        $application->save();

        return response()->json([
            'message' => 'Status updated successfully',
            'application' => $application->load('job.department')
        ]);
    }

    /**
     * Get dashboard metrics
     */
    public function dashboardMetrics()
    {
        $totalApplications = JobApplicant::count();
        $totalJobs = Job::count();
        $pendingApplications = JobApplicant::where('status', 'pending')->count();
        $shortlistedApplications = JobApplicant::where('status', 'shortlisted')->count();

        // Recent applications
        $recentApplications = JobApplicant::with('job')->latest()->take(5)->get();

        return response()->json([
            'total_applications' => $totalApplications,
            'total_jobs' => $totalJobs,
            'pending_applications' => $pendingApplications,
            'shortlisted_applications' => $shortlistedApplications,
            'recent_applications' => $recentApplications,
        ]);
    }

    /**
     * Store a new job
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'location' => 'required|string',
            'department' => 'required|string',
        ]);

        $department = \App\Models\Department::where('name', $request->department)->first();

        // Combine fields for description
        $description = "Responsibilities:\n" . ($request->responsibilities ?? 'N/A') . "\n\n";
        $description .= "Qualifications:\n" . ($request->qualifications ?? 'N/A') . "\n\n";
        $description .= "Benefits:\n" . ($request->benefits ?? 'N/A') . "\n\n";
        $description .= "Instructions:\n" . ($request->application_instructions ?? 'N/A');

        $job = Job::create([
            'title' => $request->title,
            'location' => $request->location,
            'department_id' => $department ? $department->id : null,
            'salary_from' => $request->salary_min,
            'salary_to' => $request->salary_max,
            'status' => $request->status ?? 'published',
            'expire_date' => $request->application_deadline ?? now()->addDays(30),
            'start_date' => now(),
            'description' => $description,
            'vacancies' => 1,
            'experience' => 0,
            'type' => 'Full-time',
        ]);

        return response()->json([
            'message' => 'Job created successfully',
            'job' => $job->load('department')
        ]);
    }

    /**
     * Update an existing job
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string',
            'location' => 'required|string',
            'department' => 'required|string',
        ]);

        $job = Job::findOrFail($id);
        $department = \App\Models\Department::where('name', $request->department)->first();

        $description = "Responsibilities:\n" . ($request->responsibilities ?? 'N/A') . "\n\n";
        $description .= "Qualifications:\n" . ($request->qualifications ?? 'N/A') . "\n\n";
        $description .= "Benefits:\n" . ($request->benefits ?? 'N/A') . "\n\n";
        $description .= "Instructions:\n" . ($request->application_instructions ?? 'N/A');

        $job->update([
            'title' => $request->title,
            'location' => $request->location,
            'department_id' => $department ? $department->id : null,
            'salary_from' => $request->salary_min,
            'salary_to' => $request->salary_max,
            'status' => $request->status ?? 'published',
            'expire_date' => $request->application_deadline ?? $job->expire_date,
            'description' => $description,
        ]);

        return response()->json([
            'message' => 'Job updated successfully',
            'job' => $job->load('department')
        ]);
    }
}
