<?php

namespace App\Http\Controllers;

use App\Models\Merchant;
use App\Models\PaymentDetail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class ReportController extends Controller
{
    // start admin
    public function indexAdmin(Request $request)
    {
        $merchants = Merchant::where('status', 'Enable')->get();

        if ($request->ajax()) {
            $data = PaymentDetail::query()
                ->where('payment_status', 'success')
                ->when($request->merchant, fn ($q) => $q->where('merchant_code', $request->merchant))
                ->selectRaw('SUM(amount) as total_amount')
                ->selectRaw('COUNT(amount) as order_count')
                ->selectRaw("(DATE_FORMAT(created_at, '%Y-%m-%d')) as date")
                ->selectRaw('Currency')
                ->groupBy('date', 'Currency');

            return DataTables::of($data)
                ->editColumn('date', function ($data) use ($request) {
                    if ($request->merchant) {
                        return '<a href="'.route('admin-summary-report-by-date', ['date' => $data['date'], 'merchant_code' => $request->merchant]).'" class="text-blue text-decoration-underline">'.$data['date'].'</a>';
                    } else {
                        return '<a href="'.route('admin-summary-report-by-date', $data['date']).'" class="text-blue text-decoration-underline">'.$data['date'].'</a>';
                    }
                })
                ->editColumn('total_amount', function ($data) {
                    return number_format($data['total_amount'], 2);
                })
                ->filter(function ($data) use ($request) {
                    if ($request->daterange) {
                        $dateInput = explode('-', $request->daterange);

                        $date[0] = "$dateInput[0]/$dateInput[1]/$dateInput[2]";
                        if (count($dateInput) > 3) {
                            $date[1] = "$dateInput[3]/$dateInput[4]/$dateInput[5]";
                        }

                        $start_date = Carbon::parse($date[0]);
                        $end_date = Carbon::parse($date[1]);

                        $data->whereDate('created_at', '>=', $start_date)
                            ->whereDate('created_at', '<=', $end_date);
                    }

                    if ($request->merchant) {
                        $data->where('merchant_code', $request->merchant);
                    }
                })
                ->rawColumns(['date'])
                ->make(true);
        }

        return view('reports.admin.index', compact('merchants'));
    }

    public function indexAdminReportByDate(Request $request, $date, $merchant_code = null)
    {
        $date = $date;
        $merchant_code = $merchant_code;
        $merchant_name = Merchant::where('merchant_code', $merchant_code)->value('merchant_name');

        if ($request->ajax()) {
            $data = PaymentDetail::whereDate('created_at', $date)
                ->where('payment_status', 'success')
                ->when($merchant_code, fn ($q) => $q->where('merchant_code', $merchant_code))
                ->with('merchantData:merchant_code,merchant_name')
                ->select('amount', 'merchant_code', 'transaction_id', 'customer_name', 'fourth_party_transection', 'created_at', 'Currency')
                ->get();

            return DataTables::of($data)
                ->editColumn('amount', function ($data) {
                    return number_format($data->amount, 2);
                })
                // ->editColumn('cny_amount', function ($data) {
                //     return number_format($data->cny_amount, 2);
                // })
                ->editColumn('created_at', function ($data) {
                    return getAuthPreferenceTimezone($data->created_at);
                })
                ->addColumn('merchant_name', function ($data) {
                    return $data->merchantData?->merchant_name;
                })
                ->make(true);
        }

        return view('reports.admin.detail', compact('date', 'merchant_code', 'merchant_name'));
    }

    public function exportAdminReport($date, $merchant_code = null)
    {
        $data = PaymentDetail::whereDate('created_at', $date)
            ->where('payment_status', 'success')
            ->when($merchant_code, fn ($q) => $q->where('merchant_code', $merchant_code))
            ->with('merchantData:merchant_code,merchant_name')
            ->select(
                'amount',
                'merchant_code',
                'transaction_id',
                'customer_name',
                'fourth_party_transection',
                'created_at',
                'Currency'
            )
            ->orderBy('created_at', 'desc')
            ->get();

        $data_array[] = [
            trans('messages.Created Time'),
            trans('messages.Merchant Code'),
            trans('messages.Merchant Name'),
            trans('messages.Transaction ID'),
            trans('messages.Merchant Track No.'),
            trans('messages.Customer Name'),
            trans('messages.Amount'),
            trans('messages.Currency'),
        ];

        foreach ($data as $item) {
            $data_array[] = [
                trans('messages.Created Time') => getAuthPreferenceTimezone($item->created_at),
                trans('messages.Merchant Code') => $item->merchant_code,
                trans('messages.Merchant Name') => $item->merchantData?->merchant_name,
                trans('messages.Transaction ID') => $item->fourth_party_transection,
                trans('messages.Merchant Track No.') => $item->transaction_id,
                trans('messages.Customer Name') => $item->customer_name,
                trans('messages.Amount') => number_format($item->amount, 2),
                trans('messages.Currency') => $item->Currency,
            ];
        }

        exportExcel($data_array, $date, 'admin');
    }
    // end admin

    // start merchant
    public function indexMerchant(Request $request)
    {
        if ($request->ajax()) {
            $merchantCode = Merchant::where('id', auth()->user()->merchant_id)->value('merchant_code');

            $data = PaymentDetail::query()
                ->where('payment_status', 'success')
                ->where('merchant_code', $merchantCode)
                ->selectRaw('SUM(amount) as total_amount')
                ->selectRaw('COUNT(amount) as order_count')
                ->selectRaw("(DATE_FORMAT(created_at, '%Y-%m-%d')) as date")
                ->selectRaw('Currency')
                ->groupBy('date', 'Currency');

            return DataTables::of($data)
                ->editColumn('date', function ($data) {
                    return '<a href="'.route('merchant-summary-report-by-date', $data['date']).'" class="text-blue text-decoration-underline">'.$data['date'].'</a>';
                })
                ->editColumn('total_amount', function ($data) {
                    return number_format($data['total_amount'], 2);
                })
                ->filter(function ($data) use ($request) {
                    if ($request->daterange) {
                        $dateInput = explode('-', $request->daterange);

                        $date[0] = "$dateInput[0]/$dateInput[1]/$dateInput[2]";
                        if (count($dateInput) > 3) {
                            $date[1] = "$dateInput[3]/$dateInput[4]/$dateInput[5]";
                        }

                        $start_date = Carbon::parse($date[0]);
                        $end_date = Carbon::parse($date[1]);

                        $data->whereDate('created_at', '>=', $start_date)
                            ->whereDate('created_at', '<=', $end_date);
                    }
                })
                ->rawColumns(['date'])
                ->make(true);
        }
 
        return view('reports.merchant.index');
    }

    public function indexMerchantReportByDate(Request $request, $date)
    {
        $date = $date;

        if ($request->ajax()) {
            $merchantCode = Merchant::where('id', auth()->user()->merchant_id)->value('merchant_code');

            $data = PaymentDetail::whereDate('created_at', $date)
                ->where('payment_status', 'success')
                ->where('merchant_code', $merchantCode)
                ->select('amount', 'transaction_id', 'customer_name', 'fourth_party_transection', 'created_at', 'Currency')
                ->get();

            return DataTables::of($data)
                ->editColumn('amount', function ($data) {
                    return number_format($data->amount, 2);
                })
                // ->editColumn('cny_amount', function ($data) {
                //     return $data->cny_amount;
                // })
                ->editColumn('created_at', function ($data) {
                    return getAuthPreferenceTimezone($data->created_at);
                })
                ->make(true);
        }

        return view('reports.merchant.detail', compact('date'));
    }

    public function exportMerchantReport($date)
    {
        $merchantCode = Merchant::where('id', auth()->user()->merchant_id)->value('merchant_code');

        $data = PaymentDetail::whereDate('created_at', $date)
            ->where('payment_status', 'success')
            ->where('merchant_code', $merchantCode)
            ->select('amount', 'transaction_id', 'customer_name', 'fourth_party_transection', 'created_at', 'Currency')
            ->orderBy('created_at', 'desc')
            ->get();

        $data_array[] = [
            trans('messages.Created Time'),
            trans('messages.Transaction ID'),
            trans('messages.Merchant Track No.'),
            trans('messages.Customer Name'),
            trans('messages.Amount'),
            trans('messages.Currency'),
        ];

        foreach ($data as $item) {
            $data_array[] = [
                trans('messages.Created Time') => $item->created_at->format('Y-m-d H:i:s'),
                trans('messages.Transaction ID') => $item->fourth_party_transection,
                trans('messages.Merchant Track No.') => $item->transaction_id,
                trans('messages.Customer Name') => $item->customer_name,
                trans('messages.Amount') => number_format($item->amount, 2),
                trans('messages.Currency') => $item->Currency,
            ];
        }

        exportExcel($data_array, $date, 'merchant');
    }
    // end merchant
}
