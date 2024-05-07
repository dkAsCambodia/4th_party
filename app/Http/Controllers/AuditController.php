<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Audit;
use Yajra\DataTables\DataTables;

class AuditController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Audit::with('user:id,name')
                ->select('id', 'event', 'auditable_type', 'ip_address', 'created_at', 'user_agent', 'user_id')
                ->get();

            return DataTables::of($data)
                ->addColumn('user_id', function ($data) {
                    return $data->user->name ?? '-';
                })
                ->editColumn('created_at', function ($data) {
                    return getAuthPreferenceTimezone($data->created_at);
                })
                ->editColumn('auditable_type', function ($data) {
                    return stripslashes(str_replace("App\Models", "", $data->auditable_type));
                })
                ->editColumn('user_agent', function ($data) {
                    return (strpos($data->user_agent, "Windows") == false) ? '--' : 'PC';
                })
                ->make(true);
        }

        return view('form.auditlist.auditListing');
    }
}
