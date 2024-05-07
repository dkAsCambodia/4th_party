<?php

namespace App\Http\Controllers;

use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use DB;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class PaymentMethodController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = PaymentMethod::select('id', 'method_name', 'created_at', 'status')->get();

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

                    if (auth()->user()->can('Method: Update Method')) {
                        $action .= '
                            <a class="btn btn-primary shadow btn-xs sharp me-1 edit_method"
                                href="#" data-toggle="modal" data-target="#edit_user"
                                data-id="' . $data->id . '" data-name="' . $data->method_name . '"
                                data-status="' . $data->status . '">
                                <i class="fas fa-pencil-alt"></i>
                            </a>
                        ';
                    }

                    if (auth()->user()->can('Method: Delete Method')) {
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
                ->rawColumns(['action', 'status'])
                ->make(true);
        }

        return view('form.payment.methodTable');
    }

    public function store(Request $request)
    {
        $messages = [
            'required' => __('validation.The :attribute field is required.'),
            'regex'    =>  __('validation.The :attribute must be alphabets.'),
        ];

        $validator = Validator::make($request->all(), [
            //'method_name' => 'required|regex:/^[\pL\s\-]+$/u',
            'status'       => 'required',
        ], $messages);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()]);
        }

        DB::beginTransaction();
        try {

            $addRecord = [
                'method_name' => $request->method_name,
                'status' => $request->status,
            ];
            PaymentMethod::create($addRecord);
            $success_msg = __('messages.Added Successfully');
            $success = __('messages.Success');
            Toastr::success($success_msg, $success);

            DB::commit();
            return redirect()->back();
        } catch (\Exception $e) {
            DB::rollback();
            $error_msg =  __('messages.fail, Add Method Name');
            Toastr::error($error_msg, 'Error');
            return redirect()->back();
        }
    }

    public function update(Request $request)
    {
        $method = PaymentMethod::findOrFail($request->id);
        $messages = [
            'required' => __('validation.The :attribute field is required.'),
            'regex'    =>  __('validation.The :attribute must be alphabets.'),
        ];

        $validator = Validator::make($request->all(), [
            //'method_name' => 'required|regex:/^[\pL\s\-]+$/u',
            'status'       => 'required',
        ], $messages);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()]);
        }

        DB::beginTransaction();
        try {
            $method->update([
                'method_name' => $request->method_name,
                'status' => $request->status,
            ]);
            $messages2 =  __('messages.Updated Successfully');
            $success = __('messages.Success');
            Toastr::success($messages2, $success);
            DB::commit();
            return redirect()->back();
        } catch (\Exception $e) {
            DB::rollback();
            $messages2_error =  __('messages.fail, Update Method Name');
            Toastr::error($messages2_error, 'Error');
            return redirect()->back();
        }
    }

    public function deleteRecord(Request $request)
    {
        try {
            PaymentMethod::destroy($request->id);
            $delMsg = __('messages.Deleted Successfully');
            $success = __('messages.Success');
            Toastr::success($delMsg, $success);
            return redirect()->back();
        } catch (\Exception $e) {

            DB::rollback();
            $delMsg_error = __('messages.Method delete fail');
            Toastr::error($delMsg_error, 'Error');
            return redirect()->back();
        }
    }
}
