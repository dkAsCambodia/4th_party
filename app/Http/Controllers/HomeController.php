<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\Merchant;
use App\Models\SettleRequest;
use App\Models\Billing;
use App\Models\PaymentDetail;
use App\Models\User;
use App\Notifications\PaymentDetailNotification;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $amount = PaymentDetail::where('payment_status',  'success')
                ->whereBetween('created_at', [Carbon::now()->subDays($i)->format('Y-m-d 0:0:0'), Carbon::now()->subDays($i)->format('Y-m-d 23:59:59')])
                ->when(auth()->user()->role_name == 'Merchant', function ($q) {
                    $merchantCode = Merchant::where('id', auth()->user()->merchant_id)->value('merchant_code');
                    $q->where('merchant_code', $merchantCode);
                })
                ->when(auth()->user()->role_name == 'Agent', function ($q) {
                    $merchants = Merchant::where('agent_id', auth()->user()->agent_id)->get();
                    $merchantCode = [];
                    foreach ($merchants as $mer) {
                        array_push($merchantCode, $mer->merchant_code);
                    }
                    $q->whereIn('merchant_code', $merchantCode);
                })
                ->sum('amount');

            $data[$date] = (int)$amount;
        }

        $total_transactions_today = PaymentDetail::whereDate('created_at', today())
            ->when(auth()->user()->role_name == 'Merchant', function ($q) {
                $merchantCode = Merchant::where('id', auth()->user()->merchant_id)->value('merchant_code');
                $q->where('merchant_code', $merchantCode);
            })
            ->when(auth()->user()->role_name == 'Agent', function ($q) {
                $merchants = Merchant::where('agent_id', auth()->user()->agent_id)->get();
                $merchantCode = [];
                foreach ($merchants as $mer) {
                    array_push($merchantCode, $mer->merchant_code);
                }
                $q->whereIn('merchant_code', $merchantCode);
            })
            ->count();
        $total_transactions_amount_today = PaymentDetail::whereDate('created_at', today())
            ->when(auth()->user()->role_name == 'Merchant', function ($q) {
                $merchantCode = Merchant::where('id', auth()->user()->merchant_id)->value('merchant_code');
                $q->where('merchant_code', $merchantCode);
            })
            ->when(auth()->user()->role_name == 'Agent', function ($q) {
                $merchants = Merchant::where('agent_id', auth()->user()->agent_id)->get();
                $merchantCode = [];
                foreach ($merchants as $mer) {
                    array_push($merchantCode, $mer->merchant_code);
                }
                $q->whereIn('merchant_code', $merchantCode);
            })
            ->where('payment_status',  'success')
            ->sum('amount');
        $total_transactions_month = PaymentDetail::whereMonth('created_at', now()->month)
            ->when(auth()->user()->role_name == 'Merchant', function ($q) {
                $merchantCode = Merchant::where('id', auth()->user()->merchant_id)->value('merchant_code');
                $q->where('merchant_code', $merchantCode);
            })
            ->when(auth()->user()->role_name == 'Agent', function ($q) {
                $merchants = Merchant::where('agent_id', auth()->user()->agent_id)->get();
                $merchantCode = [];
                foreach ($merchants as $mer) {
                    array_push($merchantCode, $mer->merchant_code);
                }
                $q->whereIn('merchant_code', $merchantCode);
            })
            ->count();
        $total_transactions_amount_month = PaymentDetail::whereMonth('created_at', now()->month)
            ->when(auth()->user()->role_name == 'Merchant', function ($q) {
                $merchantCode = Merchant::where('id', auth()->user()->merchant_id)->value('merchant_code');
                $q->where('merchant_code', $merchantCode);
            })
            ->when(auth()->user()->role_name == 'Agent', function ($q) {
                $merchants = Merchant::where('agent_id', auth()->user()->agent_id)->get();
                $merchantCode = [];
                foreach ($merchants as $mer) {
                    array_push($merchantCode, $mer->merchant_code);
                }
                $q->whereIn('merchant_code', $merchantCode);
            })
            ->where('payment_status',  'success')
            ->sum('amount');

        $total_transactions_amount = PaymentDetail::where('payment_status',  'success')->sum('amount');
        $total_agent = Agent::count();
        $total_merchant =  Merchant::count();
        $total_transactions_count = PaymentDetail::count();

        $billing = Billing::when(auth()->user()->role_name == 'Merchant', function ($q) {
            $q->where('merchant_id', auth()->user()->merchant_id);
        })
            ->when(auth()->user()->role_name == 'Agent', function ($q) {
                $q->where('agent_id', auth()->user()->agent_id);
            })
            ->first();

        $details = null;

        if (auth()->user()->role_name == 'Merchant') {
            $details = Merchant::where('id', auth()->user()->merchant_id)
                ->with('agent:id,agent_name,agent_code')
                ->first();
        } else {
            $details = Agent::where('id', Auth()->user()->agent_id)->first(); 
        }

        //For Merchant and Agent by DK
           $totalDepositSum = PaymentDetail::where('payment_status',  'success')
            ->when(auth()->user()->role_name == 'Merchant', function ($q) {
                $merchantCode = Merchant::where('id', auth()->user()->merchant_id)->value('merchant_code');
                $q->where('merchant_code', $merchantCode);
            })
            ->when(auth()->user()->role_name == 'Agent', function ($q) {
                $merchants = Merchant::where('agent_id', auth()->user()->agent_id)->get();
                $merchantCode = [];
                foreach ($merchants as $mer) {
                    array_push($merchantCode, $mer->merchant_code);
                }
                $q->whereIn('merchant_code', $merchantCode);
            })
            ->sum('amount');

            $totalDepositCount = PaymentDetail::where('payment_status',  'success')
            ->when(auth()->user()->role_name == 'Merchant', function ($q) {
                $merchantCode = Merchant::where('id', auth()->user()->merchant_id)->value('merchant_code');
                $q->where('merchant_code', $merchantCode);
            })
            ->when(auth()->user()->role_name == 'Agent', function ($q) {
                $merchants = Merchant::where('agent_id', auth()->user()->agent_id)->get();
                $merchantCode = [];
                foreach ($merchants as $mer) {
                    array_push($merchantCode, $mer->merchant_code);
                }
                $q->whereIn('merchant_code', $merchantCode);
            })
            ->count('amount');

            $total_payout = SettleRequest::where('status',  'success')
            ->when(auth()->user()->role_name == 'Merchant', function ($q) {
                $merchantCode = Merchant::where('id', auth()->user()->merchant_id)->value('merchant_code');
                $q->where('merchant_code', $merchantCode);
            })
            ->when(auth()->user()->role_name == 'Agent', function ($q) {
                $merchants = Merchant::where('agent_id', auth()->user()->agent_id)->get();
                $merchantCode = [];
                foreach ($merchants as $mer) {
                    array_push($merchantCode, $mer->merchant_code);
                }
                $q->whereIn('merchant_code', $merchantCode);
            })
            ->sum('total');

            $total_payout_count = SettleRequest::where('status',  'success')
            ->when(auth()->user()->role_name == 'Merchant', function ($q) {
                $merchantCode = Merchant::where('id', auth()->user()->merchant_id)->value('merchant_code');
                $q->where('merchant_code', $merchantCode);
            })
            ->when(auth()->user()->role_name == 'Agent', function ($q) {
                $merchants = Merchant::where('agent_id', auth()->user()->agent_id)->get();
                $merchantCode = [];
                foreach ($merchants as $mer) {
                    array_push($merchantCode, $mer->merchant_code);
                }
                $q->whereIn('merchant_code', $merchantCode);
            })
            ->count('total');

            // $transaction_charge =$totalDepositCount*20;
            // $AvailableforPayout=$totalDepositSum-$transaction_charge;
            // $finalAmount=$AvailableforPayout-$total_payout;
            $payoutController = new PayoutController();
            $transaction_charge =$payoutController->depositchangeFun($totalDepositSum);
            $AvailableforPayout=$totalDepositSum-$transaction_charge;

            $payout_charge = $total_payout_count*10;
            $totalPayoutwithCharge = $payout_charge + $total_payout;
            @$finalAmount=$AvailableforPayout-$totalPayoutwithCharge;

           


        return view('dashboard.home', compact('data', 'finalAmount', 'totalDepositCount', 'totalDepositSum', 'total_payout', 'total_payout_count', 'total_transactions_today', 'total_transactions_amount_today', 'total_transactions_month', 'total_transactions_amount_month', 'total_agent', 'total_merchant', 'total_transactions_amount', 'total_transactions_count', 'billing', 'details'));
    }

    public function dataByDate()
    {
        for ($i = (request()->date - 1); $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $amount = PaymentDetail::where('payment_status',  'success')
                ->whereBetween('created_at', [Carbon::now()->subDays($i)->format('Y-m-d 0:0:0'), Carbon::now()->subDays($i)->format('Y-m-d 23:59:59')])
                ->when(auth()->user()->role_name == 'Merchant', function ($q) {
                    $merchantCode = Merchant::where('id', auth()->user()->merchant_id)->value('merchant_code');
                    $q->where('merchant_code', $merchantCode);
                })
                ->when(auth()->user()->role_name == 'Agent', function ($q) {
                    $merchants = Merchant::where('agent_id', auth()->user()->agent_id)->get();
                    $merchantCode = [];
                    foreach ($merchants as $mer) {
                        array_push($merchantCode, $mer->merchant_code);
                    }
                    $q->whereIn('merchant_code', $merchantCode);
                })
                ->sum('amount');

            $data[$date] = (int)$amount;
        }

        return $data;
    }

    public function unreadNoti()
    {
        return response()->json(auth()->user()->unreadNotifications);
    }

    public function markAsRead()
    {
        auth()->user()->unreadNotifications->markAsRead();
        return back();
    }
}
