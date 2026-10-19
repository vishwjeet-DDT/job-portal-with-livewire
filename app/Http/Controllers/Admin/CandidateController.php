<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Str;
use App\Services\QueryFilterService;
use Yajra\DataTables\Facades\DataTables;
use App\Models\User;

class CandidateController extends Controller
{
     protected $filterService;

    public function __construct(QueryFilterService $filterService)
    {
        $this->filterService = $filterService;
    }

    public function index(Request $request)
    {

        $roleName = 'Candidates';
        if ($request->ajax()) {
            $roleName = 'Candidates';
            $role = Role::where('name', $roleName)->first();
            $query = $role->users()->getQuery()->orderByDesc('users.id');
            return DataTables::of($query)
                ->filter(function ($q) use ($request) {
                    $this->filterService->handle($request, $q);
                })
                ->addColumn('name', function ($row) {
                    return "<span class='nameTexts'>{$row->name}</span><br>{$row->email}";
                })
                 ->addColumn('experience_type', function ($row) {
                    return $row->total_experience;
                })
                ->addColumn('skills.name', function ($row) {
                    return $row->skill_name ? $row->skill_name : 'N/A';
                })
                ->addColumn('employments.job_title', function ($row) {
                    return $row->job_title ? $row->job_title  : 'N/A';
                })
                ->addColumn('employments.company_name', function ($row) {
                    return $row->currentEmployment->first() ? $row->currentEmployment->first()->company_name : 'N/A';
                })
                ->addColumn('action', function ($row) {
                    return '
                        <a class="btn btn-outline-warning btn-sm mx-2" href="' . route('admin.candidates.edit', $row->id) . '">
                            <i class="ri-edit-box-line"></i>
                        </a>
                        <button type="button"
                                class="btn btn-outline-danger btn-sm delete-button"
                                data-url="' . route('admin.candidates.destroy', $row->id) . '"
                                data-table="dataTable">
                            <i class="ri-delete-bin-line"></i>
                        </button>
                    ';
                })

                ->addIndexColumn()
                 ->rawColumns(['name', 'action','experience_type'])
                ->make(true);
        }

        return view('admin.candidates.index', compact('roleName'));
    }

    public function edit($id)
    {
        $user = User::find($id);
        return view('admin.candidates.edit',compact('user'));
    }

    public function destroy($id)
    {
        $User = User::find($id);
        if (!$User) {
            return response()->json(['message' => 'User not found.'], 404);
        }
        $User->delete();
        return response()->json(['message' => 'User deleted successfully.']);
    }
}
