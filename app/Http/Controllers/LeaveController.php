<?php



namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use App\Models\Leave;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Log;

class LeaveController extends Controller
{
    public function index()
    {
        $userId = auth()->id();

        return view('leaves.index', compact('userId'));
    }

    public function leaveRequests()
    {
        $userId = auth()->id();

        return view('leaves.requests', compact('userId'));
    }

    public function leaveBalances()
    {
        $userId = auth()->id();

        return view('leaves.balances', compact('userId'));
    }

    public function stats()
    {

        return view('leaves.stats');
    }

    public function create()
    {
        return view('leaves.assign');
    }

    public function employeeLeaves()
    {
        $userId = auth()->user()->id;

        return view('employee.leaves.index', ['userId' => $userId]);
    }

    public function leaveHistory()
    {
        return view('employee.leaves.history');
    }

    public function leavePlans()
    {

        return view('employee.leaves.plans');
    }

    public function leaveSummary()
    {
        return view('leaves.summary');
    }

    public function leaveAnalytics()
    {
        return view('leaves.analytics');
    }

    public function teamLeaves()
    {
        $userId = auth()->user()->id;
        return view('leaves.team', ['userId' => $userId]);
    }



    public function downloadDocument($filename)
    {
        $userId = auth()->id();
        $user = auth()->user();

        // Find the leave record associated with this document
        $leave = Leave::where('document', $filename)->first();

        if (!$leave) {
            abort(404, 'Document not found.');
        }

        // Authorization Checks
        $isApplicant = $leave->user_id === $userId;
        $isManager = $user->id === $leave->user->manager_id; // Assuming manager_id exists on User model. Verify if needed.
        // Actually, let's check the leave relationship or user hierarchy more robustly if needed.
        // Based on LeaveApiController, manager is passed during creation. 
        // But for viewing, we can rely on roles.
        
        $isHr = $user->is_hr === 1;
        $isHod = $user->is_hod === 1 && $user->hodDepartments->contains('id', $leave->user->department_id);
        
        // Also check if user is the specific manager assigned to the user
        $isDirectManager = $user->id === $leave->user->manager_id;
        
        // HOD check might need to be more specific based on how `hodDepartments` works.
        // In LeaveApiController: $hasAuthority = $approver->hodDepartments()->where('department_id', $leave->user->department_id)->exists();
        if ($isHod) {
             // Re-verify strictly
             $isHod = $user->hodDepartments()->where('department_id', $leave->user->department_id)->exists();
        }


        if (!$isApplicant && !$isHr && !$isHod && !$isDirectManager && $user->designation_id !== 1) { // Designation 1 is Country Manager?
             abort(403, 'Unauthorized access to this document.');
        }

        $path = 'leave-documents/' . $filename;

        if (!Storage::exists($path)) {
            abort(404, 'File not found in storage.');
        }

        return Storage::download($path);
    }
}
