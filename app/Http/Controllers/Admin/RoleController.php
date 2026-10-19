<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use App\Services\QueryFilterService;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;

class RoleController extends Controller
{
    protected $filterService;

    public function __construct(QueryFilterService $filterService)
    {
        $this->filterService = $filterService;
    }
    public function index(Request $request)
    {



        if ($request->ajax()) {
            $query = Role::query();
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
                                    data-url="' . route('admin.roles.destroy', $row->id) . '"
                                    data-table="dataTable">
                                <i class="ri-delete-bin-line"></i>
                            </button>
                        ';
                })

                ->addIndexColumn()
                ->make(true);
        }

         return view('admin.roles.index');
    }

    public function create()
    {
        return view('admin.roles.create');
    }

    public function edit(Role $role)
    {
        return view('admin.roles.edit', compact('role'));
    }

    public function destroy($id)
    {
        $role = Role::find($id);
        if (!$role) {
            return response()->json(['message' => 'Role not found.'], 404);
        }
        $protectedRoleIds = [1, 2, 3, 4];

        if (in_array($role->id, $protectedRoleIds)) {
            return response()->json(['message' => 'This role cannot be deleted.'], 403);
        }
        $role->delete();
        return response()->json(['message' => 'Role deleted successfully.']);
    }
}
