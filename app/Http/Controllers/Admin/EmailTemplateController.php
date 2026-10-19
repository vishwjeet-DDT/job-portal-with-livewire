<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailTemplate;
use Illuminate\Http\Request;
use App\Services\QueryFilterService;
use Yajra\DataTables\Facades\DataTables;
class EmailTemplateController extends Controller
{
     protected $filterService;

    public function __construct(QueryFilterService $filterService)
    {
        $this->filterService = $filterService;
    }
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = EmailTemplate::query();
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
                                    data-url="' . route('admin.industries.destroy', $row->id) . '"
                                    data-table="dataTable">
                                <i class="ri-delete-bin-line"></i>
                            </button>
                        ';
                })

                ->addIndexColumn()
                ->make(true);
        }

         return view('admin.Emails.index');
    }

    public function destroy($id)
    {
        $emailTemplate = EmailTemplate::find($id);
        if (!$emailTemplate) {
            return response()->json(['message' => 'EmailTemplate not found.'], 404);
        }
        $emailTemplate->delete();
        return response()->json(['message' => 'EmailTemplate deleted successfully.']);
    }
}
