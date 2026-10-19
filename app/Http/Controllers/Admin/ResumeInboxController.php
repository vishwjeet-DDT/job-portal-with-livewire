<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class ResumeInboxController extends Controller
{
    public function index()
    {

        // dd('sdf');
        return view('admin.resume-inbox.index');
    }
}
