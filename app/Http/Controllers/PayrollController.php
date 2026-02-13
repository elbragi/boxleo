<?php

namespace App\Http\Controllers;

use App\Models\User;

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
            'user.salary'
        ])->findOrFail($id);

        $employee = $payslip->user;

        $pdf = \PDF::loadView('payroll.payslip', compact('payslip', 'employee'))->setPaper('a5', 'portrait');
        
        $monthName = \Carbon\Carbon::createFromDate($payslip->year, $payslip->month, 1)->format('F');
        $filename = 'payslip-' . $employee->firstname . '-' . $monthName . '-' . $payslip->year . '.pdf';
        
        if (request()->has('download')) {
            return $pdf->download($filename);
        }
        
        return $pdf->stream($filename);
    }

}
