<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class Controller 
{

    /**
     * Show the application home page.
     */
    public function index()
    {
        // return a Blade view stored in resources/views/home.blade.php
        return view('comingSoon');
    }
}
