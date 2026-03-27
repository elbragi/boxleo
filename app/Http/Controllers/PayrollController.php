<?php

namespace App\Http\Controllers;

use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Carbon\Carbon;

class PayrollController extends Controller
{

    public function index()
    {
        return view('payroll.index');
    }


    public function settings(){
        return view('payroll.settings');}


     // fetch user payslip with user info

    public function payslipWithUser($id)
    {
        return User::with('user_detail','earnings.earningType','deductions.deductionType')->findOrFail($id);
    }   

    public function printPayslip($id)
    {
        $payslip = \App\Models\Payslip::with([
            'user.user_detail',
            'user.earnings.earningType',
            'user.deductions.deductionType',
            'user.unit',
            'user.office',
            'user.department',
            'user.designation',
            'user.salary',
            'earnings',
            'statutoryDeductions',
            'otherDeductions'
        ])->findOrFail($id);

        $employee = $payslip->user;
        $name = $payslip->is_rider ? $payslip->rider_name : ($employee ? $employee->firstname : 'Employee');

        $pdf = PDF::loadView('payroll.payslip', compact('payslip', 'employee'))->setPaper('a5', 'portrait');
        
        $filename = '';
        if ($payslip->is_rider && $payslip->start_date && $payslip->end_date) {
            $startDate = Carbon::parse($payslip->start_date)->format('M-jS');
            $endDate = Carbon::parse($payslip->end_date)->format('M-jS');
            $filename = 'payslip-' . str_replace(' ', '-', $name) . '-' . $startDate . '-to-' . $endDate . '.pdf';
        } else {
            $month = $payslip->month ?: Carbon::now()->month;
            $year = $payslip->year ?: Carbon::now()->year;
            $monthName = Carbon::createFromDate($year, $month, 1)->format('F');
            $filename = 'payslip-' . str_replace(' ', '-', $name) . '-' . $monthName . '-' . $year . '.pdf';
        }

        if (request()->has('download')) {
            return $pdf->download($filename);
        }
        
        return $pdf->stream($filename);
    }

    public function employeePrintPayslip($id)
    {
        $payslip = \App\Models\Payslip::with([
            'user.user_detail',
            'user.earnings.earningType',
            'user.deductions.deductionType',
            'user.unit',
            'user.office',
            'user.department',
            'user.designation',
            'user.salary',
            'earnings',
            'statutoryDeductions',
            'otherDeductions'
        ])->findOrFail($id);

        if ($payslip->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to payslip');
        }

        $employee = $payslip->user;
        $name = $employee ? $employee->firstname : 'Employee';

        $pdf = PDF::loadView('payroll.payslip', compact('payslip', 'employee'))->setPaper('a5', 'portrait');

        $month = $payslip->month ?: Carbon::now()->month;
        $year = $payslip->year ?: Carbon::now()->year;
        $monthName = Carbon::createFromDate($year, $month, 1)->format('F');
        $filename = 'payslip-' . str_replace(' ', '-', $name) . '-' . $monthName . '-' . $year . '.pdf';

        if (request()->has('download')) {
            return $pdf->download($filename);
        }
        
        return $pdf->stream($filename);
    }

}
