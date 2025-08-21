<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    //
    public function dashboard(Request $request)
    {
        // Your logic for the dashboard
        return view('dashboard');
    }
}
