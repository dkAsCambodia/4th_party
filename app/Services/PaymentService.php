<?php

namespace App\Services;

use App\Models\Merchant;
use App\Models\PaymentDetail;
use App\Models\PaymentMap;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;

class PaymentService
{

    public function getPaymentPrices($params): JsonResponse
    {

        try {
            //$paymentMap = PaymentMap::whereIn('id', [1, 12])->where('status', 'Enable')->get();
            $paymentMap = PaymentMap::whereIn('id', [ 2 ,3,18])->where('status', 'Enable')->get();

            // $amount = [5, 11];
            // $cny_amount = [5, 11];
            $amount = [ 10,1,109];
            $cny_amount = [ 10,1,109];
            if (!empty($paymentMap)) {
                $i = 0;
                $totalAmount = 0;
               // $merchant = Merchant::whereId('4')->first();
                $merchant = Merchant::whereId('1')->first();
                $merchantCode = !empty($merchant->merchant_code) ? $merchant->merchant_code : '';
                $merchantName = !empty($merchant->merchant_name) ? $merchant->merchant_name : '';
                $totalAmount = PaymentDetail::where(['payment_status' => 'success', 'merchant_code' => $merchantCode])->sum('amount');
                $totalCnyAmount = PaymentDetail::where(['payment_status' => 'success', 'merchant_code' => $merchantCode])->sum('cny_amount');
                $data['merchant']['merchant_name'] = $merchantName;
                $data['merchant']['merchant_code'] = $merchantCode;
                $data['merchant']['total_amount'] = !empty($totalAmount) ? number_format($totalAmount, 2) : '0.00';
                $data['merchant']['total_cny_amount'] = !empty($totalCnyAmount) ? number_format($totalCnyAmount, 2) : '0.00';
                foreach ($paymentMap as $payment) {

                    $results['url'] =  url('/') . '/api/payment?merchant_code=' . $merchantCode . '&customer_name=guest&customer_id=0&product_id=' . $payment->id . '&transaction_id=T' . rand(100000, 999999) . '&callback_url=' . url('/') . '&amount=' . $amount[$i] . '&cny_amount=' . $cny_amount[$i] . '&redirect_url=' . url('/') . '/payment_status';
                    $results['amount'] = $amount[$i];
                    $results['cny_amount_range'] = number_format($payment->cny_min, 2) . ' - ' . number_format($payment->cny_max, 2);
                    $i++;
                    $data['prices'][] = $results;
                }
            }
            $response = [
                'status' => true,
                'message' => 'Payment Price list fetched successfully',
                'data' => $data
            ];

            return response()->json($response, 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 200);
        }
    }
    public function getPaymentPricesNew($params)
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


        $productId = preg_replace('/[^0-9]/', '', $params['product_id']);
        $merchantCode = $params['merchant_code'];
        $customerId = $params['customer_id'];
        $customerName = $params['customer_name'] ?? '';
        $transaction_id = $params['transaction_id'] ?? 'T'.rand(100000, 999999);
        $callback_url = $params['callback_url'] ?? '';
        $ipAddress = $ip;



        try {

            $merchant = Merchant::with('userData')->where('merchant_code',$merchantCode)->first();
            $merchantCode = !empty($merchant->merchant_code) ? $merchant->merchant_code : '';
            if($merchantCode == ''){
                $response = [
                    'status' => false,
                    'message' => 'Merchant code not found'
                ];
                return response()->json($response, 401);
            }
            if($ip != $merchant->userData->url){
                $response = [
                    'status' => false,
                    'message' => 'Domain mismatched'
                ];
                return response()->json($response, 401);
            }

            // $where = [
            //     'transaction_id' => $transaction_id

            // ];
            // $checkPayment = PaymentDetail::where($where)->first();
            // if (!empty($checkPayment)) {
            //     $response = [
            //         'status' => false,
            //         'message' => 'Please pass unique transection id'
            //     ];
            //     return response()->json($response, 401);
            // }

            $where = [
                'ip_address' => $ipAddress
                //'customer_id' => $customerId
            ];
            $checkPayment = PaymentDetail::whereDate('created_at', Carbon::now()->format('Y-m-d'))->where($where)->first();
            if (!empty($checkPayment)) {
                $response = [
                    'status' => false,
                    'message' => 'Payment already exist for this IP today'
                ];
                return response()->json($response, 401);
            }

            $where = ['customer_id' => $customerId,'merchant_code' => $merchantCode,'product_id'=>$productId];
            $checkPayment = PaymentDetail::whereDate('created_at', Carbon::now()->format('Y-m-d'))->where($where)->first();
            if (!empty($checkPayment)) {
                $response = [
                    'status' => false,
                    'message' => 'Payment already exist for this customer today'
                ];
                return response()->json($response, 401);
            }



            $paymentMap = PaymentMap::where('id', $productId)->where('status', 'Enable')->first();

            $amount = [5, 11];
            $cny_amount = [5, 11];
            if (!empty($paymentMap)) {
                $i = 0;

                 $amountDollar = $paymentMap->min_value;
                 $amountCny = $paymentMap->cny_min;

                    $data['url'] =  url('/') . '/api/payment?merchant_code=' . $merchantCode . '&customer_name=' . $customerName . '&customer_id=' . $customerId . '&product_id=' . $paymentMap->id . '&transaction_id=' . $transaction_id . '&callback_url=' . $callback_url . '&amount=' . $amountDollar . '&cny_amount=' . $amountCny . '&redirect_url=' . url('/') . '/payment_status';

            }


            $response = [
                'status' => true,
                'message' => 'Payment url fetched successfully',
                'data' => $data
            ];

            return response()->json($response, 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 200);
        }
    }
}
