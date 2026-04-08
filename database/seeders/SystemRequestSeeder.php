<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SystemRequest;
use App\Models\Department;
use App\Models\User;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SystemRequestSeeder extends Seeder
{
    public function run()
    {
        $csvPath = base_path('Boxleo New System Requests - March - Support tracking.csv');
        if (!File::exists($csvPath)) {
            $this->command->error("CSV file not found at $csvPath");
            return;
        }

        // Clean table first
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        SystemRequest::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $data = array_map('str_getcsv', file($csvPath));
        $header = array_shift($data);

        $departments = Department::pluck('id', 'name')->toArray();
        $deptMap = [
            'Transport' => 8,
            'Warehouse' => 11,
            'Data' => 9,
            'Finance' => 2,
            'Management' => 4,
            'IT' => 3,
            'Call Center' => 6,
            'Operations' => 5,
        ];

        $firstUserId = User::first()->id ?? 1;

        $count = 0;
        foreach ($data as $row) {
            if (empty($row[1]) || trim($row[1]) == '' || $row[1] == 'Title / Summary') continue;

            $title = $row[1];
            $description = $row[2] ?? '';
            $priority = strtolower(trim($row[3] ?? 'medium'));
            $status = strtolower(trim($row[4] ?? 'pending'));
            $deptName = trim($row[5] ?? '');
            $requestedBy = trim($row[6] ?? '');
            $reportedAt = trim($row[7] ?? '');
            $effort = trim($row[8] ?? '');

            if (str_contains($status, 'complete')) $status = 'completed';
            elseif (str_contains($status, 'progress')) $status = 'in_progress';
            elseif (str_contains($status, 'pending')) $status = 'pending';
            elseif (str_contains($status, 'hold')) $status = 'on_hold';
            elseif (str_contains($status, 'cancel')) $status = 'cancelled';
            else $status = 'pending';

            if (!in_array($priority, ['low', 'medium', 'high', 'urgent'])) $priority = 'medium';

            $departmentId = null;
            foreach ($deptMap as $key => $id) {
                if (str_contains(strtolower($deptName), strtolower($key))) {
                    $departmentId = $id;
                    break;
                }
            }

            try {
                SystemRequest::create([
                    'title' => substr($title, 0, 255),
                    'description' => $description ?: 'No description provided.',
                    'priority' => $priority,
                    'status' => $status,
                    'department_id' => $departmentId,
                    'requested_by' => $requestedBy,
                    'reported_at' => $this->parseDate($reportedAt),
                    'effort_hours' => is_numeric($effort) ? (float)$effort : null,
                    'developer_name' => 'Mohammed',
                    'created_by' => $firstUserId
                ]);
                $count++;
            } catch (\Exception $e) {
                $this->command->error("Failed to import: " . substr($title, 0, 50) . " - " . $e->getMessage());
            }
        }

        $this->command->info("Successfully imported $count tasks.");
    }

    private function parseDate($dateString)
    {
        if (!$dateString || trim($dateString) == '') return null;
        try {
            return Carbon::parse($dateString)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }
}
