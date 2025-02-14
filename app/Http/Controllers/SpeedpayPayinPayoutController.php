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
        //   echo "<pre>";  print_r($res);die;
        $frtransaction = $this->generateUniqueCode();
        $client_ip = (isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR']);
        date_default_timezone_set('Asia/Phnom_Penh');
        $TransactionDateTime=date("Y‐m‐d h:i:sA");
        // Call Curl API code START
        $postData = [
            'ClientIp' => $client_ip,
            'RefID' => $frtransaction,
            'CustomerID' => 'ZCUST1001',
            'CurrencyCode' => $request->Currency ?? $request->currency,
            'Amount' => $request->amount,
            'TransactionDateTime' => $TransactionDateTime,
            'Remark' => 'payment',
            'CustomerFullName' => $request->bank_account_name ?? $request->customer_name,
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
        // echo "<pre>";  print_r($postData); die;
        // Redirect to the payment link
        if (isset($jsonData['RedirectionUrl'])) {
            //Insert data into DB
             // for speedpay deposit charge START
            if(!empty($request->amount)){
                $percentage = 1.6;
                $totalWidth = $request->amount;
                $mdr_fee_amount = ($percentage / 100) * $totalWidth;
                $net_amount= $totalWidth-$mdr_fee_amount;
            }
            // for speedpay deposit charge END
            $addRecord = [
                'agent_id' => $merchantData->agent_id,
                'merchant_id' => $merchantData->id,
                'merchant_code' => $request->merchant_code,
                'transaction_id' => $request->referenceId,
                'fourth_party_transection' => $frtransaction,
                'TransId' => $jsonData['DepositID'],
                'callback_url' => $request->callback_url,
                'amount' => $request->amount,
                'Currency' => $request->Currency ?? $request->currency,
                'product_id' => $request->product_id,
                'bank_account_name' => $request->bank_account_name ?? $request->customer_name,
                'bank_code' => $request->bank_code,
                'bank_account_number' => $request->customer_account_number,
                'payment_channel' => $gatewayPaymentChannel->id,
                'payment_method' => $paymentMethod->method_name,
                'request_data' => json_encode($res),
                'gateway_name' => 'SpeedPay',
                'customer_name' => $request->customer_name,
                'customer_email' => $request->customer_email,
                'payin_arr' => json_encode($jsonData),
                'receipt_url' => $jsonData['RedirectionUrl'],
                'ip_address' => $client_ip,
                'net_amount' => $net_amount ?? '',
                'mdr_fee_amount' => $mdr_fee_amount ?? '',
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

    public function s2pDepositNotifiication(Request $request)
    {
        // Decode the JSON payload automatically
        $results = $request->json()->all();
        if(!empty($results)) {
            // Extract data
            $RefID = $results['RefID'];
            $status = $results['Status'];
            if ( in_array($status, ['Successful', 'Approved']) ) {
                $orderStatus = 'success';
            } elseif (in_array($status, ['Pending', 'Processing', 'Created'])) {
                $orderStatus = 'processing';
            } else {
                $orderStatus = 'failed';
            }
            // Simulate delay
            // sleep(20);
            $updateData = [
                'payment_status' => $orderStatus,
                'response_data' => json_encode($results),
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

    public function s2pPayoutform(Request $request)
    {
        return view('payment-form.s2p.payout-form');
    }

    public function payout(Request $request)
    {
        // echo "<pre>";  print_r($request->all()); die;
        $CURRENCY = $request->Currency ?? $request->currency;
        $totalDepositSumAfterCharge = PaymentDetail::where('merchant_code', $request->merchant_code)->where('Currency', $CURRENCY)->where('payment_status', 'success')->sum('net_amount');
        $totalPayoutSumAfterCharge = SettleRequest::where('merchant_code', $request->merchant_code)->where('Currency', $CURRENCY)->where('status', 'success')->sum('net_amount');
        $AvailableforPayout=$totalDepositSumAfterCharge-$totalPayoutSumAfterCharge;
        //  For speedpay charge START
        $percentage = 0.7;
        $totalWidth = $AvailableforPayout;
        $new_width = ($percentage / 100) * $totalWidth;
        @$finalAmount = $totalWidth-$new_width;
        //  For speedpay charge END
        if($finalAmount < $request->amount){
            return "<h2 style='color:red'>Balance is not enough in Gateway Wallet!</h2>"; 
        }
        // echo "<pre>"; print_r($totalPayoutSum); die;
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
        date_default_timezone_set('Asia/Phnom_Penh');
        $TransactionDateTime=date("Y‐m‐d h:i:sA");
        // Call Curl API code START
        $postData = [
            'ClientIp' => $client_ip,
            'RefID' => $frtransaction,
            'CustomerID' => 'ZCUST1001',
            'ToCurrencyCode' => $request->Currency ?? $request->currency,
            'ToAmount' => $request->amount,
            'TransactionDateTime' => $TransactionDateTime,
            'Remark' => 'payment',
            'ToBankAccountName' => $request->bank_account_name ?? $request->customer_name,
            'ToBankAccountNumber' => $request->customer_account_number,
            'ToBankCode' => $request->bank_code,
        ];
        $response = Http::withHeaders([
            'Content-Type' => 'application/x-www-form-urlencoded',
            'API-AGENT-CODE' => $res['api_agent_code'],
            'API-KEY' => $res['apiKey'],
            'API-AGENT-USER-NAME' => $res['api_agent_username'],
        ])->asForm()->post($res['api_url'], $postData);
    
        $result = $response->json();
        // echo "<pre>";  print_r($result); die;
        if($result['success'] == '1'){
            $RefId = $result['RefId'];

            $response2 = Http::withHeaders([
                'Content-Type' => 'application/x-www-form-urlencoded',
                'API-AGENT-CODE' => $res['api_agent_code'],
                'API-KEY' => $res['apiKey'],
                'API-AGENT-USER-NAME' => $res['api_agent_username'],
            ])->asForm()->post('https://agent.99speedpay.com/api/services/CheckPayout', [
                'RefID' => $RefId
            ]);
            $result2 = $response2->json();

            if(!empty($result2)){
                $Transactionid = $result2['info']['PayoutID']; 
                $message = $result2['info']['Status']; 
                $status = match ($result2['info']['Status'] ?? '') {
                    'Approved' => 'success',
                    'Successful' => 'success',
                    'Created' => 'processing',
                    'Pending' => 'processing',
                    'Processing' => 'processing',
                    default => 'failed',
                };
            }


        } else {
            $message = $result['message']; 
            $status = 'failed';
        }

        ////Insert Record into DB
            $client_ip = (isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR']);
             // for speedpay payout charge START
            if(!empty($request->amount)){
                $percentage = 0.7;
                $totalWidth = $request->amount;
                $mdr_fee_amount = ($percentage / 100) * $totalWidth;
                $net_amount= $totalWidth+$mdr_fee_amount;
            }
            // for speedpay charge END
                $addRecord = [
                    'settlement_trans_id' => $Transactionid ?? '',
                    'fourth_party_transection' => $frtransaction,
                    'merchant_track_id' => $request->referenceId,
                    'gateway_name' => 'speedPay',
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
                    'api_response' => json_encode($result2 ?? $result),
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

    public function s2pPayoutcallbackURL(Request $request)
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

    public function s2pWithdrawNotifiication(Request $request)
    {
        // Decode the JSON payload automatically
        $results = $request->json()->all();
        if(!empty($results)) {
            // Extract data
            $RefID = $results['RefID'];
            $status = $results['Status'];
            if ( in_array($status, ['Successful', 'Approved']) ) {
                $orderStatus = 'success';
            } elseif (in_array($status, ['Pending', 'Processing', 'Created'])) {
                $orderStatus = 'processing';
            } else {
                $orderStatus = 'failed';
            }
            // Simulate delay
            // sleep(20);
            $updateData = [
                'status' => $orderStatus,
                'api_response' => json_encode($results),
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
                'amount' => $paymentDetail->total,
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
