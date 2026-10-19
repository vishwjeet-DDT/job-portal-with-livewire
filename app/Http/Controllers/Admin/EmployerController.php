<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\QueryFilterService;
use Yajra\DataTables\Facades\DataTables;
use App\Models\Employer;

class EmployerController extends Controller
{
    protected $filterService;

    public function __construct(QueryFilterService $filterService)
    {
        $this->filterService = $filterService;
    }
    public function index(Request $request)
    {
        $employers = Employer::with('user')->get();

        if ($request->ajax()) {
            $query = Employer::with('user');
            $columns = $request->get('columns', []);
            $columns[] = ['data' => 'user.email', 'name' => 'user.email', 'searchable' => true];
            $columns[] = ['data' => 'user.mobile_number', 'name' => 'user.mobile_number', 'searchable' => true];
            $request->merge(['columns' => $columns]);

            return DataTables::of($query)
                ->filter(function ($q) use ($request) {
                    $this->filterService->handle($request, $q);

                })
                ->addColumn('user.name', function ($row) {
                    return "<span class='nameTexts'>" . (optional($row->user)->name ?? '-') . "</span><br>"
                        . (optional($row->user)->email ?? '') . "<br>"
                        . (optional($row->user)->mobile_number ?? '');
                })

               ->addColumn('company_name', function ($row) {
                    $image = '';
                    if (!empty($row->logo)) {
                        $imageUrl = asset('storage/' . $row->logo);
                        $image = "<img src='{$imageUrl}' width='70' alt='Profile Image'>";
                    }

                    return "<div class='d-flex flex-column align-items-center'> {$image}<br><span class='nameTexts text-center'>{$row->company_name}</span></div>";
                })

                ->addColumn('status', function ($row) {
                        $checked = optional($row->user)->status == 'active' ? 'checked' : '';
                        return '
                            <div class="form-check form-switch">
                                <input class="form-check-input toggle-status" type="checkbox"
                                    data-id="' . $row->user_id . '"
                                    ' . $checked . '>
                            </div>
                        ';
                    })

                ->addColumn('action', function ($row) {
                $editUrl = route('admin.employers.edit', $row->id);
                $deleteUrl = route('admin.employers.destroy', $row->id);

                return '
                    <button class="btn btn-outline-warning btn-sm mr-1" onclick="window.location.href=\'' . $editUrl . '\'">
                        <i class="ri-edit-box-line"></i>
                    </button>
                    <button type="button"
                            class="btn btn-outline-danger btn-sm delete-button"
                            data-url="' . $deleteUrl . '"
                            data-table="dataTable">
                        <i class="ri-delete-bin-line"></i>
                    </button>
                ';
            })


                ->addIndexColumn()
                 ->rawColumns(['user.name', 'action','status','company_name'])
                ->make(true);
        }
        return view('admin.employers.index',compact('employers'));
    }

    public function create()
    {
        return view('admin.employers.create');
    }

    public function edit($id)
    {
        return view('admin.employers.edit',compact('id'));
    }

    public function destroy($id)
    {
        $Employer = Employer::find($id);
        if (!$Employer) {
            return response()->json(['message' => 'Employer not found.'], 404);
        }
        if ($Employer->user) {
            $Employer->user->delete();
        }
        $Employer->delete();
        return response()->json(['message' => 'Employer deleted successfully.']);
    }
}
