<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Job;
use Illuminate\Http\Request;

class JobController extends Controller
{
    public function index(Request $request)
    {
        return view('frontend.jobs.index');
    }

    public function show(Request $request,$slug){
        $job = Job::where('slug',$slug)->first();

        $recommendedJobs = Job::with(['company', 'cities', 'job_category'])
                ->inRandomOrder()
                ->take(3)
                ->get();
        $alreadyApplied =0;
        return view('frontend.jobs.details',compact('job','recommendedJobs','alreadyApplied'));
    }
}
