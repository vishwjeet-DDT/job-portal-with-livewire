<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{Job,JobApplication};
use App\Services\QueryFilterService;
use Yajra\DataTables\Facades\DataTables;

class JobController extends Controller
{
    protected $filterService;

    public function __construct(QueryFilterService $filterService)
    {
        $this->filterService = $filterService;
    }
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Job::query();
            return DataTables::of($query)
                ->filter(function ($q) use ($request) {
                    $this->filterService->handle($request, $q);
                })
                ->addColumn('views', function($row){
                    $url = route('admin.job.apply-list', $row->id);
                    $count = $row->applicants ? $row->applicants->count() : 0;
                    return '<a href="'.$url.'" target="_blank">'.$count.'</a>';
                })
                ->addColumn('title', function ($row) {
                    $url = route('admin.job.releted_candidates', $row->id);

                    return '<a href="'.$url.'" target="_blank">'.$row->title.'</a>';
                })

                ->addColumn('cities.name',function ($row){
                     return $row->cities->pluck('name')->implode(', ') ?: '-';
                })
                ->addColumn('createdBy.name',function ($row){
                    $html = '-';
                    if($row->createdBy){
                       $html = '<span class=" badge font-weight-medium bg-light-warning text-warning">
                            '.$row->createdBy?->name ??  "-".'
                            </span>';
                    }
                   return $html;
                })
               ->addColumn('is_featured', function ($row) {
                    $checked = $row->is_featured ? 'checked' : '';
                    return '
                        <div class="form-check form-switch">
                            <input class="form-check-input toggle-data"
                                type="checkbox"
                                data-id="' . $row->id . '"
                                data-field="is_featured"
                                data-route="' . route('admin.jobs.updateStatus') . '"
                                ' . $checked . '>
                        </div>
                    ';
                })


                ->addColumn('created_at', function ($row) {
                    return '<span class="badge rounded-pill font-weight-medium bg-light-secondary text-secondary">'
                        . $row->created_at->format('d M Y') .
                        '</span>';
                })
               ->addColumn('deadline', function ($row) {

                    if (empty($row->deadline)) {
                        return '<span class="badge rounded-pill font-weight-medium bg-light-danger text-danger">No Deadline</span>';
                    }

                    return '<span class="badge rounded-pill font-weight-medium bg-light-info text-info">'
                        . \Carbon\Carbon::parse($row->deadline)->format('d M Y') .
                        '</span>';
                })


                ->addColumn('action', function ($row) {
                           $url = route('admin.jobs.edit',$row->id);
                    return '<a class="btn btn-outline-warning btn-sm mr-2" href="'.$url.'">
                                <i class="ri-edit-box-line"></i>
                            </a>' .
                        '
                        <button class="btn btn-outline-primary btn-sm" onclick="openViewModal('.$row->id.')">
                            <i class="ri-eye-line"></i>
                        </button>
                        ';
                })

                ->addIndexColumn()
                ->rawColumns(['title','action','is_featured','created_at','deadline','createdBy.name','views'])
                ->make(true);
        }

         return view('admin.jobs.index');
    }

    public function apply_list(Request $request, $id)
    {
        $job = Job::find($id);
       return view('admin.jobs.apply_list',compact('id','job'));
    }
    public function releted_candidates(Request $request, $id)
    {
        $job = Job::find($id);
       return view('admin.jobs.releted_candidates',compact('id','job'));
    }


    public function create()
    {
        return view('admin.jobs.create');
    }

    public function edit($id)
    {
        return view('admin.jobs.edit',compact('id'));
    }

    public function updateStatus(Request $request)
    {
        $item = Job::find($request->id);
        if (!$item) {
            return response()->json(['status' => false, 'message' => 'Record not found!']);
        }

        $item->{$request->field} = $request->value;
        $item->save();

        return response()->json([
            'status' => true,
            'message' => 'Status updated successfully!'
        ]);
    }
    public function destroy($id)
    {
        $Job = Job::find($id);
        if (!$Job) {
            return response()->json(['message' => 'Job not found.'], 404);
        }
        $Job->delete();
        return response()->json(['message' => 'Job deleted successfully.']);
    }

    public function apply(Request $request, $id)
    {
        $job = Job::find($id);
        if (JobApplication::where('job_id', $id)->where('user_id', auth()->id())->exists()) {
            return back()->with('error', 'You already applied for this job.');
        }

        JobApplication::create([
            'job_id'       => $job->id,
            'user_id'      => auth()->id()
        ]);

        return back()->with('success', 'Application submitted successfully!');
    }

}
