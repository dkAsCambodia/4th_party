<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\Billing;
use App\Models\Merchant;
use App\Models\PaymentDetail;
use App\Models\PaymentAccount;
use App\Models\SettleRequest;
use App\Models\SettleRequestTrans;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Validator;


class BillingController extends Controller
{
    public function index()
    {
        $billing_table = Billing::where("merchant_id", null)->where("agent_id", null)->latest()->first();
        return view('billing.billingTable', compact('billing_table'));
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            if ($request->merchant_name && $request->merchant_id) {
                if ($request->settlement_settings == "inherit") {
                    $array = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];
                    $addRecord =
                        [
                            'merchant_name' => $request->merchant_name,
                            'merchant_id' => $request->merchant_id,
                            'status' => $request->settlement_settings,
                            'withdraw_switch' => $request->withdraw_switch == 'turn_on' ? '0' : '0',
                            'week_allow_withdrawals' => $request->week_allow_withdrawals == null ? $array : $array,
                            'withdrawal_start_time' => $request->withdrawal_start_time == null ? '00:00' : '00:00',
                            'withdrawal_end_time' => $request->withdrawal_end_time == null ? '23:59' : '23:59',
                            'daily_withdrawals' => $request->daily_withdrawals == null ? '0' : '0',
                            'max_daily_withdrawals' => $request->max_daily_withdrawals == null ? '0' : '0',
                            'single_max_withdrawal' => $request->single_max_withdrawal == null ? '0' : '0',
                            'single_min_withdrawal' => $request->single_min_withdrawal == null ? '0' : '0',
                            'settlement_fee_type' => $request->settlement_fee_type == null ? '0' : '0',
                            'settlement_fee_ratio' => $request->settlement_fee_ratio == 'percentage_fee' ? '0' : '0',
                            // 'single_transaction_fee_limit' => $request->single_transaction_fee_limit == null ? '0' : '0',
                            // 'payment_method' => $request->payment_method == null ? '0' : '0',
                        ];
                } else {

                    $messages = [
                        'required' => __('validation.The :attribute field is required.'),
                        'regex' => __('validation.The :attribute must be numbers.')
                    ];

                    $validator = Validator::make($request->all(), [
                        'week_allow_withdrawals' => 'required',
                        'daily_withdrawals' => 'required|regex:/^(([0-9]*)(\.([0-9]+))?)$/',
                        'max_daily_withdrawals' => 'required|regex:/^(([0-9]*)(\.([0-9]+))?)$/',
                        'single_max_withdrawal' => 'required|regex:/^(([0-9]*)(\.([0-9]+))?)$/',
                        'single_min_withdrawal' => 'required|regex:/^(([0-9]*)(\.([0-9]+))?)$/',
                        'settlement_fee_ratio' => 'required|regex:/^(([0-9]*)(\.([0-9]+))?)$/',
                        // 'single_transaction_fee_limit' => 'required|regex:/^(([0-9]*)(\.([0-9]+))?)$/',
                    ], $messages);

                    if ($validator->fails()) {
                        return response()->json(['error' => $validator->errors()]);
                    }

                    $addRecord =
                        [
                            'merchant_name' => $request->merchant_name,
                            'merchant_id' => $request->merchant_id,
                            'status' => $request->settlement_settings,
                            'withdraw_switch' => $request->withdraw_switch,
                            'week_allow_withdrawals' => $request->week_allow_withdrawals,
                            'withdrawal_start_time' => $request->withdrawal_start_time,
                            'withdrawal_end_time' => $request->withdrawal_end_time,
                            'daily_withdrawals' => $request->daily_withdrawals,
                            'max_daily_withdrawals' => $request->max_daily_withdrawals,
                            'single_max_withdrawal' => $request->single_max_withdrawal,
                            'single_min_withdrawal' => $request->single_min_withdrawal,
                            'settlement_fee_type' => $request->settlement_fee_type,
                            'settlement_fee_ratio' => $request->settlement_fee_ratio,
                            // 'single_transaction_fee_limit' => $request->single_transaction_fee_limit,
                            // 'payment_method' => '0',
                        ];
                }
                $marchent = Billing::where('merchant_id', $request->merchant_id)->first();
                if ($marchent) {
                    $marchent->update($addRecord);
                    $messages1 = __('messages.Updated Successfully');
                    $success = __('messages.Success');
                    Toastr::success($messages1, $success);
                    DB::commit();
                    return redirect()->back();
                } else {
                    //dd($addRecord);
                    Billing::create($addRecord);
                    $messages2 = __('messages.Added Successfully');
                    $success = __('messages.Success');
                    Toastr::success($messages2, $success);
                    DB::commit();
                    return redirect()->back();
                }
            } else {

                $messages = [
                    'required' => __('validation.The :attribute field is required.'),
                    'regex' => __('validation.The :attribute must be numbers.')
                ];

                $validator = Validator::make($request->all(), [
                    'withdraw_switch' => 'required',
                    'settlement_fee_type' => 'required',
                    // 'payment_method' => 'required',
                    'week_allow_withdrawals' => 'required',
                    'daily_withdrawals' => 'required|regex:/^(([0-9]*)(\.([0-9]+))?)$/',
                    'max_daily_withdrawals' => 'required|regex:/^(([0-9]*)(\.([0-9]+))?)$/',
                    'single_max_withdrawal' => 'required|regex:/^(([0-9]*)(\.([0-9]+))?)$/',
                    'single_min_withdrawal' => 'required|regex:/^(([0-9]*)(\.([0-9]+))?)$/',
                    'settlement_fee_ratio' => 'required|regex:/^(([0-9]*)(\.([0-9]+))?)$/',
                    // 'single_transaction_fee_limit' => 'required|regex:/^(([0-9]*)(\.([0-9]+))?)$/',
                ], $messages);

                if ($validator->fails()) {
                    return response()->json(['error' => $validator->errors()]);
                }

                $addRecord =
                    [
                        'withdraw_switch' => $request->withdraw_switch,
                        'week_allow_withdrawals' => $request->week_allow_withdrawals,
                        'withdrawal_start_time' => $request->withdrawal_start_time,
                        'withdrawal_end_time' => $request->withdrawal_end_time,
                        'daily_withdrawals' => $request->daily_withdrawals,
                        'max_daily_withdrawals' => $request->max_daily_withdrawals,
                        'single_max_withdrawal' => $request->single_max_withdrawal,
                        'single_min_withdrawal' => $request->single_min_withdrawal,
                        'settlement_fee_type' => $request->settlement_fee_type,
                        'settlement_fee_ratio' => $request->settlement_fee_ratio,
                        // 'single_transaction_fee_limit' => $request->single_transaction_fee_limit,
                        // 'payment_method' => $request->payment_method,
                        'merchant_name' => null,
                        'merchant_id' => null,
                        'status' => null,
                    ];
                $marchent = Billing::where('merchant_id', null)->where("agent_id", null)->first();
                if ($marchent) {
                    $marchent->update($addRecord);
                } else {
                    Billing::create($addRecord);
                }
            }
            $messages3 = __('messages.Added Successfully');
            $success = __('messages.Success');
            Toastr::success($messages3, $success);
            DB::commit();
            return redirect()->back();
        } catch (\Exception $e) {
            DB::rollback();
            $messages4 = __('messages.Failed to delete');
            Toastr::error($messages4, 'Error');
            return redirect()->back();
        }
    }

    public function indexUnsettled()
    {
        $billing = Billing::where('merchant_id', auth()->user()->merchant_id)->first();
        if ($billing && $billing->status == "inherit") {
            $billing = Billing::where("merchant_id", null)->where("agent_id", null)->latest()->first();
        }

        $merchantBanks = PaymentAccount::where('merchant_id', auth()->user()->merchant_id)
            ->select('id', 'bank_name', 'account_number')
            ->get();

        $merchantBankFirst = PaymentAccount::with('bank:id,account_type')
            ->select('id', 'account_name', 'bank_name', 'account_number', 'bank_id')
            ->where('merchant_id', auth()->user()->merchant_id)
            ->first();

        $merchantCode = Merchant::where('id', auth()->user()->merchant_id)->value('merchant_code');

        $paymentDetails = PaymentDetail::with('paymentMaps:id,merchant_commission')
            ->where('merchant_code', $merchantCode)
            ->where('payment_status', 'success')
            ->where('merchant_settle_status', 'unsettled')
            ->select('id', 'created_at', 'transaction_id', 'fourth_party_transection', 'amount', 'Currency', 'product_id')
            ->get();

        return view('settle.unSettleTable', compact('billing', 'merchantBanks', 'merchantBankFirst', 'paymentDetails'));
    }

    public function indexUnsettledAgent()
    {
        $billing = Billing::where('agent_id', auth()->user()->agent_id)->first();
        if ($billing && $billing->status == "inherit") {
            $billing = Billing::where("merchant_id", null)->where("agent_id", null)->latest()->first();
        }

        $agentBanks = PaymentAccount::where('agent_id', auth()->user()->agent_id)
            ->select('id', 'bank_name', 'account_number')
            ->get();

        $agentBankFirst = PaymentAccount::with('bank:id,account_type')
            ->select('id', 'account_name', 'bank_name', 'account_number', 'bank_id')
            ->where('agent_id', auth()->user()->agent_id)
            ->first();

        $merchant = Merchant::where('agent_id', auth()->user()->agent_id)
            ->select('merchant_code', 'agent_id')
            ->get();

        $merchantCode = [];

        foreach ($merchant as $merchantVal) {
            array_push($merchantCode, $merchantVal->merchant_code);
        }

        $paymentDetails = PaymentDetail::with('paymentMaps:id,agent_commission')
            ->whereIn('merchant_code', $merchantCode)
            ->where('payment_status', 'success')
            ->where('agent_settle_status', 'unsettled')
            ->select('id', 'created_at', 'transaction_id', 'fourth_party_transection', 'amount', 'Currency', 'product_id')
            ->get();

        return view('settle.unSettleTableAgent', compact('billing', 'agentBanks', 'agentBankFirst', 'paymentDetails'));
    }

    public function indexSettleRequest(Request $request)
    {

        if ($request->ajax()) {
            $data = SettleRequest::query()
                ->where('merchant_id', auth()->user()->merchant_id)
                ->with([
                    'SettleRequestTrans',
                    'merchant:id,merchant_code,merchant_name',
                    'payment_account' => fn ($q) => $q->with('bank:id,account_type')
                        ->select('id', 'bank_name', 'account_name', 'bank_id', 'account_number')
                ])
                ->orderByDesc('created_at')
                ->select('*')
                ->where('settle_requests.status', 'pending');
            //   echo "<pre>"; print_r($data); die;
            return DataTables::of($data)
                ->editColumn('created_at', function ($data) {
                    return getAuthPreferenceTimezone($data?->created_at);
                })
                ->editColumn('Currency', function ($data) {
                    return $data->Currency;
                })
                ->editColumn('total', function ($data) {
                    return number_format($data->total, 2);
                })
                ->editColumn('status', function ($data) {
                    if ($data->status == 'success' || $data->status == 'Success' || $data->status == 'SUCCESS') {
                        return '<span class="text-success fw-bold">' . trans('messages.Success') . '</span>';
                    } elseif ($data->status == 'pending' || $data->status == 'Pending' || $data->status == 'PENDING') {
                        return '<span class="text-primary fw-bold">' . trans('messages.pending') . '</span>';
                    } else {
                        return '<span class="text-danger fw-bold">' . trans('messages.Failed') . '</span>';
                    }
                })
                ->addColumn('action', function ($data) use ($request) {
                    $action = '
                        <a class="btn btn-primary btn-sm" href="#" data-toggle="modal" data-target="#edit_user' . $data->id . '">' . trans("messages.View") . '</a>
                    ';

                    $action .= '
                        <div id="edit_user' . $data->id . '" class="modal custom-modal fade" role="dialog">
                            <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">' . trans("messages.Transaction") . '</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>

                                    <div class="modal-body">
                                        <div class="table-responsive">
                                            <table class="table table-bordered test" style="padding: 7px 10px; !important">
                                                <tr>
                                                    <td style="width: 25%; background-color: #6c6c70 !important; color: white;">' . trans("messages.Settlement Trans ID") . '</td>
                                                    <td>' . $data->fourth_party_transection . '</td>
                                                    <td style="width: 25%; background-color: #6c6c70 !important; color: white;">' . trans("messages.Created Time") . '</td>
                                                    <td>' . $data?->created_at . '</td>
                                                </tr>';
                    if ($request->checkValue == null) {
                        $action .= '
                                                        <tr>
                                                            <td style="width: 25%; background-color: #6c6c70 !important; color: white;">' . trans("messages.Product ID") . '</td>
                                                            <td>' . $data->product_id . '</td>
                                                            <td style="width: 25%; background-color: #6c6c70 !important; color: white;">' . trans("messages.Merchant Name") . '</td>
                                                            <td>' . $data->merchant?->merchant_name . '</td>
                                                        </tr>
                                                    ';
                    } else {
                        $action .= '
                                                        <tr>
                                                            <td style="width: 25%; background-color: #6c6c70 !important; color: white;">' . trans("messages.Agent Code") . '</td>
                                                            <td>' . $data->agent?->agent_code . '</td>
                                                            <td style="width: 25%; background-color: #6c6c70 !important; color: white;">' . trans("messages.Agent Name") . '</td>
                                                            <td>' . $data->agent?->agent_name . '</td>
                                                        </tr>
                                                    ';
                    }

                                        $action .= '<tr>
                                                    <td style="width: 25%; background-color: #6c6c70 !important; color: white;">' . trans("messages.Settlement Amount") . '</td>
                                                    <td>' . number_format($data->total, 2) . '</td>
                                                    <td style="width: 25%; background-color: #6c6c70 !important; color: white;">' . trans("messages.Merchant Code") . '</td>
                                                    <td>' . $data->merchant?->merchant_code . '</td>
                                                    
                                                </tr>
                                                <tr>
                                                    <td style="width: 25%; background-color: #6c6c70 !important; color: white;">' . trans("messages.Callback URL") . '</td>
                                                    <td>' . $data->callback_url . '</td>
                                                    <td style="width: 25%; background-color: #6c6c70 !important; color: white;">' . trans("messages.Account Name") . '</td>
                                                    <td>' . $data->customer_name . '</td>
                                                </tr>
                                                <tr>
                                                    <td style="width: 25%; background-color: #6c6c70 !important; color: white;">' . trans("messages.Message") . '</td>
                                                    <td>' . $data->message . '</td>
                                                    <td style="width: 25%; background-color: #6c6c70 !important; color: white;">' . trans("messages.Bank Name") . '</td>
                                                    <td>' . $data->customer_bank_name . '</td>
                                                </tr>
                                                <tr>
                                                    <td style="width: 25%; background-color: #6c6c70 !important; color: white;">' . trans("messages.Account Number") . '</td>
                                                    <td>' . $data->customer_account_number . '</td>
                                                    <td style="width: 25%; background-color: #6c6c70 !important; color: white;">' . trans("messages.Status") . '</td>
                                                    <td>' . $data->status . '</td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    ';

                    return $action;
                })
                ->filter(function ($data) use ($request) {
                    if ($request->merchant_code) {
                        $data->where('merchant_id', $request->merchant_code);
                    }

                    if ($request->agent_code) {
                        $data->where('agent_id', $request->agent_code);
                    }

                    if ($request->daterange) {
                        $dateInput  = explode('-', $request->daterange);

                        $date[0]  = "$dateInput[0]/$dateInput[1]/$dateInput[2]";
                        if (count($dateInput) > 3) {
                            $date[1]  = "$dateInput[3]/$dateInput[4]/$dateInput[5]";
                        }

                        $start_date = Carbon::parse($date[0]);
                        $end_date   = Carbon::parse($date[1]);

                        $data->whereDate('created_at', '>=', $start_date)
                            ->whereDate('created_at', '<=', $end_date);
                    }

                    if (!empty($request->search)) {
                        $data->where(function ($q) use ($request) {
                            $q->orWhere('settlement_trans_id', 'LIKE', '%' . $request->search . '%')
                                ->orWhere('sub_total', 'LIKE', '%' . $request->search . '%')
                                ->orWhere('total', 'LIKE', '%' . $request->search . '%')
                                ->orWhere('status', 'LIKE', '%' . $request->search . '%')
                                ->orWhere('created_at', 'LIKE', '%' . $request->search . '%');
                        });
                    }
                })
                ->rawColumns(['action', 'status'])
                ->with('checkValue', $request->checkValue)
                ->make(true);
        }

        return view('settle.settleRequestTable');
    }

    public function storeRequestAgent(Request $request)
    {
        if (empty($request->paymentId)) {
            return response()->json(['error' => __('messages.Please select at least one transaction1')]);
        }

        // check withdrawal date from billing
        $billing = Billing::where('agent_id', auth()->user()->agent_id)->first();
        if ($billing->status == "inherit") {
            $billing = Billing::where("merchant_id", null)->where("agent_id", null)->latest()->first();
        }
        if (!(date('H:i') > $billing->withdrawal_start_time && date('H:i') < $billing->withdrawal_end_time)) {
            return response()->json(['error' => __('messages.Your withdrawal start & end time:') . date('h:i A', strtotime($billing->withdrawal_start_time)) . __('messages.to') . date('h:i A', strtotime($billing->withdrawal_end_time))]);
        }

        // check daily withdraw count from settle request
        $withdrawCount = SettleRequest::where('agent_id', auth()->user()->agent_id)->whereDay('created_at', today())->count();
        if ($withdrawCount >= $billing->daily_withdrawals) {
            return response()->json(['error' => __('messages.You reached your daily withdrawal limit')]);
        }

        // check daily withdraw amount
        $maxWithdrawAmount = SettleRequest::where('agent_id', auth()->user()->agent_id)->whereDay('created_at', today())->sum('total');
        if ($maxWithdrawAmount >= $billing->max_daily_withdrawals) {
            return response()->json(['error' => __('messages.Daily amount withdrawal limit exceeded')]);
        }

        // check if amount exceed max daily withdraw
        if ($request->netAmount > $billing->max_daily_withdrawals) {
            return response()->json(['error' => __('messages.Maximum withdrawal amount per transaction exceeded')]);
        }

        // check min amount
        if ($request->netAmount < $billing->single_min_withdrawal) {
            return response()->json(['error' => __('messages.Provide the minimum transaction withdrawal amount')]);
        }

        // check max amount
        if ($request->netAmount > $billing->single_max_withdrawal) {
            return response()->json(['error' => __('messages.Maximum withdrawal amount per transaction exceeded')]);
        }

        $settle = SettleRequest::create([
            'settlement_trans_id' => "T" . rand(100000, 999999),
            'agent_id' => auth()->user()->agent_id,
            'sub_total' => $request->netAmount,
            'commission' => $request->commission,
            'total' => $request->amount,
            'transaction_amount' => $request->transactionAmount,
            'payment_account_id' => (int)$request->account_id,
            'handling_fee' => $request->handlingFee,
        ]);

        $paymentIdToArray = explode(',', $request->paymentId[0]);

        foreach ($paymentIdToArray as $paymentId) {
            $paymentDetail = PaymentDetail::find($paymentId);
            $paymentDetail->update(['agent_settle_status' => 'settleRequest']);
            SettleRequestTrans::create(
                [
                    'agent_id' => auth()->user()->agent_id,
                    'settle_request_id' => $settle->id,
                    'payment_detail_id' => $paymentId,
                ]
            );
        }
        $success = __('messages.Success');
        $msg = __('messages.Added Successfully');
        Toastr::success($msg, $success);
    }

    public function indexSettleRequestAgent(Request $request)
    {
        if ($request->ajax()) {
            $data = SettleRequest::query()
                ->where('agent_id', auth()->user()->agent_id)
                ->with([
                    'SettleRequestTrans',
                    'agent:id,agent_code,agent_name',
                    'payment_account' => fn ($q) => $q->with('bank:id,account_type')
                        ->select('id', 'bank_name', 'account_name', 'bank_id', 'account_number')
                ])
                ->orderByDesc('created_at')
                ->select('*')
                ->where('status', 'pending');

                return DataTables::of($data)
                ->editColumn('created_at', function ($data) {
                    return getAuthPreferenceTimezone($data?->created_at);
                })
                ->editColumn('Currency', function ($data) {
                    return $data->Currency;
                })
                ->editColumn('total', function ($data) {
                    return number_format($data->total, 2);
                })
                ->editColumn('status', function ($data) {
                        return '<span class="text-primary fw-bold">' . trans('messages.pending') . '</span>';
                })
                ->addColumn('action', function ($data) use ($request) {
                    $action = '
                        <a class="btn btn-primary btn-sm" href="#" data-toggle="modal" data-target="#edit_user' . $data->id . '">' . trans("messages.View") . '</a>
                    ';

                    $action .= '
                        <div id="edit_user' . $data->id . '" class="modal custom-modal fade" role="dialog">
                            <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">' . trans("messages.Transaction") . '</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>

                                    <div class="modal-body">
                                        <div class="table-responsive">
                                            <table class="table table-bordered test" style="padding: 7px 10px; !important">
                                                <tr>
                                                    <td style="width: 25%; background-color: #6c6c70 !important; color: white;">' . trans("messages.Settlement Trans ID") . '</td>
                                                    <td>' . $data->fourth_party_transection . '</td>
                                                    <td style="width: 25%; background-color: #6c6c70 !important; color: white;">' . trans("messages.Created Time") . '</td>
                                                    <td>' . $data?->created_at . '</td>
                                                </tr>
                                                <tr>
                                                    <td style="width: 25%; background-color: #6c6c70 !important; color: white;">' . trans("messages.Product ID") . '</td>
                                                    <td>' . $data->product_id . '</td>
                                                    <td style="width: 25%; background-color: #6c6c70 !important; color: white;">' . trans("messages.Merchant Name") . '</td>
                                                    <td>' . $data->merchant?->merchant_name . '</td>
                                                </tr>
                                                <tr>
                                                    <td style="width: 25%; background-color: #6c6c70 !important; color: white;">' . trans("messages.Agent Code") . '</td>
                                                    <td>' . $data->agent?->agent_code . '</td>
                                                    <td style="width: 25%; background-color: #6c6c70 !important; color: white;">' . trans("messages.Agent Name") . '</td>
                                                    <td>' . $data->agent?->agent_name . '</td>
                                                </tr>
                                                    <tr>
                                                    <td style="width: 25%; background-color: #6c6c70 !important; color: white;">' . trans("messages.Settlement Amount") . '</td>
                                                    <td>' . number_format($data->total, 2) . '</td>
                                                    <td style="width: 25%; background-color: #6c6c70 !important; color: white;">' . trans("messages.Merchant Code") . '</td>
                                                    <td>' . $data->merchant?->merchant_code . '</td>
                                                    
                                                </tr>
                                                <tr>
                                                    <td style="width: 25%; background-color: #6c6c70 !important; color: white;">' . trans("messages.Callback URL") . '</td>
                                                    <td>' . $data->callback_url . '</td>
                                                    <td style="width: 25%; background-color: #6c6c70 !important; color: white;">' . trans("messages.Account Name") . '</td>
                                                    <td>' . $data->customer_name . '</td>
                                                </tr>
                                                <tr>
                                                    <td style="width: 25%; background-color: #6c6c70 !important; color: white;">' . trans("messages.Message") . '</td>
                                                    <td>' . $data->message . '</td>
                                                    <td style="width: 25%; background-color: #6c6c70 !important; color: white;">' . trans("messages.Bank Name") . '</td>
                                                    <td>' . $data->customer_bank_name . '</td>
                                                </tr>
                                                <tr>
                                                    <td style="width: 25%; background-color: #6c6c70 !important; color: white;">' . trans("messages.Account Number") . '</td>
                                                    <td>' . $data->customer_account_number . '</td>
                                                    <td style="width: 25%; background-color: #6c6c70 !important; color: white;">' . trans("messages.Status") . '</td>
                                                    <td>' . $data->status . '</td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    ';

                    return $action;
                })
                ->filter(function ($data) use ($request) {
                    if ($request->merchant_code) {
                        $data->where('merchant_id', $request->merchant_code);
                    }

                    if ($request->agent_code) {
                        $data->where('agent_id', $request->agent_code);
                    }

                    if ($request->daterange) {
                        $dateInput  = explode('-', $request->daterange);

                        $date[0]  = "$dateInput[0]/$dateInput[1]/$dateInput[2]";
                        if (count($dateInput) > 3) {
                            $date[1]  = "$dateInput[3]/$dateInput[4]/$dateInput[5]";
                        }

                        $start_date = Carbon::parse($date[0]);
                        $end_date   = Carbon::parse($date[1]);

                        $data->whereDate('created_at', '>=', $start_date)
                            ->whereDate('created_at', '<=', $end_date);
                    }

                    if (!empty($request->search)) {
                        $data->where(function ($q) use ($request) {
                            $q->orWhere('settlement_trans_id', 'LIKE', '%' . $request->search . '%')
                                ->orWhere('sub_total', 'LIKE', '%' . $request->search . '%')
                                ->orWhere('total', 'LIKE', '%' . $request->search . '%')
                                ->orWhere('status', 'LIKE', '%' . $request->search . '%')
                                ->orWhere('created_at', 'LIKE', '%' . $request->search . '%');
                        });
                    }
                })
                ->rawColumns(['action', 'status'])
                ->with('checkValue', $request->checkValue)
                ->make(true);
        }

        return view('settle.settleRequestTableAgent');
    }

    public function indexSettleRequestAdmin(Request $request)
    {
        
        $merchant = Merchant::get();
        $agents = Agent::get();

        $merchantCount = SettleRequest::where('merchant_id', '!=', null)
            ->where('settle_requests.status', 'pending')
            ->count();
            
        $agentCount = SettleRequest::where('agent_id', '!=', null)
            ->where('settle_requests.status', 'pending')
            ->count();

        if ($request->ajax()) {
           
            $data = SettleRequest::query()
                ->with([
                    'SettleRequestTrans',
                    'merchant:id,merchant_code,merchant_name',
                    'agent:id,agent_code,agent_name',
                    'payment_account' => fn ($q) => $q->with('bank:id,account_type')
                        ->select('id', 'bank_name', 'account_name', 'bank_id', 'account_number')
                ])

                ->when($request->checkValue == null, fn ($q) => $q->where('merchant_id', '!=', null))
                ->when($request->checkValue == 'agent', fn ($q) => $q->where('agent_id', '!=', null))
                ->orderByDesc('created_at')
                ->select('*')
                ->where('settle_requests.status', 'pending');
                // echo "<pre>"; print_r($data); die;
            return DataTables::of($data)
                ->editColumn('created_at', function ($data) {
                    return getAuthPreferenceTimezone($data?->created_at);
                })
                ->addColumn('merchant_code', function ($data) {
                    return $data->merchant?->merchant_code;
                })
                ->addColumn('agent_code', function ($data) {
                    return $data->agent?->agent_code;
                })
                ->editColumn('Currency', function ($data) {
                    return $data->Currency;
                })
                
                ->editColumn('total', function ($data) {
                    return number_format($data->total, 2);
                })
                ->editColumn('status', function ($data) {
                    if ($data->status == 'success' || $data->status == 'Success' || $data->status == 'SUCCESS') {
                        return '<span class="text-success fw-bold">' . trans('messages.Success') . '</span>';
                    } elseif ($data->status == 'pending' || $data->status == 'Pending' || $data->status == 'PENDING') {
                        return '<span class="text-primary fw-bold">' . trans('messages.pending') . '</span>';
                    } else {
                        return '<span class="text-danger fw-bold">' . trans('messages.Failed') . '</span>';
                    }
                })
                ->addColumn('action', function ($data) use ($request) {
                    $action = '
                        <a class="btn btn-primary btn-sm" href="#" data-toggle="modal" data-target="#edit_user' . $data->id . '">' . trans("messages.View") . '</a>
                    ';

                    $action .= '
                        <div id="edit_user' . $data->id . '" class="modal custom-modal fade" role="dialog">
                            <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">' . trans("messages.Transaction") . '</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>

                                    <div class="modal-body">
                                        <div class="table-responsive">
                                            <table class="table table-bordered test" style="padding: 7px 10px; !important">
                                                <tr>
                                                    <td style="width: 25%; background-color: #6c6c70 !important; color: white;">' . trans("messages.Settlement Trans ID") . '</td>
                                                    <td>' . $data->fourth_party_transection . '</td>
                                                    <td style="width: 25%; background-color: #6c6c70 !important; color: white;">' . trans("messages.Created Time") . '</td>
                                                    <td>' . $data?->created_at . '</td>
                                                </tr>';
                    if ($request->checkValue == null) {
                        $action .= '
                                                        <tr>
                                                            <td style="width: 25%; background-color: #6c6c70 !important; color: white;">' . trans("messages.Product ID") . '</td>
                                                            <td>' . $data->product_id . '</td>
                                                            <td style="width: 25%; background-color: #6c6c70 !important; color: white;">' . trans("messages.Merchant Name") . '</td>
                                                            <td>' . $data->merchant?->merchant_name . '</td>
                                                        </tr>
                                                    ';
                    } else {
                        $action .= '
                                                        <tr>
                                                            <td style="width: 25%; background-color: #6c6c70 !important; color: white;">' . trans("messages.Agent Code") . '</td>
                                                            <td>' . $data->agent?->agent_code . '</td>
                                                            <td style="width: 25%; background-color: #6c6c70 !important; color: white;">' . trans("messages.Agent Name") . '</td>
                                                            <td>' . $data->agent?->agent_name . '</td>
                                                        </tr>
                                                    ';
                    }

                                        $action .= '<tr>
                                                    <td style="width: 25%; background-color: #6c6c70 !important; color: white;">' . trans("messages.Settlement Amount") . '</td>
                                                    <td>' . number_format($data->total, 2) . '</td>
                                                    <td style="width: 25%; background-color: #6c6c70 !important; color: white;">' . trans("messages.Merchant Code") . '</td>
                                                    <td>' . $data->merchant?->merchant_code . '</td>
                                                    
                                                </tr>
                                                <tr>
                                                    <td style="width: 25%; background-color: #6c6c70 !important; color: white;">' . trans("messages.Callback URL") . '</td>
                                                    <td>' . $data->callback_url . '</td>
                                                    <td style="width: 25%; background-color: #6c6c70 !important; color: white;">' . trans("messages.Account Name") . '</td>
                                                    <td>' . $data->customer_name . '</td>
                                                </tr>
                                                <tr>
                                                    <td style="width: 25%; background-color: #6c6c70 !important; color: white;">' . trans("messages.Message") . '</td>
                                                    <td>' . $data->message . '</td>
                                                    <td style="width: 25%; background-color: #6c6c70 !important; color: white;">' . trans("messages.Bank Name") . '</td>
                                                    <td>' . $data->customer_bank_name . '</td>
                                                </tr>
                                                <tr>
                                                    <td style="width: 25%; background-color: #6c6c70 !important; color: white;">' . trans("messages.Account Number") . '</td>
                                                    <td>' . $data->customer_account_number . '</td>
                                                    <td style="width: 25%; background-color: #6c6c70 !important; color: white;">' . trans("messages.Status") . '</td>
                                                    <td>' . $data->status . '</td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>

                                    <div class="modal-body">
                                        <div class="table-responsive">
                                            <table id="example" class="display" style="width:100%">
                                                <thead>
                                                    <tr>
                                                        <th>' . trans('messages.Created Time') . '</th>
                                                        <th>' . trans('messages.Transaction ID') . '</th>
                                                        <th>' . trans('messages.Merchant Track No') . '</th>
                                                        <th style="text-align: right">' . trans('messages.Amount') . '</th>
                                                        <th style="text-align: right">' . trans('messages.Rate') . '(%)</th>
                                                        <th style="text-align: right">' . trans('messages.Rate Amount') . '</th>
                                                        <th style="text-align: right">' . trans('messages.Net Amount') . '</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                ';
                    foreach ($data->SettleRequestTrans as $item) {
                        $action .= '
                            <tr>
                                <td class="created_at">
                                    ' . $item->paymentDetails?->created_at . '
                                </td>
                                <td class="transaction_id">
                                    ' . $item->paymentDetails?->fourth_party_transection . '
                                </td>
                                <td class="merchant_track_no">
                                    ' . $item->paymentDetails?->transaction_id . '
                                </td>
                                <td class="amount text-end">
                                    ' . number_format($item->paymentDetails?->amount, 2) . '
                                </td>
                                <td class="rate_percentage text-end">
                                    ';
                        if ($item->agent_id != null)
                            $action .= number_format(($item->paymentDetails?->paymentMaps?->agent_commission), 2);
                        else
                            $action .= number_format(($item->paymentDetails?->paymentMaps?->merchant_commission), 2);
                        $action .= '
                                </td>
                                <td class="rate_amount text-end">
                                    ';
                        if ($item->agent_id != null) {
                            $action .= number_format(($item->paymentDetails?->amount * $item->paymentDetails?->paymentMaps?->agent_commission) / 100, 2);
                        } else {
                            $action .= number_format(($item->paymentDetails?->amount * $item->paymentDetails?->paymentMaps?->merchant_commission) / 100, 2);
                        }
                        $action .= '
                                </td>
                                <td class="Commission text-end">
                                    ';
                        if ($item->agent_id != null) {
                            $action .= number_format(($item->paymentDetails?->amount * $item->paymentDetails?->paymentMaps?->agent_commission) / 100, 2);
                        } else {
                            $realTotal = ($item->paymentDetails?->amount * $item->paymentDetails?->paymentMaps?->merchant_commission) / 100;

                            $action .= number_format($item->paymentDetails?->amount - $realTotal, 2);
                        }
                        $action .= '
                                </td>
                            </tr>
                        ';
                    }
                    $action .= '
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <div class="d-flex">
                                            <div class="d-inline-flex p-2">
                                                <a data-toggle="modal" data-target="#aproved_form" data-id="' . $data->id . '" data-type="' . ($data->merchant ? 'yes' : 'no') . '" class="btn btn-primary status_settle">
                                                    ' . __("messages.Approve") . '
                                                </a>
                                            </div>
                                            <div class="d-inline-flex p-2">
                                                <a data-toggle="modal" data-target="#reject_form" data-id="' . $data->id . '" data-type="' . ($data->merchant ? 'yes' : 'no') . '" class="btn btn-danger status_settle">
                                                    ' . __("messages.Reject") . '
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    ';

                    return $action;
                })
                ->filter(function ($data) use ($request) {
                    if ($request->merchant_code) {
                        $data->where('merchant_id', $request->merchant_code);
                    }

                    if ($request->agent_code) {
                        $data->where('agent_id', $request->agent_code);
                    }

                    if ($request->daterange) {
                        $dateInput  = explode('-', $request->daterange);

                        $date[0]  = "$dateInput[0]/$dateInput[1]/$dateInput[2]";
                        if (count($dateInput) > 3) {
                            $date[1]  = "$dateInput[3]/$dateInput[4]/$dateInput[5]";
                        }

                        $start_date = Carbon::parse($date[0]);
                        $end_date   = Carbon::parse($date[1]);

                        $data->whereDate('created_at', '>=', $start_date)
                            ->whereDate('created_at', '<=', $end_date);
                    }

                    if (!empty($request->search)) {
                        $data->where(function ($q) use ($request) {
                            $q->orWhere('settlement_trans_id', 'LIKE', '%' . $request->search . '%')
                                ->orWhere('sub_total', 'LIKE', '%' . $request->search . '%')
                                ->orWhere('total', 'LIKE', '%' . $request->search . '%')
                                ->orWhere('status', 'LIKE', '%' . $request->search . '%')
                                ->orWhere('created_at', 'LIKE', '%' . $request->search . '%');
                        });
                    }
                })
                ->rawColumns(['action', 'status'])
                ->with('checkValue', $request->checkValue)
                ->make(true);
        }

        return view('settle.settleRequestTableAdmin', compact('merchant', 'agents', 'merchantCount', 'agentCount'));
    }

    public function indexSettleApprovedAdmin(Request $request)
    {
        $merchant = Merchant::get();
        $agents = Agent::get();

        $merchantCount = SettleRequest::where('merchant_id', '!=', null)
            ->where('settle_requests.status', 'approved')
            ->count();

        $agentCount = SettleRequest::where('agent_id', '!=', null)
            ->where('settle_requests.status', 'approved')
            ->count();

        if ($request->ajax()) {
            $data = SettleRequest::query()
                ->with([
                    'SettleRequestTrans',
                    'merchant:id,merchant_code,merchant_name',
                    'agent:id,agent_code,agent_name',
                    'payment_account' => fn ($q) => $q->with('bank:id,account_type')
                        ->select('id', 'bank_name', 'account_name', 'bank_id', 'account_number')
                ])
                ->when($request->checkValue == null, fn ($q) => $q->where('merchant_id', '!=', null))
                ->when($request->checkValue == 'agent', fn ($q) => $q->where('agent_id', '!=', null))
                ->orderByDesc('created_at')
                ->select('*')
                ->where('settle_requests.status', 'approved');

            return DataTables::of($data)
                ->editColumn('created_at', function ($data) {
                    return getAuthPreferenceTimezone($data?->created_at);
                })
                ->addColumn('merchant_code', function ($data) {
                    return $data->merchant?->merchant_code;
                })
                ->addColumn('agent_code', function ($data) {
                    return $data->agent?->agent_code;
                })
                ->editColumn('Currency', function ($data) {
                    return $data->Currency;
                })
                ->editColumn('total', function ($data) {
                    return number_format($data->total, 2);
                })
                ->editColumn('status', function ($data) {
                        return '<span class="text-success fw-bold">' . trans('messages.Success') . '</span>';
                    
                })
                ->addColumn('action', function ($data) use ($request) {
                    $action = '
                        <a class="btn btn-primary btn-sm" href="#" data-toggle="modal" data-target="#edit_user' . $data->id . '">' . trans("messages.View") . '</a>

                        <a class="btn btn-primary btn-sm pay_form" href="#" data-toggle="modal" data-target="#pay_form" data-id="' . $data->id . '" data-type="' . ($data->merchant ? 'yes' : 'no') . '">' . trans("messages.Pay") . '</a>
                    ';

                    $action .= '
                        <div id="edit_user' . $data->id . '" class="modal custom-modal fade" role="dialog">
                            <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">' . trans("messages.Transaction") . '</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>

                                    <div class="modal-body">
                                        <div class="table-responsive">
                                            <table class="table table-bordered test" style="padding: 7px 10px; !important">
                                        <tr>
                                            <td style="width: 25%; background-color: #6c6c70 !important; color: white;">' . trans("messages.Settlement Trans ID") . '</td>
                                            <td>' . $data->fourth_party_transection . '</td>
                                            <td style="width: 25%; background-color: #6c6c70 !important; color: white;">' . trans("messages.Created Time") . '</td>
                                            <td>' . $data?->created_at . '</td>
                                        </tr>';
                    if ($request->checkValue == null) {
                        $action .= '
                                        <tr>
                                            <td style="width: 25%; background-color: #6c6c70 !important; color: white;">' . trans("messages.Product ID") . '</td>
                                            <td>' . $data->product_id . '</td>
                                            <td style="width: 25%; background-color: #6c6c70 !important; color: white;">' . trans("messages.Merchant Name") . '</td>
                                            <td>' . $data->merchant?->merchant_name . '</td>
                                        </tr>
                                                ';
                    } else {
                        $action .= '
                                        <tr>
                                            <td style="width: 25%; background-color: #6c6c70 !important; color: white;">' . trans("messages.Agent Code") . '</td>
                                            <td>' . $data->agent?->agent_code . '</td>
                                            <td style="width: 25%; background-color: #6c6c70 !important; color: white;">' . trans("messages.Agent Name") . '</td>
                                            <td>' . $data->agent?->agent_name . '</td>
                                        </tr>
                                                ';
                    }

                    $action .= '<tr>
                                                <td style="width: 25%; background-color: #6c6c70 !important; color: white;">' . trans("messages.Settlement Amount") . '</td>
                                                <td>' . number_format($data->total, 2) . '</td>
                                                <td style="width: 25%; background-color: #6c6c70 !important; color: white;">' . trans("messages.Merchant Code") . '</td>
                                                <td>' . $data->merchant?->merchant_code . '</td>
                                                
                                            </tr>
                                            <tr>
                                                <td style="width: 25%; background-color: #6c6c70 !important; color: white;">' . trans("messages.Callback URL") . '</td>
                                                <td>' . $data->callback_url . '</td>
                                                <td style="width: 25%; background-color: #6c6c70 !important; color: white;">' . trans("messages.Account Name") . '</td>
                                                <td>' . $data->customer_name . '</td>
                                            </tr>
                                            <tr>
                                                    <td style="width: 25%; background-color: #6c6c70 !important; color: white;">' . trans("messages.Message") . '</td>
                                                    <td>' . $data->message . '</td>
                                                    <td style="width: 25%; background-color: #6c6c70 !important; color: white;">' . trans("messages.Bank Name") . '</td>
                                                    <td>' . $data->customer_bank_name . '</td>
                                            </tr>
                                            <tr>
                                                <td style="width: 25%; background-color: #6c6c70 !important; color: white;">' . trans("messages.Account Number") . '</td>
                                                <td>' . $data->customer_account_number . '</td>
                                                <td style="width: 25%; background-color: #6c6c70 !important; color: white;">' . trans("messages.Status") . '</td>
                                                <td>' . $data->status . '</td>
                                            </tr>
                                            </table>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    ';

                    return $action;
                })
                ->filter(function ($data) use ($request) {
                    if ($request->merchant_code) {
                        $data->where('merchant_id', $request->merchant_code);
                    }

                    if ($request->agent_code) {
                        $data->where('agent_id', $request->agent_code);
                    }

                    if ($request->daterange) {
                        $dateInput  = explode('-', $request->daterange);

                        $date[0]  = "$dateInput[0]/$dateInput[1]/$dateInput[2]";
                        if (count($dateInput) > 3) {
                            $date[1]  = "$dateInput[3]/$dateInput[4]/$dateInput[5]";
                        }

                        $start_date = Carbon::parse($date[0]);
                        $end_date   = Carbon::parse($date[1]);

                        $data->whereDate('created_at', '>=', $start_date)
                            ->whereDate('created_at', '<=', $end_date);
                    }

                    if (!empty($request->search)) {
                        $data->where(function ($q) use ($request) {
                            $q->orWhere('settlement_trans_id', 'LIKE', '%' . $request->search . '%')
                                ->orWhere('sub_total', 'LIKE', '%' . $request->search . '%')
                                ->orWhere('total', 'LIKE', '%' . $request->search . '%')
                                ->orWhere('status', 'LIKE', '%' . $request->search . '%')
                                ->orWhere('created_at', 'LIKE', '%' . $request->search . '%');
                        });
                    }
                })
                ->rawColumns(['action', 'status'])
                ->with('checkValue', $request->checkValue)
                ->make(true);
        }

        return view('settle.settleApprovedTableAdmin', compact('merchant', 'agents', 'merchantCount', 'agentCount'));
    }

    public function indexSettled()
    {
        $merchant = Merchant::find(Auth()->user()->merchant_id);
        $payment_table = PaymentDetail::join('payment_maps', 'payment_maps.id', '=', 'payment_details.product_id')
            ->select('payment_details.*', 'payment_maps.map_value as amount')
            ->where('payment_details.merchant_code', $merchant->merchant_code)
            ->where('payment_details.merchant_settle_status', 'settled')
            ->latest('created_at')
            ->paginate(10);

        return view('settle.settleTable', compact('payment_table'));
    }

    public function indexSettledAgent()
    {
        // $agent = Agent::find(Auth()->user()->agent_id);
        $merchant = Merchant::where('agent_id', Auth()->user()->agent_id)->get();
        $merchantCode = array();
        foreach ($merchant as $merchantVal) {
            array_push($merchantCode, $merchantVal->merchant_code);
        }
        // dd($merchantCode);
        $payment_table = PaymentDetail::join('payment_maps', 'payment_maps.id', '=', 'payment_details.product_id')
            ->select('payment_details.*', 'payment_maps.map_value as amount', 'payment_maps.agent_commission', 'payment_maps.merchant_commission')
            ->whereIn('payment_details.merchant_code', $merchantCode)
            ->where('payment_details.agent_settle_status', 'settled')
            ->latest('created_at')
            ->paginate(10);

        // return view('settle.unSettleTableAgent', compact('payment_table'));
        return view('settle.settleTableAgent', compact('payment_table'));
    }

    public function indexSettledAdmin()
    {
        // $merchant = Merchant::find(Auth()->user()->merchant_id);
        $payment_table = PaymentDetail::join('payment_maps', 'payment_maps.id', '=', 'payment_details.product_id')
            ->select('payment_details.*', 'payment_maps.map_value as amount')
            // ->where('payment_details.merchant_code', $merchant->merchant_code)
            ->where('payment_details.merchant_settle_status', 'settled')
            ->latest('created_at')
            ->paginate(10);

        return view('settle.settleTable', compact('payment_table'));
    }

    public function storeRequest(Request $request)
    {
        if (empty($request->paymentId)) {
            return response()->json(['error' => __('messages.Please select at least one transaction')]);
        }

        // check withdrawal date from billing
        $billing = Billing::where('merchant_id', auth()->user()->merchant_id)->first();

        if ($billing->status == "inherit") {
            $billing = Billing::where("merchant_id", null)->where("agent_id", null)->latest()->first();
        }
        if (!(date('H:i') > $billing->withdrawal_start_time && date('H:i') < $billing->withdrawal_end_time)) {
            return response()->json(['error' => __('messages.Your withdrawal start & end time:') . date('h:i A', strtotime($billing->withdrawal_start_time)) . __('messages.to') . date('h:i A', strtotime($billing->withdrawal_end_time))]);
        }

        // check daily withdraw count from settle request
        $withdrawCount = SettleRequest::where('merchant_id', auth()->user()->merchant_id)->whereDay('created_at', today())->count();
        if ($withdrawCount > $billing->daily_withdrawals) {
            return response()->json(['error' => __('messages.You reached your daily withdrawal limit')]);
        }

        // check daily withdraw amount
        $maxWithdrawAmount = SettleRequest::where('merchant_id', auth()->user()->merchant_id)->whereDay('created_at', today())->sum('total');
        if ($maxWithdrawAmount > $billing->max_daily_withdrawals) {
            return response()->json(['error' => __('messages.Daily amount withdrawal limit exceeded')]);
        }

        // check if amount exceed max daily withdraw
        if ($request->netAmount > $billing->max_daily_withdrawals) {
            return response()->json(['error' => __('messages.Maximum withdrawal amount per transaction exceeded')]);
        }

        // check min amount
        if ($request->netAmount < $billing->single_min_withdrawal) {
            return response()->json(['error' => __('messages.Provide the minimum transaction withdrawal amount')]);
        }

        // check max amount
        if ($request->netAmount > $billing->single_max_withdrawal) {
            return response()->json(['error' => __('messages.Maximum withdrawal amount per transaction exceeded')]);
        }

        $settle = SettleRequest::create([
            'settlement_trans_id' => "T" . rand(100000, 999999),
            'merchant_id' => auth()->user()->merchant_id,
            'sub_total' => $request->netAmount,
            'commission' => $request->commission,
            'total' => $request->amount,
            'transaction_amount' => $request->transactionAmount,
            'payment_account_id' => (int)$request->account_id,
            'handling_fee' => $request->handlingFee,
        ]);

        $paymentIdToArray = explode(',', $request->paymentId[0]);

        foreach ($paymentIdToArray as $paymentId) {
            $paymentDetail = PaymentDetail::find($paymentId);
            $paymentDetail->update(['merchant_settle_status' => 'settleRequest']);
            SettleRequestTrans::create(
                [
                    'merchant_id' => auth()->user()->merchant_id,
                    'settle_request_id' => $settle->id,
                    'payment_detail_id' => $paymentId,
                ]
            );
        }
        $success = __('messages.Success');
        Toastr::success(__('messages.Added Successfully'), $success);
        return back();
    }

    public function viewBilling(Request $request, $merchant)
    {
        try {
            $billing = Billing::where('merchant_id', $merchant)->first();
            if ($billing->status == "inherit") {
                $merchant = Billing::where('merchant_id', $merchant)->first();
                $billing = Billing::where("merchant_id", null)->where("agent_id", null)->latest()->first();
                return response()->json(['Merchant Name' => $merchant->merchant_name, 'Merchant Id' => $merchant->merchant_id, 'Billing' => $billing]);
            }
            return response()->json($billing, 200);
        } catch (\Exception $e) {
            return response()->json([
                'messages' => ['Error'],
            ], 500);
        }
    }

    public function viewBillingAgent(Request $request, $agent)
    {
        try {
            $billing = Billing::where('agent_id', $agent)->first();
            if ($billing->status == "inherit") {
                $agent = Billing::where('agent_id', $agent)->first();
                $billing = Billing::where("agent_id", null)->latest()->first();
                return response()->json(['Agent Name' => $agent->agent_name, 'Agent Id' => $agent->agent_id, 'Billing' => $billing]);
            }
            return response()->json($billing, 200);
        } catch (\Exception $e) {
            return response()->json([
                'messages' => ['Error'],
            ], 500);
        }
    }

    public function changeRequestStatus(Request $request)
    {
        DB::beginTransaction();
        try {
            if ($request->userType == 'yes') {
                $workType = 'merchant_settle_status';
            } else {
                $workType = 'agent_settle_status';
            }
            $settleRequest = SettleRequest::find($request->settlement_id);
            if ($request->status == 'approved') {
                $settleRequest->update(['status' => 'approved']);
                $msg2_success = __('messages.Request Approved Successfully');
                $success = __('messages.Success');
                Toastr::success($msg2_success, $success);
            }
            if ($request->status == 'rejected') {
                $settleRequest->update(['status' => 'rejected']);

                $settleRequestTrans = SettleRequestTrans::where('settle_request_id', $settleRequest->id)->get();
                foreach ($settleRequestTrans as $item) {
                    PaymentDetail::where('id', $item->payment_detail_id)->update([$workType => 'unsettled']);
                }
                $msg2_err = __('messages.Request Rejected Successfully');
                $success = __('messages.Success');
                Toastr::success($msg2_err, $success);
            }

            if ($request->status == 'paid') {
                $settleRequest->update(['status' => 'paid']);

                $settleRequestTrans = SettleRequestTrans::where('settle_request_id', $settleRequest->id)->get();
                foreach ($settleRequestTrans as $item) {
                    PaymentDetail::where('id', $item->payment_detail_id)->update([$workType => 'settled']);
                }
                $success = __('messages.Success');
                Toastr::success('Request Paid Successfully :)', $success);
            }

            $settleRequest->update(['remark' => $request->remark]);
            DB::commit();
            return redirect()->back();
        } catch (\Exception $e) {
            DB::rollback();
            $msgErr = __('messages.Failed to delete');
            Toastr::error($msgErr, 'Error');
            return redirect()->back();
        }
    }

    public function agentStore(Request $request)
    {
        DB::beginTransaction();
        try {
            if ($request->agent_name && $request->agent_id) {
                if ($request->settlement_settings == "inherit") {
                    $array = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];
                    $addRecord =
                        [
                            'agent_name' => $request->agent_name,
                            'agent_id' => $request->agent_id,
                            'status' => $request->settlement_settings,
                            'withdraw_switch' => $request->withdraw_switch == 'turn_on' ? '0' : '0',
                            'week_allow_withdrawals' => $request->week_allow_withdrawals == null ? $array : $array,
                            'withdrawal_start_time' => $request->withdrawal_start_time == null ? '00:00' : '00:00',
                            'withdrawal_end_time' => $request->withdrawal_end_time == null ? '23:59' : '23:59',
                            'daily_withdrawals' => $request->daily_withdrawals == null ? '0' : '0',
                            'max_daily_withdrawals' => $request->max_daily_withdrawals == null ? '0' : '0',
                            'single_max_withdrawal' => $request->single_max_withdrawal == null ? '0' : '0',
                            'single_min_withdrawal' => $request->single_min_withdrawal == null ? '0' : '0',
                            'settlement_fee_type' => $request->settlement_fee_type == null ? '0' : '0',
                            'settlement_fee_ratio' => $request->settlement_fee_ratio == 'percentage_fee' ? '0' : '0',
                            // 'single_transaction_fee_limit' => $request->single_transaction_fee_limit == null ? '0' : '0',
                            // 'payment_method' => $request->payment_method == null ? '0' : '0',
                        ];
                } else {

                    $messages = [
                        'required' => __('validation.The :attribute field is required.'),
                        'regex' => __('validation.The :attribute must be numbers.')
                    ];

                    $validator = Validator::make($request->all(), [
                        'week_allow_withdrawals' => 'required',
                        'daily_withdrawals' => 'required|regex:/^(([0-9]*)(\.([0-9]+))?)$/',
                        'max_daily_withdrawals' => 'required|regex:/^(([0-9]*)(\.([0-9]+))?)$/',
                        'single_max_withdrawal' => 'required|regex:/^(([0-9]*)(\.([0-9]+))?)$/',
                        'single_min_withdrawal' => 'required|regex:/^(([0-9]*)(\.([0-9]+))?)$/',
                        'settlement_fee_ratio' => 'required|regex:/^(([0-9]*)(\.([0-9]+))?)$/',
                        // 'single_transaction_fee_limit' => 'required|regex:/^(([0-9]*)(\.([0-9]+))?)$/',
                    ], $messages);

                    if ($validator->fails()) {
                        return response()->json(['error' => $validator->errors()]);
                    }

                    $addRecord =
                        [
                            'agent_name' => $request->agent_name,
                            'agent_id' => $request->agent_id,
                            'status' => $request->settlement_settings,
                            'withdraw_switch' => $request->withdraw_switch,
                            'week_allow_withdrawals' => $request->week_allow_withdrawals,
                            'withdrawal_start_time' => $request->withdrawal_start_time,
                            'withdrawal_end_time' => $request->withdrawal_end_time,
                            'daily_withdrawals' => $request->daily_withdrawals,
                            'max_daily_withdrawals' => $request->max_daily_withdrawals,
                            'single_max_withdrawal' => $request->single_max_withdrawal,
                            'single_min_withdrawal' => $request->single_min_withdrawal,
                            'settlement_fee_type' => $request->settlement_fee_type,
                            'settlement_fee_ratio' => $request->settlement_fee_ratio,
                            // 'single_transaction_fee_limit' => $request->single_transaction_fee_limit,
                            // 'payment_method' => '0',
                        ];
                }
                $agent = Billing::where('agent_id', $request->agent_id)->first();
                if ($agent) {
                    $agent->update($addRecord);
                    $msgSuccess = __('messages.Updated Successfully');
                    $success = __('messages.Success');
                    Toastr::success($msgSuccess, $success);
                    DB::commit();
                    return redirect()->back();
                } else {
                    //dd($addRecord);
                    Billing::create($addRecord);
                    $msgSuccess1 = __('messages.Added Successfully');
                    $success = __('messages.Success');
                    Toastr::success($msgSuccess1, $success);
                    DB::commit();
                    return redirect()->back();
                }
            } else {

                $messages = [
                    'required' => __('validation.The :attribute field is required.'),
                    'regex' => __('validation.The :attribute must be numbers.')
                ];

                $validator = Validator::make($request->all(), [
                    'week_allow_withdrawals' => 'required',
                    'daily_withdrawals' => 'required|regex:/^(([0-9]*)(\.([0-9]+))?)$/',
                    'max_daily_withdrawals' => 'required|regex:/^(([0-9]*)(\.([0-9]+))?)$/',
                    'single_max_withdrawal' => 'required|regex:/^(([0-9]*)(\.([0-9]+))?)$/',
                    'single_min_withdrawal' => 'required|regex:/^(([0-9]*)(\.([0-9]+))?)$/',
                    'settlement_fee_ratio' => 'required|regex:/^(([0-9]*)(\.([0-9]+))?)$/',
                    // 'single_transaction_fee_limit' => 'required|regex:/^(([0-9]*)(\.([0-9]+))?)$/',
                ], $messages);

                if ($validator->fails()) {
                    return response()->json(['error' => $validator->errors()]);
                }

                $addRecord =
                    [
                        'withdraw_switch' => $request->withdraw_switch,
                        'week_allow_withdrawals' => $request->week_allow_withdrawals,
                        'withdrawal_start_time' => $request->withdrawal_start_time,
                        'withdrawal_end_time' => $request->withdrawal_end_time,
                        'daily_withdrawals' => $request->daily_withdrawals,
                        'max_daily_withdrawals' => $request->max_daily_withdrawals,
                        'single_max_withdrawal' => $request->single_max_withdrawal,
                        'single_min_withdrawal' => $request->single_min_withdrawal,
                        'settlement_fee_type' => $request->settlement_fee_type,
                        'settlement_fee_ratio' => $request->settlement_fee_ratio,
                        // 'single_transaction_fee_limit' => $request->single_transaction_fee_limit,
                        // 'payment_method' => $request->payment_method,
                        'agent_name' => null,
                        'agent_id' => null,
                        'status' => null,
                    ];
                $agent = Billing::where('merchant_id', null)->where("agent_id", null)->first();
                if ($agent) {
                    $agent->update($addRecord);
                } else {
                    Billing::create($addRecord);
                }
            }
            $succcessMsg = __('messages.Added Successfully');
            Toastr::success($succcessMsg, 'Success');
            DB::commit();
            return redirect()->back();
        } catch (\Exception $e) {
            DB::rollback();
            $deletesMsg = __('messages.Failed to delete');
            Toastr::error($deletesMsg, 'Error');
            return redirect()->back();
        }
    }

    public function indexSettleHistory(Request $request)
    {
        $merchant = Merchant::get();
        $agents = Agent::get();

        $merchantCount = SettleRequest::where('merchant_id', '!=', null)->count();

        $agentCount = SettleRequest::where('agent_id', '!=', null)->count();

        if ($request->ajax()) {
            $data = SettleRequest::query()
                ->with([
                    'SettleRequestTrans',
                    'merchant:id,merchant_code,merchant_name',
                    'agent:id,agent_code,agent_name',
                    'payment_account' => fn ($q) => $q->with('bank:id,account_type')
                        ->select('id', 'bank_name', 'account_name', 'bank_id', 'account_number')
                ])
                ->when($request->checkValue == null, fn ($q) => $q->where('merchant_id', '!=', null))
                ->when($request->checkValue == 'agent', fn ($q) => $q->where('agent_id', '!=', null))
                ->orderByDesc('created_at')
                ->select('*');

            return DataTables::of($data)
                ->editColumn('created_at', function ($data) {
                    return getAuthPreferenceTimezone($data?->created_at);
                })
                ->addColumn('merchant_code', function ($data) {
                    return $data->merchant?->merchant_code;
                })
                ->addColumn('agent_code', function ($data) {
                    return $data->agent?->agent_code;
                })
                ->editColumn('Currency', function ($data) {
                    return $data->Currency;
                })
                ->editColumn('total', function ($data) {
                    return number_format($data->total, 2);
                })
                ->editColumn('status', function ($data) {
                    if ($data->status == 'success' || $data->status == 'Success' || $data->status == 'SUCCESS') {
                        return '<span class="text-success fw-bold">' . trans('messages.Success') . '</span>';
                    }elseif ($data->status == 'approved' || $data->status == 'Approved' || $data->status == 'APPROVED') {
                            return '<span class="text-success fw-bold">' . trans('messages.Approved') . '</span>';
                    }elseif ($data->status == 'paid' || $data->status == 'Paid' || $data->status == 'PAID') {
                            return '<span class="text-success fw-bold">' . trans('messages.Paid') . '</span>';
                    } elseif ($data->status == 'pending' || $data->status == 'Pending' || $data->status == 'PENDING') {
                        return '<span class="text-primary fw-bold">' . trans('messages.pending') . '</span>';
                    } else {
                        return '<span class="text-danger fw-bold">' . trans('messages.Failed') . '</span>';
                    }
                })
                ->addColumn('action', function ($data) use ($request) {
                    $action = '
                        <a class="btn btn-primary btn-sm" href="#" data-toggle="modal" data-target="#edit_user' . $data->id . '">' . trans("messages.View") . '</a>
                    ';

                    $action .= '
                        <div id="edit_user' . $data->id . '" class="modal custom-modal fade" role="dialog">
                            <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">' . trans("messages.Transaction") . '</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>

                                    <div class="modal-body">
                                        <div class="table-responsive">
                                            <table class="table table-bordered test" style="padding: 7px 10px; !important">
                                            <tr>
                                                <td style="width: 25%; background-color: #6c6c70 !important; color: white;">' . trans("messages.Settlement Trans ID") . '</td>
                                                <td>' . $data->fourth_party_transection . '</td>
                                                <td style="width: 25%; background-color: #6c6c70 !important; color: white;">' . trans("messages.Created Time") . '</td>
                                                <td>' . $data?->created_at . '</td>
                                            </tr>';
                    if ($request->checkValue == null) {
                        $action .= '
                                                <tr>
                                                    <td style="width: 25%; background-color: #6c6c70 !important; color: white;">' . trans("messages.Product ID") . '</td>
                                                    <td>' . $data->product_id . '</td>
                                                    <td style="width: 25%; background-color: #6c6c70 !important; color: white;">' . trans("messages.Merchant Name") . '</td>
                                                    <td>' . $data->merchant?->merchant_name . '</td>
                                                </tr>
                                                    ';
                    } else {
                        $action .= '
                                                <tr>
                                                    <td style="width: 25%; background-color: #6c6c70 !important; color: white;">' . trans("messages.Agent Code") . '</td>
                                                    <td>' . $data->agent?->agent_code . '</td>
                                                    <td style="width: 25%; background-color: #6c6c70 !important; color: white;">' . trans("messages.Agent Name") . '</td>
                                                    <td>' . $data->agent?->agent_name . '</td>
                                                </tr>
                                                    ';
                    }
                    $action .= '<tr>
                                                    <td style="width: 25%; background-color: #6c6c70 !important; color: white;">' . trans("messages.Settlement Amount") . '</td>
                                                    <td>' . number_format($data->total, 2) . '</td>
                                                    <td style="width: 25%; background-color: #6c6c70 !important; color: white;">' . trans("messages.Merchant Code") . '</td>
                                                    <td>' . $data->merchant?->merchant_code . '</td>
                                                    
                                                </tr>
                                                <tr>
                                                    <td style="width: 25%; background-color: #6c6c70 !important; color: white;">' . trans("messages.Callback URL") . '</td>
                                                    <td>' . $data->callback_url . '</td>
                                                    <td style="width: 25%; background-color: #6c6c70 !important; color: white;">' . trans("messages.Account Name") . '</td>
                                                    <td>' . $data->customer_name . '</td>
                                                </tr>
                                                <tr>
                                                    <td style="width: 25%; background-color: #6c6c70 !important; color: white;">' . trans("messages.Message") . '</td>
                                                    <td>' . $data->message . '</td>
                                                    <td style="width: 25%; background-color: #6c6c70 !important; color: white;">' . trans("messages.Bank Name") . '</td>
                                                    <td>' . $data->customer_bank_name . '</td>
                                                </tr>
                                                <tr>
                                                    <td style="width: 25%; background-color: #6c6c70 !important; color: white;">' . trans("messages.Account Number") . '</td>
                                                    <td>' . $data->customer_account_number . '</td>
                                                    <td style="width: 25%; background-color: #6c6c70 !important; color: white;">' . trans("messages.Status") . '</td>
                                                    <td>' . $data->status . '</td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    ';

                    return $action;
                })
                ->filter(function ($data) use ($request) {
                    if ($request->status) {
                        $data->where('status', $request->status);
                    }

                    if ($request->merchant_code) {
                        $data->where('merchant_id', $request->merchant_code);
                    }

                    if ($request->agent_code) {
                        $data->where('agent_id', $request->agent_code);
                    }

                    if ($request->daterange) {
                        $dateInput  = explode('-', $request->daterange);

                        $date[0]  = "$dateInput[0]/$dateInput[1]/$dateInput[2]";
                        if (count($dateInput) > 3) {
                            $date[1]  = "$dateInput[3]/$dateInput[4]/$dateInput[5]";
                        }

                        $start_date = Carbon::parse($date[0]);
                        $end_date   = Carbon::parse($date[1]);

                        $data->whereDate('created_at', '>=', $start_date)
                            ->whereDate('created_at', '<=', $end_date);
                    }

                    if (!empty($request->search)) {
                        $data->where(function ($q) use ($request) {
                            $q->orWhere('settlement_trans_id', 'LIKE', '%' . $request->search . '%')
                            ->orWhere('sub_total', 'LIKE', '%' . $request->search . '%')
                            ->orWhere('total', 'LIKE', '%' . $request->search . '%')
                            ->orWhere('status', 'LIKE', '%' . $request->search . '%')
                            ->orWhere('created_at', 'LIKE', '%' . $request->search . '%');
                        });
                    }
                })
                ->rawColumns(['action', 'status'])
                ->with('checkValue', $request->checkValue)
                ->make(true);
        }

        return view('settle.settleRequestHistory', compact('merchant', 'agents', 'merchantCount', 'agentCount'));
    }

    public function indexSettleHistoryMerchant(Request $request)
    {
        if ($request->ajax()) {
            $data = SettleRequest::query()
                ->where('merchant_id', auth()->user()->merchant_id)
                ->with([
                    'SettleRequestTrans',
                    'merchant:id,merchant_code,merchant_name',
                    'payment_account' => fn ($q) => $q->with('bank:id,account_type')
                        ->select('id', 'bank_name', 'account_name', 'bank_id', 'account_number')
                ])
                ->orderByDesc('created_at')
                ->select('*');

                return DataTables::of($data)
                ->editColumn('created_at', function ($data) {
                    return getAuthPreferenceTimezone($data?->created_at);
                })
                ->addColumn('merchant_code', function ($data) {
                    return $data->merchant?->merchant_code;
                })
                ->addColumn('agent_code', function ($data) {
                    return $data->agent?->agent_code;
                })
                ->editColumn('Currency', function ($data) {
                    return $data->Currency;
                })
                ->editColumn('total', function ($data) {
                    return number_format($data->total, 2);
                })
                ->editColumn('status', function ($data) {
                    if ($data->status == 'success' || $data->status == 'Success' || $data->status == 'SUCCESS') {
                        return '<span class="text-success fw-bold">' . trans('messages.Success') . '</span>';
                    }elseif ($data->status == 'approved' || $data->status == 'Approved' || $data->status == 'APPROVED') {
                            return '<span class="text-success fw-bold">' . trans('messages.Success') . '</span>';
                    }elseif ($data->status == 'paid' || $data->status == 'Paid' || $data->status == 'PAID') {
                            return '<span class="text-success fw-bold">' . trans('messages.Success') . '</span>';
                    } elseif ($data->status == 'pending' || $data->status == 'Pending' || $data->status == 'PENDING') {
                        return '<span class="text-primary fw-bold">' . trans('messages.pending') . '</span>';
                    } else {
                        return '<span class="text-danger fw-bold">' . trans('messages.Failed') . '</span>';
                    }
                })
                ->addColumn('action', function ($data) use ($request) {
                    $action = '
                        <a class="btn btn-primary btn-sm" href="#" data-toggle="modal" data-target="#edit_user' . $data->id . '">' . trans("messages.View") . '</a>
                    ';

                    $action .= '
                        <div id="edit_user' . $data->id . '" class="modal custom-modal fade" role="dialog">
                            <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">' . trans("messages.Transaction") . '</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>

                                    <div class="modal-body">
                                        <div class="table-responsive">
                                            <table class="table table-bordered test" style="padding: 7px 10px; !important">
                                            <tr>
                                                <td style="width: 25%; background-color: #6c6c70 !important; color: white;">' . trans("messages.Settlement Trans ID") . '</td>
                                                <td>' . $data->fourth_party_transection . '</td>
                                                <td style="width: 25%; background-color: #6c6c70 !important; color: white;">' . trans("messages.Created Time") . '</td>
                                                <td>' . $data?->created_at . '</td>
                                            </tr>';
                    if ($request->checkValue == null) {
                        $action .= '
                                                <tr>
                                                    <td style="width: 25%; background-color: #6c6c70 !important; color: white;">' . trans("messages.Product ID") . '</td>
                                                    <td>' . $data->product_id . '</td>
                                                    <td style="width: 25%; background-color: #6c6c70 !important; color: white;">' . trans("messages.Merchant Name") . '</td>
                                                    <td>' . $data->merchant?->merchant_name . '</td>
                                                </tr>
                                                    ';
                    } else {
                        $action .= '
                                                <tr>
                                                    <td style="width: 25%; background-color: #6c6c70 !important; color: white;">' . trans("messages.Agent Code") . '</td>
                                                    <td>' . $data->agent?->agent_code . '</td>
                                                    <td style="width: 25%; background-color: #6c6c70 !important; color: white;">' . trans("messages.Agent Name") . '</td>
                                                    <td>' . $data->agent?->agent_name . '</td>
                                                </tr>
                                                    ';
                    }
                    $action .= '<tr>
                                                    <td style="width: 25%; background-color: #6c6c70 !important; color: white;">' . trans("messages.Settlement Amount") . '</td>
                                                    <td>' . number_format($data->total, 2) . '</td>
                                                    <td style="width: 25%; background-color: #6c6c70 !important; color: white;">' . trans("messages.Merchant Code") . '</td>
                                                    <td>' . $data->merchant?->merchant_code . '</td>
                                                    
                                                </tr>
                                                <tr>
                                                    <td style="width: 25%; background-color: #6c6c70 !important; color: white;">' . trans("messages.Callback URL") . '</td>
                                                    <td>' . $data->callback_url . '</td>
                                                    <td style="width: 25%; background-color: #6c6c70 !important; color: white;">' . trans("messages.Account Name") . '</td>
                                                    <td>' . $data->customer_name . '</td>
                                                </tr>
                                                <tr>
                                                    <td style="width: 25%; background-color: #6c6c70 !important; color: white;">' . trans("messages.Message") . '</td>
                                                    <td>' . $data->message . '</td>
                                                    <td style="width: 25%; background-color: #6c6c70 !important; color: white;">' . trans("messages.Bank Name") . '</td>
                                                    <td>' . $data->customer_bank_name . '</td>
                                                </tr>
                                                <tr>
                                                    <td style="width: 25%; background-color: #6c6c70 !important; color: white;">' . trans("messages.Account Number") . '</td>
                                                    <td>' . $data->customer_account_number . '</td>
                                                    <td style="width: 25%; background-color: #6c6c70 !important; color: white;">' . trans("messages.Status") . '</td>
                                                    <td>' . $data->status . '</td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    ';

                    return $action;
                })
                ->filter(function ($data) use ($request) {
                    if ($request->status) {
                        $data->where('status', $request->status);
                    }

                    if ($request->merchant_code) {
                        $data->where('merchant_id', $request->merchant_code);
                    }

                    if ($request->agent_code) {
                        $data->where('agent_id', $request->agent_code);
                    }

                    if ($request->daterange) {
                        $dateInput  = explode('-', $request->daterange);

                        $date[0]  = "$dateInput[0]/$dateInput[1]/$dateInput[2]";
                        if (count($dateInput) > 3) {
                            $date[1]  = "$dateInput[3]/$dateInput[4]/$dateInput[5]";
                        }

                        $start_date = Carbon::parse($date[0]);
                        $end_date   = Carbon::parse($date[1]);

                        $data->whereDate('created_at', '>=', $start_date)
                            ->whereDate('created_at', '<=', $end_date);
                    }

                    if (!empty($request->search)) {
                        $data->where(function ($q) use ($request) {
                            $q->orWhere('settlement_trans_id', 'LIKE', '%' . $request->search . '%')
                            ->orWhere('sub_total', 'LIKE', '%' . $request->search . '%')
                            ->orWhere('total', 'LIKE', '%' . $request->search . '%')
                            ->orWhere('status', 'LIKE', '%' . $request->search . '%')
                            ->orWhere('created_at', 'LIKE', '%' . $request->search . '%');
                        });
                    }
                })
                ->rawColumns(['action', 'status'])
                ->with('checkValue', $request->checkValue)
                ->make(true);
        }

        return view('settle.settleRequestHistoryMerchant');
    }

    public function indexSettleHistoryAgent(Request $request)
    {
        if ($request->ajax()) {
            $data = SettleRequest::query()
                ->where('agent_id', auth()->user()->agent_id)
                ->with([
                    'SettleRequestTrans',
                    'agent:id,agent_code,agent_name',
                    'payment_account' => fn ($q) => $q->with('bank:id,account_type')
                        ->select('id', 'bank_name', 'account_name', 'bank_id', 'account_number')
                ])
                ->orderByDesc('created_at')
                ->select('*');

            return DataTables::of($data)
                ->editColumn('created_at', function ($data) {
                    return getAuthPreferenceTimezone($data?->created_at);
                })
                ->addColumn('merchant_code', function ($data) {
                    return $data->merchant?->merchant_code;
                })
                ->addColumn('agent_code', function ($data) {
                    return $data->agent?->agent_code;
                })
                ->editColumn('Currency', function ($data) {
                    return $data->Currency;
                })
                ->editColumn('total', function ($data) {
                    return number_format($data->total, 2);
                })
                ->editColumn('status', function ($data) {
                    if ($data->status == 'success' || $data->status == 'Success' || $data->status == 'SUCCESS') {
                        return '<span class="text-success fw-bold">' . trans('messages.Success') . '</span>';
                    }elseif ($data->status == 'approved' || $data->status == 'Approved' || $data->status == 'APPROVED') {
                            return '<span class="text-success fw-bold">' . trans('messages.Success') . '</span>';
                    }elseif ($data->status == 'paid' || $data->status == 'Paid' || $data->status == 'PAID') {
                            return '<span class="text-success fw-bold">' . trans('messages.Success') . '</span>';
                    } elseif ($data->status == 'pending' || $data->status == 'Pending' || $data->status == 'PENDING') {
                        return '<span class="text-primary fw-bold">' . trans('messages.pending') . '</span>';
                    } else {
                        return '<span class="text-danger fw-bold">' . trans('messages.Failed') . '</span>';
                    }
                })
                ->addColumn('action', function ($data) use ($request) {
                    $action = '
                        <a class="btn btn-primary btn-sm" href="#" data-toggle="modal" data-target="#edit_user' . $data->id . '">' . trans("messages.View") . '</a>
                    ';

                    $action .= '
                        <div id="edit_user' . $data->id . '" class="modal custom-modal fade" role="dialog">
                            <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">' . trans("messages.Transaction") . '</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>

                                    <div class="modal-body">
                                        <div class="table-responsive">
                                            <table class="table table-bordered test" style="padding: 7px 10px; !important">
                                            <tr>
                                                <td style="width: 25%; background-color: #6c6c70 !important; color: white;">' . trans("messages.Settlement Trans ID") . '</td>
                                                <td>' . $data->fourth_party_transection . '</td>
                                                <td style="width: 25%; background-color: #6c6c70 !important; color: white;">' . trans("messages.Created Time") . '</td>
                                                <td>' . $data?->created_at . '</td>
                                            </tr>
                                                <tr>
                                                    <td style="width: 25%; background-color: #6c6c70 !important; color: white;">' . trans("messages.Product ID") . '</td>
                                                    <td>' . $data->product_id . '</td>
                                                    <td style="width: 25%; background-color: #6c6c70 !important; color: white;">' . trans("messages.Merchant Name") . '</td>
                                                    <td>' . $data->merchant?->merchant_name . '</td>
                                                </tr>
                                                    
                                                <tr>
                                                    <td style="width: 25%; background-color: #6c6c70 !important; color: white;">' . trans("messages.Agent Code") . '</td>
                                                    <td>' . $data->agent?->agent_code . '</td>
                                                    <td style="width: 25%; background-color: #6c6c70 !important; color: white;">' . trans("messages.Agent Name") . '</td>
                                                    <td>' . $data->agent?->agent_name . '</td>
                                                </tr>
                                                <tr>
                                                    <td style="width: 25%; background-color: #6c6c70 !important; color: white;">' . trans("messages.Settlement Amount") . '</td>
                                                    <td>' . number_format($data->total, 2) . '</td>
                                                    <td style="width: 25%; background-color: #6c6c70 !important; color: white;">' . trans("messages.Merchant Code") . '</td>
                                                    <td>' . $data->merchant?->merchant_code . '</td>
                                                    
                                                </tr>
                                                <tr>
                                                    <td style="width: 25%; background-color: #6c6c70 !important; color: white;">' . trans("messages.Callback URL") . '</td>
                                                    <td>' . $data->callback_url . '</td>
                                                    <td style="width: 25%; background-color: #6c6c70 !important; color: white;">' . trans("messages.Account Name") . '</td>
                                                    <td>' . $data->customer_name . '</td>
                                                </tr>
                                                <tr>
                                                    <td style="width: 25%; background-color: #6c6c70 !important; color: white;">' . trans("messages.Message") . '</td>
                                                    <td>' . $data->message . '</td>
                                                    <td style="width: 25%; background-color: #6c6c70 !important; color: white;">' . trans("messages.Bank Name") . '</td>
                                                    <td>' . $data->customer_bank_name . '</td>
                                                </tr>
                                                <tr>
                                                    <td style="width: 25%; background-color: #6c6c70 !important; color: white;">' . trans("messages.Account Number") . '</td>
                                                    <td>' . $data->customer_account_number . '</td>
                                                    <td style="width: 25%; background-color: #6c6c70 !important; color: white;">' . trans("messages.Status") . '</td>
                                                    <td>' . $data->status . '</td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    ';

                    return $action;
                })
                ->filter(function ($data) use ($request) {
                    if ($request->status) {
                        $data->where('status', $request->status);
                    }

                    if ($request->merchant_code) {
                        $data->where('merchant_id', $request->merchant_code);
                    }

                    if ($request->agent_code) {
                        $data->where('agent_id', $request->agent_code);
                    }

                    if ($request->daterange) {
                        $dateInput  = explode('-', $request->daterange);

                        $date[0]  = "$dateInput[0]/$dateInput[1]/$dateInput[2]";
                        if (count($dateInput) > 3) {
                            $date[1]  = "$dateInput[3]/$dateInput[4]/$dateInput[5]";
                        }

                        $start_date = Carbon::parse($date[0]);
                        $end_date   = Carbon::parse($date[1]);

                        $data->whereDate('created_at', '>=', $start_date)
                            ->whereDate('created_at', '<=', $end_date);
                    }

                    if (!empty($request->search)) {
                        $data->where(function ($q) use ($request) {
                            $q->orWhere('settlement_trans_id', 'LIKE', '%' . $request->search . '%')
                            ->orWhere('sub_total', 'LIKE', '%' . $request->search . '%')
                            ->orWhere('total', 'LIKE', '%' . $request->search . '%')
                            ->orWhere('status', 'LIKE', '%' . $request->search . '%')
                            ->orWhere('created_at', 'LIKE', '%' . $request->search . '%');
                        });
                    }
                })
                ->rawColumns(['action', 'status'])
                ->with('checkValue', $request->checkValue)
                ->make(true);
        }

        return view('settle.settleRequestHistoryAgent');
    }

    public function listBankMerchantAll(Request $request)
    {
        return PaymentAccount::where('id', $request->id)
            ->with('bank:id,account_type')
            ->select('id', 'account_name', 'bank_name', 'account_number', 'bank_id')
            ->first();
    }

    public function listBankAgentAll(Request $request)
    {
        return PaymentAccount::where('id', $request->id)
            ->with('bank:id,account_type')
            ->select('id', 'account_name', 'bank_name', 'account_number', 'bank_id')
            ->first();
    }
}
