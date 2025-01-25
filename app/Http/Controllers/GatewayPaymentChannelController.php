<?php

namespace App\Http\Controllers;

use App\Models\GatewayAccount;
use App\Models\GatewayAccountMethod;
use App\Models\GatewayPaymentChannel;
use App\Models\PaymentChannel;
use App\Models\PaymentMethod;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\Console\Input\Input;
use Yajra\DataTables\DataTables;

class GatewayPaymentChannelController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = GatewayPaymentChannel::query()
                ->leftJoin('gateway_accounts', 'gateway_accounts.id', '=', 'gateway_payment_channels.gateway_account_id')
                //->leftJoin('gateway_account_methods', 'gateway_account_methods.id', '=', 'gateway_payment_channels.gateway_account_method_id')
                ->leftJoin('payment_methods', 'payment_methods.id', '=', 'gateway_payment_channels.gateway_account_method_id')
                ->select(
                    'gateway_payment_channels.id',
                    'gateway_payment_channels.channel_id',
                    'gateway_payment_channels.channel_description',
                    'gateway_accounts.account_id',
                    'payment_methods.method_name',
                    'payment_methods.id as payment_method_id',
                    'gateway_payment_channels.daily_max_limit',
                    'gateway_payment_channels.max_limit_per_trans',
                    'gateway_payment_channels.daily_max_trans',
                    'gateway_payment_channels.status',
                    'gateway_payment_channels.risk_control',
                    'gateway_payment_channels.created_at',
                    'gateway_payment_channels.gateway_account_id'
                )->orderBy('id', 'DESC');
            //dd($data->get());
            return DataTables::of($data)
                ->addIndexColumn()
                ->editColumn('status', function ($data) {
                    return $data->status == 'Enable'
                        ? '<span class="badge light badge-success">' . trans('messages.Enable') . '</span>'
                        : '<span class="badge light badge-danger">' . trans('messages.Disable') . '</span>';
                })
                ->editColumn('risk_control', function ($data) {
                    return $data->risk_control == '1'
                        ? '<span class="badge light badge-success">' . trans('messages.On') . '</span>'
                        : '<span class="badge light badge-danger">' . trans('messages.Off') . '</span>';
                })
                ->editColumn('created_at', function ($data) {
                    return getAuthPreferenceTimezone($data->created_at);
                })
                ->addColumn('action', function ($data) {
                    $action = '<div class="d-flex">';

                    if (auth()->user()->can('GatewayPaymentChannel: Update GatewayPaymentChannel')) {
                        $action .= '
                                <a class="btn btn-primary shadow btn-xs sharp me-1 edit_gateway_payment_channel"
                                href="#" data-toggle="modal" data-target="#edit_gateway_payment_channel"
                                data-id="' . $data->id . '"
                                data-channel_id="' . $data->channel_id . '"
                                data-channel_description="' . $data->channel_description . '"
                                data-gateway_account="' . $data->gateway_account_id . '"
                                data-payment_method="' . $data->payment_method_id . '"
                                data-daily_max_limit="' . $data->daily_max_limit . '"
                                data-max_limit_per_trans="' . $data->max_limit_per_trans . '"
                                data-daily_max_trans="' . $data->daily_max_trans . '"
                                data-status="' . $data->status . '"
                                data-risk_control="' . $data->risk_control . '">
                                <i class="fas fa-pencil-alt"></i>
                            </a>
                        ';
                    }

                    if (auth()->user()->can('GatewayPaymentChannel: Delete GatewayPaymentChannel')) {
                        $action .= '
                            <a class="btn btn-danger shadow btn-xs sharp delete_gateway_payment_channel" href="#"
                                data-toggle="modal" data-target="#delete_gateway_payment_channel" data-id="' . $data->id . '">
                                <i class="fa fa-trash"></i>
                            </a>
                        ';
                    }

                    $action .= '</div>';

                    return $action;
                })
                ->filter(function ($data) use ($request) {
                    if ($request->method) {
                        $data->where('payment_methods.id', $request->method);
                    }
                    if ($request->status) {
                        $data->where('gateway_payment_channels.status', $request->status);
                    }

                    if (!empty($request->search)) {
                        $data->where(function ($q) use ($request) {
                            $q
                                ->orWhere('gateway_payment_channels.channel_id', 'LIKE', '%' . $request->search . '%')
                                ->orWhere('gateway_payment_channels.channel_description', 'LIKE', '%' . $request->search . '%')
                                ->orWhere('gateway_accounts.account_id', 'LIKE', '%' . $request->search . '%')
                                ->orWhere('payment_methods.method_name', 'LIKE', '%' . $request->search . '%')
                                ->orWhere('gateway_payment_channels.status', 'LIKE', '%' . $request->search . '%')
                                ->orWhere('gateway_payment_channels.created_at', 'LIKE', '%' . $request->search . '%');
                        });
                    }
                })
                ->rawColumns(['action', 'status', 'payment_method', 'risk_control'])
                ->make(true);
        }
        $gatewayAccount = GatewayAccount::where('status', 'Enable')->get();
        $paymentMethod = PaymentMethod::where('status', 'Enable')->get();

        return view('form.gatewayAccount.gatewayPaymentChannel', compact('paymentMethod', 'gatewayAccount'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getMethodData(Request $request)
    {
        $gatewayAccountMethod = GatewayAccountMethod::where('gateway_account_id', $request->id)->get();
        $methodIds = '';
        foreach ($gatewayAccountMethod as $items){
            $methodIds = $items->method_id.','.$methodIds;
        }

        $paymentMethod = PaymentMethod::whereIn('id', explode(',',substr($methodIds, 0, -1)))->get();
        echo '<option value="">' . __('messages.Select') . '</option>';
        foreach ($paymentMethod as $value) {
            $selected = '';
            if(isset($request->m_id) && $request->m_id == $value->id){
                $selected = 'selected';
            }
            echo '<option value="' . $value->id . '" '.$selected.'>' . $value->method_name . '</option>';
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            if ($request->risk_control) {
                $risk_control = 1;
            } else {
                $risk_control = 0;
            }

            $addRecord = [
                'channel_id' => $request->channel_id,
                'channel_description' => $request->description,
                'gateway_account_id' => $request->gateway_account,
                'gateway_account_method_id' => $request->payment_method,
                'daily_max_limit' => $request->daily_max_limit,
                'max_limit_per_trans' => $request->max_limit_per_transaction,
                'daily_max_trans' => $request->daily_max_transaction,
                'status' => $request->status,
                'risk_control' => $risk_control,
            ];
            GatewayPaymentChannel::create($addRecord);
            $succ_messages = __('messages.Added Successfully');
            $success = __('messages.Success');
            Toastr::success($succ_messages, $success);
            DB::commit();
            //return redirect()->back();
        } catch (\Exception $e) {
            DB::rollback();
            $err_msg = __('messages.fail, Add Gateway Payment Channel');
            $error = __('messages.Error');
            Toastr::error($err_msg, $error);
            //return redirect()->back();
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
        $gatewayPaymentChannel = GatewayPaymentChannel::findOrFail($request->id);
        DB::beginTransaction();

        if ($request->risk_control) {
            $risk_control = 1;
        } else {
            $risk_control = 0;
        }

        try {
            $gatewayPaymentChannel->update([
                'channel_id' => $request->channel_id,
                'channel_description' => $request->description,
                'gateway_account_id' => $request->gateway_account,
                'gateway_account_method_id' => $request->payment_method,
                'daily_max_limit' => $request->daily_max_limit,
                'max_limit_per_trans' => $request->max_limit_per_transaction,
                'daily_max_trans' => $request->daily_max_transaction,
                'status' => $request->status,
                'risk_control' => $risk_control,
            ]);
            $succ_messages1 = __('messages.Updated Successfully');
            $success = __('messages.Success');
            Toastr::success($succ_messages1, $success);
            DB::commit();
            //return redirect()->back();
        } catch (\Exception $e) {
            DB::rollback();
            $err_msg1 = __('messages.fail, Update Gateway Payment Channel');
            $error = __('messages.Error');
            Toastr::error($err_msg1, $error);
            //return redirect()->back();
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
            GatewayPaymentChannel::destroy($request->id);
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
}
