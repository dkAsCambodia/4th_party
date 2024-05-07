<?php

namespace App\Http\Controllers;
use DB;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use App\Models\WhitelistIP;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class WhitelistIPController extends Controller
{
    public function index(Request $request)
    {
        $WhitelistIP = WhitelistIP::get();

        if ($request->ajax()) {
            $data = WhitelistIP::query()
                    ->select('whitelist_ips.id', 'whitelist_ips.address', 'whitelist_ips.remarks', 'whitelist_ips.status', 'whitelist_ips.created_at');

            return DataTables::of($data)
                ->addIndexColumn()
                ->editColumn('status', function ($data) {
                    return $data->status == 1
                    ? '<span class="badge light badge-success">' . trans('messages.Enable') . '</span>'
                    : '<span class="badge light badge-danger">' . trans('messages.Disable') . '</span>';
                })
                ->editColumn('created_at', function ($data) {
                    return getAuthPreferenceTimezone($data->created_at);
                })
                ->addColumn('action', function ($data) {
                    $action = '<div class="d-flex">';

                    if (auth()->user()->can('edit/whitelist')) {
                        $action .= '
                                                <a class="btn btn-primary shadow btn-xs sharp me-1 edit_wip" href="#"
                                                    data-toggle="modal" data-target="#edit_wip"
                                                    data-id="' . $data->id . '"
                                                    data-address="' . $data->address . '"
                                                    data-remarks="' . $data->remarks . '"
                                                    data-status="' . $data->status . '">
                                                    <i class="fas fa-pencil-alt"></i>
                                                </a>
                                            ';
                    }

                    if (auth()->user()->can('whitelist/delete')) {
                        $action .= '
                                                <a class="btn btn-danger shadow btn-xs sharp delete_whitelist" href="#"
                                                    data-toggle="modal" data-target="#delete_whitelist" data-id="' . $data->id . '">
                                                    <i class="fa fa-trash"></i>
                                                </a>
                                            ';
                    }
                    $action .= '</div>';
                    return $action;
                })
                ->rawColumns(['action','status'])
                ->make(true);
        }

        return view('form.whitelist.Ipwhitelist', compact('WhitelistIP'));

    }

    public function store(Request $request)
    {
        $messages = [
			'unique'    => __('validation.The :attribute must be unique.'),
			'required'  => __('validation.The :attribute field is required.'),
			'ip'        => __('validation.The :attribute must be ip address.'),
            'regex'     => __('validation.The :attribute must be alphabets.'),
		];

        $validator = Validator::make($request->all(), [
            'address' => 'required|ip|unique:whitelist_ips',
            'status' => 'required',
            'remarks' => 'required|regex:/^[\pL\s\-]+$/u',
        ], $messages);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()]);
        }

            DB::beginTransaction();
        try {
             $addRecord = [
                'address' => $request->address,
                'remarks' => $request->remarks,
                'status' => (int)$request->status,
            ];
            WhitelistIP::create($addRecord);
            DB::commit();

            $msgSuccess = __('messages.Added successfully');
            $success = __('messages.Success');
            Toastr::success($msgSuccess, $success);
            return redirect()->back();
        } catch (\Exception $e) {
            DB::rollback();
            $errmsg = __('messages.fail, Add Whitelist Name');
            Toastr::error($errmsg, 'Error');
            return redirect()->back();
        }
    }

    public function update(Request $request)
    {
        $whitelistIP = WhitelistIP::findOrFail($request->id);
        $messages = [
			'unique'    => __('validation.The :attribute must be unique.'),
			'required'  => __('validation.The :attribute field is required.'),
			'ip'        => __('validation.The :attribute must be ip address.'),
            'regex'     => __('validation.The :attribute must be alphabets.'),
		];

        $validator = Validator::make($request->all(), [
            'address' => 'required|ip|unique:whitelist_ips,address,' . $request->id,
            'status' => 'required',
            'remarks' => 'required|regex:/^[\pL\s\-]+$/u',
        ], $messages);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()]);
        }

        DB::beginTransaction();
        try {
             $editRecord = [
                'address' => $request->address,
                'remarks' => $request->remarks,
                'status' => (int)$request->status,
            ];

            $whitelistIP->update($editRecord);

            DB::commit();

            $msgSuccess = __('messages.Updated Successfully');
            $success = __('messages.Success');
            Toastr::success($msgSuccess, $success);
            return response()->json(['success' => $msgSuccess ]);

        } catch (\Exception $e) {
            DB::rollback();
            $errmsg1 = __('messages.fail, Update Merchant Name');
            Toastr::error($errmsg, 'Error');
            return response()->json(['Error' => $errmsg]);
        }
    }

    public function deleteRecord(Request $request)
    {
         try {
            WhitelistIP::destroy($request->id);
            $msgSuccess = __('messages.Deleted Successfully');
            $success = __('messages.Success');
            Toastr::success($msgSuccess, $success);
            return redirect()->back();
        } catch (\Exception $e) {
            DB::rollback();
            $msgError = __('messages.Whitelist IP delete fail');
            Toastr::error($msgError, 'Error');
            return redirect()->back();
        }
    }
}
