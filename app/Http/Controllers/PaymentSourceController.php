<?php

namespace App\Http\Controllers;

use App\Models\PaymentSource;
use Illuminate\Http\Request;
use DB;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class PaymentSourceController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = PaymentSource::select('id', 'source_name', 'created_at', 'status')->get();

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

                    if (auth()->user()->can('Source: Update Source')) {
                        $action .= '
                            <a class="btn btn-primary shadow btn-xs sharp me-1 edit_source"
                                href="#" data-toggle="modal" data-target="#edit_user"
                                data-id="' . $data->id . '" data-name="' . $data->source_name . '"
                                data-status="' . $data->status . '">
                                <i class="fas fa-pencil-alt"></i>
                            </a>
                        ';
                    }

                    if (auth()->user()->can('Source: Delete Source')) {
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

        return view('form.payment.sourceTable');
    }

    public function store(Request $request)
    {
        $messages = [
            'required' => __('validation.The :attribute field is required.'),
            'regex'    =>  __('validation.The :attribute must be alphabets.'),
        ];

        $validator = Validator::make($request->all(), [
            //'source_name' => 'required|regex:/^[\pL\s\-]+$/u',
            'status'       => 'required',
        ], $messages);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()]);
        }

        DB::beginTransaction();
        try {

            $addRecord = [
                'source_name' => $request->source_name,
                'status' => $request->status,
            ];
            PaymentSource::create($addRecord);
            $succ_messages = __('messages.Added Successfully');
            $success = __('messages.Success');
            Toastr::success($succ_messages, $success);
            DB::commit();
            return redirect()->back();
        } catch (\Exception $e) {
            DB::rollback();
            $err_msg = __('messages.fail, Add Source Name');
            Toastr::error($err_msg, 'Error');
            return redirect()->back();
        }
    }

    public function update(Request $request)
    {
        $source = PaymentSource::findOrFail($request->id);
        $messages = [
            'required' => __('validation.The :attribute field is required.'),
            'regex'    =>  __('validation.The :attribute must be alphabets.'),
        ];

        $validator = Validator::make($request->all(), [
            //'source_name' => 'required|regex:/^[\pL\s\-]+$/u',
            'status'       => 'required',
        ], $messages);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()]);
        }

        DB::beginTransaction();
        try {
            $source->update([
                'source_name' => $request->source_name,
                'status' => $request->status,
            ]);
            $succ_messages1 = __('messages.Updated Successfully');
            $success = __('messages.Success');
            Toastr::success($succ_messages1, $success);
            DB::commit();
            return redirect()->back();
        } catch (\Exception $e) {
            DB::rollback();
            $err_msg1 = __('messages.fail, Update Source Name');
            Toastr::error($err_msg1, 'Error');
            return redirect()->back();
        }
    }

    public function deleteRecord(Request $request)
    {
        try {

            PaymentSource::destroy($request->id);
            $del_success = __('messages.Deleted Successfully');
            $success = __('messages.Success');
            Toastr::success($del_success, $success);
            return redirect()->back();
        } catch (\Exception $e) {

            DB::rollback();
            $err_msg = __('messages.Failed to delete');
            Toastr::error($err_msg, 'Error');
            return redirect()->back();
        }
    }
}
