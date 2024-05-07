<?php

namespace App\Http\Controllers;

use App\Http\Requests\RoleFormRequest;
use App\Http\Requests\RoleFormUpdateRequest;
use App\Services\RoleService;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use stdClass;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class RoleController extends Controller
{
    public function __construct(private RoleService $roleService)
    {
    }

    public function store(Request $request)
    {
        $messages = [
            'required' => __('validation.The :attribute field is required.'),
            'unique' => __('validation.The :attribute must be unique.'),
            'array' => __('validation.The :attribute must be an array.'),
            'permissions.required' => __('validation.Please select at least one item from below.'),
        ];

        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:roles,name',
            'permissions' => 'required',
        ], $messages);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()]);
        }

        return $this->roleService->store($request->all());
    }

    public function update(Request $request)
    {
        $role = Role::where('id', $request->id)->first();

        $messages = [
            'required' => __('validation.The :attribute field is required.'),
            'unique' => __('validation.The :attribute must be unique.'),
            'array' => __('validation.The :attribute must be an array.'),
            'permissions.required' => __('validation.Please select at least one item from below.'),
        ];

        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:roles,name,' . $role->id,
            'permissions' => 'required',
        ], $messages);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()]);
        }

        return $this->roleService->update($role, $request->all());
    }

    public function delete(Request $request, Role $role)
    {
        return $this->roleService->delete($request, $role);
    }

    public function get(Role $role)
    {
        $permission = Permission::orderBy('name')->select('id', 'name')->get();

        $permissionsArrayParent = array();

        foreach ($permission as $p) {
            $child = new stdClass();
            $name = explode(':', $p->name);
            $id = $p->id;
            $child->id = $id;

            if (array_key_exists($name[0], $permissionsArrayParent)) {
                $parentObj = (array) $permissionsArrayParent[$name[0]];
                if (count($name) > 1) {
                    $child->name = $name[1];
                    array_push($parentObj, $child);
                }
                $permissionsArrayParent[$name[0]] = $parentObj;
            } else {
                if (count($name) > 1) {
                    $parentObj = array();
                    $child->name = $name[1];
                    array_push($parentObj, $child);
                    $permissionsArrayParent[$name[0]] = $parentObj;
                }
            }
        }

        $permissions = $permissionsArrayParent;


        $asignPermissions = DB::table('permissions')
            ->join('role_has_permissions', 'permissions.id', '=', 'role_has_permissions.permission_id')

            ->select('permissions.*')
            ->where('role_has_permissions.role_id', $role->id)
            ->get();

        $assPer = [];
        foreach ($asignPermissions as $temp) {
            $assPer[] = $temp->id;
        }



        return view('roles.updateRole', compact('permissions', 'assPer', 'role'));
        // return response()->json($role->load('permissions'), 200);
    }

    public function paginate(Request $request)
    {
        return $this->roleService->paginate($request);
    }

    public function roles()
    {
        return response()->json(Role::with('permissions')->get(), 200);
    }

    public function users(Role $role)
    {
        return response()->json($role->users, 200);
    }

    public function paginatePermissions(Request $request)
    {
        return $this->roleService->paginatePermissions($request);
    }

    public function permissions()
    {
        return response()->json(Permission::orderBy('name')->get(), 200);
    }

    public function all(Request $request)
    {
        $permission = Permission::orderBy('name')->select('id', 'name')->get();

        $permissionsArrayParent = array();

        foreach ($permission as $p) {
            $child = new stdClass();
            $name = explode(':', $p->name);
            $id = $p->id;

            $child->id = $id;

            if (array_key_exists($name[0], $permissionsArrayParent)) {
                $parentObj = (array) $permissionsArrayParent[$name[0]];
                if (count($name) > 1) {
                    $child->name = $name[1];
                    array_push($parentObj, $child);
                }
                $permissionsArrayParent[$name[0]] = $parentObj;
            } else {
                if (count($name) > 1) {
                    $parentObj = array();
                    $child->name = $name[1];
                    array_push($parentObj, $child);
                    $permissionsArrayParent[$name[0]] = $parentObj;
                }
            }
        }

        $permissions = $permissionsArrayParent;

        if ($request->ajax()) {
            $data = Role::select('id', 'name', 'created_at', 'remarks')
                ->where('id', '!=', 1)
                ->get();

            return DataTables::of($data)
                ->editColumn('created_at', function ($data) {
                    return getAuthPreferenceTimezone($data->created_at);
                })
                ->addColumn('action', function ($data) {
                    $action = '';
                    if (auth()->user()->can('Role: Edit/Update Role')) {
                        $action .= '
                            <a class="btn btn-primary shadow btn-xs sharp me-1 edit_role" href="' . route("Role: Show Role", $data->id) . '">
                                <i class="fas fa-pencil-alt"></i>
                            </a>
                        ';
                    }
                    if (auth()->user()->can(route('Channel: Delete Channel'))) {
                        $action .= '
                            <a class="btn btn-danger shadow btn-xs sharp delete_user" href="#" data-toggle="modal" data-target="#delete_role" data-id="' . $data->id . '">
                                <i class="fa fa-trash"></i>
                            </a>
                        ';
                    }
                    return $action;
                })
                ->make(true);
        }

        return view('roles.roleTable', compact('permissions'));
    }

    public function keyValue(Request $request)
    {
        // code...
        return $this->roleService->keyValue($request);
    }
}
