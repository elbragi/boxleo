<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Api\DashboardApiController;
use App\Http\Controllers\Api\LeaveApiController;
use App\Http\Controllers\Api\AttendanceApiController;
use App\Http\Controllers\Api\UserApiController;
use App\Http\Controllers\Api\HolidayApiController;
use App\Http\Controllers\Api\DepartmentApiController;
use App\Http\Controllers\Api\EmployeesPerformance;
use App\Http\Controllers\Api\LeaveTypeApiController;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
  public function index()
  {
    $user = auth()->user();

    // Pass the authenticated user to the view
    return view('dashboard.index', compact('user'));
  }

  public function stats(Request $request)
  {
    return (new DashboardApiController())->index($request);
  }

  public function userStats(Request $request, $id)
  {
    $user = \App\Models\User::findOrFail($id);
    return (new DashboardApiController())->userStatistics($user);
  }

  public function leaves(Request $request)
  {
    return (new LeaveApiController())->index($request);
  }

  public function attendances(Request $request)
  {
    return (new AttendanceApiController())->index($request);
  }

  public function users(Request $request)
  {
    return (new UserApiController())->index($request);
  }

  public function holidays(Request $request)
  {
    return (new HolidayApiController())->index($request);
  }

  public function attendanceAnalytics(Request $request)
  {
    return (new AttendanceApiController())->attendanceAnalytics();
  }

  public function departments(Request $request)
  {
    return (new DepartmentApiController())->index();
  }

  public function agentPerformance(Request $request)
  {
    return (new EmployeesPerformance())->agentPerformance($request);
  }

  public function leaveTypes(Request $request)
  {
    return (new LeaveTypeApiController())->index();
  }

  public function userAnalytics(Request $request, $id)
  {
    return (new DashboardApiController())->getUserAnalytics($id);
  }
}
