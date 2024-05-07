<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LoginLog;
use Yajra\DataTables\DataTables;

class LoginLogController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = LoginLog::with('user:id,name,role_name')
                ->select('id', 'ip_address', 'created_at', 'user_agent', 'user_id')
                ->get();

            return DataTables::of($data)
                ->addColumn('user_id', function ($data) {
                    return $data->user->name ?? '-';
                })
                ->addColumn('user_role', function ($data) {
                    return $data->user->role_name ?? '-';
                })
                ->editColumn('created_at', function ($data) {
                    return getAuthPreferenceTimezone($data->created_at);
                })
                ->editColumn('user_agent', function ($data) {
                    return (strpos($data->user_agent, "Windows") == false) ? '--' : 'PC';
                })
                ->make(true);
        }

        return view('form.loginlog.loginloglist');
    }
}
