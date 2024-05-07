<?php

namespace App\Http\Controllers;

use App\Models\Timezone;
use Illuminate\Http\Request;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use DB;
use Yajra\DataTables\DataTables;

class TimezoneController extends Controller
{
    public function updateTimezone(Request $request)
    {
        auth()->user()->update([
            'timezone_id' => $request->tz,
        ]);

        return back();
    }

    public function store(Request $request)
    {
        $messages = [
            'required' => __('validation.The :attribute field is required.')
        ];

        $validator = Validator::make($request->all(), [
            'timezone' => 'required',
            'status' => 'required'
        ], $messages);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()]);
        }

        DB::beginTransaction();
        try {

            $addRecord = [
                'timezone' => $request->timezone,
                'status'       => $request->status,
            ];
            Timezone::create($addRecord);
            $message = __('messages.Added Successfully');
            $success = __('messages.Success');
            Toastr::success($message, $success);

            DB::commit();
            return redirect()->back();
        } catch (\Exception $e) {
            DB::rollback();
            Log::error($e);
            $message_error = __('messages.fail, Add Timezone');
            Toastr::error($message_error, 'Error');
            return redirect()->back();
        }
    }

    public function update(Request $request)
    {
        $messages = [
            'required' => __('validation.The :attribute field is required.')
        ];

        $validator = Validator::make($request->all(), [
            'timezone' => 'required',
            'status' => 'required'
        ], $messages);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()]);
        }
        try {
            $timezone = Timezone::where('id', $request->id)->first();

            if ($timezone) {
                $updateRecord = [
                    'timezone' => $request->timezone,
                    'status'       => $request->status,
                ];
                $timezone->update($updateRecord);
                $messages_success = __('messages.Updated Successfully');
                $success = __('messages.Success');
                Toastr::success($messages_success, $success);
            }
        } catch (\Exception $e) {
            Log::error($e);
            $messages_error = __('messages.fail, Update Timezone');
            Toastr::error($messages_error, 'Error');
        }
    }

    public function timezoneList(Request $request)
    {
        if ($request->ajax()) {
            $data = Timezone::select('id', 'timezone', 'status', 'created_at')
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
                        <a class="btn btn-primary shadow btn-xs sharp me-1 update_timezone" href="#" data-toggle="modal" data-target="#update_timezone" data-id="' . $data->id . '" data-timezone="' . $data->timezone . '" data-status="' . $data->status . '">
                            <i class="fas fa-pencil-alt"></i>
                        </a>

                        <a class="btn btn-danger shadow btn-xs sharp delete_timezone" href="#" data-toggle="modal" data-target="#delete_timezone" data-id="' . $data->id . '">
                            <i class="fa fa-trash"></i>
                        </a>
                    ';
                })
                ->rawColumns(['action', 'status'])
                ->make(true);
        }

        return view('form.settings.timezonesettingsTable');
    }

    public function delete(Request $request)
    {
        try {
            Timezone::where('id', $request->id)->delete();
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
