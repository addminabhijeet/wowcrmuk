<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Logins; // model for logins table

class LoginsController extends Controller
{
    public function index()
    {
        $logins = Logins::with('user')->latest()->get(); 
        return view('admin.logins', compact('logins'));
    }
}
