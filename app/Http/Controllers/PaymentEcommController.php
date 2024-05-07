<?php

namespace App\Http\Controllers;

use App\Models\Billing;
use App\Models\GatewayAccount;
use App\Models\GatewayAccountMethod;
use App\Models\GatewayPaymentChannel;
use App\Models\Merchant;
use App\Models\ParameterSetting;
use App\Models\ParameterValue;
use App\Models\PaymentDetail;
use App\Models\PaymentMap;
use App\Models\PaymentMethod;
use App\Models\PaymentUrl;
use App\Models\User;
use App\Notifications\PaymentDetailNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PaymentEcommController extends Controller
{
    public function paymentNewNew(Request $request)
    {
        $arrayData = [];

        $getGatewayParameters = [];

        $paymentMap = PaymentMap::where('id', $request->product_id)
            ->first();

        if (! $paymentMap) {
            return 'product not exist';
        }

        if ($paymentMap->status == 'Disable') {
            return 'product is Disable';
        }

        if ($paymentMap->channel_mode == 'single') {
            $gatewayPaymentChannel = GatewayPaymentChannel::where('id', $paymentMap->gateway_payment_channel_id)
                ->first();
            if (! $gatewayPaymentChannel) {
                return 'gatewayPaymentChannel not exist';
            }
            if ($gatewayPaymentChannel->status == 'Disable') {
                return 'gatewayPaymentChannel is Disable';
            }

            $paymentMethod = PaymentMethod::where('id', $gatewayPaymentChannel->gateway_account_method_id)->first();
            $arrayData['method_name'] = $paymentMethod->method_name;
            if (! $paymentMethod) {
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
            $gatewayPaymentChannel = GatewayPaymentChannel::whereIn(
                'id',
                explode(',', $paymentMap->gateway_payment_channel_id)
            )->get();

            if (! $gatewayPaymentChannel) {
                return 'gatewayPaymentChannel not exist';
            }

            foreach ($gatewayPaymentChannel as $item) {
                if ($item->status == 'Enable') {
                    $paymentMethod = PaymentMethod::where('id', $item->gateway_account_method_id)->first();
                    $arrayData['method_name'] = $paymentMethod->method_name;
                    if (! $paymentMethod) {
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
        $res = array_merge($arrayData, $getGatewayParameters);

        $frtransaction = $this->generateUniqueCode();

        // $res['SecurityCode'] = 'zSAIDEPVZLyuc4ESXKO2';  //4thparty
        // $res['Merchant'] = 'PA020';  //4thparty
        // product_id  // 4thparty

        $res['merchant_code'] = $request->merchant_code;
        $res['currency'] = $request->currency;
        $res['amount'] = $request->amount;

        $res['payin_api_token'] = $request->payin_api_token;
        $res['transaction_id'] = $frtransaction; // 4th party
        $res['callback_url'] = url('payment_status');
        $res['customer_name'] = $request->customer_name;
        $res['customer_email'] = $request->customer_email;  
        $res['customer_phone'] = $request->customer_phone;  
        $res['customer_bank_name'] = $request->customer_bank_name; 
        if(isset($request->customer_account_number) && !empty($request->customer_account_number)){
            $res['customer_account_number'] = $request->customer_account_number; 
        }
        $res['customer_addressline_1'] = $request->customer_addressline_1; 
        $res['customer_zip'] = $request->customer_zip; 
        $res['customer_country'] = $request->customer_country; 
        $res['customer_city'] = $request->customer_city; 

        $this->storePayamentDetails(
            $paymentMap,
            $request,
            $gatewayPaymentChannel,
            $paymentMethod,
            $res,
            $res['amount'],
            $frtransaction,
            $res['amount']
        );
        // dd($res);

        return view('payment-form.payment-page', compact('res'));
    }

    public function getPaymentResponse(Request $request)
    {
        $data = $request->all();
        $totalAmountCny = '0.00';
        $exchangeRate = null;
        if (! empty($data['response_data']['meta']['wechat_alipay_info']['total_amount_cny'])) {

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

        if (! empty($paymentDetail->callback_url)) {
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
        if (! $gatewayAccountMethod) {
            return 'gatewayAccountMethod not exist';
        }
        if ($gatewayAccountMethod->status == 'Disable') {
            return 'gatewayAccountMethod is Disable';
        }
        // return $gatewayAccountMethod;
        $gatewayAccount = GatewayAccount::where('id', $gatewayPaymentChannel->gateway_account_id)->first(); // web site details
        $arrayData['e_comm_website'] = $gatewayAccount->e_comm_website;
        if (! $gatewayAccount) {
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
                    // $arrayData[str_replace(' ', '_', strtolower($parameterSettingVal->parameter_name))] = $parameterValueVal->parameter_setting_value;
                    $arrayData[$parameterSettingVal->parameter_name] = $parameterValueVal->parameter_setting_value;
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

    public function storePayamentDetails($paymentMap, $request, $gatewayPaymentChannel, $paymentMethod, $res = null, $amount = null, $frtransaction = null, $merchantAmount = null)
    {
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

        $addRecord = [
            'merchant_code' => $request->merchant_code,
            'transaction_id' => $request->transaction_id,
            // 'fourth_party_transection' => "TR" . rand(100000, 999999),
            'fourth_party_transection' => $frtransaction,
            'customer_name' => $request->customer_name,
            'callback_url' => $request->callback_url,
            'amount' => $amount,
            //  'cny_amount' => $amountTemp,
            'product_id' => $request->product_id,
            'payment_channel' => $gatewayPaymentChannel->id,
            'payment_method' => $paymentMethod->method_name,
            'request_data' => json_encode($res),
            'customer_id' => ! empty($request->customer_id) ? $request->customer_id : 0,
            'ip_address' => $ip,
            'merchantAmount' => $merchantAmount,
            'Currency' => $request->currency,
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
        $mytime = Carbon::now();
        $currentDateTime = str_replace(' ', '', $mytime->parse($mytime->toDateTimeString())->format('Ymd His'));
        $fourth_party_transection = $currentDateTime.random_int(1000, 9999);
        // do {
        //     $mytime = Carbon::now();
        //     $currentDateTime = str_replace(' ', '', $mytime->parse($mytime->toDateTimeString())->format('Ymd His'));
        //     $fourth_party_transection = $currentDateTime . random_int(1000, 9999);
        // } while (PaymentDetail::where('fourth_party_transection', '=', 'TR' . $fourth_party_transection)->first());

        return 'TR'.$fourth_party_transection;
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
        $data = $request;

        return view('form.paymentDetails.demoPaymentForm', compact('data'));
    }

    public function getSuccessMessages($data, $status = true)
    {
        $successMessage = [];
        if (! empty($data['message'])) {
            $successMessage['message'] = $data['message'];
        }
        if (! empty($data['data'])) {
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

        $billing = Billing::where('merchant_id', $payment->merchantData->id)->latest()->first();
        if (! $billing) {
            $billing = Billing::where('merchant_id', null)->latest()->first();
        }
        $payment['billing_table'] = $billing;

        return response()->json([
            'data' => $payment,
        ]);
    }

    public function testCallBackUrl(Request $request)
    {
        $data = $request->all();

        $paymentDetail = PaymentDetail::where('transaction_id', $data['transaction_id'])->first();
        $postData = [];
        if (! empty($paymentDetail->callback_url)) {
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
