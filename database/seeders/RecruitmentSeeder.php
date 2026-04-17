<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Job;
use App\Models\JobApplicant;
use App\Models\Department;
use Carbon\Carbon;

class RecruitmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Sample Jobs
        $jobs = [
            [
                'title' => 'Software Engineer (Laravel/Vue)',
                'department_id' => 3, // IT
                'location' => 'Nairobi, Kenya (Hybrid)',
                'vacancies' => 2,
                'experience' => 3,
                'type' => 'Full-time',
                'status' => 'published',
                'start_date' => Carbon::now(),
                'expire_date' => Carbon::now()->addDays(30),
                'description' => 'We are looking for a Software Engineer to join our IT team at Boxleo.',
            ],
            [
                'title' => 'Logistics Coordinator',
                'department_id' => 8, // Transport & Logistics
                'location' => 'Mombasa, Kenya',
                'vacancies' => 1,
                'experience' => 2,
                'type' => 'Full-time',
                'status' => 'published',
                'start_date' => Carbon::now()->subDays(5),
                'expire_date' => Carbon::now()->addDays(25),
                'description' => 'Handle logistics operations for Boxleo.',
            ],
            [
                'title' => 'Customer Experience Representative',
                'department_id' => 15, // Customer Experience
                'location' => 'Remote',
                'vacancies' => 5,
                'experience' => 1,
                'type' => 'Contract',
                'status' => 'published',
                'start_date' => Carbon::now()->subDays(10),
                'expire_date' => Carbon::now()->addDays(20),
                'description' => 'Provide excellent customer service to our clients.',
            ],
        ];

        foreach ($jobs as $jobData) {
            $job = Job::create($jobData);

            // Create some applicants for each job
            JobApplicant::create([
                'job_id' => $job->id,
                'name' => 'John Doe',
                'email' => 'john.doe@example.com',
                'phone' => '0711223344',
                'cv' => 'resumes/john_doe_cv.pdf',
                'status' => 'pending',
                'message' => 'I am very interested in this position.',
            ]);

            JobApplicant::create([
                'job_id' => $job->id,
                'name' => 'Jane Smith',
                'email' => 'jane.smith@example.com',
                'phone' => '0722334455',
                'cv' => 'resumes/jane_smith_cv.pdf',
                'status' => 'shortlisted',
                'message' => 'I have 5 years of experience in this field.',
            ]);

            JobApplicant::create([
                'job_id' => $job->id,
                'name' => 'Mark Wilson',
                'email' => 'mark.wilson@example.com',
                'phone' => '0733445566',
                'cv' => 'resumes/mark_wilson_cv.pdf',
                'status' => 'rejected',
                'message' => 'I am applying for several roles.',
            ]);
        }
    }
}
