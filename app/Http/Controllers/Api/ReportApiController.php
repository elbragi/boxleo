<?php

namespace App\Http\Controllers\Api;

use App\Exports\AssetReportExport;
use App\Exports\AttendanceExport;
use App\Exports\LeaveExport;
use App\Exports\MonthlySummaryExport;
use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\Attendance;
use App\Models\Holiday;
use App\Models\Leave;
use App\Models\LeaveType;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
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

    public function monthlySummaryReport(Request $request)
    {
        try {
            $validated = $request->validate([
                'year'          => 'required|integer|min:2020|max:2100',
                'month'         => 'required|integer|between:1,12',
                'unit_id'       => 'nullable|exists:units,id',
                'department_id' => 'nullable|exists:departments,id',
            ]);

            $year  = (int) $validated['year'];
            $month = (int) $validated['month'];

            $start = Carbon::create($year, $month, 1)->startOfDay();
            $end   = Carbon::create($year, $month, 1)->endOfMonth()->endOfDay();
            $startStr = $start->toDateString();
            $endStr   = $end->toDateString();

            // All leave types
            $leaveTypes = LeaveType::orderBy('name')->get();

            // Employees
            $empQuery = User::with(['unit', 'department', 'leaveBalances.leaveType'])
                ->where('is_enabled', true)
                ->whereNull('deleted_at')
                ->whereHas('roles', fn($q) => $q->where('name', 'employee'));

            if (!empty($validated['unit_id'])) {
                $empQuery->where('unit_id', $validated['unit_id']);
            }
            if (!empty($validated['department_id'])) {
                $empQuery->where('department_id', $validated['department_id']);
            }

            $employees = $empQuery->get();

            // Attendance records for the month, keyed by user_id
            $attendances = Attendance::whereBetween('attendance_date', [$startStr, $endStr])
                ->whereIn('user_id', $employees->pluck('id'))
                ->get()
                ->groupBy('user_id');

            // Approved leaves overlapping the month, keyed by user_id
            $leaves = Leave::with('leave_type')
                ->where('status', 'Approved')
                ->whereIn('user_id', $employees->pluck('id'))
                ->where('from', '<=', $endStr)
                ->where('to',   '>=', $startStr)
                ->get()
                ->groupBy('user_id');

            // Public holidays in the month
            $holidayDates = Holiday::whereBetween('date', [$startStr, $endStr])
                ->pluck('date')
                ->map(fn($d) => Carbon::parse($d)->toDateString())
                ->unique()
                ->toArray();

            // Day-name → Carbon integer map
            $dayNameMap = [
                'sunday' => Carbon::SUNDAY, 'monday' => Carbon::MONDAY,
                'tuesday' => Carbon::TUESDAY, 'wednesday' => Carbon::WEDNESDAY,
                'thursday' => Carbon::THURSDAY, 'friday' => Carbon::FRIDAY,
                'saturday' => Carbon::SATURDAY,
            ];

            $rows = $employees->map(function ($emp) use (
                $attendances, $leaves, $leaveTypes,
                $start, $end, $startStr, $endStr,
                $holidayDates, $dayNameMap
            ) {
                $empAtts   = $attendances->get($emp->id, collect());
                $empLeaves = $leaves->get($emp->id, collect());

                // Resolve weekend days for this employee's unit
                $weekendInts = [];
                $unit = $emp->unit;
                if ($unit && isset($unit->weekend_day)) {
                    $raw = is_array($unit->weekend_day) ? $unit->weekend_day : [$unit->weekend_day];
                    foreach ($raw as $d) {
                        $weekendInts[] = is_numeric($d)
                            ? (int) $d
                            : ($dayNameMap[strtolower(trim($d))] ?? Carbon::SATURDAY);
                    }
                } else {
                    $weekendInts = [Carbon::SATURDAY, Carbon::SUNDAY];
                }

                // Count working days in the month for this employee
                $workingDays = 0;
                foreach (CarbonPeriod::create($start, $end) as $day) {
                    $dateStr = $day->toDateString();
                    if (!in_array($day->dayOfWeek, $weekendInts) && !in_array($dateStr, $holidayDates)) {
                        $workingDays++;
                    }
                }

                // Present = unique dates with an attendance record
                $presentDays = $empAtts->unique('attendance_date')->count();
                $lateDays    = $empAtts->where('status', 'Late')->count();
                $inFieldDays = $empAtts->where('in_field', true)->count();

                // Holiday count (public holidays in month, exclude weekends)
                $holidayCount = count(array_filter($holidayDates, function ($d) use ($weekendInts) {
                    return !in_array(Carbon::parse($d)->dayOfWeek, $weekendInts);
                }));

                // Leave days per type (clipped to month boundaries)
                $leaveCounts = [];
                foreach ($leaveTypes as $lt) {
                    $leaveCounts[$lt->id] = 0;
                }

                foreach ($empLeaves as $leave) {
                    $leaveStart = Carbon::parse($leave->from)->max($start);
                    $leaveEnd   = Carbon::parse($leave->to)->min($end);

                    if ($leaveStart->gt($leaveEnd)) continue;

                    // Count working days within the leave period
                    $leaveDays = 0;
                    foreach (CarbonPeriod::create($leaveStart, $leaveEnd) as $day) {
                        if (!in_array($day->dayOfWeek, $weekendInts)) {
                            $leaveDays++;
                        }
                    }

                    $typeId = $leave->leave_type?->id;
                    if ($typeId && isset($leaveCounts[$typeId])) {
                        $leaveCounts[$typeId] += $leaveDays;
                    }
                }

                $totalLeaveDays = array_sum($leaveCounts);
                $absent = max(0, $workingDays - $presentDays - $totalLeaveDays - $holidayCount);

                // Annual leave balance
                $annualBalance = $emp->leaveBalances
                    ->filter(fn($lb) => strtolower($lb->leaveType?->name ?? '') === 'annual leave')
                    ->first()?->balance ?? '—';

                $row = [
                    'id'             => $emp->id,
                    'name'           => $emp->firstname . ' ' . $emp->lastname,
                    'department'     => $emp->department?->name,
                    'branch'         => $emp->unit?->name,
                    'present'        => $presentDays,
                    'late'           => $lateDays,
                    'in_field'       => $inFieldDays,
                    'absent'         => $absent,
                    'holidays'       => $holidayCount,
                    'working_days'   => $workingDays,
                    'leave_balance'  => $annualBalance,
                ];

                foreach ($leaveTypes as $lt) {
                    $row['lt_' . $lt->id] = $leaveCounts[$lt->id];
                }

                return $row;
            })->sortBy('name')->values();

            return response()->json([
                'report'      => $rows,
                'leave_types' => $leaveTypes->map(fn($lt) => ['id' => $lt->id, 'name' => $lt->name]),
                'month_label' => Carbon::create($year, $month)->format('F Y'),
                'working_days_in_month' => $rows->first()['working_days'] ?? 0,
            ]);
        } catch (\Exception $e) {
            Log::error('Monthly summary report failed: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function attendanceExcelReport(Request $request)
    {
        $attendances = json_decode($request->input('attendances'));
        return Excel::download(new AttendanceExport($attendances), 'attendance_report.xlsx');

    }

    public function monthlySummaryExcel(Request $request)
    {
        $validated = $request->validate([
            'year'        => 'required|integer',
            'month'       => 'required|integer|between:1,12',
            'report'      => 'required|string',
            'leave_types' => 'required|string',
            'month_label' => 'required|string',
        ]);

        $report     = json_decode($validated['report'],     true);
        $leaveTypes = json_decode($validated['leave_types'], true);
        $monthLabel = $validated['month_label'];

        // Attach short display name for Blade view
        $shortNames = [
            'Annual Leave'         => 'Annual ⊙',
            'Maternity Leave'      => 'Maternity M',
            'Paternity Leave'      => 'Paternity P',
            'Sick Leave'           => 'Sick ⊕',
            'Compassionate Leave'  => 'Compassionate φ',
            'Study Leave'          => 'Study ∞',
            'Monthly Saturday Off' => 'Sat Off +',
            'Sunday Compensation'  => 'Sun Comp $',
            'Holiday compensation' => 'Hol Comp ☆',
            'Unpaid Leave'         => 'Unpaid',
            'Rest Day'             => 'Rest Day',
        ];
        foreach ($leaveTypes as &$lt) {
            $lt['short'] = $shortNames[$lt['name']] ?? $lt['name'];
        }
        unset($lt);

        $filename = str_replace(' ', '_', $monthLabel) . '_Attendance_Report.xlsx';
        return Excel::download(new MonthlySummaryExport($report, $leaveTypes, $monthLabel), $filename);
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




