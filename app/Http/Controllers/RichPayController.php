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

class RichPayController extends Controller
{
    public function payinform(Request $request)
    {
        return view('payment-form.r2p.payin-form');
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

        $secretKey =  $res['secretKey']; // Store secret key in .env file
        $orderId = $frtransaction; // Replace with actual Order ID
        $amount =  $request->amount; // Replace with actual Amount
        // Step 1: Concatenate in required format
        $signatureString = "{$secretKey}:{$orderId}:{$amount}";
        // Step 2: Encode using Base64
        $encodedSignature = base64_encode($signatureString);

        // Call Curl API code START
        $postData = [
            // 'UrlFront' => url('s2p/payinResponse'), 
            'order_id' => $frtransaction,
            'amount' => $request->amount,
            'ref_account' => $request->customer_account_number,
            'ref_bank_code' => $request->bank_code,
            'ref_name_th' => $request->customer_name,
            'ref_name_en' => $request->customer_name,
            'ref_user_id' => '',
            'ref1' => '',
            'ref2' => '',
            'callback_url' => url('api/r2pDepositNotifiication'),
        ];
        $response = Http::withHeaders([
            'Authorization' => 'Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ0eXBlIjoiT1NNTyIsInNpZ25hdHVyZSI6ImNTMUNYMlZ6ZEU0MkxWUjNiMnRLZDFNeE1qQmhXRUpTZWpSTlFtOUVSVlpzWTFGblRXeHpNR1ZGVmtGRFoyNXdOSGxrTW1adVMyaFlTemxLTkRFeGFIa3phV3hwYjJVdE1VOXRlblYyY1daak16TXhVVkpIYVd0d09XeHJZV2xPVEhKVVRuZzNhV3cxVHpaMVpVNDVTMkZ0T0VoZlZraFhWRk56WW1GWGJEUkZUbEJTYXpWcVdrcHhNRXhuVkZabWN6bEJQUT09IiwiaWRlbnRpZmllciI6IlowRkJRVUZCUW01NVdISlJUa1JyZUdaVFRIcFdhMk41ZURaM2NVSm1SMmhCWkU5TU5scGxkR1E1TlV0bWNuaFZjQzExV213eFRFZGFSRXRTTUhKMFdqVm9iRGhrTTBwd2RFNU1aa1Y0VGtGYU1DMXphVmgzTlRZd1gzTXRhazVRWnpVeFMxTm5kVXBhWTBwSk0xbHdUVGRDU2tGRk5HODkifQ.gvm5-f2jGiJyDXYlaRsgLgscgQv3YS2zV7IHE4ZgWwg', // Ensure proper concatenation
            'x-api-key' => $res['apiKey'], // Correct usage of array key
            'x-signature' => $encodedSignature, // No change needed
        ])->post($res['api_url'], $postData);
        $jsonData = $response->json();
        // echo "<pre>";  print_r($jsonData); die;
        // Redirect to the payment link
        if (isset($jsonData['qr_image_link'])) {
            //Insert data into DB
             // for speedpay deposit charge START
            if(!empty($request->amount)){
                $percentage = 1.35;     // Deposit Charge for RichPay
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
                'TransId' => $jsonData['ref_id'],
                'callback_url' => $request->callback_url,
                'amount' => $request->amount,
                'Currency' => $request->Currency,
                'product_id' => $request->product_id,
                'bank_account_name' => $request->bank_account_name ?? $request->customer_name,
                'bank_code' => $request->bank_code_character ?? $request->bank_code,
                'bank_account_number' => $request->customer_account_number,
                'payment_channel' => $gatewayPaymentChannel->id,
                'payment_method' => $paymentMethod->method_name,
                'request_data' => json_encode($res),
                'gateway_name' => 'RichPay',
                'customer_name' => $request->customer_name ?? $request->bank_account_name,
                'customer_email' => $request->customer_email,
                'payin_arr' => json_encode($jsonData),
                'receipt_url' => $jsonData['qr_image_link'],
                'ip_address' => $client_ip,
                'net_amount' => $net_amount ?? '',
                'mdr_fee_amount' => $mdr_fee_amount ?? '',
            ];
            //   echo "<pre>";  print_r($addRecord); die;
            PaymentDetail::create($addRecord);
            // sleep(20);
            return redirect(url('r2pPaymentPage/'.base64_encode($frtransaction)));
        }else{
            return back()->with('error', 'Payment link not found.');
        }

    }

    public function paymentPage(Request $request, $frtransaction)
    {
        $RefID = base64_decode($frtransaction);
        $paymentDetail = PaymentDetail::where('fourth_party_transection', $RefID)->first();
          
            $data = [
                'merchant_code' => $paymentDetail->merchant_code,
                'referenceId' => $paymentDetail->transaction_id,
                'transaction_id' => $paymentDetail->fourth_party_transection,
                'amount' => $paymentDetail->amount,
                'Currency' => $paymentDetail->Currency,
                'customer_name' => $paymentDetail->customer_name,
                'bank_account_name' => $paymentDetail->bank_account_name,
                'bank_code' => $paymentDetail->bank_code,
                'bank_account_number' => $paymentDetail->bank_account_number,
                'receipt_url' => $paymentDetail->receipt_url,
                'payment_status' => $paymentDetail->payment_status,
                'created_at' => $paymentDetail->created_at,
            ];
                // echo "<pre>";  print_r($data); die;
        return view('payment-form.r2p.paymentPage', compact('data'));
    }

    public function paymentProcessingPage(Request $request, $frtransaction)
    {
        $RefID = base64_decode($frtransaction);
        $paymentDetail = PaymentDetail::where('fourth_party_transection', $RefID)->first();
            $data = [
                'merchant_code' => $paymentDetail->merchant_code,
                'referenceId' => $paymentDetail->transaction_id,
                'transaction_id' => $paymentDetail->fourth_party_transection,
                'amount' => $paymentDetail->amount,
                'Currency' => $paymentDetail->Currency,
                'customer_name' => $paymentDetail->customer_name,
                'bank_account_name' => $paymentDetail->bank_account_name,
                'bank_code' => $paymentDetail->bank_code,
                'bank_account_number' => $paymentDetail->bank_account_number,
                'receipt_url' => $paymentDetail->receipt_url,
                'payment_status' => $paymentDetail->payment_status,
                'created_at' => $paymentDetail->created_at,
            ];
            // echo "<pre>";  print_r($data); die;
        return view('payment-form.r2p.paymentProcessingPage', compact('data'));
    }

    public function payinResponse(Request $request, $frtransaction)
    {
        $RefID = base64_decode($frtransaction);
        // $paymentDetail = PaymentDetail::where('fourth_party_transection', $RefID)->first();
        // echo "<pre>";  print_r($paymentDetail); die;
        // $secretKey =  'Z0FBQUFBQm55WHJRYllhRGdjNXl5NjFvTDRLRHNhcElGamN3'; // Store secret key in .env file
        // $signatureString = "{$secretKey}:{$RefID}:{$paymentDetail->amount}";
        // $encodedSignature = base64_encode($signatureString);
        // // Call Curl API code START
        // $response = Http::withHeaders([
        //     'Authorization' => 'Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ0eXBlIjoiT1NNTyIsInNpZ25hdHVyZSI6ImNTMUNYMlZ6ZEU0MkxWUjNiMnRLZDFNeE1qQmhXRUpTZWpSTlFtOUVSVlpzWTFGblRXeHpNR1ZGVmtGRFoyNXdOSGxrTW1adVMyaFlTemxLTkRFeGFIa3phV3hwYjJVdE1VOXRlblYyY1daak16TXhVVkpIYVd0d09XeHJZV2xPVEhKVVRuZzNhV3cxVHpaMVpVNDVTMkZ0T0VoZlZraFhWRk56WW1GWGJEUkZUbEJTYXpWcVdrcHhNRXhuVkZabWN6bEJQUT09IiwiaWRlbnRpZmllciI6IlowRkJRVUZCUW01NVdISlJUa1JyZUdaVFRIcFdhMk41ZURaM2NVSm1SMmhCWkU5TU5scGxkR1E1TlV0bWNuaFZjQzExV213eFRFZGFSRXRTTUhKMFdqVm9iRGhrTTBwd2RFNU1aa1Y0VGtGYU1DMXphVmgzTlRZd1gzTXRhazVRWnpVeFMxTm5kVXBhWTBwSk0xbHdUVGRDU2tGRk5HODkifQ.gvm5-f2jGiJyDXYlaRsgLgscgQv3YS2zV7IHE4ZgWwg', // Ensure proper concatenation
        //     'x-api-key' => 'c48beec83f740331c0ff58', // Correct usage of array key
        //     'x-signature' => $encodedSignature, // No change needed
        // ])->post('https://service.richpay.io/api/v1/client/resend_callback_for_transaction', ['ref_id' => $paymentDetail->TransId ]);
        // $jsonData = $response->json();
        // echo "<pre>";  print_r($jsonData); die;
        // $orderStatus = match ($jsonData['status'] ?? '') {
        //     'SUCCESS' => 'success',
        //     'AUTO_SUCCESS' => 'success',
        //     'PROCESSING' => 'processing',
        //     default => 'failed',
        // };
        $updateData = [
            'payment_status' => 'processing',
            // 'response_data' => json_encode($jsonData),
        ];
        PaymentDetail::where('fourth_party_transection', $RefID)->update($updateData);
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

        return view('payment.payment_status', compact('request', 'postData', 'callbackUrl'));
    }

    public function r2pPayinCallbackURL(Request $request)
    {
        $data = $request->all();
        // echo "Transaction Information as follows" . '<br/>' .
        //     "Merchant : " . $data['merchant_code'] . '<br/>' .
        //     "ReferenceId : " . $data['referenceId'] . '<br/>' .
        //     "TransactionId : " . $data['transaction_id'] . '<br/>' .
        //     "Type : Deposit" .'<br/>' .
        //     "Currency : " . $data['Currency'] . '<br/>' .
        //     "Amount : " . $data['amount'] . '<br/>' .
        //     "customer_name : " . $data['customer_name'] . '<br/>' .
        //     "Datetime : " . $data['created_at'] . '<br/>' .
        //     "Status : " . $data['payment_status'];
        return view('payment-form.r2p.deposit-response-page', compact('data'));
    }

    public function r2pDepositNotifiication(Request $request)
    {
        // echo "<pre>";  print_r($request->all());
        // $results = '{
        //   "type": "DEPOSIT",
        //   "status": "SUCCESS",
        //   "status_des": "",
        //   "amount": 0,
        //   "txn_ref_id": "",
        //   "txn_ref_order_id": "",
        //   "txn_ref_bank_code": "",
        //   "txn_ref_bank_acc_no": "",
        //   "txn_ref_bank_acc_name": "",
        //   "txn_ref_user_id": "",
        //   "txn_ref1": "",
        //   "txn_ref2": "",
        //   "txn_timestamp": "1970-01-01T00:00:00",
        //   "stm_timestamp": "1970-01-01T00:00:00",
        //   "stm_bank_code": "",
        //   "stm_bank_acc_no": "",
        //   "stm_bank_acc_name": "",
        //   "stm_desc": "",
        //   "stm_ref_id": ""
        // }';
        $data = $request->json()->all(); // Get JSON data from request
        if (!empty($data)) {
             $orderStatus = match ($data['status'] ?? '') {
                'SUCCESS' => 'success',
                'AUTO_SUCCESS' => 'success',
                'PROCESSING' => 'processing',
                'Failed' => 'failed',
                default => 'not confirm',
            };
            $RefID = $data['txn_ref_order_id'];
            sleep(50);
            $updateData = [
                'payment_status' => $orderStatus ?? $data['status'],
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

    public function r2pPayoutform(Request $request)
    {
        return view('payment-form.r2p.payout-form');
    }

    public function payout(Request $request)
    {
        // echo "<pre>";  print_r($request->all()); die;
        $totalDepositSumAfterCharge = PaymentDetail::where('merchant_code', $request->merchant_code)->where('Currency', $request->Currency)->where('payment_status', 'success')->sum('net_amount');
        $totalPayoutSumAfterCharge = SettleRequest::where('merchant_code', $request->merchant_code)->where('Currency', $request->Currency)->where('status', 'success')->sum('net_amount');
        $AvailableforPayout=$totalDepositSumAfterCharge-$totalPayoutSumAfterCharge;
        //  For RichPay charge START
        $perTransaction = 10;
        $availableBalance = $AvailableforPayout - $perTransaction;
        //  For RichPay charge END
        if($availableBalance < $request->amount){
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
        $secretKey =  $res['secretKey']; // Store secret key in .env file
        $orderId = $frtransaction; // Replace with actual Order ID
        $amount =  $request->amount; // Replace with actual Amount
        // Step 1: Concatenate in required format
        $signatureString = "{$secretKey}:{$orderId}:{$amount}";
        // Step 2: Encode using Base64
        $encodedSignature = base64_encode($signatureString);

        // Call Curl API code START
        $postData = [
            'amount' => $request->amount,
            'dest_bank_acc_no' => $request->customer_account_number,
            'dest_bank_acc_name' => $request->customer_name ?? $request->bank_account_name,
            'dest_bank_code' => $request->bank_code,
            'withdraw_code' => '482615',
            'callback_url' => url('api/r2pWithdrawNotifiication'),
            'order_id' => $frtransaction,
        ];
        $response = Http::withHeaders([
            'Authorization' => 'Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ0eXBlIjoiT1NNTyIsInNpZ25hdHVyZSI6ImNTMUNYMlZ6ZEU0MkxWUjNiMnRLZDFNeE1qQmhXRUpTZWpSTlFtOUVSVlpzWTFGblRXeHpNR1ZGVmtGRFoyNXdOSGxrTW1adVMyaFlTemxLTkRFeGFIa3phV3hwYjJVdE1VOXRlblYyY1daak16TXhVVkpIYVd0d09XeHJZV2xPVEhKVVRuZzNhV3cxVHpaMVpVNDVTMkZ0T0VoZlZraFhWRk56WW1GWGJEUkZUbEJTYXpWcVdrcHhNRXhuVkZabWN6bEJQUT09IiwiaWRlbnRpZmllciI6IlowRkJRVUZCUW01NVdISlJUa1JyZUdaVFRIcFdhMk41ZURaM2NVSm1SMmhCWkU5TU5scGxkR1E1TlV0bWNuaFZjQzExV213eFRFZGFSRXRTTUhKMFdqVm9iRGhrTTBwd2RFNU1aa1Y0VGtGYU1DMXphVmgzTlRZd1gzTXRhazVRWnpVeFMxTm5kVXBhWTBwSk0xbHdUVGRDU2tGRk5HODkifQ.gvm5-f2jGiJyDXYlaRsgLgscgQv3YS2zV7IHE4ZgWwg', // Ensure proper concatenation
            'x-api-key' => $res['apiKey'], // Correct usage of array key
            'x-signature' => $encodedSignature, // No change needed
        ])->post($res['api_url'], $postData);
        $jsonData = $response->json();
        // echo "<pre>";  print_r($jsonData['detail']); die;

        if(!empty($jsonData['status'])){
            $Transactionid = $jsonData['ref_id'];
            $status = match ($jsonData['status'] ?? '') {
                'SUCCESS' => 'success',
                'AUTO_SUCCESS' => 'success',
                'PROCESSING' => 'processing',
                'PENDING_CONFIRM' => 'pending',
                default => 'failed',
            };
            $message = $status;
        } else {
            $status = 'failed';
            $message = $jsonData['detail'] ?? ''; 
        }

        ////Insert Record into DB
            $client_ip = (isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR']);
             // for RichPay payout charge START
            if(!empty($request->amount)){
                $mdr_fee_amount = 10;
                $net_amount= $request->amount+$mdr_fee_amount;
            }
            // for RichPay charge END
                $addRecord = [
                    'settlement_trans_id' => $Transactionid ?? '',
                    'fourth_party_transection' => $frtransaction,
                    'merchant_track_id' => $request->referenceId,
                    'gateway_name' => 'RichPay',
                    'agent_id' => $merchantData->agent_id,
                    'merchant_id' => $merchantData->id,
                    'merchant_code' => $request->merchant_code,
                    'callback_url' => $request->callback_url,
                    'total' => $request->amount,
                    'net_amount' => $net_amount,
                    'mdr_fee_amount' => $mdr_fee_amount,
                    'customer_bank_name' => $request->bank_account_name ?? $request->customer_name,
                    'bank_code' => $request->bank_code_character ?? $request->bank_code,
                    'customer_account_number' => $request->customer_account_number,
                    'Currency' => $request->Currency,
                    'product_id' => $request->product_id,
                    'payment_channel' => $gatewayPaymentChannel->id,
                    'payment_method' => $paymentMethod->method_name,
                    'customer_name' => $request->customer_name ?? $request->bank_account_name,
                    'api_response' => json_encode($jsonData),
                    'message' => $message ?? '',
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

    public function r2pPayoutcallbackURL(Request $request)
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

    public function r2pWithdrawNotifiication(Request $request)
    {
         // $results = '{
        //   "type": "DEPOSIT",
        //   "status": "SUCCESS",
        //   "status_des": "",
        //   "amount": 0,
        //   "txn_ref_id": "",
        //   "txn_ref_order_id": "",
        //   "txn_ref_bank_code": "",
        //   "txn_ref_bank_acc_no": "",
        //   "txn_ref_bank_acc_name": "",
        //   "txn_ref_user_id": "",
        //   "txn_ref1": "",
        //   "txn_ref2": "",
        //   "txn_timestamp": "1970-01-01T00:00:00",
        //   "stm_timestamp": "1970-01-01T00:00:00",
        //   "stm_bank_code": "",
        //   "stm_bank_acc_no": "",
        //   "stm_bank_acc_name": "",
        //   "stm_desc": "",
        //   "stm_ref_id": ""
        // }';

        // Decode the JSON payload automatically
        $results = $request->json()->all();
        if(!empty($results)) {
            $status = match ($results['status'] ?? '') {
                'SUCCESS' => 'success',
                'AUTO_SUCCESS' => 'success',
                'PROCESSING' => 'processing',
                'PENDING_CONFIRM' => 'pending',
                default => 'failed',
            };
            $RefID = $results['txn_ref_order_id'];
            sleep(50);
            $updateData = [
                'status' => $status,
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

    public function payintest(Request $request)
    {
        return view('payment-form.r2p.payintest');
    }
    
    public function payouttest(Request $request)
    {
        return view('payment-form.r2p.payouttest');
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
