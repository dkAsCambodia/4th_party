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
        //   echo "<pre>";  print_r($res);die;
        $frtransaction = $this->generateUniqueCode();
        $client_ip = (isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR']);
        //Insert data into DB
             // for Help2Pay deposit charge START
             if(!empty($request->amount)){
                $percentage = 1;
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
                'gateway_name' => 'M2p',
                'customer_name' => $request->customer_name,
                'ip_address' => $client_ip,
                'net_amount' => $net_amount ?? '',
                'mdr_fee_amount' => $mdr_fee_amount ?? '',
            ];
            //   echo "<pre>";  print_r($addRecord); die;
            PaymentDetail::create($addRecord);
            $res = [
                "amount" =>  $request->amount,
                "apiToken" => $res['apiToken'],
                "api_url" => $res['api_url'],
                "callbackUrl" => url('api/m2pDepositNotifiication'),
                "currency" => $request->Currency,
                'Reference' => $frtransaction,
                'secretKey' => $res['secretKey'],
            ];
        return view('payment-form.m2p.gateway-form', compact('res'));

    }
    
    public function callDepositAPI(Request $request)
    {
        $postData = $request->all();
        unset($postData['secretKey']);
        unset($postData['Reference']);
        unset($postData['api_url']);
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post($request->api_url, $postData);
        $responseData = $response->json();
        // echo "<pre>";  print_r($responseData); die;

        if (!empty($responseData) && isset($responseData['checkoutUrl'])) {
            $updateData = [
                'TransId' =>  $responseData['paymentId'],
                'receipt_url' =>  $responseData['checkoutUrl'],
                'payin_arr' => json_encode($responseData),
            ];
            PaymentDetail::where('fourth_party_transection', $request->Reference)->update($updateData);
            return redirect()->to($responseData['checkoutUrl']);
        }else{
            $updateData = [
                'payment_status' => 'failed',
                'payin_arr' => json_encode($responseData),
            ];
            PaymentDetail::where('fourth_party_transection', $request->Reference)->update($updateData);
            $paymentDetail = PaymentDetail::where('fourth_party_transection', $request->Reference)->first();
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

    }

    public function m2pPayinCallbackURL(Request $request)
    {
        $data = $request->all();
        echo "Transaction Information as follows" . '<br/>' .
            "Merchant : " . $data['merchant_code'] . '<br/>' .
            "ReferenceId : " . $data['referenceId'] . '<br/>' .
            "TransactionId : " . $data['transaction_id'] . '<br/>' .
            "Type : Crypto Deposit" .'<br/>' .
            "Currency : " . $data['Currency'] . '<br/>' .
            "Amount : " . $data['amount'] . '<br/>' .
            "customer_name : " . $data['customer_name'] . '<br/>' .
            "Datetime : " . $data['created_at'] . '<br/>' .
            "Status : " . $data['payment_status'];
         die;
    }


    
    public function m2pDepositNotifiication(Request $request)
    {
        // echo "<pre>";  print_r($request->all());
        // $results = '{
        //     "depositAddress":"C9wic7ex7etARjPGQPKBHGLr2cRcCD17aZ",
        //     "cryptoTransactionInfo":
        //       [
        //         {
        //         "txid":"b20feab400c3cd61a9d0daec8526d739a2335fe1900415f24835001e58a837a7",
        //         "confirmations":2,
        //         "amount":0.10000000,
        //         "confirmedTime":"Mar 20, 2019 7:06:38PM",
        //         "status":"DONE",    
        //         "processingFee":0.00500000,
        //         "conversionRate":3198.64800
        //         }
        //       ],
        //     "paymentId":"99ca8c34-5191-41d9-a1a2-666b9badf1ce",
        //     "status":"DONE", //PENDING
        //     "transactionAmount":0.10000000,
        //     "netAmount":0.09500000,
        //     "transactionCurrency":"BTC",
        //     "processingFee":0.00500000,
        //     "finalAmount":303.87,
        //     "finalCurrency":"USD",
        //     "conversionRate":3198.65
        //     }';
        $data = $request->json()->all(); // Get JSON data from request
        if (!empty($data)) {
             $orderStatus = match ($data['status'] ?? '') {
                'DONE' => 'success',
                'PENDING' => 'pending',
                'NEW' => 'processing',
                default => 'failed',
            };
            $TransId = $data['paymentId'];
            $updateData = [
                'payment_status' => $orderStatus,
                'response_data' => json_encode($data),
            ];
            // echo "<pre>";  print_r($updateData); die;
            PaymentDetail::where('TransId', $TransId)->update($updateData);
            echo "Transaction updated successfully!";
            //Call webhook API START
            $paymentDetail = PaymentDetail::where('TransId', $TransId)->first();
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

    public function m2pPayoutform(Request $request)
    {
        return view('payment-form.m2p.payout-form');
    }

    public function payout(Request $request)
    {
        // echo "<pre>";  print_r($request->all()); die;
        $totalDepositSumAfterCharge = PaymentDetail::where('merchant_code', $request->merchant_code)->where('Currency', $request->Currency)->where('payment_status', 'success')->sum('net_amount');
        $totalPayoutSumAfterCharge = SettleRequest::where('merchant_code', $request->merchant_code)->where('Currency', $request->Currency)->where('status', 'success')->sum('net_amount');
        $AvailableforPayout=$totalDepositSumAfterCharge-$totalPayoutSumAfterCharge;
         //  For speedpay charge START
         $percentage = 1;
         $totalWidth = $AvailableforPayout;
         $new_width = ($percentage / 100) * $totalWidth;
         @$finalAmount = $totalWidth-$new_width;
         //  For speedpay charge END
         if($finalAmount < $request->amount){
             return "<h2 style='color:red'>Balance is not enough in Gateway Wallet!</h2>"; 
         }
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
        //Insert data into DB
             // for M2p deposit charge START
             if(!empty($request->amount)){
                $percentage = 1;
                $totalWidth = $request->amount;
                $mdr_fee_amount = ($percentage / 100) * $totalWidth;
                $net_amount= $totalWidth+$mdr_fee_amount;
            }
            // for M2p deposit charge END
            $addRecord = [
                  // 'settlement_trans_id' => $Transactionid ?? '',
                  'fourth_party_transection' => $frtransaction,
                  'merchant_track_id' => $request->referenceId,
                  'gateway_name' => 'M2p',
                  'agent_id' => $merchantData->agent_id,
                  'merchant_id' => $merchantData->id,
                  'merchant_code' => $request->merchant_code,
                  'callback_url' => $request->callback_url,
                  'customer_name' => $request->customer_name,
                  'Currency' => $request->Currency,
                  'total' => $request->amount ?? '',
                  'net_amount' => $net_amount ?? '',
                  'mdr_fee_amount' => $mdr_fee_amount ?? '',
                  'customer_bank_name' => $request->customer_name,
                  'customer_account_number' => $request->customer_account_number,
                  'product_id' => $request->product_id,
                  'payment_channel' => $gatewayPaymentChannel->id,
                  'payment_method' => $paymentMethod->method_name,
                  'ip_address' => $client_ip, 
                //   'api_response' => json_encode($resArray),
                //   'message' => $message,
                //   'status' => $status,
            ];
            //   echo "<pre>";  print_r($addRecord); die;
            SettleRequest::create($addRecord);
            $res = [
                "amount" =>  $request->amount,
                "apiToken" => $res['apiToken'],
                "api_url" => $res['api_url'],
                "callbackUrl" => url('api/m2pWithdrawNotification'),
                "currency" => $request->Currency,
                'Reference' => $frtransaction,
                'secretKey' => $res['secretKey'],
                'address' => $request->customer_account_number,
            ];
        return view('payment-form.m2p.gateway-form-payout', compact('res'));

    }

    public function callWithdarwAPI(Request $request)
    {
        $postData = $request->all();
        unset($postData['secretKey']);
        unset($postData['Reference']);
        unset($postData['api_url']);
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post($request->api_url, $postData);
        $responseData = $response->json();
        // echo "APIURL:".$request->api_url;
        // echo "<pre> PostData:";  print_r($postData); 
        // echo "<pre> Response:";  print_r($responseData); die;
        if (!empty($responseData)) {
            $orderStatus = match ($responseData['status'] ?? '') {
                'NEW' => 'processing',
                'ADMIN CONFIRMATION' => 'processing',
                'DONE' => 'success',
                'PENDING' => 'pending',
                default => 'failed',
            };
            $updateData = [
                'status' => $orderStatus,
                'settlement_trans_id' => $responseData['paymentId'] ?? '',
                'api_response' => json_encode($responseData),
                'message' => $responseData['errorList'][0] ?? $responseData['status'],
            ];
            // echo "<pre>";  print_r($updateData); die;
            SettleRequest::where('fourth_party_transection', $request->Reference)->update($updateData);
            $paymentDetail = SettleRequest::where('fourth_party_transection', $request->Reference)->first();
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

    }

    public function m2pPayoutcallbackURL(Request $request)
    {
        $data = $request->all();
        echo "Transaction Information as follows" . '<br/>' .
            "Merchant : " . $data['merchant_code'] . '<br/>' .
            "ReferenceId : " . $data['referenceId'] . '<br/>' .
            "TransactionId : " . $data['transaction_id'] . '<br/>' .
            "Type : Crypto Withdrawal" .'<br/>' .
            "Currency : " . $data['Currency'] . '<br/>' .
            "Amount : " . $data['amount'] . '<br/>' .
            "customer_name : " . $data['customer_name'] . '<br/>' .
            "Datetime : " . $data['created_at'] . '<br/>' .
            "Status : " . $data['status'];
         die;
    }

    public function m2pWithdrawNotification(Request $request)
    {
        // echo "<pre>";  print_r($request->all());
        // $results = '{
        //     "depositAddress":"C9wic7ex7etARjPGQPKBHGLr2cRcCD17aZ",
        //     "cryptoTransactionInfo":
        //       [
        //         {
        //         "txid":"b20feab400c3cd61a9d0daec8526d739a2335fe1900415f24835001e58a837a7",
        //         "confirmations":2,
        //         "amount":0.10000000,
        //         "confirmedTime":"Mar 20, 2019 7:06:38PM",
        //         "status":"DONE",    
        //         "processingFee":0.00500000,
        //         "conversionRate":3198.64800
        //         }
        //       ],
        //     "paymentId":"99ca8c34-5191-41d9-a1a2-666b9badf1ce",
        //     "status":"DONE", //PENDING
        //     "transactionAmount":0.10000000,
        //     "netAmount":0.09500000,
        //     "transactionCurrency":"BTC",
        //     "processingFee":0.00500000,
        //     "finalAmount":303.87,
        //     "finalCurrency":"USD",
        //     "conversionRate":3198.65
        //     }';
        $data = $request->json()->all(); // Get JSON data from request
        if (!empty($data)) {
             $orderStatus = match ($data['status'] ?? '') {
                'DONE' => 'success',
                'PENDING' => 'pending',
                'NEW' => 'processing',
                default => 'failed',
            };
            $TransId = $data['paymentId'];
            $updateData = [
                'status' => $orderStatus,
                'api_response' => json_encode($data),
                'message' => $orderStatus,
            ];
            // echo "<pre>";  print_r($updateData); die;
            SettleRequest::where('settlement_trans_id', $TransId)->update($updateData);
            echo "Transaction updated successfully!";
            //Call webhook API START
            $paymentDetail = SettleRequest::where('settlement_trans_id', $TransId)->first();
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

    public function payintest(Request $request)
    {
        return view('payment-form.m2p.payintest');
    }
    
    public function payouttest(Request $request)
    {
        return view('payment-form.m2p.payouttest');
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
