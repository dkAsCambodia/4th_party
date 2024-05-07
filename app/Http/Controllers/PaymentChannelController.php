<?php

namespace App\Http\Controllers;

use App\Models\ParameterSetting;
use App\Models\PaymentChannel;
use App\Models\PaymentMap;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class PaymentChannelController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = PaymentChannel::select('id', 'channel_name', 'created_at', 'status')->get();

            return DataTables::of($data)
                ->addIndexColumn()
                ->editColumn('status', function ($data) {
                    return $data->status == 'Enable'
                        ? '<span class="badge light badge-success">' . trans('messages.Enable') . '</span>'
                        : '<span class="badge light badge-danger">' . trans('messages.Disable') . '</span>';
                })
                ->editColumn('created_at', function ($data) {
                    return getAuthPreferenceTimezone($data->created_at);
                })
                ->addColumn('action', function ($data) {
                    $action = '<div class="d-flex">';

                    // if (auth()->user()->can('Channel: Update Channel')) {
                    //     $action .= '
                    //         <a onclick="addParameter(' . $data->id . ');" class="btn btn-danger shadow btn-xs sharp me-1 add_parameter"
                    //             href="#" data-toggle="modal" data-target="#add_parameter">
                    //             <i class="fa fa-plus"></i>
                    //         </a>
                    //     ';
                    // }

                    if (auth()->user()->can('Channel: Update Channel')) {
                        $action .= '
                            <a class="btn btn-primary shadow btn-xs sharp me-1 edit_channel"
                                href="#" data-toggle="modal" data-target="#edit_channel"
                                data-id="' . $data->id . '" data-name="' . $data->channel_name . '"
                                data-status="' . $data->status . '">
                                <i class="fas fa-pencil-alt"></i>
                            </a>
                        ';
                    }

                    if (auth()->user()->can('Channel: Delete Channel')) {
                        $action .= '
                            <a class="btn btn-danger shadow btn-xs sharp delete_channel" href="#"
                                data-toggle="modal" data-target="#delete_channel" data-id="' . $data->id . '">
                                <i class="fa fa-trash"></i>
                            </a>
                        ';
                    }

                    $action .= '</div>';

                    return $action;
                })
                ->rawColumns(['action', 'status'])
                ->make(true);
        }

        return view('form.payment.channelTable');
    }

    public function store(Request $request)
    {
        $messages = [
            'required' => __('validation.The :attribute field is required.'),
            'regex'    =>  __('validation.The :attribute must be alphabets.'),
        ];

        $validator = Validator::make($request->all(), [
            //'channel_name' => 'required|regex:/^[\pL\s\-]+$/u',
            'status'       => 'required',
        ], $messages);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()]);
        }

        DB::beginTransaction();
        try {

            $addRecord = [
                'channel_name' => $request->channel_name,
                'status' => $request->status,
            ];
            $paymentChannel = PaymentChannel::create($addRecord);

            if ($request->parameter_name && $paymentChannel->id) {
                foreach ($request->parameter_name as $parameter) {
                    if ($parameter) {
                        ParameterSetting::create([
                            'channel_id' => $paymentChannel->id,
                            'parameter_name' => $parameter,
                        ]);
                    }
                }
            }

            $messages1 = __('messages.Added Successfully');
            $success = __('messages.Success');
            Toastr::success($messages1, $success);
            DB::commit();
            return redirect()->back();
        } catch (\Exception $e) {
            DB::rollback();
            $errormsg = __('messages.fail, Add Channel Name');
            Toastr::error($errormsg, 'Error');
            return redirect()->back();
        }
    }

    public function update(Request $request)
    {
        $channel = PaymentChannel::findOrFail($request->id);
        $messages = [
            'required' => __('validation.The :attribute field is required.'),
            'regex'    =>  __('validation.The :attribute must be alphabets.'),
        ];

        $validator = Validator::make($request->all(), [
            //'channel_name' => 'required|regex:/^[\pL\s\-]+$/u',
            'status'       => 'required',
        ], $messages);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()]);
        }

        DB::beginTransaction();
        try {
            $channel->update([
                'channel_name' => $request->channel_name,
                'status' => $request->status,
            ]);
            $messages2 = __('messages.Updated Successfully');
            $success = __('messages.Success');
            Toastr::success($messages2, $success);
            DB::commit();
            return redirect()->back();
        } catch (\Exception $e) {
            DB::rollback();
            $errormsg1 = __('messages.fail, Update Channel Name');
            Toastr::error($errormsg1, 'Error');
            return redirect()->back();
        }
    }

    public function deleteRecord(Request $request)
    {
        try {

            PaymentChannel::destroy($request->id);
            $succMsg =  __('messages.Deleted Successfully');
            $success = __('messages.Success');
            Toastr::success($succMsg, $success);
            return redirect()->back();
        } catch (\Exception $e) {

            DB::rollback();
            $errorMsg =  __('messages.Channel delete fail');
            Toastr::error($errorMsg, 'Error');
            return redirect()->back();
        }
    }
}
