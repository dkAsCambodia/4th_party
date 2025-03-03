<?php
namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\GatewayAccount;
use App\Models\GatewayAccountMethod;
use App\Models\GatewayPaymentChannel;
use App\Models\Merchant;
use App\Models\ParameterSetting;
use App\Models\ParameterValue;
use App\Models\PaymentDetail;
use App\Models\PaymentMap;
use App\Models\PaymentMethod;
use App\Models\SettleRequest;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use Session;

class M2pController extends Controller
{
    public function payinform(Request $request)
    {
        return view('payment-form.m2p.payin-form');
    }

    public function payin(Request $request)
    {
        // echo "<pre>";  print_r($request->all()); die;
        $arrayData = [];
        $getGatewayParameters = [];
        $paymentMap = PaymentMap::where('id', $request->product_id)->first();
        if (! $paymentMap) {
            return 'product not exist';
        }
        if ($paymentMap->status == 'Disable') {
            return 'product is Disable';
        }
        $merchantData=Merchant::where('merchant_code', $request->merchant_code)->first();
        if (empty($merchantData)) {
            return 'Invalid Merchants!';
        }

        if ($paymentMap->channel_mode == 'single') {
            $gatewayPaymentChannel = GatewayPaymentChannel::where('id', $paymentMap->gateway_payment_channel_id)->first();
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
            $gatewayPaymentChannel = GatewayPaymentChannel::whereIn('id', explode(',', $paymentMap->gateway_payment_channel_id))->get();
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
        //   echo "<pre>";  print_r($res);
        $frtransaction = $this->generateUniqueCode();
        $client_ip = (isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR']);
        // date_default_timezone_set('UTC'); //GMT+0
        // $dated=date("Y-m-d H:i:s");
        // $Datetime=date("YmdHis");
        // $formattedAmount = is_float($request->amount) ? $request->amount : number_format($request->amount, 2, '.', '');
        

        $apiToken = $res['apiToken'];
        $callbackUrl = url('api/m2p/payinResponse');
        $paymentCurrency='USX';
        $paymentGatewayName = 'USDT TRC20';
        $timestamp = time();
        $secretKey = $res['secretKey'];

        // Concatenate the string
        $finalString = $request->amount . $apiToken . $callbackUrl . $request->Currency . $paymentCurrency . $paymentGatewayName . $timestamp . $secretKey;
        // Generate SHA-384 hash
        $signature = hash('sha384', $finalString);
        echo $signature;



        $apiUrl = 'https://m2p.match-trade.com/api/v2/deposit/crypto_agent';
        $postdata = [
            "amount" =>  $request->amount,
            "apiToken" => $apiToken,
            "callbackUrl" => $callbackUrl,
            "currency" => $request->Currency,
            "paymentCurrency" => $paymentCurrency,
            "paymentGatewayName" => $paymentGatewayName,
            "signature" => $signature,
            "timestamp" => $timestamp,
            "tradingAccountLogin" => "tradingAccountLogin"
        ];

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Content-Length: ' . strlen(json_encode($postdata)),
        ])->post($apiUrl, json_encode($postdata));
        // if ($response->failed()) {
        //     return response()->json(['error' => 'Request failed', 'details' => $response->body()], 500);
        // }
        $responseData = $response->json();
        echo "<pre>";  print_r($responseData); die;
        // Check response and update transaction status
        if (!empty($responseData['paymentId'])) {
            // Your logic for updating transaction status
        }


       
        //Insert data into DB
             // for Help2Pay deposit charge START
             if(!empty($request->amount)){
                $percentage = 3.5;
                $totalWidth = $request->amount;
                $mdr_fee_amount = ($percentage / 100) * $totalWidth;
                $net_amount= $totalWidth-$mdr_fee_amount;
            }
            // for Help2Pay deposit charge END
            $addRecord = [
                'agent_id' => $merchantData->agent_id,
                'merchant_id' => $merchantData->id,
                'merchant_code' => $request->merchant_code,
                'transaction_id' => $request->referenceId,
                'fourth_party_transection' => $frtransaction,
                'callback_url' => $request->callback_url,
                'amount' => $request->amount,
                'Currency' => $request->Currency,
                'product_id' => $request->product_id,
                'bank_code' => $request->bank_code,
                'payment_channel' => $gatewayPaymentChannel->id,
                'payment_method' => $paymentMethod->method_name,
                'request_data' => json_encode($res),
                'gateway_name' => 'Help2Pay',
                'customer_name' => $request->customer_name,
                'ip_address' => $client_ip,
                'net_amount' => $net_amount ?? '',
                'mdr_fee_amount' => $mdr_fee_amount ?? '',
            ];
            //   echo "<pre>";  print_r($addRecord); die;
            PaymentDetail::create($addRecord);
            $res = [
                'Merchant' => $res['Merchant'],
                'Currency' => $request->Currency ?? $request->currency,
                'Customer' => $request->customer_name,
                'Reference' => $frtransaction,
                'Key' => $Key,
                'Amount' => $request->amount,
                'Note' => 'payment',
                'Datetime' => $dated,
                'FrontURI' => url('api/h2p/payinResponse'), 
                'BackURI' => url('api/h2pDepositNotifiication'), 
                'Language' => 'en-us',
                'Bank' => $request->bank_code,
                'ClientIP' => $client_ip,
                'CompanyName' => 'Zaffran PSP',
                'api_url' => $res['api_url'],
            ];
        return view('payment-form.h2p.gateway-form', compact('res'));

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

    public function generateUniqueCode()
    {
        $mytime = Carbon::now();
        $currentDateTime = str_replace(' ', '', $mytime->parse($mytime->toDateTimeString())->format('Ymd His'));
        $fourth_party_transection = $currentDateTime.random_int(1000, 9999);
        return 'TR'.$fourth_party_transection;
    }
}
