<?php

namespace App\Http\Controllers;

use App\Models\Bank;
use App\Models\Setting;
use Illuminate\Http\Request;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use DB;
use Yajra\DataTables\DataTables;

class SettingController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $messages = [
            'required' => __('validation.The :attribute field is required.'),
            'unique'   => __('validation.The :attribute must be unique.'),
            'regex'    => __('validation.The :attribute must be alphabets.')
        ];

        $validator = Validator::make($request->all(), [
            'account_type' => 'required|regex:/^[\pL\s\-]+$/u|unique:banks',
            'status' => 'required'
        ], $messages);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()]);
        }

        DB::beginTransaction();
        try {

            $addRecord = [
                'account_type' => $request->account_type,
                'status'       => $request->status,
            ];
            Bank::create($addRecord);
            $message = __('messages.Added Successfully');
            $success = __('messages.Success');
            Toastr::success($message, $success);

            DB::commit();
            return redirect()->back();
        } catch (\Exception $e) {
            DB::rollback();
            Log::error($e);
            $message_error = __('messages.fail, Add Account Type');
            Toastr::error($message_error, 'Error');
            return redirect()->back();
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Setting  $setting
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $messages = [
            'required' => __('validation.The :attribute field is required.'),
            'unique'   => __('validation.The :attribute must be unique.'),
            'regex'    => __('validation.The :attribute must be alphabets.')
        ];

        $validator = Validator::make($request->all(), [
            'account_type' => 'required|regex:/^[\pL\s\-]+$/u|unique:banks,account_type,' . $request->accountID,
            'status' => 'required'
        ], $messages);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()]);
        }

        DB::beginTransaction();
        try {
            $account_types = Bank::where('id', $request->accountID)->first();
            if ($account_types) {
                $addRecord = [
                    'account_type' => $request->account_type,
                    'status'       => $request->status,
                ];
                $account_types->update($addRecord);
                $messages_success = __('messages.Updated Successfully');
                $success = __('messages.Success');
                Toastr::success($messages_success, $success);
            }
        } catch (\Exception $e) {
            Log::error($e);
            $messages_error = __('messages.fail, Add Account');
            Toastr::error($messages_error, 'Error');
        }
    }

    public function accountList(Request $request)
    {
        if ($request->ajax()) {
            $data = Bank::select('id', 'account_type', 'status', 'created_at')
                ->get();

            return DataTables::of($data)
                ->editColumn('created_at', function ($data) {
                    return getAuthPreferenceTimezone($data->created_at);
                })
                ->editColumn('status', function ($data) {
                    return $data->status == 'active'
                        ? '<span class="badge light badge-success">' . trans('messages.active') . '</span>'
                        : '<span class="badge light badge-danger">' . trans('messages.inactive') . '</span>';
                })
                ->addColumn('action', function ($data) {
                    return '
                        <a class="btn btn-primary shadow btn-xs sharp me-1 update_account" href="#" data-toggle="modal" data-target="#update_account" data-id="' . $data->id . '" data-account_type="' . $data->account_type . '" data-status="' . $data->status . '">
                            <i class="fas fa-pencil-alt"></i>
                        </a>

                        <a class="btn btn-danger shadow btn-xs sharp delete_account" href="#" data-toggle="modal" data-target="#delete_account" data-id="' . $data->id . '">
                            <i class="fa fa-trash"></i>
                        </a>
                    ';
                })
                ->rawColumns(['action', 'status'])
                ->make(true);
        }

        return view('form.settings.accountsettingsTable');
    }

    public function getAccountType(Request $request, $Id)
    {
        $accountType = Bank::where('id', $Id)->first();
        return response()->json($accountType);
    }

    public function delete(Request $request)
    {
        try {
            Bank::where('id', $request->id)->delete();
            $messages_succ = __('messages.Deleted Successfully');
            $success = __('messages.Success');
            Toastr::success($messages_succ, $success);
            return redirect()->back();
        } catch (\Exception $e) {
            DB::rollback();
            $messages_error = __('messages.Failed to delete');
            Toastr::error($messages_error, 'Error');
            return redirect()->back();
        }
    }
}
