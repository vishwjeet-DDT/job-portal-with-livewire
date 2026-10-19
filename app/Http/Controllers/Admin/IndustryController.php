<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Industry;
use Illuminate\Http\Request;
use App\Services\QueryFilterService;
use Yajra\DataTables\Facades\DataTables;


class IndustryController extends Controller
{

    protected $filterService;

    public function __construct(QueryFilterService $filterService)
    {
        $this->filterService = $filterService;
    }
    public function index(Request $request)
    {



        if ($request->ajax()) {
            $query = Industry::query();
            return DataTables::of($query)
                ->filter(function ($q) use ($request) {
                    $this->filterService->handle($request, $q);
                })
                ->addColumn('name', function ($row) {
                    return ucwords($row->name);
                })
                ->addColumn('industryType.name', function ($row) {
                    return optional($row->industryType)->name ?? 'N/A';
                })
                ->addColumn('action', function ($row) {

                    return '<button class="btn btn-outline-warning btn-sm mr-2" onclick="openEditModal(' . $row->id . ')">
                                <i class="ri-edit-box-line"></i>
                            </button>' .
                        '
                            <button type="button"
                                    class="btn btn-outline-danger btn-sm delete-button"
                                    data-url="' . route('admin.industries.destroy', $row->id) . '"
                                    data-table="dataTable">
                                <i class="ri-delete-bin-line"></i>
                            </button>
                        ';
                })

                ->addIndexColumn()
                ->make(true);
        }

         return view('admin.industries.index');
    }

      public function destroy($id)
    {
        $Industry = Industry::find($id);
        if (!$Industry) {
            return response()->json(['message' => 'Industry not found.'], 404);
        }
        $Industry->delete();
        return response()->json(['message' => 'Industry deleted successfully.']);
    }
}
