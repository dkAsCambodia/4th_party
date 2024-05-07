<?php

namespace App\Http\Controllers;

use App\Models\GatewayAccount;
use App\Models\ParameterSetting;
use App\Models\PaymentChannel;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\Console\Input\Input;
use Yajra\DataTables\DataTables;

class GatewayAccountController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = GatewayAccount::query()
                ->leftJoin('payment_channels', 'payment_channels.id', '=', 'gateway_accounts.gateway')
                ->select(
                    'gateway_accounts.id',
                    'gateway_accounts.account_id',
                    'gateway_accounts.description',
                    'gateway_accounts.e_comm_website',
                    'gateway_accounts.gateway',
                    'gateway_accounts.website_description',
                    'gateway_accounts.created_at',
                    'gateway_accounts.status',
                    'payment_channels.channel_name as gateway_name'
                );
            // dd($data);
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

                    if (auth()->user()->can('GatewayAccount: Update Gateway Account')) {
                        $action .= '
                                <a class="btn btn-primary shadow btn-xs sharp me-1 edit_account_gateway"
                                href="#" data-toggle="modal" data-target="#edit_account_gateway"
                                data-id="' . $data->id . '"
                                data-account_id="' . $data->account_id . '"
                                data-description="' . $data->description . '"
                                data-e_comm_website="' . $data->e_comm_website . '"
                                data-gateway="' . $data->gateway_name . '"
                                data-website_description="' . $data->website_description . '"
                                data-status="' . $data->status . '">
                                <i class="fas fa-pencil-alt"></i>
                            </a>
                        ';
                    }

                    if (auth()->user()->can('GatewayAccount: Delete Gateway Account')) {
                        $action .= '
                            <a class="btn btn-danger shadow btn-xs sharp delete_account_gateway" href="#"
                                data-toggle="modal" data-target="#delete_account_gateway" data-id="' . $data->id . '">
                                <i class="fa fa-trash"></i>
                            </a>
                        ';
                    }

                    $action .= '</div>';

                    return $action;
                })
                ->addColumn('payment_method', function ($data) {
                    $action2 = '<div class="d-flex">';
                    if (auth()->user()->can('GatewayAccountMethod: View Method Account')) {
                        $payment_method_msg = __('messages.Payment Method');
                        $action2 .= '
                            <a href="' . route("GatewayAccountMethod: View Method Account", [$data->id, $data->gateway]) . '" class="btn btn-success btn-sm">
                            '.$payment_method_msg.'
                            </a>
                        ';
                    }
                    $action2 .= '</div>';

                    return $action2;
                })
                ->filter(function ($data) use ($request) {
                    if ($request->channel) {
                        $data->where('gateway_accounts.gateway', $request->channel);
                    }
                    if ($request->status) {
                        $data->where('gateway_accounts.status', $request->status);
                    }

                    if (!empty($request->search)) {
                        $data->where(function ($q) use ($request) {
                            $q
                                ->orWhere('gateway_accounts.account_id', 'LIKE', '%' . $request->search . '%')
                                ->orWhere('gateway_accounts.status', 'LIKE', '%' . $request->search . '%')
                                ->orWhere('gateway_accounts.description', 'LIKE', '%' . $request->search . '%')
                                ->orWhere('gateway_accounts.created_at', 'LIKE', '%' . $request->search . '%');
                        });
                    }
                })
                ->rawColumns(['action', 'status', 'payment_method'])
                ->make(true);
        }
        $paymentGateway = PaymentChannel::where('status', 'Enable')->get();

        return view('form.gatewayAccount.table', compact('paymentGateway'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function store(Request $request)
    {
        $messages = [
            'unique' =>  __('validation.The :attribute must be alphabets.'),
        ];
        $validator = Validator::make($request->all(), [
            'account_id' => 'unique:gateway_accounts,account_id',
        ], $messages);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()]);
        }

        DB::beginTransaction();
        try {

            $addRecord = [
                'account_id' => $request->account_id,
                'description' => $request->description,
                'e_comm_website' => $request->e_comm_website,
                'gateway' => $request->gateway,
                'website_description' => $request->website_description,
                'status' => $request->status,
            ];
            GatewayAccount::create($addRecord);
            $succ_messages = __('messages.Added Successfully');
            $success = __('messages.Success');
            Toastr::success($succ_messages, $success);
            DB::commit();
            return redirect()->back();
        } catch (\Exception $e) {
            DB::rollback();
            $err_msg = __('messages.fail, Add Gateway Account');
            $error = __('messages.Error');
            Toastr::error($err_msg, $error);
            return redirect()->back();
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\GatewayAccount  $gatewayAccount
     * @return \Illuminate\Http\Response
     */
    public function show(GatewayAccount $gatewayAccount)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\GatewayAccount  $gatewayAccount
     * @return \Illuminate\Http\Response
     */
    public function edit(GatewayAccount $gatewayAccount)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\GatewayAccount  $gatewayAccount
     * @return \Illuminate\Http\Response
     */

    public function update(Request $request)
    {
        $gatewayAccount = GatewayAccount::findOrFail($request->id);

        $messages = [
            'unique' =>  __('validation.The :attribute must be alphabets.'),
        ];
        $validator = Validator::make($request->all(), [
            'account_id' => 'unique:gateway_accounts,account_id,' . $gatewayAccount->id,
        ], $messages);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()]);
        }

        DB::beginTransaction();
        try {
            $gatewayAccount->update([
                'description' => $request->description,
                'e_comm_website' => $request->e_comm_website,
                'website_description' => $request->website_description,
                'status' => $request->status,
            ]);
            $succ_messages1 = __('messages.Updated Successfully');
            $success = __('messages.Success');
            Toastr::success($succ_messages1, $success);
            DB::commit();
            //return redirect()->back();
        } catch (\Exception $e) {
            DB::rollback();
            $err_msg1 = __('messages.fail, Update Gateway Account');
            $error = __('messages.Error');
            Toastr::error($err_msg1, $error);
            return redirect()->back();
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\GatewayAccount  $gatewayAccount
     * @return \Illuminate\Http\Response
     */
    public function destroy(GatewayAccount $gatewayAccount)
    {
        //
    }
    public function deleteRecord(Request $request)
    {
        try {

            GatewayAccount::destroy($request->id);
            $del_success = __('messages.Deleted Successfully');
            $success = __('messages.Success');
            Toastr::success($del_success, $success);
            return redirect()->back();
        } catch (\Exception $e) {

            DB::rollback();
            $err_msg = __('messages.Failed to delete');
            $error = __('messages.Error');
            Toastr::error($err_msg, $error);
            return redirect()->back();
        }
    }

    public function checkAccount(Request $request)
    {
        $gatewayAccount = GatewayAccount::where('account_id', $request->account_id)->first();
        if (!empty($gatewayAccount)) {
            return response()->json(['value' => false]);
        } else {
            return response()->json(['value' => true]);
        }
    }
}
