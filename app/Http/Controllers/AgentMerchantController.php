<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Merchant;
use App\Models\Agent;
use Illuminate\Http\Request;
use DB;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\DataTables;

class AgentMerchantController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Merchant::where('agent_id', auth()->user()->agent_id)
                ->select('id', 'merchant_name', 'merchant_code', 'agent_id', 'created_at', 'status')->get();

            return DataTables::of($data)
                ->editColumn('status', function ($data) {
                    return $data->status == 'Enable'
                        ? '<span class="badge light badge-success">' . trans('messages.Enable') . '</span>'
                        : '<span class="badge light badge-danger">' . trans('messages.Disable') . '</span>';
                })
                ->editColumn('created_at', function ($data) {
                    return getAuthPreferenceTimezone($data->created_at);
                })
                ->rawColumns(['status'])
                ->make(true);
        }

        return view('form.agentMerchant.AgentmerchantTable');
    }
}
