<?php

namespace App\Http\Controllers;

use App\Models\Merchant;
use App\Models\PaymentDetail;
use App\Models\Timezone;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PaymentDetailReportController extends Controller
{
    public function adminExportPaymentDetails(Request $request)
    {
        $data = PaymentDetail::when($request->status, fn ($q) => $q->where('payment_status', $request->status))
            ->when($request->daterange, function ($q) use ($request) {
                $date = explode(' - ', $request->daterange);
                $start_date = Carbon::parse($date[0]);
                $end_date = Carbon::parse($date[1]);

                $q->whereDate('created_at', '>=', $start_date)
                    ->whereDate('created_at', '<=', $end_date);
            })
            ->select(
                'created_at',
                'fourth_party_transection',
                'transaction_id',
                'customer_name',
                'amount',
                'mdr_fee_amount',
                'net_amount',
                'Currency',
                'payment_status',
            )
            ->orderBy('created_at', 'desc')
            ->get();

        $data_array[] = [
            __('messages.Created Time'),
            __('messages.Transaction ID'),
            __('messages.Reference ID'),
            __('messages.Customer Name'),
            __('messages.Amount'),
            __('messages.MDR'),
            __('messages.Net'),
            __('messages.Currency'),
            __('messages.Status'),
        ];

        $timezone = Timezone::where('id', $request->timezone)->value('timezone');

        foreach ($data as $item) {
            $data_array[] = [
                __('messages.Created Time') => $item->created_at->timezone($timezone)->format('Y-m-d H:i:s'),
                __('messages.Transaction ID') => $item->fourth_party_transection,
                __('messages.Reference ID') => $item->transaction_id,
                __('messages.Customer Name') => $item->customer_name,
                __('messages.Amount') => number_format($item->amount, 2),
                __('messages.MDR') => number_format($item->mdr_fee_amount, 2),
                __('messages.Net') => number_format($item->net_amount, 2),
                __('messages.Currency') => $item->Currency,
                __('messages.Status') => __('messages.'.$item->payment_status),
            ];
        }

        exportExcel($data_array, $request->daterange, 'payment-detail');
    }

    // merchant
    public function merchantExportPaymentDetails(Request $request)
    {
        $merchantCode = Merchant::where('id', auth()->user()->merchant_id)->value('merchant_code');

        $data = PaymentDetail::where('merchant_code', $merchantCode)
            ->when($request->status, fn ($q) => $q->where('payment_status', $request->status))
            ->when($request->daterange, function ($q) use ($request) {
                $date = explode(' - ', $request->daterange);
                $start_date = Carbon::parse($date[0]);
                $end_date = Carbon::parse($date[1]);

                $q->whereDate('created_at', '>=', $start_date)
                    ->whereDate('created_at', '<=', $end_date);
            })
            ->select(
                'created_at',
                'fourth_party_transection',
                'transaction_id',
                'customer_name',
                'amount',
                'mdr_fee_amount',
                'net_amount',
                'Currency',
                'payment_status',
            )
            ->orderBy('created_at', 'desc')
            ->get();

            $data_array[] = [
                __('messages.Created Time'),
                __('messages.Transaction ID'),
                __('messages.Invoice Number'),
                __('messages.Customer Name'),
                __('messages.Amount'),
                __('messages.MDR'),
                __('messages.Net'),
                __('messages.Currency'),
                __('messages.Status'),
            ];
    
            $timezone = Timezone::where('id', $request->timezone)->value('timezone');
    
            foreach ($data as $item) {
                $data_array[] = [
                    __('messages.Created Time') => $item->created_at->timezone($timezone)->format('Y-m-d H:i:s'),
                    __('messages.Transaction ID') => $item->fourth_party_transection,
                    __('messages.Invoice Number') => $item->transaction_id,
                    __('messages.Customer Name') => $item->customer_name,
                    __('messages.Amount') => number_format($item->amount, 2),
                    __('messages.MDR') => number_format($item->mdr_fee_amount, 2),
                    __('messages.Net') => number_format($item->net_amount, 2),
                    __('messages.Currency') => $item->Currency,
                    __('messages.Status') => __('messages.'.$item->payment_status),
                ];
            }

        exportExcel($data_array, $request->daterange, 'merchant-payment-detail');
    }

    // agent
    public function agentExportPaymentDetails(Request $request)
    {
        $merchant = Merchant::where('agent_id', auth()->user()->agent_id)->get();
        $merchantCode = [];
        foreach ($merchant as $merchantVal) {
            array_push($merchantCode, $merchantVal->merchant_code);
        }

        $data = PaymentDetail::whereIn('merchant_code', $merchantCode)
            ->when($request->status, fn ($q) => $q->where('payment_status', $request->status))
            ->when($request->daterange, function ($q) use ($request) {
                $date = explode(' - ', $request->daterange);
                $start_date = Carbon::parse($date[0]);
                $end_date = Carbon::parse($date[1]);

                $q->whereDate('created_at', '>=', $start_date)
                    ->whereDate('created_at', '<=', $end_date);
            })
            ->select(
               'created_at',
                'fourth_party_transection',
                'transaction_id',
                'customer_name',
                'amount',
                'mdr_fee_amount',
                'net_amount',
                'Currency',
                'payment_status',
            )
            ->orderBy('created_at', 'desc')
            ->get();

        $data_array[] = [
            __('messages.Created Time'),
            __('messages.Transaction ID'),
            __('messages.Reference ID'),
            __('messages.Customer Name'),
            __('messages.Amount'),
            __('messages.MDR'),
            __('messages.Net'),
            __('messages.Currency'),
            __('messages.Status'),
        ];

        $timezone = Timezone::where('id', $request->timezone)->value('timezone');

        foreach ($data as $item) {
            $data_array[] = [
                __('messages.Created Time') => $item->created_at->timezone($timezone)->format('Y-m-d H:i:s'),
                __('messages.Transaction ID') => $item->fourth_party_transection,
                __('messages.Reference ID') => $item->transaction_id,
                __('messages.Customer Name') => $item->customer_name,
                __('messages.Amount') => number_format($item->amount, 2),
                __('messages.MDR') => number_format($item->mdr_fee_amount, 2),
                __('messages.Net') => number_format($item->net_amount, 2),
                __('messages.Currency') => $item->Currency,
                __('messages.Status') => __('messages.'.$item->payment_status),
            ];
        }

        exportExcel($data_array, $request->daterange, 'agent-payment-detail');
    }
}
