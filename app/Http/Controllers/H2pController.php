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

class H2pController extends Controller
{
    public function payinform(Request $request)
    {
        return view('payment-form.h2p.payin-form');
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
        //   echo "<pre>";  print_r($res);die;
        $frtransaction = $this->generateUniqueCode();
        $client_ip = (isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR']);
        date_default_timezone_set('UTC'); //GMT+0
        $dated=date("Y-m-d H:i:s");
        $Datetime=date("YmdHis");
        $formattedAmount = is_float($request->amount) ? $request->amount : number_format($request->amount, 2, '.', '');
        $Keystring= $res['Merchant'].$frtransaction.$request->customer_name.$formattedAmount.$request->Currency.$Datetime.$res['SecurityCode'].$client_ip;
        // echo $Keystring;
        $Key= MD5($Keystring);

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
    
    public function payinResponse(Request $request)
    {
        // Decode the JSON payload automatically
        $results = $request->all();
        // echo "<pre>";  print_r($results); die;
        $fourth_party_transection = $results['Reference'];
        $orderstatus = $results['Status'];
        if ($orderstatus == '000' || $orderstatus == '006') {
            $orderstatus = 'success';
        } elseif ($orderstatus == '009') {
            $orderstatus = 'pending';
        } else {
            $orderstatus = 'failed';
        }
         
        $updateData = [
            'TransId' =>  $results['ID'],
            'payment_status' => $orderstatus,
            'payin_arr' => json_encode($results),
        ];
        PaymentDetail::where('fourth_party_transection', $fourth_party_transection)->update($updateData);
        $paymentDetail = PaymentDetail::where('fourth_party_transection', $fourth_party_transection)->first();
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

    public function h2pPayinCallbackURL(Request $request)
    {
        $data = $request->all();
        echo "Transaction Information as follows" . '<br/>' .
            "Merchant : " . $data['merchant_code'] . '<br/>' .
            "ReferenceId : " . $data['referenceId'] . '<br/>' .
            "TransactionId : " . $data['transaction_id'] . '<br/>' .
            "Type : Deposit" .'<br/>' .
            "Currency : " . $data['Currency'] . '<br/>' .
            "Amount : " . $data['amount'] . '<br/>' .
            "customer_name : " . $data['customer_name'] . '<br/>' .
            "Datetime : " . $data['created_at'] . '<br/>' .
            "Status : " . $data['payment_status'];
         die;
    }

    public function h2pDepositNotifiication(Request $request)
    {
          // Get all request data (since it's application/x-www-form-urlencoded)    XML data
         $data = $request->all();
        if (!empty($data)) {
 
             if (!isset($data['Status'], $data['TransactionID'], $data['ID'])) {
                 return response()->json(['error' => 'Invalid Data'], 400);
             }
 
            $orderStatus = $data['Status'] == '000' ? 'success' : 'failed';
            $RefID = $data['TransactionID'];
            $updateData = [
                'payment_status' => $orderStatus,
                'response_data' => json_encode($data),
            ];
            // echo "<pre>";  print_r($updateData); die;
            PaymentDetail::where('fourth_party_transection', $RefID)->update($updateData);
            echo "Transaction updated successfully!";
            //Call webhook API START
            $paymentDetail = PaymentDetail::where('fourth_party_transection', $RefID)->first();
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
            try {
                if ($paymentDetail->callback_url != null) {
                    $response = Http::post($paymentDetail->callback_url, $postData);
                    echo $response->body(); die;
                }
            } catch (\Exception $e) {
                return response()->json(['error' => 'Failed to call webhook','message' => $e->getMessage()], 500);
            }
             //Call webhook API START

        }else{
            return response()->json(['error' => 'Data Not Found or Invalid Request!'], 400);
        }
    }

    public function h2pPayoutform(Request $request)
    {
        return view('payment-form.h2p.payout-form');
    }

    public function payout(Request $request)
    {
        // echo "<pre>";  print_r($request->all()); die;
        $CURRENCY = $request->Currency ?? $request->currency;
        $totalDepositSumAfterCharge = PaymentDetail::where('merchant_code', $request->merchant_code)->where('Currency', $CURRENCY)->where('payment_status', 'success')->sum('net_amount');
        $totalPayoutSumAfterCharge = SettleRequest::where('merchant_code', $request->merchant_code)->where('Currency', $CURRENCY)->where('status', 'success')->sum('net_amount');
        $AvailableforPayout=$totalDepositSumAfterCharge-$totalPayoutSumAfterCharge;
        //  For speedpay charge START
        $percentage = 2.5;
        $totalWidth = $AvailableforPayout;
        $new_width = ($percentage / 100) * $totalWidth;
        @$finalAmount = $totalWidth-$new_width;
        //  For speedpay charge END
        if($finalAmount < $request->amount){
            return "<h2 style='color:red'>Balance is not enough in Gateway Wallet!</h2>"; 
        }
        // echo "<pre>"; print_r($finalAmount); die;
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
        $frtransaction = $this->generateUniqueCode();
        //  echo "<pre>";  print_r($res); die;
        // Call Curl API code START
        $client_ip = (isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR']);
        date_default_timezone_set('UTC'); //GMT+0
        $dated=date("Y-m-d h:i:sA");
        $Datetime=date("YmdHis");
        $formattedAmount = is_float($request->amount) ? $request->amount : number_format($request->amount, 2, '.', '');
        $Keystring= $res['Merchant'].$frtransaction.$request->referenceId.$formattedAmount.$request->Currency.$Datetime.$request->customer_account_number.$res['SecurityCode'];
        $Key= MD5($Keystring);

        // Withdrawal verification URL code START
        // $verificationUrl = url("api/h2p/payout/verifytransaction?transId=" . $frtransaction . "&key=" . $Key);
        // $response = Http::post($verificationUrl);
        // $response->body();
        // Withdrawal verification URL code END

        // Call Curl API code START
        $response = Http::asForm()->withHeaders([
            'User-Agent' => request()->header('User-Agent'),
            'Content-Type' => 'application/x-www-form-urlencoded',
        ])->post('https://app.safepaymentapp.com/merchantpayout/G0313', [
            'ClientIP' => $client_ip,
            'ReturnURI' => url('api/h2p/withdraw/notifiication'), 
            'MerchantCode' => $res['Merchant'],
            'TransactionID' => $frtransaction,
            'CurrencyCode' => $request->Currency,
            'MemberCode' => $request->referenceId,
            'Amount' => $formattedAmount,
            'TransactionDateTime' => $dated,
            'BankCode' => $request->bank_code,
            'toBankAccountName' => $request->customer_name,
            'toBankAccountNumber' => $request->customer_account_number,
            'Key' => $Key,
        ]);
        if ($response->successful()) {
            $xml = simplexml_load_string($response->body());
            $json = json_encode($xml);
            $resArray = json_decode($json, true);
            // echo "<pre>";  print_r($resArray); die;
            if (!empty($resArray) && isset($resArray['statusCode'])) {
                $status = ($resArray['statusCode'] == '000') ? 'success' : 'failed';
                $message = $resArray['message'];
            }
        } else {
            $message = $resArray['message']; 
            $status = 'failed';
        }

        ////Insert Record into DB
          
             // for H2p payout charge START
            if(!empty($request->amount)){
                $percentage = 2.5;
                $totalWidth = $request->amount;
                $mdr_fee_amount = ($percentage / 100) * $totalWidth;
                $net_amount= $totalWidth+$mdr_fee_amount;
            }
            // for H2p charge END
                $addRecord = [
                    // 'settlement_trans_id' => $Transactionid ?? '',
                    'fourth_party_transection' => $frtransaction,
                    'merchant_track_id' => $request->referenceId,
                    'gateway_name' => 'hlep2Pay',
                    'agent_id' => $merchantData->agent_id,
                    'merchant_id' => $merchantData->id,
                    'merchant_code' => $request->merchant_code,
                    'callback_url' => $request->callback_url,
                    'total' => $request->amount,
                    'net_amount' => $net_amount,
                    'mdr_fee_amount' => $mdr_fee_amount,
                    'customer_bank_name' => $request->customer_name,
                    'bank_code' => $request->bank_code,
                    'customer_account_number' => $request->customer_account_number,
                    'Currency' => $request->Currency,
                    'product_id' => $request->product_id,
                    'payment_channel' => $gatewayPaymentChannel->id,
                    'payment_method' => $paymentMethod->method_name,
                    'customer_name' => $request->customer_name,
                    'api_response' => json_encode($resArray),
                    'message' => $message,
                    'ip_address' => $client_ip, 
                    'status' => $status,
                ];
                SettleRequest::create($addRecord);

                $paymentDetail = SettleRequest::where('fourth_party_transection', $frtransaction)->first();
                $callbackUrl = $paymentDetail->callback_url;
                $postData = [
                    'merchant_code' => $paymentDetail->merchant_code,
                    'referenceId' => $paymentDetail->merchant_track_id,
                    'transaction_id' => $paymentDetail->fourth_party_transection,
                    'amount' => $paymentDetail->total,
                    'Currency' => $paymentDetail->Currency,
                    'customer_name' => $paymentDetail->customer_name,
                    'status' => $paymentDetail->status,
                    'created_at' => $paymentDetail->created_at,
                    'orderremarks' => $paymentDetail->message,
                ];
                return view('payout.payout_status', compact('request', 'postData', 'callbackUrl'));
            
        
    }

    public function verifyPayoutTransaction(Request $request)
    {
         if(!empty($request->transId) && !empty($request->key)){
            echo "True";
         }else{
            echo "False";
         }
    } 

    public function h2pPayoutcallbackURL(Request $request)
    {
        $data = $request->all();
        echo "Transaction Information as follows" . '<br/>' .
            "Merchant_code : " . $data['merchant_code'] . '<br/>' .
            "ReferenceId : " . $data['referenceId'] . '<br/>' .
            "TransactionId : " . $data['transaction_id'] . '<br/>' .
            "Type : Withdrawal" .'<br/>' .
            "Currency : " . $data['Currency'] . '<br/>' .
            "Amount : " . $data['amount'] . '<br/>' .
            "customer_name : " . $data['customer_name'] . '<br/>' .
            "Datetime : " . $data['created_at'] . '<br/>' .
            "Status : " . $data['status'];
         die;
    }

    public function h2pWithdrawNotifiication(Request $request)
    {
         // Get all request data (since it's application/x-www-form-urlencoded)
         $data = $request->all();
        if (!empty($data)) {
            
             if (!isset($data['Status'], $data['TransactionID'], $data['ID'])) {
                 return response()->json(['error' => 'Invalid Data'], 400);
             }
 
            $orderStatus = $data['Status'] == '000' ? 'success' : 'failed';
            $RefID = $data['TransactionID'];
             // Simulate delay
            // sleep(20);
            $updateData = [
                'settlement_trans_id' => $data['ID'],
                'status' => $orderStatus,
                'message' => $data['Message'],
                'api_response' => json_encode($data),
            ];
            // echo "<pre>";  print_r($updateData); die;
            SettleRequest::where('fourth_party_transection', $RefID)->update($updateData);
            echo "Transaction updated successfully!";
            //Call webhook API START
            $paymentDetail = SettleRequest::where('fourth_party_transection', $RefID)->first();
            $callbackUrl = $paymentDetail->callback_url;
            $postData = [
                'merchant_code' => $paymentDetail->merchant_code,
                'referenceId' => $paymentDetail->transaction_id,
                'transaction_id' => $paymentDetail->fourth_party_transection,
                'amount' => $paymentDetail->amount,
                'Currency' => $paymentDetail->Currency,
                'customer_name' => $paymentDetail->customer_name,
                'status' => $paymentDetail->status,
                'created_at' => $paymentDetail->created_at,
            ];
            try {
                if ($paymentDetail->callback_url != null) {
                    $response = Http::post($paymentDetail->callback_url, $postData);
                    echo $response->body(); die;
                }
            } catch (\Exception $e) {
                return response()->json(['error' => 'Failed to call webhook','message' => $e->getMessage()], 500);
            }
             //Call webhook API START

        }else{
            return response()->json(['error' => 'Data Not Found or Invalid Request!'], 400);
        }
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
