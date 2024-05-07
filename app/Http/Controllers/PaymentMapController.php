<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaymentUrlRequest;
use App\Models\PaymentMap;
use Illuminate\Http\Request;
use App\Models\Merchant;
use App\Models\PaymentMethod;
use App\Models\PaymentUrl;
use App\Services\PaymentService;
use DB;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Validator;

class PaymentMapController extends Controller
{


    public function __construct(protected PaymentService $paymentMapService)
    {

    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Merchant $merchant, $urlId)
    {
        $paymentMethod = PaymentMethod::where('status', 'Enable')->get();

        $map_table = PaymentMap::where('payment_url_id', $urlId)->where('merchant_id', $merchant->id)->simplePaginate(10);

        return view('form.payment.addPaymentMap', compact('paymentMethod', 'map_table'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $messages = [
            'required' => __('validation.The :attribute field is required.'),
            'regex' => __('validation.The :attribute must be numbers.'),
            'gt' => __('validation.The :attribute must be greater than agent rate.'),
        ];

        $validator = Validator::make($request->all(), [
            // 'channel_multi' => 'required',
            'max_value' => 'required|regex:/^(([0-9]*)(\.([0-9]+))?)$/',
            'min_value' => 'required|regex:/^(([0-9]*)(\.([0-9]+))?)$/',
            'agent_rate' => 'required|regex:/^(([0-9]*)(\.([0-9]+))?)$/|',
            'merchant_rate' => 'required|gt:agent_rate|regex:/^(([0-9]*)(\.([0-9]+))?)$/',
            'status' => 'required',
            'cny_min' => 'required|regex:/^(([0-9]*)(\.([0-9]+))?)$/',
            'cny_max' => 'required|regex:/^(([0-9]*)(\.([0-9]+))?)$/',
        ], $messages);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()]);
        }
        $channelData = '';
        if ($request->channel_mode == 'single') {
            $channelData = implode(',', $request->channel_single);
        }
        if ($request->channel_mode == 'rotate') {
            $validator = Validator::make($request->all(), [
                'channel_multi' => 'required',
            ], $messages);
            if ($request->channel_multi) {
                $channelData = implode(',', $request->channel_multi);
            } else {
                return response()->json(["error" => [ "channel_multi" => ["The channel field is required."]]]);
            }
        }

        try {
            $map = PaymentMap::create([
                'payment_method_id' => $request->payment_method,
                'gateway_payment_channel_id' => $channelData,
                'max_value' => $request->max_value,
                'min_value' => $request->min_value,
                'merchant_id' => $request->merchant_id,
                'agent_commission' => $request->agent_rate,
                'merchant_commission' => $request->merchant_rate,
                'cny_min' => $request->cny_min,
                'cny_max' => $request->cny_max,
                'channel_mode' => $request->channel_mode,
                'status' => $request->status,
            ]);

            $map->update([
                'product_id' => 'P00' .  $map->id,
            ]);

            $messages2 = __('messages.Added Successfully');
            $success = __('messages.Success');
            Toastr::success($messages2, $success);
            return redirect()->back();
        } catch (\Exception $e) {
            $error_msg = __('messages.fail, Add Payment Map');
            Toastr::error($error_msg, 'Error');
            return redirect()->back();
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\PaymentMap  $paymentMap
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $messages = [
            'required' => __('validation.The :attribute field is required.'),
            'regex' => __('validation.The :attribute must be numbers.'),
            'gt' => __('validation.The :attribute must be greater than agent rate.'),
        ];

        $validator = Validator::make($request->all(), [
            'max_value' => 'required|regex:/^(([0-9]*)(\.([0-9]+))?)$/',
            'min_value' => 'required|regex:/^(([0-9]*)(\.([0-9]+))?)$/',
            'agent_rate' => 'required|regex:/^(([0-9]*)(\.([0-9]+))?)$/|',
            'merchant_rate' => 'required|gt:agent_rate|regex:/^(([0-9]*)(\.([0-9]+))?)$/',
            'status' => 'required',
            'cny_min' => 'required|regex:/^(([0-9]*)(\.([0-9]+))?)$/',
            'cny_max' => 'required|regex:/^(([0-9]*)(\.([0-9]+))?)$/',
        ], $messages);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()]);
        }

        $channelData = '';
        if ($request->channel_mode == 'single') {
            $channelData = implode(',', $request->channel_single);
        }
        if ($request->channel_mode == 'rotate') {
            $validator = Validator::make($request->all(), [
                            'channel_multi' => 'required',
                        ], $messages);
            if ($request->channel_multi) {
                $channelData = implode(',', $request->channel_multi);
            } else {
                return response()->json(["error" => [ "channel_multi" => ["The channel field is required."]]]);
            }
        }

        $paymentMap = PaymentMap::where('id', $request->id)->first();

        try {
            $paymentMap->update([
                'payment_method_id' => $request->payment_method,
                'gateway_payment_channel_id' => $channelData,
                'max_value' => $request->max_value,
                'min_value' => $request->min_value,
                'merchant_id' => $request->merchant_id,
                'agent_commission' => $request->agent_rate,
                'merchant_commission' => $request->merchant_rate,
                'cny_min' => $request->cny_min,
                'cny_max' => $request->cny_max,
                'channel_mode' => $request->channel_mode,
                'status' => $request->status,
            ]);
            $messages_success = __('messages.Updated Successfully');
            $success = __('messages.Success');
            Toastr::success($messages_success, $success);
            return redirect()->back();
        } catch (\Exception $e) {
            $messages_error = __('messages.fail, Update Payment Map');
            Toastr::error($messages_error, 'Error');
            return redirect()->back();
        }
    }

    public function deleteRecord(Request $request)
    {
        try {
            PaymentMap::destroy($request->id);
            $del_success = __('messages.Deleted Successfully');
            $success = __('messages.Success');
            Toastr::success($del_success, $success);
        } catch (\Exception $e) {
            DB::rollback();
            $del_error = __('messages.Product delete fail');
            Toastr::error($del_error, 'Error');
        }
    }

    public function copyPaymentLink()
    {
        $paymentMap = PaymentMap::whereId(request()->id)->first();
        $merchant = Merchant::whereId($paymentMap->merchant_id)->first();
        $amount = rand($paymentMap->min_value,$paymentMap->max_value);
        echo url('/') . '/api/payment?merchant_code=' . $merchant->merchant_code . '&customer_name=guest&customer_id=0&product_id=' . $paymentMap->id . '&transaction_id=T' . rand(100000, 999999) . '&callback_url=' . url('/') . '&amount='.$amount.'&redirect_url='.url('/') .'/payment_status';
    }

    public function getPaymentPrices(Request $request)
    {
       return  $this->paymentMapService->getPaymentPrices($request);

    }
    public function getPaymentPricesNew(PaymentUrlRequest $request)
    {
       return  $this->paymentMapService->getPaymentPricesNew($request);

    }
}
