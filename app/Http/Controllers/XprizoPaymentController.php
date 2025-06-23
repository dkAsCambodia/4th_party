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
use App\Models\SettleRequest; 
use App\Models\PaymentMap;
use App\Models\PaymentMethod;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use Session;

class XprizoPaymentController extends Controller
{
    public function xpzDepositform(Request $request)
    {
        return view('payment-form.xpz.deposit');
    }

    public function xpzDepositApifun(Request $request)
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
        $frtransaction = $this->generateUniqueCode();
        //  echo "<pre>";  print_r($res); die;
        // Call Curl API code START
        $expiration = $request->expiration;
        if(empty($expiration)){
            $expiryMonth =$request->expiryMonth;
            $expiryYear =$request->expiryYear;
        }else{
            list($expiryMonth, $expiryYear) = explode('/', $expiration);
        }
        $response = Http::withHeaders([
            'x-api-version' => '1.0',
            'x-api-key' => $res['apiKey'],
            'Accept' => 'text/plain',
            'Content-Type' => 'application/json',
        ])->post($res['api_url'], [
            'description' => 'REDIRECT-PASS',
            'reference' => $frtransaction,
            'amount' => $request->amount,
            'currencyCode' => $request->Currency,
            'accountId' => $res['accountId'],
            'transferAccountId' => $res['transferAccountId'],
            'customer' => $request->customer_name,
            'customerData' => [
                'name' => $request->customer_name,
                'email' => $request->customer_email ?? 'default@example.com', // Ensure email is available
                'mobile' => '+855 69861408', 
                'birthDate' => '2025-02-03T10:21:01.871Z', 
                'ipAddress' => request()->ip(), // Fetch user's IP dynamically
                'address' => [
                    'address' => 'poipet',
                    'countryCode' => 'KHM',
                    'street' => 'poipet',
                    'city' => 'poipet',
                    'stateProvinceRegion' => 'Battambang Province',
                    'zipPostalCode' => '273154'
                ],
                'device' => [
                    'width' => $request->device_width ?? 'unknown',
                    'height' => $request->device_height ?? 'unknown',
                    'userAgent' => $request->header('User-Agent') ?? 'unknown',
                    'colorDepth' => $request->colorDepth ?? 'unknown'
                ]
            ],
            'creditCard' => [
                'name' => $request->customer_name,
                'number' => $request->card_number,
                'expiryMonth' => $expiryMonth ?? $request->expiryMonth,
                // 'expiryYear' => '20'. $expiryYear ?? $request->expiryYear,
                 'expiryYear' => '20' . ($expiryYear ?? $request->expiryYear),
                'cvv' => $request->cvv,
            ],
            'productCode' => '',
            'redirect' => url('xpz/deposit/gatewayResponse'), 
            'sourceType' => '',
        ]);

        $result = $response->json();
        //  echo "<pre>";  print_r($result); die;
         // for XPRIXO deposit charge START
            if(!empty($request->amount)){
                $percentage = 1.35;     // Deposit Charge for RichPay
                $totalWidth = $request->amount;
                $mdr_fee_amount = ($percentage / 100) * $totalWidth;
                $net_amount= $totalWidth-$mdr_fee_amount;
            }
            // for XPRIXO deposit charge END
        if (isset($result['status'])) {
            if ($result['status'] == 'Redirect') {
                     //Insert data into DB
                     $client_ip = (isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR']);
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
                         'payment_channel' => $gatewayPaymentChannel->id,
                         'payment_method' => $paymentMethod->method_name,
                         'request_data' => json_encode($res),
                         'gateway_name' => 'Xprizo card payment',
                         'customer_name' => $request->customer_name,
                         'payin_arr' => json_encode($result),
                         'receipt_url' => $result['value'],
                         'ip_address' => $client_ip, 
                         'net_amount' => $net_amount ?? '',
                         'mdr_fee_amount' => $mdr_fee_amount ?? '',     
                     ];
                    //  echo "<pre>";  print_r($addRecord); die;
                    PaymentDetail::create($addRecord);
                    return redirect($result['value']);
            } else {
                   $client_ip = (isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR']);
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
                         'payment_channel' => $gatewayPaymentChannel->id,
                         'payment_method' => $paymentMethod->method_name,
                         'request_data' => json_encode($res),
                         'gateway_name' => 'Xprizo card payment',
                         'customer_name' => $request->customer_name,
                         'payin_arr' => json_encode($result),
                         'payment_status' => 'failed',
                         'receipt_url' => $result['message'],
                         'ip_address' => $client_ip,  
                         'net_amount' => $net_amount ?? '',
                         'mdr_fee_amount' => $mdr_fee_amount ?? '',    
                     ];
                    PaymentDetail::create($addRecord);
               echo "<pre>"; print_r($result); 
            }
        } else {
            echo "Unexpected Response"; echo "<pre>"; print_r($result); die;
        }
    }

    public function xpzDepositGatewayResponse(Request $request)
    {
        $response = $request->all();
        $RefId = $response['reference'] ?? null;
        $Transactionid = $response['key'] ?? null;
    
        $orderstatus = match ($response['status'] ?? null) {
            'Active' => 'success',
            'Pending' => 'pending',
            default => 'failed',
        };

        $updateData = [
            'TransId' => $Transactionid,
            'payment_status' => $orderstatus,
            'payin_arr' => json_encode($response)
        ];
        PaymentDetail::where('fourth_party_transection', $RefId)->update($updateData);
        $paymentDetail = PaymentDetail::where('fourth_party_transection', $RefId)->first();
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

    public function xpzDepositResponse(Request $request)
    {
        $data = $request->all();
        echo "Transaction Information as follows" . '<br/>' .
            "Merchant_code : " . $data['merchant_code'] . '<br/>' .
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


    public function xpzWebhookNotifiication(Request $request)
    {
        // {
        //     "statusType": 3,
        //     "status": "Rejected",  // New /Accepted/Cancelled
        //     "description": "Reason for rejection",
        //     "actionedById": 1,
        //     "affectedContactIds": [],
        //     "transaction": {
        //       "id": 0,
        //       "createdById": 2,
        //       "type": "UCD",
        //       "date": "2021-04-20T20:34:00.7606173+02:00",
        //       "reference": 234234234,
        //       "currencyCode": "USD",
        //       "amount": 100.00
        //     }
        // }
        // Decode the JSON payload automatically
        $results = $request->json()->all();
        if(!empty($results)) {
            $RefID = $results['transaction']['reference'] ?? null;
            $orderStatus = match ($results['status'] ?? '') {
                'Active' => 'success',
                'Pending' => 'pending',
                default => 'failed',
            };
            sleep(10);         // Simulate delay
                if(!empty($results['transaction']['type']=='UCD')) {
                            $updateData = [
                                'payment_status' => $orderStatus,
                                'response_data' => json_encode($results),
                            ];
                            PaymentDetail::where('fourth_party_transection', $RefID)->update($updateData);
                            echo "Deposit Transaction updated successfully!";
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
                        $updateData = [
                            'status' => $orderStatus,
                            'api_response' => json_encode($results),
                        ];
                        SettleRequest::where('fourth_party_transection', $RefID)->update($updateData);
                        echo "Withdrawal Transaction updated successfully!";
                        //Call webhook API START
                        $paymentDetail = SettleRequest::where('fourth_party_transection', $RefID)->first();
                        $callbackUrl = $paymentDetail->callback_url;
                        $postData = [
                            'merchant_code' => $paymentDetail->merchant_code,
                            'referenceId' => $paymentDetail->merchant_track_id,
                            'transaction_id' => $paymentDetail->fourth_party_transection,
                            'amount' => $paymentDetail->total,
                            'Currency' => $paymentDetail->Currency,
                            'customer_name' => $paymentDetail->customer_name,
                            'payment_status' => $paymentDetail->status,
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
                }

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
