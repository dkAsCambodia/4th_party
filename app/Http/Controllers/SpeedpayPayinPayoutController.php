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
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use Session;

class SpeedpayPayinPayoutController extends Controller
{
    public function payinform(Request $request)
    {
        return view('payment-form.s2p.payin-form');
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
        date_default_timezone_set('Asia/Phnom_Penh');
        $TransactionDateTime=date("Y‐m‐d h:i:sA");
        // Call Curl API code START
        $postData = [
            'ClientIp' => $client_ip,
            'RefID' => $frtransaction,
            'CustomerID' => 'ZCUST1001',
            'CurrencyCode' => $request->currency,
            'Amount' => $request->amount,
            'TransactionDateTime' => $TransactionDateTime,
            'Remark' => 'payment',
            'CustomerFullName' => $request->customer_name,
            'BankCode' => 'THAIQR',
            'UrlFront' => url('s2p/payinResponse'), 
            'CustomerAccountNumber' => $request->customer_account_number,
            'CustomerAccountBankCode' => $request->bank_code
        ];
        $response = Http::withHeaders([
            'Content-Type' => 'application/x-www-form-urlencoded',
            'API-AGENT-CODE' => $res['api_agent_code'],
            'API-KEY' => $res['apiKey'],
            'API-AGENT-USER-NAME' => $res['api_agent_username'],
        ])->asForm()->post($res['api_url'], $postData);
        $jsonData = $response->json();
        // echo "<pre>";  print_r($jsonData); die;
        // Redirect to the payment link
        if (isset($jsonData['RedirectionUrl'])) {
            //Insert data into DB
            $addRecord = [
                'agent_id' => $merchantData->agent_id,
                'merchant_id' => $merchantData->id,
                'merchant_code' => $request->merchant_code,
                'transaction_id' => $request->referenceId,
                'fourth_party_transection' => $frtransaction,
                'TransId' => $jsonData['DepositID'],
                'callback_url' => $request->callback_url,
                'amount' => $request->amount,
                'Currency' => $request->currency,
                'product_id' => $request->product_id,
                'payment_channel' => $gatewayPaymentChannel->id,
                'payment_method' => $paymentMethod->method_name,
                'request_data' => json_encode($res),
                'gateway_name' => 'SpeedPay',
                'customer_name' => $request->customer_name,
                'customer_email' => $request->customer_email,
                'payin_arr' => json_encode($jsonData),
                'receipt_url' => $jsonData['RedirectionUrl'],
                'ip_address' => $client_ip,
                
            ];
            //   echo "<pre>";  print_r($addRecord); die;
            PaymentDetail::create($addRecord);
            return redirect($jsonData['RedirectionUrl']);
        }else{
            return back()->with('error', 'Payment link not found.');
        }

    }

    public function payinResponse(Request $request)
    {
        //   echo "<pre>";  print_r($request->all()); die;
        $updateData = [
            // 'TransId' => $request->RefId,
            'payment_status' => $request->status,
            // 'payin_arr' => '',
        ];
        PaymentDetail::where('fourth_party_transection', $request->RefId)->update($updateData);
        $paymentDetail = PaymentDetail::where('fourth_party_transection', $request->RefId)->first();
        $callbackUrl = $paymentDetail->callback_url;
        $postData = [
            'merchant_code' => $paymentDetail->merchant_code,
            'referenceId' => $paymentDetail->transaction_id,
            'transaction_id' => $paymentDetail->fourth_party_transection,
            'amount' => $paymentDetail->amount,
            'Currency' => $paymentDetail->Currency,
            'customer_name' => $paymentDetail->customer_name,
            'payment_status' => $paymentDetail->payment_status,
            'created_at' => $paymentDetail->created_at,
        ];

        return view('payment.payment_status', compact('request', 'postData', 'callbackUrl'));
    }

    public function s2pPayinCallbackURL(Request $request)
    {
        $data = $request->all();
        echo "Transaction Information as follows" . '<br/>' .
            "ReferenceId : " . $data['referenceId'] . '<br/>' .
            "TransactionId : " . $data['transaction_id'] . '<br/>' .
            "Currency : " . $data['Currency'] . '<br/>' .
            "Amount : " . $data['amount'] . '<br/>' .
            "customer_name : " . $data['customer_name'] . '<br/>' .
            "Datetime : " . $data['created_at'] . '<br/>' .
            "Status : " . $data['payment_status'];
         die;
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
