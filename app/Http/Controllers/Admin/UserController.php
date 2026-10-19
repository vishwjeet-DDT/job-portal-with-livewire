<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Str;
use App\Services\QueryFilterService;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;
use App\Models\User;

class UserController extends Controller
{
     protected $filterService;

    public function __construct(QueryFilterService $filterService)
    {
        $this->filterService = $filterService;
    }

    public function user_list(Request $request, $name)
    {

        $roleName = str_replace('-', ' ', Str::title($name));
        if ($request->ajax()) {
            $roleName = str_replace('-', ' ', Str::title($name));
            $role = Role::where('name', $roleName)->first();
            $query = $role->users()->getQuery();
            // $result = $this->filterService->handle($request, $query);

            // dd($result);
            return DataTables::of($query)
                ->filter(function ($q) use ($request) {
                    $this->filterService->handle($request, $q);
                })
                ->addColumn('name', function ($row) {
                    return "<span class='nameTexts'>{$row->name}</span><br>{$row->email}";
                })
                ->addColumn('status', function ($row) {
                        $checked = $row->status == 'active' ? 'checked' : '';
                        return '
                            <div class="form-check form-switch">
                                <input class="form-check-input toggle-status" type="checkbox"
                                    data-id="' . $row->id . '"
                                    ' . $checked . '>
                            </div>
                        ';
                    })

                ->addColumn('action', function ($row) {
                    return '
                        <button class="btn btn-outline-warning btn-sm mx-2" onclick="openEditModal(' . e((string) $row->id) . ')">
                            <i class="ri-edit-box-line"></i>
                        </button>
                        <button type="button"
                                class="btn btn-outline-danger btn-sm delete-button"
                                data-url="' . route('admin.users.destroy', $row->id) . '"
                                data-table="dataTable">
                            <i class="ri-delete-bin-line"></i>
                        </button>
                    ';
                })

                ->addIndexColumn()
                 ->rawColumns(['name', 'action','status'])
                ->make(true);
        }

        return view('admin.users.index', compact('roleName'));
    }

     public function destroy($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }
        $user->delete();
        return response()->json(['message' => 'User deleted successfully.']);
    }

    public function updateStatus(Request $request)
    {
        $user = User::find($request->id);
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'User not found']);
        }

        $user->status = $request->status;
        $user->save();

        return response()->json(['status' => true, 'message' => 'Status updated successfully']);
    }


}
