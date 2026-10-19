<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Language;
use App\Services\QueryFilterService;
use Yajra\DataTables\Facades\DataTables;

class LanguagController extends Controller
{
    protected $filterService;

    public function __construct(QueryFilterService $filterService)
    {
        $this->filterService = $filterService;
    }
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Language::query();
            return DataTables::of($query)
                ->filter(function ($q) use ($request) {
                    $this->filterService->handle($request, $q);
                })
                ->addColumn('name', function ($row) {
                    return ucwords($row->name);
                })
                ->addColumn('action', function ($row) {

                    return '<button class="btn btn-outline-warning btn-sm mr-2" onclick="openEditModal(' . $row->id . ')">
                                <i class="ri-edit-box-line"></i>
                            </button>' .
                        '
                            <button type="button"
                                    class="btn btn-outline-danger btn-sm delete-button"
                                    data-url="' . route('admin.languages.destroy', $row->id) . '"
                                    data-table="dataTable">
                                <i class="ri-delete-bin-line"></i>
                            </button>
                        ';
                })

                ->addIndexColumn()
                ->make(true);
        }

         return view('admin.languages.index');
    }

    public function destroy($id)
    {
        $lan = Language::find($id);
        if (!$lan) {
            return response()->json(['message' => 'Language not found.'], 404);
        }
        $lan->delete();
        return response()->json(['message' => 'Language deleted successfully.']);
    }
}
