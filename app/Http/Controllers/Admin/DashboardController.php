<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{Job,Employer,Candidate};

class DashboardController extends Controller
{
   public function dashboard()
   {
      $total =[];
      $total['jobs'] = Job::count();
      $total['employer'] = Employer::count();
      $total['candidate'] = Candidate::count();


      return view('admin.dashboard',compact('total'));
   }

    public function user_list()
   {
      return view('admin.dashboard');
   }


}
