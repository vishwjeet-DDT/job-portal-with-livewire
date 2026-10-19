<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Permission;
use App\Services\QueryFilterService;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PermissionController extends Controller
{
    protected $filterService;

    public function __construct(QueryFilterService $filterService)
    {
        $this->filterService = $filterService;
    }
    public function index(Request $request)
    {
         $data = Permission::whereNull('parent_id')->get();
        if ($request->ajax()) {
            $permissions = Permission::whereNull('parent_id')
                            ->with('children')
                            ->get();

            $hierarchicalPermissions = [];
            foreach ($permissions as $parent) {
                $hierarchicalPermissions[] = $parent;
                foreach ($parent->children as $child) {
                    $hierarchicalPermissions[] = $child;
                }
            }
            // dd(collect($hierarchicalPermissions));
            return DataTables::of(collect($hierarchicalPermissions))
                ->addColumn('label', function ($row) {
                    $isParent = is_null($row->parent_id);

                    $label = Str::title(Str::replace('.', ' ', $row->name));
                    $label =$row->label ?? $label;
                    if ($isParent) {
                        $label = '<strong>' . $label . '</strong>';
                    } else {
                        $label = '&nbsp;&nbsp;&nbsp; ⇨ ' . $label;
                    }

                    return $label;
                })
                ->addColumn('action', function ($row) {
                    return '<button class="btn btn-outline-warning btn-sm mx-2" onclick="openEditModal(' . $row->id . ')">
                                <i class="ri-edit-box-line"></i>
                            </button>' .
                            '<button type="button"
                                class="btn btn-outline-danger btn-sm delete-button"
                                data-url="' . route('admin.permissions.destroy', $row->id) . '"
                                data-table="dataTable">
                                <i class="ri-delete-bin-line"></i>
                            </button>';
                })
                ->rawColumns(['label','action'])
                ->make(true);
        }

        return view('admin.permission.index');
    }



    public function destroy($id)
    {
        $permission = Permission::findOrFail($id);
        $permission->delete();

        return response()->json(['message' => 'Permission deleted successfully.']);
    }

    public function assing_permission(Request $request)
    {
        return view('admin.roles.assing_permission');
    }
}

