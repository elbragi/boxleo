<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Job;
use App\Models\JobApplicant;
use App\Mail\NewJobApplication;
use App\Mail\CandidateAutoReply;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ExternalJobController extends Controller
{
    /**
     * Get all published jobs for external website
     */
    public function index()
    {
        try {
            $jobs = Job::with('department')
                ->where('status', 'published')
                ->where(function($query) {
                    $query->whereNull('expire_date')
                          ->orWhere('expire_date', '>=', now());
                })
                ->latest()
                ->get();

            return response()->json([
                'success' => true,
                'data' => $jobs
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching external jobs: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch jobs'
            ], 500);
        }
    }

    /**
     * Store a new job application from external website
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'job_id' => 'required|exists:jobs,id',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'message' => 'nullable|string',
            'cv' => 'required|file|mimes:pdf,doc,docx|max:10240', // 10MB max
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $cvPath = null;
            if ($request->hasFile('cv')) {
                $file = $request->file('cv');
                $filename = time() . '_' . $file->getClientOriginalName();
                // Store in public/cvs disk
                $cvPath = $file->storeAs('cvs', $filename, 'public');
            }

            $applicant = new JobApplicant();
            $applicant->job_id = $request->job_id;
            $applicant->name = $request->name;
            $applicant->email = $request->email;
            $applicant->phone = $request->phone;
            $applicant->message = $request->message;
            $applicant->cv = $cvPath;
            $applicant->status = 'new';
            $applicant->save();
            
            // Load the job relationship for emails
            $applicant->load('job');

            // Send notification to HR
            Mail::to('hr@boxleocourier.com')->send(new NewJobApplication($applicant));

            // Send auto-reply to applicant
            Mail::to($applicant->email)->send(new CandidateAutoReply($applicant));

            return response()->json([
                'success' => true,
                'message' => 'Application submitted successfully',
                'data' => $applicant
            ], 201);

        } catch (\Exception $e) {
            Log::error('Error submitting application: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit application',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

