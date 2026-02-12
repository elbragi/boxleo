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
        return User::with('userdetails','earnings.earningType','deductions.deductionType')->findOrFail($id);
    }   

    public function printPayslip($id)
    {
        $employee = User::with([
            'userdetails',
            'earnings.earningType',
            'deductions.deductionType',
            'unit',
            'office',
            'department',
            'designation',
            'employee_job_info',
            'employee_detail',
            'employee_salary'
        ])->findOrFail($id);

        $pdf = \PDF::loadView('payroll.payslip', compact('employee'))->setPaper('a5', 'portrait');
        return $pdf->stream('payslip-' . $id . '.pdf');
    }

}
