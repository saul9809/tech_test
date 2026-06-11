<?php

namespace App\Http\Controllers;

class SpaController extends Controller
{
    // -- Screen app
    public function index()
    {
        return view('app');
    }
}
