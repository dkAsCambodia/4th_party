<?php

namespace App\Http\Controllers;

use App\Models\CurrencyExchangeRate;
use App\Models\MyMember;
use App\Models\PaymentDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class MyMemberController extends Controller
{
    public function __construct()
    {
    }

    public function store(Request $request)
    {

        $prodId = str_replace('P00', '', $request->input('product_id'));

        $data = $request->all();
        $data['status'] = 'Pending';
        $data['amount'] = CurrencyExchangeRate::where(['product_id' => $prodId])->first()->dollar;
        $data['merchant_id'] = $request->merchant_code;
        $data['call_back_url'] = $request->callback_url;
        $data['payment_method'] = 'alipay';
        $result = MyMember::create($data);

        return response()->json($result, 200);
    }

    public function payment_status(Request $request)
    {

        $data = $request->all();
        if ($data['payment_status'] == 'success' || $data['payment_status'] == 'Success' || $data['payment_status'] == 'SUCCESS') {
            $paymentStatus = 'success';
        }elseif ($data['payment_status'] == 'pending' || $data['payment_status'] == 'Pending' || $data['payment_status'] == 'PENDING') {
            $paymentStatus = 'pending';
        }else {
            $paymentStatus = 'failed';
        }
        PaymentDetail::where('fourth_party_transection', $data['transaction_id'])->update([
            'TransId' => $data['payment_transaction_id'],
            'payment_status' => $paymentStatus,
            'response_data' => $data,
        ]);

        $paymentDetail = PaymentDetail::where('fourth_party_transection', $data['transaction_id'])->first();

        $callbackUrl = $paymentDetail->callback_url;

        $postData = [
            'merchant_code' => $paymentDetail->merchant_code,
            'transaction_id' => $paymentDetail->transaction_id,
            'amount' => $paymentDetail->amount,
            'Currency' => $paymentDetail->Currency,
            'customer_name' => $paymentDetail->customer_name,
            'payment_status' => $paymentDetail->payment_status,
            'created_at' => $paymentDetail->created_at,
        ];

        // dd($paymentDetail, $paymentDetailUpdate);
        // if ($paymentDetail->callback_url != null) {
        //     return Http::post($paymentDetail->callback_url, $postData);
        // }

        return view('payment.payment_status', compact('request', 'postData', 'callbackUrl'));
    }
}
