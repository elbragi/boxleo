<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SystemRequestController extends Controller
{
    public function index()
    {
        return view('tasks.system_requests');
    }
}
