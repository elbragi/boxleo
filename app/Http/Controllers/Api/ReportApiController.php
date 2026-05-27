<?php

namespace App\Http\Controllers\Api;

use App\Exports\AssetReportExport;
use App\Exports\AttendanceExport;
use App\Exports\LeaveExport;
use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\Attendance;
use App\Models\Leave;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class ReportApiController extends Controller
{
    private $crm_email = "";
    private $crm_password = "";
    public function leaveReport(Request $request)
    {
        $validated = $request->validate([
            'employee' => 'nullable|exists:users,id',
            'leaveType' => 'nullable|exists:leave_types,id',
            'leaveStatus' => 'nullable|string|in:Approved,Pending,Rejected,Cancelled',
            'start' => 'nullable|date',
            'end' => 'nullable|date',
        ]);

        $query = Leave::with('user', 'leave_type');

        if (!empty($validated['employee'])) {
            $query->where('user_id', $validated['employee']);
        }

        if (!empty($validated['leaveType'])) {
            $query->where('leave_type_id', $validated['leaveType']);
        }

        if (!empty($validated['leaveStatus'])) {
            $query->where('status', $validated['leaveStatus']);
        }

        if (!empty($validated['start'])) {
            $query->where('from', '>=', $validated['start']);
        }

        if (!empty($validated['end'])) {
            $query->where('to', '<=', $validated['end']);
        }

        $leaveRecords = $query->orderBy('created_at', 'desc')->get();

        $statuses = [
            'total' => $leaveRecords->count(),
            'approved' => $leaveRecords->where('status', 'Approved')->count(),
            'pending' => $leaveRecords->where('status', 'Pending')->count(),
            'rejected' => $leaveRecords->where('status', 'Rejected')->count(),
            'cancelled' => $leaveRecords->where('status', 'Cancelled')->count(),
        ];

        return response()->json([
            'leaveReport' => $leaveRecords->map(function ($record) {
                return [
                    'id' => $record->id,
                    'created_at' => $record->created_at,
                    'fullName' => $record->user ? $record->user->firstname . ' ' . $record->user->lastname : 'deleted user',
                    'leaveType' => $record->leave_type->name,
                    'from' => $record->from,
                    'to' => $record->to,
                    'status' => $record->status,
                    'days' => $record->days,
                    'notes' => $record->comment,
                ];
            }),
            'leaveReportStatuses' => $statuses,
        ]);
    }

    public function attendanceReport(Request $request)
    {
        try {
            $validated = $request->validate([
                'employee'         => 'nullable|exists:users,id',
                'attendanceStatus' => 'nullable|string|in:In Time,Late',
                'unit_id'          => 'nullable|exists:units,id',
                'department_id'    => 'nullable|exists:departments,id',
                'start'            => 'nullable|date',
                'end'              => 'nullable|date',
            ]);

            $query = Attendance::with('user.unit', 'user.department');

            $query->whereHas('user', function ($q) {
                $q->whereNull('deleted_at');
            });

            if (!empty($validated['employee'])) {
                $query->where('user_id', $validated['employee']);
            }

            if (!empty($validated['attendanceStatus'])) {
                $query->where('status', $validated['attendanceStatus']);
            }

            if (!empty($validated['unit_id'])) {
                $query->whereHas('user', fn($q) => $q->where('unit_id', $validated['unit_id']));
            }

            if (!empty($validated['department_id'])) {
                $query->whereHas('user', fn($q) => $q->where('department_id', $validated['department_id']));
            }

            if (!empty($validated['start'])) {
                $query->where('attendance_date', '>=', $validated['start']);
            }

            if (!empty($validated['end'])) {
                $query->where('attendance_date', '<=', $validated['end']);
            }

            $attendanceRecords = $query->orderBy('attendance_date', 'desc')
                                       ->orderBy('clock_in_time', 'asc')
                                       ->get();

            $statuses = [
                'total'   => $attendanceRecords->count(),
                'in_time' => $attendanceRecords->where('status', 'In Time')->count(),
                'late'    => $attendanceRecords->where('status', 'Late')->count(),
            ];

            return response()->json([
                'attendanceReport' => $attendanceRecords->map(function ($record) {
                    // Calculate hours worked if both clock-in and clock-out exist
                    $duration = null;
                    if ($record->clock_in_time && $record->clock_out_time) {
                        try {
                            $in  = \Carbon\Carbon::parse($record->attendance_date . ' ' . $record->clock_in_time);
                            $out = \Carbon\Carbon::parse($record->attendance_date . ' ' . $record->clock_out_time);
                            if ($out->gt($in)) {
                                $mins = $in->diffInMinutes($out);
                                $duration = floor($mins / 60) . 'h ' . ($mins % 60) . 'm';
                            }
                        } catch (\Exception $e) {}
                    }

                    return [
                        'id'              => $record->id,
                        'attendance_date' => $record->attendance_date,
                        'clock_in'        => $record->clock_in_time,
                        'clock_out'       => $record->clock_out_time,
                        'duration'        => $duration,
                        'name'            => $record->user
                            ? $record->user->firstname . ' ' . $record->user->lastname
                            : 'deleted user',
                        'branch'          => $record->user?->unit?->name,
                        'department'      => $record->user?->department?->name,
                        'status'          => $record->status,
                        'notes'           => $record->notes,
                    ];
                }),
                'attendanceReportStatuses' => $statuses,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch attendance report: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    public function dailyAttendanceReport(Request $request)
    {
        try {
            $validated = $request->validate([
                'date'          => 'required|date',
                'unit_id'       => 'nullable|exists:units,id',
                'department_id' => 'nullable|exists:departments,id',
                'employee'      => 'nullable|exists:users,id',
            ]);

            $date = Carbon::parse($validated['date'])->toDateString();

            // Build employee query
            $employeeQuery = User::with('unit', 'department', 'designation')
                ->where('is_enabled', true)
                ->whereNull('deleted_at')
                ->whereHas('roles', fn($q) => $q->where('name', 'employee'));

            if (!empty($validated['unit_id'])) {
                $employeeQuery->where('unit_id', $validated['unit_id']);
            }
            if (!empty($validated['department_id'])) {
                $employeeQuery->where('department_id', $validated['department_id']);
            }
            if (!empty($validated['employee'])) {
                $employeeQuery->where('id', $validated['employee']);
            }

            $employees = $employeeQuery->get();

            // Attendance records for the day
            $attendances = Attendance::where('attendance_date', $date)
                ->whereIn('user_id', $employees->pluck('id'))
                ->get()
                ->keyBy('user_id');

            // Approved leaves covering the day
            $leaves = Leave::with('leave_type')
                ->whereIn('user_id', $employees->pluck('id'))
                ->where('status', 'Approved')
                ->where('from', '<=', $date)
                ->where('to', '>=', $date)
                ->get()
                ->keyBy('user_id');

            $rows = $employees->map(function ($emp) use ($attendances, $leaves, $date) {
                $att   = $attendances->get($emp->id);
                $leave = $leaves->get($emp->id);

                if ($att) {
                    $status    = $att->status; // 'In Time' or 'Late'
                    $clockIn   = $att->clock_in_time;
                    $clockOut  = $att->clock_out_time;
                    $leaveType = null;
                    $duration  = null;
                    if ($clockIn && $clockOut) {
                        try {
                            $in   = Carbon::parse($date . ' ' . $clockIn);
                            $out  = Carbon::parse($date . ' ' . $clockOut);
                            if ($out->gt($in)) {
                                $mins     = $in->diffInMinutes($out);
                                $duration = floor($mins / 60) . 'h ' . ($mins % 60) . 'm';
                            }
                        } catch (\Exception $e) {}
                    }
                } elseif ($leave) {
                    $status    = 'On Leave';
                    $clockIn   = null;
                    $clockOut  = null;
                    $duration  = null;
                    $leaveType = $leave->leave_type?->name;
                } else {
                    $status    = 'Absent';
                    $clockIn   = null;
                    $clockOut  = null;
                    $duration  = null;
                    $leaveType = null;
                }

                return [
                    'id'          => $emp->id,
                    'name'        => $emp->firstname . ' ' . $emp->lastname,
                    'branch'      => $emp->unit?->name,
                    'department'  => $emp->department?->name,
                    'designation' => $emp->designation?->name,
                    'clock_in'    => $clockIn,
                    'clock_out'   => $clockOut,
                    'duration'    => $duration,
                    'status'      => $status,
                    'leave_type'  => $leaveType,
                ];
            })->sortBy('name')->values();

            $statuses = [
                'total'    => $rows->count(),
                'in_time'  => $rows->where('status', 'In Time')->count(),
                'late'     => $rows->where('status', 'Late')->count(),
                'on_leave' => $rows->where('status', 'On Leave')->count(),
                'absent'   => $rows->where('status', 'Absent')->count(),
            ];

            return response()->json([
                'report'   => $rows,
                'statuses' => $statuses,
                'date'     => $date,
            ]);
        } catch (\Exception $e) {
            Log::error('Daily attendance report failed: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function attendanceExcelReport(Request $request)
    {
        $attendances = json_decode($request->input('attendances'));
        return Excel::download(new AttendanceExport($attendances), 'attendance_report.xlsx');

    }

    public function leaveExcelReport(Request $request)
    {
        $leaves = json_decode($request->input('leaves'));
        return Excel::download(new LeaveExport($leaves), 'leave_report.xlsx');

    }

    // public function  assetReport(){


    //     $reportdata =Asset::all();

    //     return response()->json($reportdata);

    // }


    public function assetReport()
    {
        return Excel::download(new AssetReportExport, 'asset_report.xlsx');
    }
}




