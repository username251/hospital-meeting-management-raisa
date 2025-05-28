<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use Illuminate\Http\Request;
use Carbon\Carbon;

class StaffDashboardController extends Controller
{
    public function index()
    {
        return view('staff.index');
    }
}