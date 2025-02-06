<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Billing;
use App\Models\GatewayAccount;
use App\Models\GatewayAccountMethod;
use App\Models\GatewayPaymentChannel;
use App\Models\Merchant;
use App\Models\TransactionNotification;
use App\Models\ParameterSetting;
use App\Models\ParameterValue;
use App\Models\SettleRequest;
use App\Models\PaymentMap;
use App\Models\PaymentMethod;
use App\Models\User;
use App\Models\PaymentDetail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

use App\Events\DepositCreated;

class PayoutController extends Controller
{
    public function payoutRequest(Request $request)
    {

        $totalDepositSumAfterCharge = PaymentDetail::where('merchant_code', $request->merchant_code)->where('Currency', $request->currency)->where('payment_status', 'success')->sum('net_amount');
        $totalPayoutSumAfterCharge = SettleRequest::where('merchant_code', $request->merchant_code)->where('Currency', $request->currency)->where('status', 'success')->sum('net_amount');
        $AvailableforPayout=$totalDepositSumAfterCharge-$totalPayoutSumAfterCharge;

        // for vizpay charge START
        // $totalPayoutSum = SettleRequest::where('merchant_code', $request->merchant_code)->where('status', 'success')->sum('total');
        // $totalpayoutCount = SettleRequest::where('merchant_code', $request->merchant_code)->where('status', 'success')->count('total');
        // $payout_charge = ($totalpayoutCount*10)+10;
        // $totalPayoutwithCharge = $payout_charge + $totalPayoutSum;
        // @$finalAmount=$AvailableforPayout-$totalPayoutwithCharge;
         // for vizpay charge END

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
        $res['transaction_id'] = $frtransaction; // 4th party
        $res['callback_url'] = url('payout_status');
        $res['customer_name'] = $request->customer_name;
        $res['customer_email'] = $request->customer_email;  //optional
        $res['customer_phone'] = $request->customer_phone;  //optional
        if(isset($request->customer_bank_name) && !empty($request->customer_bank_name)){
            $res['customer_bank_name'] = $request->customer_bank_name; 
        }
        if(isset($request->customer_account_number) && !empty($request->customer_account_number)){
            $res['customer_account_number'] = $request->customer_account_number; 
        }
        if(isset($request->card_number) && !empty($request->card_number)){
            $res['card_number'] = $request->card_number; 
            $res['expiryMonth'] = $request->expiryMonth; 
            $res['expiryYear'] = $request->expiryYear; 
            $res['cvv'] = $request->cvv; 
        }
        $res['customer_addressline_1'] = $request->customer_addressline_1; //optional
        $res['customer_zip'] = $request->customer_zip; //optional
        $res['customer_country'] = $request->customer_country; //optional
        $res['customer_city'] = $request->customer_city; //optional

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

        return view('payout.payout-form', compact('res')); 
    }

    // public function depositchangeFun($totalDepositSum){
    //     $percentage = 2.3;
    //     // $totalDepositSum = 200;
    //     $new_width = ($percentage / 100) * $totalDepositSum;
    //     // echo $totalDepositSum-$new_width;
    //     return $new_width;

    // }

    public function checkLimitationRiskMode($gatewayPaymentChannel, $paymentMap)
    {
        $paymentDetail = SettleRequest::where('product_id', $paymentMap->id)->where('payout_status', 'success')->get();
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
        // do {
        //     $mytime = Carbon::now();
        //     $currentDateTime = str_replace(' ', '', $mytime->parse($mytime->toDateTimeString())->format('Ymd His'));
        //     $fourth_party_transection = $currentDateTime . random_int(1000, 9999);
        // } while (SettleRequest::where('fourth_party_transection', '=', 'TR' . $fourth_party_transection)->first());

        return 'TR'.$fourth_party_transection;
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
        // for speedpay payout charge START
        if(!empty($amount)){
            $percentage = 0.7;
            $totalWidth = $amount;
            $mdr_fee_amount = ($percentage / 100) * $totalWidth;
            $net_amount= $totalWidth+$mdr_fee_amount;
        }
        // for H2p speedpay charge END

        $merchentdata = Merchant::where('merchant_code', $request->merchant_code)->first();
        if(!empty($merchentdata)){
            $addRecord = [
                'merchant_id' => $merchentdata->id,
                'merchant_code' => $request->merchant_code,
                'agent_id' => $merchentdata->agent_id,
                'merchant_track_id' => $request->transaction_id,
                'fourth_party_transection' => $frtransaction,
                'customer_name' => $request->customer_name,
                'customer_email' => $request->customer_email,
                'customer_bank_name' => $request->customer_bank_name,
                'customer_account_number' => $request->customer_account_number,
                'callback_url' => $request->callback_url,
                'total' => $amount,
                'product_id' => $request->product_id,
                'payment_channel' => $gatewayPaymentChannel->id,
                'payment_method' => $paymentMethod->method_name,
                'api_response' => json_encode($res),
                'net_amount' => $net_amount ?? '',
                'mdr_fee_amount' => $mdr_fee_amount ?? '',
                'customer_id' => ! empty($request->customer_id) ? $request->customer_id : 0,
                'ip_address' => $ip,
                'Currency' => $request->currency,
            ];
    
            SettleRequest::create($addRecord);
             // Broadcast the event Notification code START
            $data = [
                'type' => 'Withdrawl',
                'transaction_id' => $request->transaction_id,
                'amount' => $amount,
                'Currency' => $request->currency,
                'status' => 'pending',
                'msg' => 'New Withdrawl Transaction Created!',
            ];
            event(new DepositCreated($data));
            // Broadcast the event Notification code START
             // Insert data in Notification table Code START
            $addNotificationRecord = [
                'notifiable_type' => 'Withdrawl',
                'agent_id' => $merchentdata->agent_id,
                'merchant_id' => $merchentdata->id,
                'data' => json_encode($data,true),
                'msg' => 'New Withdrawl Transaction Created!',
            ];
            TransactionNotification::create($addNotificationRecord);
            // Insert data in Notification table Code END

        }else{
            return "Merhcant details not found!";
        }
        

       
    }

    public function payout_status(Request $request)
    {
       
        $data = $request->all();
        if ($data['payment_status'] == 'Successful' || $data['payment_status'] == 'success' || $data['payment_status'] == 'Success' || $data['payment_status'] == 'SUCCESS') {
            $paymentStatus = 'success';
        }elseif ($data['payment_status'] == 'pending' || $data['payment_status'] == 'Pending' || $data['payment_status'] == 'PENDING') {
            $paymentStatus = 'pending';
        }elseif ($data['payment_status'] == 'processing' || $data['payment_status'] == 'Processing' ) {
            $paymentStatus = 'processing';
        }else {
            $paymentStatus = 'failed';
        }
       
        SettleRequest::where('fourth_party_transection', $data['transaction_id'])->update([
            'settlement_trans_id' => $data['settlement_trans_id'],
            'status' => $paymentStatus,
            'api_response' => json_encode($data),
            'message' => $data['orderremarks']
        ]);
       
        $paymentDetail = SettleRequest::where('fourth_party_transection', $data['transaction_id'])->first();
        $callbackUrl = $paymentDetail->callback_url;
        $postData = [
            'merchant_code' => $paymentDetail->merchant_code,
            'transaction_id' => $paymentDetail->merchant_track_id,
            'amount' => $paymentDetail->total,
            'Currency' => $paymentDetail->Currency,
            'customer_name' => $paymentDetail->customer_name,
            'status' => $paymentDetail->status,
            'created_at' => $paymentDetail->created_at,
            'orderremarks' => $paymentDetail->message,
        ];

        // if ($paymentDetail->callback_url != null) {
        //     return Http::post($paymentDetail->callback_url, $postData);
        // }

        // print_r($paymentDetail->callback_url); 
        //  echo "<pre>"; print_r($paymentDetail); die;
        // Check if callback URL is not null
        if ($callbackUrl != null) {
            $response = Http::post($callbackUrl, $postData); 
            if ($response->failed()) {
                throw new Exception('Failed to send callback request: ' . $response->body());
            }
            // return $response->json(); 
        }
         // Broadcast the event Notification code START
         $data = [
            'type' => 'Withdrawl',
            'transaction_id' => $paymentDetail->merchant_track_id,
            'amount' => $paymentDetail->total,
            'Currency' => $paymentDetail->Currency,
            'status' => $paymentDetail->status,
            'msg' => 'One Withdrawl Transaction Updated!',
        ];
        event(new DepositCreated($data));
        // Broadcast the event Notification code START
          // Insert data in Notification table Code START
          $addNotificationRecord = [
            'notifiable_type' => 'Withdrawl',
            'agent_id' => $paymentDetail->agent_id,
            'merchant_id' => $paymentDetail->merchant_id,
            'data' => json_encode($data,true),
            'msg' => 'One Withdrawl Transaction Updated!',
        ];
        TransactionNotification::create($addNotificationRecord);
        // Insert data in Notification table Code END

        return view('payout.payout_status', compact('request', 'postData', 'callbackUrl'));
    }

    public function sendWithdrawNotification($id)
    {
        $paymentDetail = SettleRequest::where('id', base64_decode($id))->first();
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

         // Broadcast the event Notification code START
         $data = [
            'type' => 'Withdrawl',
            'transaction_id' => $paymentDetail->merchant_track_id,
            'amount' => $paymentDetail->total,
            'Currency' => $paymentDetail->Currency,
            'status' => $paymentDetail->status,
            'msg' => 'One Transaction notified!',
        ];
        event(new DepositCreated($data));
        // Broadcast the event Notification code START

        return view('payout.payoutNotification', compact('postData', 'callbackUrl'));
    }


}
