<?php

namespace App\Http\Controllers;

use App\Models\PaymentUrl;
use App\Models\PaymentChannel;
use App\Models\PaymentMethod;
//use App\Models\PaymentSource;
use Illuminate\Http\Request;
use DB;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class PaymentUrlController extends Controller
{
    public function index(Request $request)
    {
        $channel = PaymentChannel::where('status', 'Enable')->get();
        $method = PaymentMethod::where('status', 'Enable')->get();
        //$source = PaymentSource::where('status', 'Enable')->get();

        if ($request->ajax()) {
            $data = PaymentUrl::query()
                //->with('channel:id,channel_name', 'method:id,method_name', 'source:id,source_name');
                ->with('channel:id,channel_name', 'method:id,method_name');

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('channel_name', function ($data) {
                    return $data->channel->channel_name ?? '-';
                })
                ->addColumn('method_name', function ($data) {
                    return $data->method->method_name ?? '-';
                })
                /* ->addColumn('source_name', function ($data) {
                    return $data->source->source_name ?? '-';
                }) */
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

                    if (auth()->user()->can('PaymentUrl: Update PaymentUrl')) {
                        $action .= '
                            <a class="btn btn-primary shadow btn-xs sharp me-1 edit_url" href="#"
                                data-toggle="modal" data-target="#edit_user"
                                data-id="' . $data->id . '" data-url="' . $data->url . '"
                                data-merchantkey="' . $data->merchant_key . '"
                                data-merchantcode="' . $data->merchant_code . '"
                                data-pre_sign="' . $data->sign_pre . '"
                                data-channel="' . $data->channel_id . '"
                                data-method="' . $data->method_id . '"
                                data-status="' . $data->status . '">
                                <i class="fas fa-pencil-alt"></i>
                            </a>
                        ';
                    }

                    if (auth()->user()->can('PaymentUrl: Delete PaymentUrl')) {
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
                    if ($request->channel) {
                        $data->where('channel_id', $request->channel);
                    }

                    if ($request->method) {
                        $data->where('method_id', $request->method);
                    }

                    /* if ($request->source) {
                        $data->where('source_id', $request->source);
                    } */

                    if (!empty($request->search)) {
                        $data->where(function ($q) use ($request) {
                            $q->orWhere('url_name', 'LIKE', '%' . $request->search . '%')
                                ->orWhere('status', 'LIKE', '%' . $request->search . '%')
                                ->orWhere('created_at', 'LIKE', '%' . $request->search . '%');
                            $q->whereHas('channel', function ($q) use ($request) {
                                $q->where('channel_name', 'like', '%' . $request->search . '%');
                            });
                            $q->whereHas('method', function ($q) use ($request) {
                                $q->where('method_name', 'like', '%' . $request->search . '%');
                            });
                            /* $q->whereHas('source', function ($q) use ($request) {
                                $q->where('source_name', 'like', '%' . $request->search . '%');
                            }); */
                        });
                    }
                })
                ->rawColumns(['action', 'status'])
                ->make(true);
        }

        return view('form.payment.urlTable', compact('channel', 'method'));
    }

    public function store(Request $request)
    {
        $messages = [
            'required' => __('validation.The :attribute field is required.'),
            'url'    => __('validation.The :attribute must be url.'),
            'regex' => __('validation.The :attribute must be alpha numerics and dash.'),
        ];

        $validator = Validator::make($request->all(), [
            'url' => 'required|url',
            'merchant_key' => 'required|regex:/^[a-zA-Z0-9\s-]+$/',
            'merchant_code' => 'required|regex:/^[a-zA-Z0-9\s-]+$/',
            //'pre_sign' => 'required|regex:/^[a-zA-Z0-9\s-]+$/',
            'channel_id' => 'required',
            'method_id' => 'required',
            //'source_id' => 'required',
            'status'       => 'required',
        ], $messages);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()]);
        }

        DB::beginTransaction();
        try {

            $channel = PaymentChannel::where('id', $request->channel_id)->first();
            $method = PaymentMethod::where('id', $request->method_id)->first();
            //$source = PaymentSource::where('id', $request->source_id)->first();

            $addRecord = [
                'url_name' => $channel->channel_name . ' ' . $method->method_name,
                'status' => $request->status,
                'channel_id' => $request->channel_id,
                'method_id' => $request->method_id,
                //'source_id' => $request->source_id,
                'url' => $request->url,
                'merchant_key' => $request->merchant_key,
                'merchant_code' => $request->merchant_code,
                'sign_pre' => $request->pre_sign,
            ];
            PaymentUrl::create($addRecord);
            $messages = __('messages.Added Successfully');
            $success = __('messages.Success');
            Toastr::success($messages, 'Success');
            DB::commit();
            return redirect()->back();
        } catch (\Exception $e) {
            DB::rollback();
            $messages_error = __('messages.fail, Add Url Name');
            Toastr::error($messages_error, 'Error');
            return redirect()->back();
        }
    }

    public function update(Request $request)
    {
        $paymentUrl = PaymentUrl::findOrFail($request->id);
        $messages = [
            'required' => __('validation.The :attribute field is required.'),
            'url'    => __('validation.The :attribute must be url.'),
            'regex' => __('validation.The :attribute must be alpha numerics and dash.'),
        ];

        $validator = Validator::make($request->all(), [
            'url' => 'required|url',
            'merchant_key' => 'required|regex:/^[a-zA-Z0-9\s-]+$/',
            'merchant_code' => 'required|regex:/^[a-zA-Z0-9\s-]+$/',
            //'pre_sign' => 'required|regex:/^[a-zA-Z0-9\s-]+$/',
            'channel_id' => 'required',
            'method_id' => 'required',
            //'source_id' => 'required',
            'status'       => 'required',
        ], $messages);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()]);
        }

        DB::beginTransaction();
        try {
            $channel = PaymentChannel::where('id', $request->channel_id)->first();
            $method  = PaymentMethod::where('id', $request->method_id)->first();
            //$source  = PaymentSource::where('id', $request->source_id)->first();

            $editRecord = [
                'url_name' => $channel->channel_name . ' ' . $method->method_name,
                'status' => $request->status,
                'channel_id' => $request->channel_id,
                'method_id' => $request->method_id,
                //'source_id' => $request->source_id,

                'url' => $request->url,
                'merchant_key' => $request->merchant_key,
                'merchant_code' => $request->merchant_code,
                'sign_pre' => $request->pre_sign,
            ];
            $paymentUrl->update($editRecord);
            $msg_updated = __('messages.Updated Successfully');
            $success = __('messages.Success');
            Toastr::success($msg_updated, $success);
            DB::commit();
            return redirect()->back();
        } catch (\Exception $e) {
            DB::rollback();
            $msg_error = __('messages.Failed to delete');
            Toastr::error($msg_error, 'Error');
            return redirect()->back();
        }
    }

    public function deleteRecord(Request $request)
    {
        try {

            PaymentUrl::destroy($request->id);
            $delMsg_success = __('messages.Deleted successfully');
            $success = __('messages.Success');
            Toastr::success($delMsg_success, $success);
            return redirect()->back();
        } catch (\Exception $e) {
            DB::rollback();
            $delMsg_error = __('messages.Failed to delete');
            Toastr::error($delMsg_error, 'Error');
            return redirect()->back();
        }
    }
}
