<?php

namespace App\Http\Controllers;

use App\Models\GatewayAccount;
use App\Models\GatewayAccountMethod;
use App\Models\GatewayPaymentChannel;
use App\Models\ParameterSetting;
use App\Models\ParameterValue;
use App\Models\PaymentMethod;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class GatewayAccountMethodController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $gatewayAccountId = $request->gatewayAccountId;
        $paymentMethod = PaymentMethod::where('status', 'Enable')->get();

        $gatewayAccount = GatewayAccount::leftJoin('payment_channels', 'payment_channels.id', '=', 'gateway_accounts.gateway')
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
            )->where('gateway_accounts.id', $gatewayAccountId)->where('gateway_accounts.status', 'Enable')->first();

        if ($request->ajax()) {
            $data = GatewayAccountMethod::query()
                // ->leftJoin('payment_methods', 'payment_methods.id', '=', 'gateway_account_methods.method_id')
                ->where('gateway_account_methods.gateway_account_id', $gatewayAccountId)
                ->select('gateway_account_methods.*');
            // , 'payment_methods.method_name as gateway_method_name'



            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('gateway_method_name', function ($data) {
                    $paymentMethodData = PaymentMethod::whereIn('id', explode(",", $data->method_id))->get();
                    $methodNameVal = '';
                    foreach ($paymentMethodData as $item) {
                        $methodNameVal = $item->method_name . ', ' . $methodNameVal;
                    }
                    return $methodNameVal;
                })
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

                    if (auth()->user()->can('GatewayAccountMethod: Update Method Account')) {
                        $action .= '
                                    <a onclick="addParameterVal(' . $data->id . ');" class="btn btn-primary shadow btn-xs sharp me-1 edit_account_gateway"
                                    href="#" data-toggle="modal" data-target="#edit_account_gateway"
                                    data-id="' . $data->id . '"
                                    data-method_id="' . $data->method_id . '"
                                    data-gateway_account_id="' . $data->gateway_account_id . '"
                                    data-payment_link="' . $data->payment_link . '"
                                    data-merchant_key="' . $data->merchant_key . '"
                                    data-merchant_code="' . $data->merchant_code . '"
                                    data-sign_pre="' . $data->sign_pre . '"
                                    data-username="' . $data->username . '"
                                    data-password="' . $data->password . '"
                                    data-clint_id="' . $data->clint_id . '"
                                    data-gateway_method_name="' . $data->gateway_method_name . '"
                                    data-status="' . $data->status . '">
                                    <i class="fas fa-pencil-alt"></i>
                                </a>
                            ';
                    }

                    if (auth()->user()->can('GatewayAccountMethod: Delete Method Account')) {
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
                // ->filter(function ($data) use ($request) {
                //     if ($request->method) {
                //         $data->where('payment_methods.id', $request->method);
                //     }
                //     if ($request->status) {
                //         $data->where('gateway_account_methods.status', $request->status);
                //     }

                //     if (!empty($request->search)) {
                //         $data->where(function ($q) use ($request) {
                //             $q
                //                 ->orWhere('gateway_account_methods.payment_link', 'LIKE', '%' . $request->search . '%')
                //                 ->orWhere('gateway_account_methods.status', 'LIKE', '%' . $request->search . '%')
                //                 ->orWhere('gateway_account_methods.merchant_key', 'LIKE', '%' . $request->search . '%')
                //                 ->orWhere('gateway_account_methods.merchant_code', 'LIKE', '%' . $request->search . '%')
                //                 ->orWhere('gateway_account_methods.sign_pre', 'LIKE', '%' . $request->search . '%')
                //                 ->orWhere('gateway_account_methods.created_at', 'LIKE', '%' . $request->search . '%');
                //         });
                //     }
                // })
                ->rawColumns(['action', 'status', 'gateway_method_name'])
                ->make(true);
        }
        $parameterSetting = ParameterSetting::where('channel_id', $request->gatewayChannet)->get();
        $gatewayAccountFirst = GatewayAccount::where('id', $request->gatewayAccountId)->first();
        $gatewayChannetId = $request->gatewayChannet;
        // dd($request->gatewayChannet);
        return view('form.gatewayAccount.methodTable', compact('paymentMethod', 'gatewayAccountId', 'gatewayAccount', 'parameterSetting', 'gatewayChannetId', 'gatewayAccountFirst'));
        //
    }
    public function selectedPaymentMethod(Request $request)
    {
        $paymentMethodData = PaymentMethod::where('status', 'Enable')->get();
        foreach ($paymentMethodData as $item) {
            $selected = '';
            if (in_array($item->id, explode(",", $request->id))) {
                $selected = 'selected';
            }
            echo '
                <option value="' . $item->id . '" ' . $selected . '>' . $item->method_name . '</option>
            ';
        }
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $addRecord = [
                'method_id' => implode(",", $request->method_id),
                'gateway_account_id' => $request->gateway_account_id,
                'status' => $request->status,
            ];
            $gamId = GatewayAccountMethod::create($addRecord);

            if ($request->parameter_id) {
                // dd('sushil');
                foreach ($request->parameter_id as $parameterId) {
                    if ($request['parameter_val' . $parameterId]) {
                        $addRecordParam = [
                            'parameter_setting_id' => $parameterId,
                            'gateway_channet_id' => $request->gateway_channet_id,
                            'gateway_account_method_id' => $gamId->id,
                            'parameter_setting_value' => $request['parameter_val' . $parameterId],
                        ];
                        ParameterValue::create($addRecordParam);
                    }
                }
            }

            $succ_messages = __('messages.Added Successfully');
            $success = __('messages.Success');
            Toastr::success($succ_messages, $success);
            DB::commit();
            return redirect()->back();
        } catch (\Exception $e) {
            DB::rollback();
            $err_msg = __('messages.fail, Add Method');
            Toastr::error($err_msg, 'Error');
            return redirect()->back();
        }
    }
    public function update(Request $request)
    {
        $gatewayAccount = GatewayAccountMethod::findOrFail($request->id);
        DB::beginTransaction();
        try {
            $gatewayAccount->update([
                'method_id' => implode(",", $request->method_id),
                'gateway_account_id' => $request->gateway_account_id,
                'status' => $request->status,
            ]);

            if ($request->parameter_id) {
                ParameterValue::where('gateway_channet_id', $request->gateway_channet_id)->delete();
                foreach ($request->parameter_id as $parameterId) {
                    if ($request['parameter_val' . $parameterId]) {
                        $addRecordParam = [
                            'parameter_setting_id' => $parameterId,
                            'gateway_channet_id' => $request->gateway_channet_id,
                            'gateway_account_method_id' => $gatewayAccount->id,
                            'parameter_setting_value' => $request['parameter_val' . $parameterId],
                        ];
                        ParameterValue::create($addRecordParam);
                    }
                }
            }

            $succ_messages1 = __('messages.Updated Successfully');
            $success = __('messages.Success');
            Toastr::success($succ_messages1, $success);
            DB::commit();
            return redirect()->back();
        } catch (\Exception $e) {
            DB::rollback();
            $err_msg1 = __('messages.fail, Update Method');
            Toastr::error($err_msg1, 'Error');
            return redirect()->back();
        }
    }
    public function deleteRecord(Request $request)
    {
        try {
            ParameterValue::where('gateway_account_method_id', $request->id)->delete();
            GatewayAccountMethod::destroy($request->id);
            $del_success = __('messages.Deleted Successfully');
            $success = __('messages.Success');
            Toastr::success($del_success, 'Success');
            return redirect()->back();
        } catch (\Exception $e) {

            DB::rollback();
            $err_msg = __('messages.Failed to delete');
            Toastr::error($err_msg, 'Error');
            return redirect()->back();
        }
    }
}
