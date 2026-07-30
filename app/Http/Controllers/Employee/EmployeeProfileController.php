<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmployeeProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function index(Request $request): View
    {
        $employee = auth()->user()->employee;

        return view('employee.profile', [
            'employee' => $employee,
            'user' => $request->user(),
        ]);
    }
}
