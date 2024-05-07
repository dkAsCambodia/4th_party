<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaymentFormRequest;
use App\Models\Billing;
use App\Models\GatewayAccount;
use App\Models\GatewayAccountMethod;
use App\Models\GatewayPaymentChannel;
use App\Models\Merchant;
use App\Models\ParameterSetting;
//use App\Models\PaymentSource;
use App\Models\ParameterValue;
use App\Models\PaymentChannel;
use App\Models\PaymentDetail;
use App\Models\PaymentMap;
use App\Models\PaymentMethod;
use App\Models\PaymentUrl;
use App\Models\SettleRequest;
use App\Models\Timezone;
use App\Models\User;
use App\Notifications\PaymentDetailNotification;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Yajra\DataTables\DataTables;

class PaymentDetailController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $timezones = Timezone::where('status', 'active')->get();

        if ($request->ajax()) {
            $payment_count = PaymentDetail::when($request->status, fn($q) => $q->where('payment_status', $request->status))
                ->when($request->daterange, function ($q) use ($request) {
                    $dateInput = explode('-', $request->daterange);

                    $date[0] = "$dateInput[0]/$dateInput[1]/$dateInput[2]";
                    if (count($dateInput) > 3) {
                        $date[1] = "$dateInput[3]/$dateInput[4]/$dateInput[5]";
                    }

                    $start_date = Carbon::parse($date[0]);
                    $end_date = Carbon::parse($date[1]);

                    $q->whereDate('created_at', '>=', $start_date)
                        ->whereDate('created_at', '<=', $end_date);
                })
                ->when($request->search, function ($q) use ($request) {
                    $q->where('payment_details.order_status', 'LIKE', '%' . $request->search . '%')
                        ->orWhere('payment_details.amount', 'LIKE', '%' . $request->search . '%')
                        ->orWhere('payment_details.transaction_id', 'LIKE', '%' . $request->search . '%')
                        ->orWhere('payment_details.merchant_code', 'LIKE', '%' . $request->search . '%')
                        ->orWhere('payment_details.fourth_party_transection', 'LIKE', '%' . $request->search . '%');
                })
                ->count();

            $order_amount_sum = PaymentDetail::when($request->status, fn($q) => $q->where('payment_status', $request->status))
                ->when($request->daterange, function ($q) use ($request) {
                    $dateInput = explode('-', $request->daterange);

                    $date[0] = "$dateInput[0]/$dateInput[1]/$dateInput[2]";
                    if (count($dateInput) > 3) {
                        $date[1] = "$dateInput[3]/$dateInput[4]/$dateInput[5]";
                    }

                    $start_date = Carbon::parse($date[0]);
                    $end_date = Carbon::parse($date[1]);

                    $q->whereDate('created_at', '>=', $start_date)
                        ->whereDate('created_at', '<=', $end_date);
                })
                ->when($request->search, function ($q) use ($request) {
                    $q->where('payment_details.order_status', 'LIKE', '%' . $request->search . '%')
                        ->orWhere('payment_details.amount', 'LIKE', '%' . $request->search . '%')
                        ->orWhere('payment_details.transaction_id', 'LIKE', '%' . $request->search . '%')
                        ->orWhere('payment_details.merchant_code', 'LIKE', '%' . $request->search . '%')
                        ->orWhere('payment_details.fourth_party_transection', 'LIKE', '%' . $request->search . '%');
                })
                ->sum('amount');

            $order_success_count = PaymentDetail::where('payment_status', 'success')
                ->when($request->status, fn($q) => $q->where('payment_status', $request->status))
                ->when($request->daterange, function ($q) use ($request) {
                    $dateInput = explode('-', $request->daterange);

                    $date[0] = "$dateInput[0]/$dateInput[1]/$dateInput[2]";
                    if (count($dateInput) > 3) {
                        $date[1] = "$dateInput[3]/$dateInput[4]/$dateInput[5]";
                    }

                    $start_date = Carbon::parse($date[0]);
                    $end_date = Carbon::parse($date[1]);

                    $q->whereDate('created_at', '>=', $start_date)
                        ->whereDate('created_at', '<=', $end_date);
                })
                ->when($request->search, function ($q) use ($request) {
                    $q->where('payment_details.order_status', 'LIKE', '%' . $request->search . '%')
                        ->orWhere('payment_details.amount', 'LIKE', '%' . $request->search . '%')
                        ->orWhere('payment_details.transaction_id', 'LIKE', '%' . $request->search . '%')
                        ->orWhere('payment_details.merchant_code', 'LIKE', '%' . $request->search . '%')
                        ->orWhere('payment_details.fourth_party_transection', 'LIKE', '%' . $request->search . '%');
                })
                ->count();

            $order_success_sum = PaymentDetail::where('payment_status', 'success')
                ->when($request->status, fn($q) => $q->where('payment_status', $request->status))
                ->when($request->daterange, function ($q) use ($request) {
                    $dateInput = explode('-', $request->daterange);

                    $date[0] = "$dateInput[0]/$dateInput[1]/$dateInput[2]";
                    if (count($dateInput) > 3) {
                        $date[1] = "$dateInput[3]/$dateInput[4]/$dateInput[5]";
                    }

                    $start_date = Carbon::parse($date[0]);
                    $end_date = Carbon::parse($date[1]);

                    $q->whereDate('created_at', '>=', $start_date)
                        ->whereDate('created_at', '<=', $end_date);
                })
                ->when($request->search, function ($q) use ($request) {
                    $q->where('payment_details.order_status', 'LIKE', '%' . $request->search . '%')
                        ->orWhere('payment_details.amount', 'LIKE', '%' . $request->search . '%')
                        ->orWhere('payment_details.transaction_id', 'LIKE', '%' . $request->search . '%')
                        ->orWhere('payment_details.merchant_code', 'LIKE', '%' . $request->search . '%')
                        ->orWhere('payment_details.fourth_party_transection', 'LIKE', '%' . $request->search . '%');
                })
                ->sum('amount');

            $merchant_income = SettleRequest::where('status', 'paid')->where('merchant_id', '!=', null)->sum('total');
            $agent_income = SettleRequest::where('status', 'paid')->where('agent_id', '!=', null)->sum('total');

            $data = PaymentDetail::query()
                ->leftJoin('payment_maps', 'payment_maps.id', '=', 'payment_details.product_id')
                //->select('payment_details.id', 'payment_details.created_at', 'payment_details.fourth_party_transection', 'payment_details.transaction_id', 'payment_details.amount', 'payment_details.customer_name', 'payment_details.payment_status', 'payment_details.merchant_code', 'payment_maps.cny_min', 'payment_maps.cny_max', 'payment_details.cny_amount');
                ->select('payment_details.id','payment_details.created_at', 'payment_details.fourth_party_transection', 'payment_details.transaction_id', 'payment_details.amount', 'payment_details.customer_name', 'payment_details.payment_status', 'payment_details.merchant_code', 'payment_details.Currency');
            $tz = Timezone::where('id', $request->timezone)->value('timezone');

            return DataTables::of($data)
                ->editColumn('payment_status', function ($data) {
                    if ($data->payment_status == 'success') {
                        return '<span class="text-success fw-bold">' . trans('messages.Success') . '</span>';
                    } elseif ($data->payment_status == 'pending') {
                        return '<span class="text-primary fw-bold">' . trans('messages.pending') . '</span>';
                    } else {
                        return '<span class="text-danger fw-bold">' . trans('messages.Failed') . '</span>';
                    }
                })
                ->editColumn('created_at', function ($data) use ($tz) {
                    return $data->created_at->timezone($tz)->format('Y-m-d H:i:s');
                })
                ->editColumn('amount', function ($data) {
                    return number_format($data->amount, 2);
                })
                ->addColumn('Currency', function ($data) {
                    return $data->Currency ?? '';
                })
                ->addColumn('action', function ($data) {
                    return '
                        <a data-view_record="' . $data->id . '" class="btn btn-primary btn-sm view_record" href="#" data-toggle="modal" data-target="#view_record">' . trans('messages.View') . '</a>
                    ';
                })
                ->filter(function ($data) use ($request) {
                    if ($request->status) {
                        $data->where('payment_details.payment_status', $request->status);
                    }

                    if ($request->daterange) {
                        $dateInput = explode('-', $request->daterange);

                        $date[0] = "$dateInput[0]/$dateInput[1]/$dateInput[2]";
                        if (count($dateInput) > 3) {
                            $date[1] = "$dateInput[3]/$dateInput[4]/$dateInput[5]";
                        }

                        $start_date = Carbon::parse($date[0]);
                        $end_date = Carbon::parse($date[1]);

                        $data->whereDate('payment_details.created_at', '>=', $start_date)
                            ->whereDate('payment_details.created_at', '<=', $end_date);
                    }

                    if (!empty($request->search)) {
                        $data->where(function ($q) use ($request) {
                            $q->orWhere('payment_details.amount', 'LIKE', '%' . $request->search . '%')
                                ->orWhere('payment_details.transaction_id', 'LIKE', '%' . $request->search . '%')
                                ->orWhere('payment_details.merchant_code', 'LIKE', '%' . $request->search . '%')
                                ->orWhere('payment_details.fourth_party_transection', 'LIKE', '%' . $request->search . '%');
                        });
                    }
                })
                ->rawColumns(['payment_status', 'action'])
                ->with([
                    'payment_count' => $payment_count,
                    'order_amount_sum' => number_format($order_amount_sum, 2),
                    'order_success_count' => $order_success_count,
                    'order_success_sum' => number_format($order_success_sum, 2),
                    'merchant_income' => number_format($merchant_income, 2),
                    'agent_income' => number_format($agent_income, 2),
                ])
                ->make(true);
        }

        return view('form.paymentDetails.paymentTable', compact('timezones'));
    }

    public function indexMerchant(Request $request)
    {
        $timezones = Timezone::where('status', 'active')->get();

        if ($request->ajax()) {
            $merchant = Merchant::find(Auth()->user()->merchant_id);
            $payment_count = PaymentDetail::where('merchant_code', $merchant->merchant_code)
                ->when($request->status, fn($q) => $q->where('payment_status', $request->status))
                ->count();
            $order_amount_sum = PaymentDetail::where('merchant_code', $merchant->merchant_code)
                ->when($request->status, fn($q) => $q->where('payment_status', $request->status))
                ->sum('amount');
            $order_success_count = PaymentDetail::where('merchant_code', $merchant->merchant_code)
                ->when($request->status, fn($q) => $q->where('payment_status', $request->status))
                ->where('payment_status', 'success')->count();
            $order_success_sum = PaymentDetail::where('merchant_code', $merchant->merchant_code)
                ->when($request->status, fn($q) => $q->where('payment_status', $request->status))
                ->where('payment_status', 'success')->sum('amount');
            $order_fail_count = PaymentDetail::where('merchant_code', $merchant->merchant_code)
                ->when($request->status, fn($q) => $q->where('payment_status', $request->status))
                ->where('payment_status', 'fail')->count();
            $order_fail_sum = PaymentDetail::where('merchant_code', $merchant->merchant_code)
                ->when($request->status, fn($q) => $q->where('payment_status', $request->status))
                ->where('payment_status', 'fail')->sum('amount');

            $data = PaymentDetail::query()
                ->leftJoin('payment_maps', 'payment_maps.id', '=', 'payment_details.product_id')
                ->where('payment_details.merchant_code', $merchant->merchant_code)
                ->select('payment_details.created_at', 'payment_details.fourth_party_transection', 'payment_details.transaction_id', 'payment_details.amount', 'payment_details.customer_name', 'payment_details.payment_status', 'payment_maps.cny_min', 'payment_maps.cny_max', 'payment_details.Currency');
            // ->select('created_at', 'fourth_party_transection', 'transaction_id', 'amount', 'order_status', 'payment_status', 'customer_name');

            $tz = Timezone::where('id', $request->timezone)->value('timezone');

            return DataTables::of($data)
                ->editColumn('payment_status', function ($data) {
                    if ($data->payment_status == 'success' || $data->payment_status == 'Success' || $data->payment_status == 'SUCCESS') {
                        return '<span class="text-success fw-bold">' . trans('messages.Success') . '</span>';
                    } elseif ($data->payment_status == 'pending' || $data->payment_status == 'Pending' || $data->payment_status == 'PENDING') {
                        return '<span class="text-primary fw-bold">' . trans('messages.pending') . '</span>';
                    } else {
                        return '<span class="text-danger fw-bold">' . trans('messages.Failed') . '</span>';
                    }
                })
                ->editColumn('created_at', function ($data) use ($tz) {
                    return $data->created_at->timezone($tz)->format('Y-m-d H:i:s');
                })
                ->editColumn('amount', function ($data) {
                    return number_format($data->amount, 2);
                })
                ->editColumn('Currency', function ($data) {
                    return $data->Currency ?? '';
                })
                ->filter(function ($data) use ($request) {
                    if ($request->status) {
                        $data->where('payment_details.payment_status', $request->status);
                    }

                    if ($request->daterange) {
                        $dateInput = explode('-', $request->daterange);

                        $date[0] = "$dateInput[0]/$dateInput[1]/$dateInput[2]";
                        if (count($dateInput) > 3) {
                            $date[1] = "$dateInput[3]/$dateInput[4]/$dateInput[5]";
                        }

                        $start_date = Carbon::parse($date[0]);
                        $end_date = Carbon::parse($date[1]);

                        $data->whereDate('payment_details.created_at', '>=', $start_date)
                            ->whereDate('payment_details.created_at', '<=', $end_date);
                    }

                    if (!empty($request->search)) {
                        $data->where(function ($q) use ($request) {
                            $q->orWhere('payment_details.order_status', 'LIKE', '%' . $request->search . '%')
                                ->orWhere('payment_details.amount', 'LIKE', '%' . $request->search . '%')
                                ->orWhere('payment_details.transaction_id', 'LIKE', '%' . $request->search . '%')
                                ->orWhere('payment_details.fourth_party_transection', 'LIKE', '%' . $request->search . '%');
                        });
                    }
                })
                ->rawColumns(['payment_status'])
                ->with([
                    'payment_count' => $payment_count,
                    'order_amount_sum' => number_format($order_amount_sum, 2),
                    'order_success_count' => $order_success_count,
                    'order_success_sum' => number_format($order_success_sum, 2),
                    'order_fail_count' => $order_fail_count,
                    'order_fail_sum' => number_format($order_fail_sum, 2),
                ])
                ->make(true);
        }

        return view('form.merchant.paymentTable', compact('timezones'));
    }

    public function indexAgent(Request $request)
    {
        $timezones = Timezone::where('status', 'active')->get();

        if ($request->ajax()) {
            $merchant = Merchant::where('agent_id', Auth()->user()->agent_id)->get();
            $merchantCode = [];
            foreach ($merchant as $merchantVal) {
                array_push($merchantCode, $merchantVal->merchant_code);
            }

            $payment_count = PaymentDetail::whereIn('merchant_code', $merchantCode)
                ->when($request->status, fn($q) => $q->where('payment_status', $request->status))
                ->count();
            $order_amount_sum = PaymentDetail::whereIn('merchant_code', $merchantCode)
                ->when($request->status, fn($q) => $q->where('payment_status', $request->status))
                ->sum('amount');
            $order_success_count = PaymentDetail::whereIn('merchant_code', $merchantCode)
                ->when($request->status, fn($q) => $q->where('payment_status', $request->status))
                ->where('payment_status', '1')->count();
            $order_success_sum = PaymentDetail::whereIn('merchant_code', $merchantCode)
                ->when($request->status, fn($q) => $q->where('payment_status', $request->status))
                ->where('payment_status', '1')->sum('amount');

            $data = PaymentDetail::query()
                ->whereIn('merchant_code', $merchantCode)
                ->select('payment_details.created_at', 'payment_details.fourth_party_transection', 'payment_details.transaction_id', 'payment_details.amount', 'payment_details.customer_name', 'payment_details.payment_status', 'payment_details.Currency');

            $tz = Timezone::where('id', $request->timezone)->value('timezone');

            return DataTables::of($data)
                ->editColumn('payment_status', function ($data) {
                    if ($data->payment_status == 'success' || $data->payment_status == 'Success' || $data->payment_status == 'SUCCESS') {
                        return '<span class="text-success fw-bold">' . trans('messages.Success') . '</span>';
                    } elseif ($data->payment_status == 'pending' || $data->payment_status == 'Pending' || $data->payment_status == 'PENDING') {
                        return '<span class="text-primary fw-bold">' . trans('messages.pending') . '</span>';
                    } else {
                        return '<span class="text-danger fw-bold">' . trans('messages.Failed') . '</span>';
                    }
                })
                ->editColumn('created_at', function ($data) use ($tz) {
                    return $data->created_at->timezone($tz)->format('Y-m-d H:i:s');
                })
                ->editColumn('amount', function ($data) {
                    return number_format($data->amount, 2);
                })
                ->editColumn('Currency', function ($data) {
                    return $data->Currency ?? '';
                })
                ->filter(function ($data) use ($request) {
                    if ($request->status) {
                        $data->where('payment_details.payment_status', $request->status);
                    }

                    if ($request->daterange) {
                        $dateInput = explode('-', $request->daterange);

                        $date[0] = "$dateInput[0]/$dateInput[1]/$dateInput[2]";
                        if (count($dateInput) > 3) {
                            $date[1] = "$dateInput[3]/$dateInput[4]/$dateInput[5]";
                        }

                        $start_date = Carbon::parse($date[0]);
                        $end_date = Carbon::parse($date[1]);

                        $data->whereDate('payment_details.created_at', '>=', $start_date)
                            ->whereDate('payment_details.created_at', '<=', $end_date);
                    }

                    if (!empty($request->search)) {
                        $data->where(function ($q) use ($request) {
                            $q->orWhere('payment_details.order_status', 'LIKE', '%' . $request->search . '%')
                                ->orWhere('payment_details.amount', 'LIKE', '%' . $request->search . '%')
                                ->orWhere('payment_details.transaction_id', 'LIKE', '%' . $request->search . '%')
                                ->orWhere('payment_details.fourth_party_transection', 'LIKE', '%' . $request->search . '%');
                        });
                    }
                })
                ->rawColumns(['payment_status'])
                ->with([
                    'payment_count' => $payment_count,
                    'order_amount_sum' => number_format($order_amount_sum, 2),
                    'order_success_count' => $order_success_count,
                    'order_success_sum' => number_format($order_success_sum, 2),
                ])
                ->make(true);
        }

        return view('form.agent.paymentTable', compact('timezones'));
    }

    public function paymentOld(PaymentFormRequest $request): JsonResponse
    {
        try {
            $addRecord = [
                'merchant_code' => $request->merchant_code,
                'transaction_id' => $request->transaction_id,
                'amount' => $request->amount,
                'customer_name' => $request->customer_name,
                'callback_url' => $request->callback_url,
                'payment_channel' => $request->payment_channel,
                'payment_method' => $request->payment_method,
                //'payment_source' => $request->payment_source,
            ];

            PaymentDetail::create($addRecord);

            $merchantData = Merchant::where('merchant_code', $request->merchant_code)->first();
            $paymentChannel = PaymentChannel::where('channel_name', $request->payment_channel)->where('status', 'Enable')->first();
            $paymentMethod = PaymentMethod::where('method_name', $request->payment_method)->where('status', 'Enable')->first();
            //$paymentSource = PaymentSource::where('source_name', $request->payment_source)->where('status', 'Enable')->first();

            if (!$merchantData) {
                $result['message'] = 'Merchant not Register';
                $result['statusCode'] = 400;

                return $this->getSuccessMessages($result, false);
            } else {
                if ($merchantData->status != 'Enable') {
                    $result['message'] = 'Merchant Disabled';
                    $result['statusCode'] = 400;

                    return $this->getSuccessMessages($result, false);
                }
            }
            if (!$paymentChannel) {
                $result['message'] = 'Payment Channel Disabled';
                $result['statusCode'] = 400;

                return $this->getSuccessMessages($result, false);
            }
            if (!$paymentMethod) {
                $result['message'] = 'Payment Method Disabled';
                $result['statusCode'] = 400;

                return $this->getSuccessMessages($result, false);
            }
            /* if (!$paymentSource) {
                $result['message'] = 'Payment Source Disabled';
                $result['statusCode'] = 400;
                return $this->getSuccessMessages($result, false);
            } */

            $paymentUrl = PaymentUrl::where('channel_id', $paymentChannel->id)
                ->where('method_id', $paymentMethod->id)
                //->where('source_id', $paymentSource->id)
                ->select('payment_urls.url', 'payment_urls.merchant_key', 'payment_urls.merchant_code', 'payment_urls.sign_pre as pre_sign')
                ->first();

            $paymentUrl['transaction_id'] = $request->transaction_id;
            $paymentUrl['customer_name'] = $request->customer_name;
            $paymentUrl['amount'] = $request->amount;
            $paymentUrl['call_backUrl'] = 'sushil.html';

            $result['message'] = 'Payment Details';
            $result['data'] = $paymentUrl;
            $result['statusCode'] = 400;

            return $this->getSuccessMessages($result);
        } catch (\Exception $e) {
            return $this->generalErrorResponse($e);
        }
    }

    public function payment(PaymentFormRequest $request)
    {

        // try {
        $prodId = str_replace('P00', '', $request->product_id);
        $merchantData = Merchant::where('merchant_code', $request->merchant_code)->first();

        $paymentMap = PaymentMap::where('id', $prodId)->where('status', 'Enable')->first();

        if (!$paymentMap) {
            $result['message'] = 'Product Disabled';
            $result['statusCode'] = 400;

            return $this->getSuccessMessages($result, false);
        } elseif ($paymentMap->merchant_id !== $merchantData->id) {
            $result['message'] = 'Product Not set for this Merchant';
            $result['statusCode'] = 400;

            return $this->getSuccessMessages($result, false);
        }

        // check transection Id

        $temTras = PaymentDetail::where('transaction_id', $request->transaction_id)->first();

        if ($temTras) {
            $result['message'] = 'Duplicate Transaction ID .';
            $result['statusCode'] = 400;

            return $this->getSuccessMessages($result, false);
        }

        //end

        $paymentChannel = PaymentChannel::where('id', $paymentMap->channel_id)->where('status', 'Enable')->first();
        $paymentMethod = PaymentMethod::where('id', $paymentMap->method_id)->where('status', 'Enable')->first();
        //$paymentSource = PaymentSource::where('id', $paymentMap->source_id)->where('status', 'Enable')->first();

        if (!$merchantData) {
            $result['message'] = 'Merchant not Register';
            $result['statusCode'] = 400;

            return $this->getSuccessMessages($result, false);
        } else {
            if ($merchantData->status != 'Enable') {
                $result['message'] = 'Merchant Disabled';
                $result['statusCode'] = 400;

                return $this->getSuccessMessages($result, false);
            }
        }
        if (!$paymentChannel) {
            $result['message'] = 'Payment Channel Disabled';
            $result['statusCode'] = 400;

            return $this->getSuccessMessages($result, false);
        }
        if (!$paymentMethod) {
            $result['message'] = 'Payment Method Disabled';
            $result['statusCode'] = 400;

            return $this->getSuccessMessages($result, false);
        }
        /* if (!$paymentSource) {
            $result['message'] = 'Payment Source Disabled';
            $result['statusCode'] = 400;
            return $this->getSuccessMessages($result, false);
        } */
        $amountTemp = rand($paymentMap->min_value, $paymentMap->max_value);
        $addRecord = [
            'merchant_code' => $request->merchant_code,
            'transaction_id' => $request->transaction_id,
            'fourth_party_transection' => 'TR' . rand(100000, 999999),
            'customer_name' => $request->customer_name,
            'callback_url' => $request->callback_url,
            'amount' => $amountTemp,
            'product_id' => $prodId,

            'payment_channel' => $paymentChannel->channel_name,
            'payment_method' => $paymentMethod->method_name,
            //'payment_source' => $paymentSource->source_name,
        ];

        PaymentDetail::create($addRecord);

        $paymentUrl = PaymentUrl::where('channel_id', $paymentChannel->id)
            ->where('method_id', $paymentMethod->id)
            //->where('source_id', $paymentSource->id)
            ->select('payment_urls.url', 'payment_urls.merchant_key', 'payment_urls.merchant_code', 'payment_urls.sign_pre as pre_sign')
            ->first();

        // dd($paymentMethod->method_name);

        if ($paymentChannel->channel_name == 'iPay88' && $paymentMethod->method_name == 'alipay') {
            $paymentUrl['payment_id'] = 233;
        }
        if ($paymentChannel->channel_name == 'iPay88' && $paymentMethod->method_name == 'WeChat') {
            $paymentUrl['payment_id'] = 240;
        }
        if ($paymentChannel->channel_name == 'iPay88' && $paymentMethod->method_name == 'unipay') {
            $paymentUrl['payment_id'] = 15;
        }
        if ($paymentChannel->channel_name == 'iPay88' && $paymentMethod->method_name == 'card') {
            $paymentUrl['payment_id'] = 1;
        }

        $paymentUrl['channel_name'] = $paymentChannel->channel_name;
        $paymentUrl['method_name'] = $paymentMethod->method_name;
        //$paymentUrl['source_name'] = $paymentSource->source_name;

        $paymentUrl['customer_id'] = $request->customer_id;
        $paymentUrl['transaction_id'] = $request->transaction_id;
        $paymentUrl['customer_name'] = $request->customer_name;
        $paymentUrl['amount'] = $amountTemp;
        $paymentUrl['min_amount'] = $paymentMap->min_value;
        $paymentUrl['max_amount'] = $paymentMap->max_value;
        $paymentUrl['call_backUrl'] = 'sushil.html';

        $result['message'] = 'Payment Details';
        $result['data'] = $paymentUrl;
        $result['statusCode'] = 400;

        return view('form.paymentDetails.autoSubmitForm', compact('paymentUrl'));
        // return $this->getSuccessMessages($result);

        // } catch (\Exception $e) {
        //     return $this->generalErrorResponse($e);
        // }
    }

    public function payment_new(Request $request)
    {
        // print_r($request->input());die;
        // try {
        $prodId = str_replace('P00', '', $request->product_id);
        $merchantData = Merchant::where('merchant_code', $request->merchant_code)->first();

        $paymentMap = PaymentMap::where('id', $prodId)->where('status', 'Enable')->first();

        if (!$paymentMap) {
            $result['message'] = 'Product Disabled';
            $result['statusCode'] = 400;

            return $this->getSuccessMessages($result, false);
        } elseif ($paymentMap->merchant_id !== $merchantData->id) {
            $result['message'] = 'Product Not set for this Merchant';
            $result['statusCode'] = 400;

            return $this->getSuccessMessages($result, false);
        }

        // check transection Id

        $temTras = PaymentDetail::where('transaction_id', $request->transaction_id)->first();

        if ($temTras) {
            $result['message'] = 'Duplicate Transaction ID .';
            $result['statusCode'] = 400;

            return $this->getSuccessMessages($result, false);
        }

        //end

        $paymentChannel = PaymentChannel::where('id', $paymentMap->channel_id)->where('status', 'Enable')->first();
        $paymentMethod = PaymentMethod::where('id', $paymentMap->method_id)->where('status', 'Enable')->first();
        //$paymentSource = PaymentSource::where('id', $paymentMap->source_id)->where('status', 'Enable')->first();

        if (!$merchantData) {
            $result['message'] = 'Merchant not Register';
            $result['statusCode'] = 400;

            return $this->getSuccessMessages($result, false);
        } else {
            if ($merchantData->status != 'Enable') {
                $result['message'] = 'Merchant Disabled';
                $result['statusCode'] = 400;

                return $this->getSuccessMessages($result, false);
            }
        }
        if (!$paymentChannel) {
            $result['message'] = 'Payment Channel Disabled';
            $result['statusCode'] = 400;

            return $this->getSuccessMessages($result, false);
        }
        if (!$paymentMethod) {
            $result['message'] = 'Payment Method Disabled';
            $result['statusCode'] = 400;

            return $this->getSuccessMessages($result, false);
        }
        /* if (!$paymentSource) {
            $result['message'] = 'Payment Source Disabled';
            $result['statusCode'] = 400;
            return $this->getSuccessMessages($result, false);
        } */
        $amountTemp = rand($paymentMap->min_value, $paymentMap->max_value);
        $addRecord = [
            'merchant_code' => $request->merchant_code,
            'transaction_id' => $request->transaction_id,
            'fourth_party_transection' => 'TR' . rand(100000, 999999),
            'customer_name' => $request->customer_name,
            'callback_url' => $request->callback_url,
            // 'amount' => $paymentMap->map_value,
            'amount' => $amountTemp,
            'product_id' => $prodId,

            'payment_channel' => $paymentChannel->channel_name,
            'payment_method' => $paymentMethod->method_name,
            //'payment_source' => $paymentSource->source_name,
        ];

        PaymentDetail::create($addRecord);

        $paymentUrl = PaymentUrl::where('channel_id', $paymentChannel->id)
            ->where('method_id', $paymentMethod->id)
            //->where('source_id', $paymentSource->id)
            ->select('payment_urls.url', 'payment_urls.merchant_key', 'payment_urls.merchant_code', 'payment_urls.sign_pre as pre_sign')
            ->first();

        // dd($paymentMethod->method_name);

        if ($paymentChannel->channel_name == 'iPay88' && $paymentMethod->method_name == 'alipay') {
            $paymentUrl['payment_id'] = 233;
        }
        if ($paymentChannel->channel_name == 'iPay88' && $paymentMethod->method_name == 'WeChat') {
            $paymentUrl['payment_id'] = 240;
        }
        if ($paymentChannel->channel_name == 'iPay88' && $paymentMethod->method_name == 'unipay') {
            $paymentUrl['payment_id'] = 15;
        }
        if ($paymentChannel->channel_name == 'iPay88' && $paymentMethod->method_name == 'card') {
            $paymentUrl['payment_id'] = 1;
        }

        $paymentUrl['channel_name'] = $paymentChannel->channel_name;
        $paymentUrl['method_name'] = $paymentMethod->method_name;
        //$paymentUrl['source_name'] = $paymentSource->source_name;

        $paymentUrl['customer_id'] = $request->customer_id;
        $paymentUrl['transaction_id'] = $request->transaction_id;
        $paymentUrl['customer_name'] = $request->customer_name;
        $paymentUrl['amount'] = $amountTemp;
        $paymentUrl['min_amount'] = $paymentMap->min_value;
        $paymentUrl['max_amount'] = $paymentMap->max_value;
        $paymentUrl['call_backUrl'] = 'sushil.html';

        $result['message'] = 'Payment Details';
        $result['data'] = $paymentUrl;
        $result['statusCode'] = 400;

        return view('form.paymentDetails.autoSubmitForm', compact('paymentUrl'));
        // return $this->getSuccessMessages($result);

        // } catch (\Exception $e) {
        //     return $this->generalErrorResponse($e);
        // }
    }

    public function paymentNewNew(Request $request)
    {
        //echo "Please wait...";

        // if (getenv('HTTP_CLIENT_IP')) {
        //     $ip = getenv('HTTP_CLIENT_IP');
        // }
        // if (getenv('HTTP_X_REAL_IP')) {
        //     $ip = getenv('HTTP_X_REAL_IP');
        // } elseif (getenv('HTTP_X_FORWARDED_FOR')) {
        //     $ip = getenv('HTTP_X_FORWARDED_FOR');
        //     $ips = explode(',', $ip);
        //     $ip = $ips[0];
        // } elseif (getenv('REMOTE_ADDR')) {
        //     $ip = getenv('REMOTE_ADDR');
        // } else {
        //     $ip = '0.0.0.0';
        // }

        // $ipAddress = $ip;
        // $where = [
        //     'ip_address' => $ipAddress,
        //     //'customer_id' => $customerId
        // ];
        // $checkPayment = PaymentDetail::whereDate('created_at', Carbon::now()->format('Y-m-d'))->where($where)->first();
        // if (!empty($checkPayment)) {
        //     $response = [
        //         'status' => false,
        //         'message' => 'Payment already exist for this IP today',
        //     ];

        //     return response()->json($response, 401);
        // }
        // $customerId = $request->customer_id;
        // $merchantCode = $request->merchant_code;
        // $productId = $request->product_id;
        // $where = ['customer_id' => $customerId, 'merchant_code' => $merchantCode, 'product_id' => $productId];
        // $checkPayment = PaymentDetail::whereDate('created_at', Carbon::now()->format('Y-m-d'))->where($where)->first();
        // if (!empty($checkPayment)) {
        //     $response = [
        //         'status' => false,
        //         'message' => 'Payment already exist for this customer today',
        //     ];

        //     return response()->json($response, 401);
        // }
        $arrayData = [];
        $frtransaction = $this->generateUniqueCode();
        // $request->transaction_id = "T" . rand(100000, 999999);
        $arrayData['merchant_code'] = $request->merchant_code;
        $arrayData['customer_name'] = $request->customer_name;
        $arrayData['customer_id'] = $request->customer_id;
        //$arrayData['transaction_id'] = $request->transaction_id;
        $arrayData['transaction_id'] = $frtransaction;
        $arrayData['callback_url'] = $request->callback_url;
        $arrayData['amount'] = $request->amount;
        $arrayData['redirect_url'] = $request->redirect_url;
        // $arrayData['notify_url'] = env('NOTIFY_URL', 'http://127.0.0.1:8000/api/payment-response');

        $getGatewayParameters = [];
        // return $request->all();
        $paymentMap = PaymentMap::where('id', $request->product_id)->first();
        // dd($paymentMap);

        if (!$paymentMap) {
            return 'product not exist';
        }

        if ($paymentMap->status == 'Disable') {
            return 'product is Disable';
        }

        if ($paymentMap->channel_mode == 'single') {
            $gatewayPaymentChannel = GatewayPaymentChannel::where('id', $paymentMap->gateway_payment_channel_id)->first();
            if (!$gatewayPaymentChannel) {
                return 'gatewayPaymentChannel not exist';
            }
            if ($gatewayPaymentChannel->status == 'Disable') {
                return 'gatewayPaymentChannel is Disable';
            }

            $paymentMethod = PaymentMethod::where('id', $gatewayPaymentChannel->gateway_account_method_id)->first();
            $arrayData['method_name'] = $paymentMethod->method_name;
            if (!$paymentMethod) {
                return 'paymentMethod not exist';
            }
            if ($paymentMethod->status == 'Disable') {
                return 'paymentMethod is Disable';
            }

            if ($gatewayPaymentChannel->risk_control == 1) {
                // daily transection limit checking
                $checkLimitationRiskMode = $this->checkLimitationRiskMode($gatewayPaymentChannel, $paymentMap);
                if ($checkLimitationRiskMode) {
                    $getGatewayParameters = $this->getGatewayParameters($gatewayPaymentChannel);
                } else {
                    return $checkLimitationRiskMode;
                }
                // daily transection limit checking
            } else {
                $getGatewayParameters = $this->getGatewayParameters($gatewayPaymentChannel);
            }
        } else {
            $gatewayPaymentChannel = GatewayPaymentChannel::whereIn('id', explode(',', $paymentMap->gateway_payment_channel_id))->get();

            if (!$gatewayPaymentChannel) {
                return 'gatewayPaymentChannel not exist';
            }

            foreach ($gatewayPaymentChannel as $item) {
                if ($item->status == 'Enable') {
                    $paymentMethod = PaymentMethod::where('id', $item->gateway_account_method_id)->first();
                    $arrayData['method_name'] = $paymentMethod->method_name;
                    if (!$paymentMethod) {
                        return 'paymentMethod not exist';
                    }
                    if ($paymentMethod->status == 'Disable') {
                        return 'paymentMethod is Disable';
                    }
                    // gateway_account_method_id
                    if ($item->risk_control == 1) {
                        // daily transection limit checking
                        $checkLimitationRiskMode = $this->checkLimitationRiskMode($item, $paymentMap);
                        if ($checkLimitationRiskMode) {
                            $getGatewayParameters = $this->getGatewayParameters($item);
                            $gatewayPaymentChannel = $item;
                        } else {
                            return $checkLimitationRiskMode;
                        }
                        // daily transection limit checking
                    } else {
                        $getGatewayParameters = $this->getGatewayParameters($item);
                    }
                }
            }
        }
        // $this->storePayamentDetails($paymentMap, $request, $gatewayPaymentChannel, $paymentMethod);
        // return array_merge($arrayData, $getGatewayParameters);
        // return view('form.paymentDetails.autoSubmitForm', compact('paymentUrl'));
        $res = array_merge($arrayData, $getGatewayParameters);
        //dd($getGatewayParameters);
        // random amount

        if (isset($request->merchantAmount) && !empty($request->merchantAmount)) {
            $merchantAmount = $request->merchantAmount;
            $paymentDetailRate = PaymentDetail::where('payment_status', 'success')->where('merchantAmount', $merchantAmount)->whereNotNull('exchangeRate')->latest('created_at')->first();
            if (!$paymentDetailRate && empty($paymentDetailRate)) {
                $paymentDetailRate = PaymentDetail::where('payment_status', 'success')->whereNotNull('exchangeRate')->latest('created_at')->first();
            }
            if ($paymentDetailRate && !empty($paymentDetailRate->exchangeRate)) {
                $mainRate = $paymentDetailRate->exchangeRate;
            } else {
                $mainRate = 7.24;
            }

            $randomAmount = $merchantAmount / $mainRate;

            $res['amount'] = $randomAmount;
            $res['cny_amount'] = $merchantAmount;
        } else {
            $merchantAmount = null;
            $randomAmount = rand($paymentMap->min_value, $paymentMap->max_value);
            $res['amount'] = $randomAmount;
            $res['cny_amount'] = !empty($request->cny_amount) ? $request->cny_amount : '';
        }

        //$res['amount'] = 0.01;
        //dd($getGatewayParameters);
        //return $res;
        $this->storePayamentDetails($paymentMap, $request, $gatewayPaymentChannel, $paymentMethod, $res, $randomAmount, $frtransaction, $merchantAmount);

        // dd($res);
        // dd($res);
        // $rep = Http::post($res['e_comm_website'], $res);

        // $response = json_decode($rep->getBody(), true);
        // $resData = @$response['data'];

        // dd(json_decode($resData));

        // dd('Sushil');

        return view('payment-form.payment-page', compact('res'));

        // if (@$resData['payment_link']) {
        //     return redirect($resData['payment_link']);
        // } else {
        //     $responses = [
        //         'message' => 'Payment invalid',
        //         'status' => false,
        //         'data' => $resData,
        //         // 'request_data' => $res
        //     ];

        //     return response()->json($responses, 400);
        // }
    }
    public function getPaymentResponse(Request $request)
    {
        $data = $request->all();
        $totalAmountCny = '0.00';
        $exchangeRate = null;
        if (!empty($data['response_data']['meta']['wechat_alipay_info']['total_amount_cny'])) {

            $totalAmountCny = $data['response_data']['meta']['wechat_alipay_info']['total_amount_cny'] ?? 0;
            $totalAmountUsd = $data['response_data']['meta']['wechat_alipay_info']['total_amount'] ?? 0;
            $exchangeRate = round($totalAmountCny / $totalAmountUsd, 2);
        }

        PaymentDetail::where('fourth_party_transection', $data['transaction_id'])->update([
            'payment_status' => $data['status'],
            'response_data' => $data['response_data'],
            'cny_amount' => $totalAmountCny,
            'exchangeRate' => $exchangeRate,
        ]);

        $paymentDetail = PaymentDetail::where('fourth_party_transection', $data['transaction_id'])->first();

        if (!empty($paymentDetail->callback_url)) {
            $postData = [
                'merchant_code' => $paymentDetail->merchant_code,
                'transaction_id' => $paymentDetail->transaction_id,
                'amount' => $paymentDetail->amount,
                'customer_id' => $paymentDetail->customer_id,
                'cny_amount' => $paymentDetail->cny_amount,
                'status' => $paymentDetail->payment_status,
            ];
            Http::post($paymentDetail->callback_url, $postData);
        }

        return response()->json([
            'message' => 'updated transaction successfully',
        ], 200);
    }

    public function getGatewayParameters($gatewayPaymentChannel): array
    {
        $arrayData = [];
        //   dd($gatewayPaymentChannel->gateway_account_method_id);
        $gatewayAccountMethod = GatewayAccountMethod::where('method_id', $gatewayPaymentChannel->gateway_account_method_id)->where('gateway_account_id', $gatewayPaymentChannel->gateway_account_id)->first();
        //dd($gatewayAccountMethod);
        if (!$gatewayAccountMethod) {
            return 'gatewayAccountMethod not exist';
        }
        if ($gatewayAccountMethod->status == 'Disable') {
            return 'gatewayAccountMethod is Disable';
        }
        // return $gatewayAccountMethod;
        $gatewayAccount = GatewayAccount::where('id', $gatewayPaymentChannel->gateway_account_id)->first(); // web site details
        $arrayData['e_comm_website'] = $gatewayAccount->e_comm_website;
        if (!$gatewayAccount) {
            return 'GatewayAccount not exist';
        }
        if ($gatewayAccount->status == 'Disable') {
            return 'GatewayAccount is Disable';
        }

        $parameterSetting = ParameterSetting::where('channel_id', $gatewayAccount->gateway)->get();

        $parameterValue = ParameterValue::where('gateway_account_method_id', $gatewayAccountMethod->id)->get();
        //dd($parameterValue);
        foreach ($parameterSetting as $parameterSettingVal) {
            foreach ($parameterValue as $parameterValueVal) {
                if ($parameterValueVal->parameter_setting_id == $parameterSettingVal->id) {
                    $arrayData[str_replace(' ', '_', strtolower($parameterSettingVal->parameter_name))] = $parameterValueVal->parameter_setting_value;
                }
            }
        }

        return $arrayData;
    }

    public function checkLimitationRiskMode($gatewayPaymentChannel, $paymentMap)
    {
        $paymentDetail = PaymentDetail::where('product_id', $paymentMap->id)->where('payment_status', 'success')->get();
        // array_sum($paymentDetail);
        $sumAmount = 0;
        foreach ($paymentDetail as $paymentDetailVal) {
            $sumAmount = $sumAmount + $paymentDetailVal->amount;
        }

        $amountTemp = rand($paymentMap->min_value, $paymentMap->max_value);
        if ($amountTemp >= $gatewayPaymentChannel->max_limit_per_trans) {
            return 'max_limit_per_trans';
        }
        if ($gatewayPaymentChannel->daily_max_trans >= count($paymentDetail)) {
            return 'daily_max_trans';
        }
        if ($sumAmount >= $gatewayPaymentChannel->daily_max_limit) {
            return 'daily_max_limit';
        }

        return true;
    }

    public function storePayamentDetails($paymentMap, $request, $gatewayPaymentChannel, $paymentMethod, $res = null, $randomAmount = null, $frtransaction = null, $merchantAmount = null)
    {

        //$amountTemp = rand($paymentMap->min_value, $paymentMap->max_value);

        if (getenv('HTTP_CLIENT_IP')) {
            $ip = getenv('HTTP_CLIENT_IP');
        }
        if (getenv('HTTP_X_REAL_IP')) {
            $ip = getenv('HTTP_X_REAL_IP');
        } elseif (getenv('HTTP_X_FORWARDED_FOR')) {
            $ip = getenv('HTTP_X_FORWARDED_FOR');
            $ips = explode(',', $ip);
            $ip = $ips[0];
        } elseif (getenv('REMOTE_ADDR')) {
            $ip = getenv('REMOTE_ADDR');
        } else {
            $ip = '0.0.0.0';
        }

        $amountTemp = $randomAmount;
        $addRecord = [
            'merchant_code' => $request->merchant_code,
            'transaction_id' => $request->transaction_id,
            // 'fourth_party_transection' => "TR" . rand(100000, 999999),
            'fourth_party_transection' => $frtransaction,
            'customer_name' => $request->customer_name,
            'callback_url' => $request->callback_url,
            'amount' => $amountTemp,
            //  'cny_amount' => $amountTemp,
            'product_id' => $request->product_id,
            'payment_channel' => $gatewayPaymentChannel->id,
            'payment_method' => $paymentMethod->method_name,
            'request_data' => json_encode($res),
            'customer_id' => !empty($request->customer_id) ? $request->customer_id : 0,
            'ip_address' => $ip,
            'merchantAmount' => $merchantAmount,
        ];

        PaymentDetail::create($addRecord);

        // $paymentUrl = PaymentUrl::where('channel_id', $paymentChannel->id)
        //     ->where('method_id', $paymentMethod->id)
        //     ->select('payment_urls.url', 'payment_urls.merchant_key', 'payment_urls.merchant_code', 'payment_urls.sign_pre as pre_sign')
        //     ->first();

        // if ($paymentChannel->channel_name == 'iPay88' && $paymentMethod->method_name == 'alipay') {
        //     $paymentUrl['payment_id'] = 233;
        // }
        // if ($paymentChannel->channel_name == 'iPay88' && $paymentMethod->method_name == 'WeChat') {
        //     $paymentUrl['payment_id'] = 240;
        // }
        // if ($paymentChannel->channel_name == 'iPay88' && $paymentMethod->method_name == 'unipay') {
        //     $paymentUrl['payment_id'] = 15;
        // }
        // if ($paymentChannel->channel_name == 'iPay88' && $paymentMethod->method_name == 'card') {
        //     $paymentUrl['payment_id'] = 1;
        // }

        // $paymentUrl['channel_name'] = $paymentChannel->channel_name;
        // $paymentUrl['method_name'] = $paymentMethod->method_name;

        // $paymentUrl['customer_id'] = $request->customer_id;
        // $paymentUrl['transaction_id'] = $request->transaction_id;
        // $paymentUrl['customer_name'] = $request->customer_name;
        // $paymentUrl['amount'] = $amountTemp;
        // $paymentUrl['min_amount'] = $paymentMap->min_value;
        // $paymentUrl['max_amount'] = $paymentMap->max_value;
        // $paymentUrl['call_backUrl'] = "sushil.html";

        // $result['message'] = 'Payment Details';
        // $result['data'] = $paymentUrl;
        // $result['statusCode'] = 400;

        // return view('form.paymentDetails.autoSubmitForm', compact('paymentUrl'));
    }

    public function generateUniqueCode()
    {
        do {
            $mytime = Carbon::now();
            $currentDateTime = str_replace(' ', '', $mytime->parse($mytime->toDateTimeString())->format('Ymd His'));
            $fourth_party_transection = $currentDateTime . random_int(1000, 9999);
            // $fourth_party_transection = random_int(100000000, 999999999);

        } while (PaymentDetail::where('fourth_party_transection', '=', 'TR' . $fourth_party_transection)->first());

        return 'TR' . $fourth_party_transection;
    }

    public function paymentSubmit(Request $request)
    {
        $paymentDetail = PaymentDetail::where('transaction_id', $request->reference_id)->latest()->first();

        if ($request->Status == 0) {
            $settleStatus = 'cancel';
            $status = 'fail';
        }
        if ($request->Status == 1) {
            $settleStatus = 'unsettled';
            $status = 'success';
        }

        $paymentDetailUpdateData['order_id'] = $request->order_id;
        $paymentDetailUpdateData['order_date'] = $request->order_date;
        $paymentDetailUpdateData['order_status'] = $request->order_status;
        $paymentDetailUpdateData['Currency'] = $request->Currency;
        $paymentDetailUpdateData['TransId'] = $request->TransId;
        $paymentDetailUpdateData['Status'] = $status;
        $paymentDetailUpdateData['payment_status'] = $status;
        $paymentDetailUpdateData['ErrDesc'] = $request->ErrDesc;
        $paymentDetailUpdateData['merchant_settle_status'] = $settleStatus;
        $paymentDetailUpdateData['agent_settle_status'] = $settleStatus;

        $paymentDetail->update($paymentDetailUpdateData);

        if ($paymentDetail->payment_status == 'success') {
            $allAdmin = User::where('role_name', 'Admin')->get();

            foreach ($allAdmin as $admin) {
                $admin->notify(new PaymentDetailNotification($paymentDetail));
            }

            $merchantId = Merchant::where('merchant_code', $paymentDetail->merchant_code)->pluck('id');
            $merchantUser = User::where('merchant_id', $merchantId)->first();
            $merchantUser->notify(new PaymentDetailNotification($paymentDetail));
        }

        if ($paymentDetail->callback_url != '') {
            return view('form.paymentDetails.redirectToDemo', compact('paymentDetail'));
        }
    }

    public function demoPaymentForm(Request $request)
    {
        // dd($request);
        $data = $request;

        return view('form.paymentDetails.demoPaymentForm', compact('data'));
    }

    public function getSuccessMessages($data, $status = true)
    {
        $successMessage = [];
        if (!empty($data['message'])) {
            $successMessage['message'] = $data['message'];
        }
        if (!empty($data['data'])) {
            $successMessage['data'] = $data['data'];
        }
        $successMessage['status'] = $status;

        return response()->json($successMessage, $data['statusCode']);
    }

    public function generalErrorResponse(\Exception $e)
    {
        return response()->json([
            'message' => $e->getMessage(),
            'status' => false,
            'trace' => [env('APP_DEBUG') ? $e->getTrace() : ''],
        ], 400);
    }

    public function getPaymentdetails($payment)
    {
        $payment = PaymentDetail::where('id', $payment)->with(['merchantData', 'paymentMaps'])->first();
        //dd($payment);

        $billing = Billing::where('merchant_id', $payment->merchantData->id)->latest()->first();
        if (!$billing) {
            $billing = Billing::where('merchant_id', null)->latest()->first();
        }
        $payment['billing_table'] = $billing;
        // return view('form.paymentDetails.paymentTable', $payment);

        return response()->json([
            'data' => $payment,
        ]);
    }

    /* public function paymentFilter(Request $request)
    {
        $query = (new PaymentDetail())->newQuery();


        $query->when($request->merchant_code, function ($query) use ($request) {
            $query->where('merchant_code', 'like', "%$request->merchant_code%");
        });

        $query->when($request->transaction_id, function ($query) use ($request) {
            $query->where('transaction_id', 'like', "%$request->transaction_id%");
        });

        $query->when($request->merchant_track_no, function ($query) use ($request) {
            $query->where('order_id', 'like', "%$request->merchant_track_no%");
        });

        $search_result = $query->paginate(10);

        return view('form.paymentDetails.paymentTable', compact('search_result'));
    } */



    public function testCallBackUrl(Request $request)
    {
        $data = $request->all();

        $paymentDetail = PaymentDetail::where('transaction_id', $data['transaction_id'])->first();
        $postData = [];
        if (!empty($paymentDetail->callback_url)) {
            $cnyAmount = (int) $paymentDetail->amount * 7.237333333333333;
            $postData = [
                'merchant_code' => $paymentDetail->merchant_code,
                'transaction_id' => $paymentDetail->transaction_id,
                'amount' => $paymentDetail->amount,
                'customer_id' => $paymentDetail->customer_id,
                'cny_amount' => $cnyAmount,
                'status' => $paymentDetail->payment_status,
            ];
            Http::post($paymentDetail->callback_url, $postData);
        }
        \Log::error($postData);
        return response()->json([
            'data' => $postData,
            'message' => 'updated transaction successfully',
        ], 200);
    }
}
