<?php
namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

use App\Models\Agent;
use App\Models\Billing;
use App\Models\Merchant;
use App\Models\PaymentDetail;
use App\Models\PaymentAccount;
use App\Models\SettleRequest;
use App\Models\SettleRequestTrans;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Validator;

class AllCustomerController extends Controller
{
    // public function getCustomerViaAPI(Request $request)
    // {
    //     $response = Http::withHeaders([
    //         'Authorization' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJMaW5vZGUiLCJpYXQiOjE3MjkzNTU3MDYsImV4cCI6MTc2MDg5MTcwNSwiYXVkIjoid3d3LnhpeG9zYWZlLmNvbSIsInN1YiI6InhpeG9zYWZlIiwiR2l2ZW5OYW1lIjoiZ3RlY2giLCJSb2xlIjoic3lzdGVtIn0.iaDMqysqVZ_FT5j6Y3Hg-WJVvwHVRCGvxbQF892m4B0'
    //     ])->post('https://xixosafe.com/api/get_members');
        
    //     if ($response->successful()) {
    //             $data = $response->json();
    //             echo "<pre>"; print_r($data); die;
    //             return view('index', ['data' => $data]);
    //     } else {
    //             return response()->json(['error' => 'Failed to retrieve customer data'], 500);
    //     }
    // }

    public function getCustomerViaAPI(Request $request)
    {
        $merchant = Merchant::get();
        $agents = Agent::get();

        $merchantCount = SettleRequest::where('merchant_id', '!=', null)->count();

        $agentCount = SettleRequest::where('agent_id', '!=', null)->count();

        if ($request->ajax()) {
            // $data = Merchant::get();
            
            
            $response = Http::withHeaders([
                        'Authorization' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJMaW5vZGUiLCJpYXQiOjE3MjkzNTU3MDYsImV4cCI6MTc2MDg5MTcwNSwiYXVkIjoid3d3LnhpeG9zYWZlLmNvbSIsInN1YiI6InhpeG9zYWZlIiwiR2l2ZW5OYW1lIjoiZ3RlY2giLCJSb2xlIjoic3lzdGVtIn0.iaDMqysqVZ_FT5j6Y3Hg-WJVvwHVRCGvxbQF892m4B0'
                    ])->post('https://xixosafe.com/api/get_members');
                    
                    if ($response->successful()) {
                            $res = $response->object();
                            $data = $res->response;
                            
                           
                    } else {
                            return response()->json(['error' => 'Failed to retrieve customer data'], 500);
                    }
                    // echo "<pre>"; print_r($data); die;
            return DataTables::of($data)
                ->editColumn('create_date', function ($data) {
                    return getAuthPreferenceTimezone($data?->create_date);
                })
                ->addColumn('username', function ($data) {
                    return $data->username;
                })
                ->addColumn('bank_account_name', function ($data) {
                    return $data->bank_account_name;
                })
                ->addColumn('bank_account_no', function ($data) {
                    return $data->bank_account_no;
                })
                ->editColumn('bank_code', function ($data) {
                    return $data->bank_code;
                })
                ->addColumn('action', function ($data) use ($request) {
                    $action = '
                        <a class="btn btn-primary btn-sm" href="#" data-toggle="modal" data-target="#edit_user' . $data->id . '">' . trans("messages.View") . '</a>
                    ';

                    $action .= '
                        <div id="edit_user' . $data->id . '" class="modal custom-modal fade" role="dialog">
                            <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">' . trans("messages.Customer Details") . '</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>

                                    <div class="modal-body">
                                        <div class="table-responsive">
                                            <table class="table table-bordered test" style="padding: 7px 10px; !important">
                                            <tr>
                                                <td style="width: 25%; background-color: #6c6c70 !important; color: white;">' . trans("messages.ID") . '</td>
                                                <td>' . $data->id . '</td>
                                                <td style="width: 25%; background-color: #6c6c70 !important; color: white;">' . trans("messages.Bank Account Name") . '</td>
                                                <td>' . $data->bank_account_name . '</td>
                                            </tr>';
                  
                        $action .= '
                                                <tr>
                                                    <td style="width: 25%; background-color: #6c6c70 !important; color: white;">' . trans("messages.Username") . '</td>
                                                    <td>' . $data->username . '</td>
                                                    <td style="width: 25%; background-color: #6c6c70 !important; color: white;">' . trans("messages.Bank Account No.") . '</td>
                                                    <td>' . $data->bank_account_no . '</td>
                                                </tr>
                                                    ';
                    $action .= '<tr>
                                                    <td style="width: 25%; background-color: #6c6c70 !important; color: white;">' . trans("messages.Created Time") . '</td>
                                                    <td>' . $data->create_date . '</td>
                                                    <td style="width: 25%; background-color: #6c6c70 !important; color: white;">' . trans("messages.Bank Code") . '</td>
                                                    <td>' . $data->bank_code . '</td>
                                                    
                                                </tr>
                                                
                                            </table>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    ';

                    return $action;
                })
                ->filter(function ($data) use ($request) {
                    // if ($request->status) {
                    //     $data->where('status', $request->status);
                    // }

                    // if ($request->merchant_code) {
                    //     $data->where('merchant_id', $request->merchant_code);
                    // }

                    // if ($request->agent_code) {
                    //     $data->where('agent_id', $request->agent_code);
                    // }

                    // if ($request->daterange) {
                    //     $dateInput  = explode('-', $request->daterange);

                    //     $date[0]  = "$dateInput[0]/$dateInput[1]/$dateInput[2]";
                    //     if (count($dateInput) > 3) {
                    //         $date[1]  = "$dateInput[3]/$dateInput[4]/$dateInput[5]";
                    //     }

                    //     $start_date = Carbon::parse($date[0]);
                    //     $end_date   = Carbon::parse($date[1]);

                    //     $data->whereDate('create_date', '>=', $start_date)
                    //         ->whereDate('create_date', '<=', $end_date);
                    // }

                    // if (!empty($request->search)) {
                    //     $data->where(function ($q) use ($request) {
                    //         $q->orWhere('settlement_trans_id', 'LIKE', '%' . $request->search . '%')
                    //         ->orWhere('sub_total', 'LIKE', '%' . $request->search . '%')
                    //         ->orWhere('total', 'LIKE', '%' . $request->search . '%')
                    //         ->orWhere('status', 'LIKE', '%' . $request->search . '%')
                    //         ->orWhere('create_date', 'LIKE', '%' . $request->search . '%');
                    //     });
                    // }
                })
                ->rawColumns(['action', 'status'])
                ->with('checkValue', $request->checkValue)
                ->make(true);
        }

        return view('customer.all-customer', compact('merchant', 'agents', 'merchantCount', 'agentCount'));
    }


}
