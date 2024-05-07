<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Timezone;
use DB;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class UserManagementController extends Controller
{
    // index page
    public function index(Request $request)
    {
        $roles = Role::get();

        $timezones = Timezone::where('status', 'active')->get();

        if ($request->ajax()) {
            $data = User::query()->with('role:id,name')
                ->where('role_name', 'Admin')
                ->select('id', 'user_name', 'name', 'mobile_number', 'email', 'role_name', 'status', 'created_at', 'role_id', 'timezone_id');

            return DataTables::of($data)
                ->editColumn('role_name', function ($data) {
                    return $data->role?->name;
                })
                ->editColumn('status', function ($data) {
                    return $data->status == 'active'
                        ? '<span class="badge light badge-success">' . trans('messages.active') . '</span>'
                        : '<span class="badge light badge-danger">' . trans('messages.inactive') . '</span>';
                })
                ->editColumn('created_at', function ($data) {
                    return getAuthPreferenceTimezone($data->created_at);
                })
                ->addColumn('action', function ($data) {
                    $action = '<div class="d-flex">';

                    if (auth()->user()->can('User: Update User')) {
                        $action .= '
                            <a class="btn btn-primary shadow btn-xs sharp me-1 edit_user" href="#"
                                data-toggle="modal" data-target="#edit_user"
                                data-id="' . $data->id . '"
                                data-user_name="' . $data->user_name . '"
                                data-nick_name="' . $data->name . '"
                                data-mobile_number="' . $data->mobile_number . '"
                                data-email="' . $data->email . '"
                                data-role_name="' . $data->role_name . '"
                                data-role_id="' . $data->role_id . '"
                                data-timezone="' . $data->timezone_id . '"
                                data-created_at="' . $data->created_at . '">
                                <i class="fas fa-pencil-alt"></i>
                            </a>
                        ';
                    }

                    if (auth()->user()->can('User: Delete User')) {
                        $action .= '
                            <a class="btn btn-danger shadow btn-xs sharp delete_user" href="#"
                                data-toggle="modal" data-target="#delete_user" data-id="' . $data->id . '">
                                <i class="fa fa-trash"></i>
                            </a>
                        ';
                    }
                    $action .= '</div>';
                    return $action;
                })
                ->filter(function ($data) use ($request) {
                    if ($request->filterRole) {
                        $data->where('role_id', $request->filterRole);
                    }

                    if (!empty($request->search)) {
                        $data->where(function ($q) use ($request) {
                            $q->orWhere('user_name', 'LIKE', '%' . $request->search . '%')
                                ->orWhere('name', 'LIKE', '%' . $request->search . '%')
                                ->orWhere('status', 'LIKE', '%' . $request->search . '%')
                                ->orWhere('created_at', 'LIKE', '%' . $request->search . '%');
                        });
                    }
                })
                ->rawColumns(['action', 'status'])
                ->make(true);
        }

        return view('usermanagement.usertable', compact('roles', 'timezones'));
    }

    // add record
    public function addRecord(Request $request)
    {
        $messages = [
            'required' => __('validation.The :attribute field is required.'),
            'string'   => __('validation.The :attribute must be text format'),
            'user_name.min' => __('validation.The :attribute must be minimum 6.'),
            'user_name.max' => __('validation.The :attribute must be maximum 15.'),
            'nick_name.min' => __('validation.The :attribute must be minimum 4.'),
            'nick_name.max' => __('validation.The :attribute must be maximum 15.'),
            'regex'  => __('validation.The :attribute must be alphabets.'),
            'unique' =>  __('validation.The :attribute must be unique.'),
            'mobile_number.regex'  => __('validation.The :attribute must be numbers.'),
            'email'  => __('validation.The :attribute must be email.'),
            'password.min'  => __('validation.The :attribute must be minimum 8.'),
        ];
        $validator = Validator::make($request->all(), [
            'user_name' => 'required|min:6|max:15|regex:/^[\pL\s]+$/u|unique:users,user_name',
            'nick_name' => 'required|min:4|max:15|regex:/^[\pL\s]+$/u|unique:users,name',
            'role' => 'required',
            'password'  => 'required|string|min:6|max:15',
            'email'  => 'required|email',
            'timezone'  => 'required',
            'mobile_number'  => 'required|regex:/^[0-9]+$/',
        ], $messages);

        if ($validator->passes()) {

            $user = User::create([
                'user_name'      => $request->user_name,
                'name'      => $request->nick_name,
                'email'     => $request->email,
                'mobile_number' => $request->mobile_number,
                'role_name' => "Admin",
                'status' => "active",
                'password'  => Hash::make($request->password),
                'role_id' => $request->role,
                'timezone_id' => $request->timezone,
            ]);

            $user->assignRole(Role::whereId($request->role)->first());

            $msgSuccess =  __('messages.Added Successfully');

            $success = __('messages.Success');

            Toastr::success($msgSuccess, $success);

            return response()->json(['success' => $msgSuccess]);
        }

        return response()->json(['error' => $validator->errors()]);
    }

    // update record
    public function updateRecord(Request $request)
    {
        $messages = [
            'required' => __('validation.The :attribute field is required.'),
            'string'   => __('validation.The :attribute must be text format'),
            'user_name.min' => __('validation.The :attribute must be minimum 6.'),
            'user_name.max' => __('validation.The :attribute must be maximum 15.'),
            'nick_name.min' => __('validation.The :attribute must be minimum 4.'),
            'nick_name.max' => __('validation.The :attribute must be maximum 15.'),
            'regex'  => __('validation.The :attribute must be alphabets.'),
            'unique' =>  __('validation.The :attribute must be unique.'),
            'mobile_number.regex'  => __('validation.The :attribute must be numbers.'),
            'email'  => __('validation.The :attribute must be email.'),
            'password.min'  => __('validation.The :attribute must be minimum 8.'),
        ];
        $validator = Validator::make($request->all(), [
            'user_name' => 'required|min:6|max:15|regex:/^[\pL\s]+$/u|unique:users,user_name,' . $request->userId,
            'nick_name' => 'required|min:4|max:15|regex:/^[\pL\s]+$/u|unique:users,name,' . $request->userId,
            'role' => 'required',
            'password'  => 'required|string|min:6|max:15',
            'email'  => 'required|email',
            'timezone'  => 'required',
            'mobile_number'  => 'required|regex:/^[0-9]+$/',
        ], $messages);

        if ($validator->passes()) {
            $user = User::find($request->userId);

            $user->update([
                'user_name'      => $request->user_name,
                'name'      => $request->nick_name,
                //'avatar'    => $request->image,
                'email'     => $request->email,
                'mobile_number' => $request->mobile_number,
                'role_name' => "Admin",
                'status' => "active",
                'password'  => Hash::make($request->password),
                'role_id' => $request->role,
                'timezone_id' => $request->timezone,
            ]);

            $user->assignRole(Role::whereId($request->role)->first());

            $msgSuccess =  __('messages.Updated Successfully');
            $success = __('messages.Success');
            Toastr::success($msgSuccess, $success);

            return response()->json(['success' => $msgSuccess]);
        }

        return response()->json(['error' => $validator->errors()]);
    }

    /** delete record */
    public function deleteRecord(Request $request)
    {
        try {
            User::destroy($request->id);
            $msgSuccess = __('messages.Deleted Successfully');
            $success = __('messages.Success');
            Toastr::success($msgSuccess, $success);
            return redirect()->back();
        } catch (\Exception $e) {

            DB::rollback();
            $msgErr = __('messages.Failed to delete');
            Toastr::error($msgErr, 'Error');
            return redirect()->back();
        }
    }

    /** profile user */
    public function profileUser()
    {
        return view('usermanagement.userprofile');
    }

    /** get latest merchant user by merchant id */
    public function getMerchantUser($merchantId)
    {
        Log::debug($merchantId);
        $user = User::whereMerchantId($merchantId)->latest()->first();
        return  response()->json([
            'data' => $user
        ]);
    }

    /** get latest agent user by agent id */
    public function getAgentUser($agentId)
    {
        Log::debug($agentId);
        $user = User::whereagentId($agentId)->latest()->first();
        return  response()->json([
            'data' => $user
        ]);
    }

    /** get role name and role id */
    public function getRoleName()
    {
        $roleList = Role::get();
        return  response()->json([
            'data' => $roleList
        ]);
    }
}
