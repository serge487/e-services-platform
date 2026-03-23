<?php

namespace App\Http\Controllers\Municipality;

use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        return view('municipality.dashboard');
    }
}