<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateMerchantUserFormRequest;
use App\Models\User;
use App\Models\PaymentChannel;
use App\Models\PaymentMethod;
//use App\Models\PaymentSource;
use App\Models\PaymentAccount;

use App\Models\Merchant;
use App\Models\Agent;
use App\Models\Bank;

use App\Models\Billing;
use App\Models\GatewayPaymentChannel;
use App\Models\PaymentUrl;
use App\Models\PaymentMap;
use App\Models\Timezone;
use Illuminate\Http\Request;
use DB;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\DataTables;

class MerchantController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $agents = Agent::where('status', 'Enable')->orderBy('agent_name', 'ASC')->get();

        $timezones = Timezone::where('status', 'active')->get();

        if ($request->ajax()) {
            $data = Merchant::query()->where('is_show', 'yes')
                ->with('agent:id,agent_name', 'userData:id,user_name,mobile_number,email,merchant_id,timezone_id,url')
                ->select('id', 'merchant_name', 'merchant_code', 'agent_id', 'created_at', 'status');

            return DataTables::of($data)
                ->addColumn('agent_name', function ($data) {
                    return $data->agent?->agent_name;
                })
                ->editColumn('created_at', function ($data) {
                    return getAuthPreferenceTimezone($data->created_at);
                })
                ->editColumn('status', function ($data) {
                    return $data->status == 'Enable'
                        ? '<span class="badge light badge-success">' . trans('messages.Enable') . '</span>'
                        : '<span class="badge light badge-danger">' . trans('messages.Disable') . '</span>';
                })
                ->addColumn('action', function ($data) {
                    $action = '<div class="d-flex">';

                    if (auth()->user()->can('Merchant: View Billing Merchant')) {
                        $action .= '
							<a title="' . trans("messages.Settlement settings") . '" class="btn btn-danger shadow btn-xs sharp me-1 add_record" href="' . route("Merchant: View Billing Merchant", $data->id) . '">
								<i class="fa fa-plus"></i>
							</a>
						';
                    }

                    if (auth()->user()->can('Merchant: Update Merchant')) {
                        $action .= '
					        <a title="' . trans("messages.Edit Merchant") . '" class="btn btn-primary shadow btn-xs sharp me-1 edit_merchant" href="#" data-toggle="modal" data-target="#edit_user" data-id="' . $data->id . '" data-merchant_name="' . $data->merchant_name . '" data-merchant_code="' . $data->merchant_code . '" data-agent="' . $data->agent_id . '" data-username="' . $data->userData?->user_name . '" data-email="' . $data->userData?->email . '" data-mobile_number="' . $data->userData?->mobile_number . '" data-status="' . $data->status . '" data-timezone="'. $data->userData?->timezone_id .'" data-url="'. $data->userData?->url .'">
								<i class="fas fa-pencil-alt"></i>
							</a>
					    ';
                    }

                    if (auth()->user()->can('Merchant: Delete Merchant')) {
                        $action .= '
					        <a title="' . trans('messages.Delete Merchant') . '" class="btn btn-danger shadow btn-xs sharp delete_user"href="#" data-toggle="modal" data-target="#delete_user" data-id="' . $data->id . '">
								<i class="fa fa-trash"></i>
							</a>
					    ';
                    }

                    $action .= '</div>';

                    return $action;
                })
                ->addColumn('payment_map', function ($data) {
                    if (auth()->user()->can('Merchant: View PaymentMap Merchant')) {
                        return '<a href="' . route('Merchant: View PaymentMap Merchant', $data->id) . '" class="btn btn-success btn-sm add_record">' . trans('messages.Configure Payment') . '</a>';
                    }
                })
                ->filter(function ($data) use ($request) {
                    if ($request->agent) {
                        $data->where('agent_id', $request->agent);
                    }

                    if ($request->status) {
                        $data->where('status', $request->status);
                    }

                    if (!empty($request->search)) {
                        $data->where(function ($q) use ($request) {
                            $q->orWhere('merchant_code', 'LIKE', '%' . $request->search . '%')
                                ->orWhere('merchant_name', 'LIKE', '%' . $request->search . '%')
                                ->orWhere('status', 'LIKE', '%' . $request->search . '%')
                                ->orWhere('created_at', 'LIKE', '%' . $request->search . '%');
                        });
                    }
                })
                ->rawColumns(['action', 'status', 'payment_map'])
                ->make(true);
        }

        return view('form.merchant.merchantTable', compact('agents', 'timezones'));
    }

    public function store(Request $request)
    {
        $messages = [
            'unique'    => __('validation.The :attribute must be unique.'),
            'required' => __('validation.The :attribute field is required.'),
            'confirmed' => __('validation.The :attribute confirmation does not match.'),
            'alpha_num' => __('validation.The :attribute must only contain letters and numbers.'),
            'merchant_name.regex'    =>  __('validation.The :attribute must be alphabets.'),
            'mobile_number.regex' => __('validation.The :attribute must be numbers.'),
            'email' => __('validation.The :attribute must be email.'),
            'merchant_code.regex' => __('validation.The :attribute must be alpha numerics and dash.'),
            'url'    => __('validation.The :attribute must be url.'),
        ];

        $validator = Validator::make($request->all(), [
            'merchant_name' => 'required|regex:/^[\pL\s\-]+$/u|unique:merchants,merchant_name',
            'merchant_code' => 'required|unique:merchants,merchant_code|regex:/^[a-zA-Z0-9\s-]+$/',
            'user_name' => 'required|alpha_num|unique:users,user_name',
            'email' => 'required|unique:users,email|email',
            'mobile_number' => 'required|regex:/^[0-9]+$/|unique:users,mobile_number',
            'password' => 'required|confirmed',
            'password_confirmation' => 'required',
            'timezone' => 'required',
            'url' => 'required|url',
        ], $messages);


        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()]);
        }

        //if ($validator->passes()) {
        $merchant = Merchant::create([
            'merchant_name' => $request->merchant_name,
            'merchant_code' => $request->merchant_code,
            'agent_id' => $request->agent,
            'status' => 'Enable',
        ]);

        User::create([
            'name' => $request->merchant_name,
            'email' => $request->email,
            'status' => 'active',
            'role_name' => 'Merchant',
            'password' => Hash::make($request->password),
            'merchant_id' => $merchant->id,
            'user_name' => $request->user_name,
            'mobile_number' => $request->mobile_number,
            'timezone_id' => $request->timezone,
            'url' => $request->url,
        ]);
        $messages = __('messages.Added Successfully');
        $success = __('messages.Success');
        Toastr::success($messages, $success);
        //}

        //return response()->json(['error' => $validator->errors()]);
    }

    public function addAccount(Request $request, Merchant $merchant)
    {
        // echo $merchant->id;
        // die();
        DB::beginTransaction();
        try {
            $merchant_account_details = PaymentAccount::whereMerchantId($merchant->id)->first();

            if ($merchant_account_details) {
                $addRecord = [
                    'bank_name' => $request->bank_name,
                    'account_name' => $request->account_name,
                    'account_province' => $request->province,
                    'account_number' => $request->account_number,
                    'account_outlet' => $request->outlet,
                    'account_city' => $request->city,
                    'merchant_id' => $merchant->id,
                ];
                $merchant_account_details->update($addRecord);
                $messages1 = __('messages.Updated Successfully');
                $success = __('messages.Success');
                Toastr::success($messages1, $success);
            } else {
                $addRecord = [
                    'bank_name' => $request->bank_name,
                    'account_name' => $request->account_name,
                    'account_province' => $request->province,
                    'account_number' => $request->account_number,
                    'account_outlet' => $request->outlet,
                    'account_city' => $request->city,
                    'merchant_id' => $merchant->id,
                ];
                PaymentAccount::create($addRecord);
                $messages2 = __('messages.Added Successfully');
                $success = __('messages.Success');
                Toastr::success($messages2, $success);
            }
            DB::commit();
            return redirect()->back();
        } catch (\Exception $e) {
            DB::rollback();
            Log::error($e);
            Toastr::error('fail, Add Account :)', 'Error');
            return redirect()->back();
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Merchant  $merchant
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $merchant = Merchant::findOrFail($request->id);
        $user = User::where('merchant_id', $request->id)->first();

        $messages = [
            'unique'    => __('validation.The :attribute must be unique.'),
            'required' => __('validation.The :attribute field is required.'),
            'confirmed' => __('validation.The :attribute confirmation does not match.'),
            'alpha_num' => __('validation.The :attribute must only contain letters and numbers.'),
            'merchant_name.regex'    =>  __('validation.The :attribute must be alphabets.'),
            'mobile_number.regex' => __('validation.The :attribute must be numbers.'),
            'email' => __('validation.The :attribute must be email.'),
            'merchant_code.regex' => __('validation.The :attribute must be alpha numerics and dash.'),
            'url'    => __('validation.The :attribute must be url.'),
        ];

        $validator = Validator::make($request->all(), [
            'merchant_name' => 'required|regex:/^[\pL\s\-]+$/u|unique:merchants,merchant_name,' . $merchant->id,
            'merchant_code' => 'required|regex:/^[a-zA-Z0-9\s-]+$/|unique:merchants,merchant_code,' . $merchant->id,
            'user_name' => 'required|alpha_num|unique:users,user_name,' . $user->id,
            'email' => 'required|email|unique:users,email,' . $user->id,
            'mobile_number' => 'nullable|regex:/^[0-9]+$/|unique:users,mobile_number,' . $user->id,
            'password' => 'nullable|confirmed',
            'password_confirmation' => 'nullable',
            'timezone' => 'required',
            'url' => 'required|url',
        ], $messages);

        if ($validator->passes()) {
            $merchant->update([
                'merchant_name' => $request->merchant_name,
                'merchant_code' => $request->merchant_code,
                'agent_id' => $request->agent,
                'status' => $request->status,
            ]);

            $user->update([
                'name' => $request->merchant_name,
                'email' => $request->email,
                'user_name' => $request->user_name,
                'mobile_number' => $request->mobile_number,
                'timezone_id' => $request->timezone,
                'url' => $request->url,
            ]);

            if ($request->password != null) {
                $user->update([
                    'password' => Hash::make($request->password),
                ]);
            }
            $messages3 = __('messages.Updated Successfully');
            $success = __('messages.Success');
            Toastr::success($messages3, $success);
        }

        return response()->json(['error' => $validator->errors()]);
    }

    public function createAuth(Request $request, Merchant $merchant)
    {
        DB::beginTransaction();
        try {
            $userEmail = User::whereEmail($request->email)->whereMerchantId($merchant->id)->first();
            //dd($userEmail,$request->All());
            if ($userEmail) {
                $updateRecord = [
                    'name'      => $request->name,
                    'email'     => $request->email,
                    'user_name' => $request->user_name,
                    'mobile_number' => $request->mobile_number,
                    //'merchant_id'     => $merchant->id,
                    'role_name' => 'Merchant',
                    'status' => "active",
                    'password'  => Hash::make($request->password),
                ];
                //dd($updateRecord);
                $userEmail->update($updateRecord);
                $messages1 = __('messages.Updated Successfully');
                $success = __('messages.Success');
                Toastr::success($messages1, $success);
            } else {
                User::create([
                    'name'      => $request->name,
                    'email'     => $request->email,
                    'user_name' => $request->user_name,
                    'mobile_number' => $request->mobile_number,
                    'merchant_id'   => $merchant->id,
                    'role_name' => 'Merchant',
                    'status' => "active",
                    'password'  => Hash::make($request->password),
                ]);
                $messages2 = __('messages.Added Successfully');
                $success = __('messages.Success');
                Toastr::success($messages2, 'Success');
            }
            DB::commit();

            return redirect()->back();
        } catch (\Exception $e) {
            DB::rollback();
            $messages3 = __('messages.fail, Update Merchant Name');
            Toastr::error($messages3, 'Error');
            return redirect()->back();
        }
    }

    public function deleteRecord(Request $request)
    {
        try {
            Merchant::where('id', $request->id)->delete();
            User::where('merchant_id', $request->id)->delete();
            $delMsg = __('messages.Deleted successfully');
            $success = __('messages.Success');
            Toastr::success($delMsg, $success);
            return redirect()->back();
        } catch (\Exception $e) {
            DB::rollback();
            $errdMsg = __('messages.Merchant delete fail');
            Toastr::error($errdMsg, 'Error');
            return redirect()->back();
        }
    }

    public function sowPaymentMap(Request $request, Merchant $merchant)
    {
        $merchantId = $merchant->id;
        $paymentUrl = PaymentUrl::where('status', 'Enable')->get();
        $paymentMethod = PaymentMethod::where('status', 'Enable')->get();
        $merchantName = Merchant::where('id', $merchantId)->first()->merchant_name;

        if ($request->ajax()) {
            $data = PaymentMap::query()
                ->with('methodPayment:id,method_name')
                ->where('merchant_id', $merchant->id)
                ->select(
                    'id',
                    'max_value',
                    'min_value',
                    'product_id',
                    'merchant_id',
                    'agent_commission',
                    'merchant_commission',
                    'cny_min',
                    'cny_max',
                    'status',
                    'created_at',
                    'channel_mode',
                    'gateway_payment_channel_id',
                    'payment_method_id'
                );
            // dd($data->get());
            return DataTables::of($data)
                ->addColumn('payment_method_name', function ($data) {
                    return $data->methodPayment->method_name;
                })
                ->addColumn('cny_range', function ($data) {
                    return number_format($data->cny_min, 2) . " - " . number_format($data->cny_max, 2);
                })
                ->editColumn('max_value', function ($data) {
                    return number_format($data->max_value, 2);
                })
                ->editColumn('min_value', function ($data) {
                    return number_format($data->min_value, 2);
                })
                ->editColumn('status', function ($data) {
                    return $data->status == 'Enable'
                        ? '<span class="badge light badge-success">' . trans('messages.Enable') . '</span>'
                        : '<span class="badge light badge-danger">' . trans('messages.Disable') . '</span>';
                })
                ->editColumn('created_at', function ($data) {
                    return getAuthPreferenceTimezone($data->created_at);
                })
                ->editColumn('agent_commission', function ($data) {
                    return number_format($data->agent_commission, 2);
                })
                ->editColumn('merchant_commission', function ($data) {
                    return number_format($data->merchant_commission, 2);
                })
                ->addColumn('url', function ($data) {
                    if ($data->status == 'Enable') {
                        return '
							<button value="copy" class="btn btn-primary btn-xs" onclick="copyToClipboard(' . $data->id . ')">' . trans('messages.copy') . '</button>
						';
                    }
                })
                ->addColumn('action', function ($data) {
                    $action = '<div class="d-flex">';

                    if (auth()->user()->can('PaymentMap: Update PaymentMap')) {
                        $action .= '
					        <a class="btn btn-primary shadow btn-xs sharp me-1 edit_channel" href="#"
                            data-toggle="modal" data-target="#edit_product"
                            data-id="' . $data->id . '" data-max_value="' . $data->max_value . '" data-min_value="' . $data->min_value . '"
                            data-url="' . $data->payment_url_id . '" data-agent_rate="' . $data->agent_commission . '"
                            data-merchant_rate="' . $data->merchant_commission . '" data-status="' . $data->status . '"
                            data-cny_min="' . $data->cny_min . '" data-cny_max="' . $data->cny_max . '"
                            data-channel_mode="' . $data->channel_mode . '"
                            data-gateway_payment_channel_id="' . $data->gateway_payment_channel_id . '"
                            data-payment_method_id="' . $data->payment_method_id . '" >

								<i class="fas fa-pencil-alt"></i>
							</a>
					    ';
                    }

                    if (auth()->user()->can('PaymentUrl: Delete PaymentUrl')) {
                        $action .= '
					        <a class="btn btn-danger shadow btn-xs sharp delete_product" href="#"
					            data-toggle="modal" data-target="#delete_product" data-id="' . $data->id . '">
					            <i class="fa fa-trash"></i>
					        </a>
					    ';
                    }

                    $action .= '</div>';

                    return $action;
                })
                ->filter(function ($data) use ($request) {
                    if ($request->pMethod) {
                        $data->where('payment_method_id', $request->pMethod);
                    }

                    if ($request->status) {
                        $data->where('status', $request->status);
                    }

                    if (!empty($request->search)) {
                        $data->where(function ($q) use ($request) {
                            $q->orWhere('product_id', 'LIKE', '%' . $request->search . '%')
                                ->orWhere('max_value', 'LIKE', '%' . $request->search . '%')
                                ->orWhere('min_value', 'LIKE', '%' . $request->search . '%')
                                ->orWhere('agent_commission', 'LIKE', '%' . $request->search . '%')
                                ->orWhere('merchant_commission', 'LIKE', '%' . $request->search . '%')
                                ->orWhere('cny_min', 'LIKE', '%' . $request->search . '%')
                                ->orWhere('cny_max', 'LIKE', '%' . $request->search . '%')
                                ->orWhere('max_value', 'LIKE', '%' . $request->search . '%')
                                ->orWhere('min_value', 'LIKE', '%' . $request->search . '%')
                                ->orWhere('created_at', 'LIKE', '%' . $request->search . '%');
                        });
                    }
                })
                ->rawColumns(['action', 'status', 'url', 'cny_range', 'payment_method_name'])
                ->make(true);
        }

        return view('form.payment.paymentMap', compact('paymentUrl', 'merchantId', 'paymentMethod', 'merchantName'));
    }

    public function sowPaymentMapApi(Merchant $merchant)
    {
        $query = PaymentMap::join('payment_urls', 'payment_urls.id', '=', 'payment_maps.payment_url_id')
            ->join('payment_channels', 'payment_channels.id', '=', 'payment_maps.channel_id')
            ->join('payment_methods', 'payment_methods.id', '=', 'payment_maps.method_id')
            //->join('payment_sources', 'payment_sources.id', '=', 'payment_maps.source_id')
            ->where('payment_maps.merchant_id', $merchant->id)
            ->where('payment_maps.status', 'Enable')
            ->select(
                'payment_maps.id as product_id',
                'payment_urls.url_name as product_name',
                'payment_maps.map_value as product_amount',
                'payment_channels.channel_name',
                'payment_methods.method_name',
                //'payment_sources.source_name'
            );

        $map_table = $query->get();

        foreach ($map_table as $key => $map_table_id) {
            $map_table[$key]->id = $map_table_id->product_id;
            $map_table[$key]->product_id = "P00" . $map_table_id->product_id;
        }

        $result['message'] = 'Payment Details';
        $result['data'] = $map_table;
        $result['statusCode'] = 400;

        // echo "<table style='font-family: arial, sans-serif; border-collapse: collapse; width: 40%;'>
        // <tr>
        //     <th style='border: 1px solid #dddddd; text-align: left; padding: 8px;'>product_id</th>
        //     <th style='border: 1px solid #dddddd; text-align: left; padding: 8px;'>product_name</th>
        //     <th style='border: 1px solid #dddddd; text-align: left; padding: 8px;'>product_amount</th>
        //     <th style='border: 1px solid #dddddd; text-align: left; padding: 8px;'>channel_name</th>
        //     <th style='border: 1px solid #dddddd; text-align: left; padding: 8px;'>method_name</th>
        //     <th style='border: 1px solid #dddddd; text-align: left; padding: 8px;'>source_name</th>
        // </tr> ";
        // foreach($map_table as $map_tableVal){
        //     echo "
        //     <tr>
        //         <td style='border: 1px solid #dddddd; text-align: left; padding: 8px;'>P00".$map_tableVal->product_id."</td>
        //         <td style='border: 1px solid #dddddd; text-align: left; padding: 8px;'>".$map_tableVal->product_name."</td>
        //         <td style='border: 1px solid #dddddd; text-align: left; padding: 8px;'>".$map_tableVal->product_amount."</td>
        //         <td style='border: 1px solid #dddddd; text-align: left; padding: 8px;'>".$map_tableVal->channel_name."</td>
        //         <td style='border: 1px solid #dddddd; text-align: left; padding: 8px;'>".$map_tableVal->method_name."</td>
        //         <td style='border: 1px solid #dddddd; text-align: left; padding: 8px;'>".$map_tableVal->source_name."</td>
        //     </tr>";
        // }
        // echo "</table>";

        return $this->getSuccessMessages($result);
    }

    public function getSuccessMessages($data, $status = true)
    {
        $successMessage = [];
        if (!empty($data['message'])) {
            $successMessage['message'] = $data['message'];
        }
        if (!empty($data['data'])) {
            $successMessage['data'] = $data['data'];
        }
        $successMessage['status'] = $status;

        return response()->json($successMessage, $data['statusCode']);
    }

    public function sowPaymentProduct(Request $request)
    {


        if ($request->ajax()) {
            $data = PaymentMap::select('id', 'cny_max', 'cny_min', 'product_id', 'agent_commission', 'merchant_commission', 'created_at', 'status')
                ->where('merchant_id', auth()->user()->merchant_id)
                ->get();

            return DataTables::of($data)
                ->editColumn('status', function ($data) {
                    return $data->status == 'Enable'
                        ? '<span class="badge light badge-success">' . trans('messages.Enable') . '</span>'
                        : '<span class="badge light badge-danger">' . trans('messages.Disable') . '</span>';
                })
                ->editColumn('cny_max', function ($data) {
                    return number_format($data->cny_max, 2);
                })
                ->editColumn('cny_min', function ($data) {
                    return number_format($data->cny_min, 2);
                })
                ->editColumn('created_at', function ($data) {
                    return getAuthPreferenceTimezone($data->created_at);
                })

                ->rawColumns(['status'])
                ->make(true);
        }

        return view('form.merchant.paymentMap');
    }

    public function billingMerchant(Merchant $merchant)
    {
        $merchant_billing = Merchant::where('id', $merchant->id)->first();
        $billing = Billing::where('merchant_id', $merchant->id)->first();
        return view('form.merchant.merchantBillingTable', compact('merchant_billing', 'billing'));
    }

    /** get latest merchant user by merchant id */
    public function getAccountDetails($merchantId)
    {
        $merchant = Merchant::find($merchantId)->paymentAccount;
        return  response()->json([
            'data' => $merchant
        ]);
    }

    public function Bankindex()
    {
        $paymentUrl = Bank::where('status', 'active')->get();
        $merchant_table = Merchant::with('agent')->simplePaginate(10);
        return view('form.merchant.merchantTable', compact('merchant_table', 'paymentUrl'));
    }

    /** get latest Agent user by Agent id */
    public function getAgentList()
    {
        $data = Agent::where('status', 'Enable')->orderBy('agent_name', 'ASC')->get();
        return  response()->json($data);
    }

    public function getChannelData(Request $request)
    {
        $channel = GatewayPaymentChannel::leftJoin('payment_methods', 'payment_methods.id', '=', 'gateway_payment_channels.gateway_account_method_id')
            ->select('gateway_payment_channels.id', 'gateway_payment_channels.channel_id', 'gateway_payment_channels.channel_description')
            ->where('gateway_payment_channels.gateway_account_method_id', $request->id)->get();


        foreach ($channel as $value) {
            $selected = '';
            $checked = '';
            if (in_array($value->id, explode(',', $request->m_id))) {
                $checked = 'checked';
            }

            if ($request->mode == 'table') {
                echo '
                    <tr>
                        <td><input type="checkbox" ' . $checked . ' name="channel_multi[]" value="' . $value->id . '"></td>
                        <td>' . $value->channel_id . '</td>
                        <td>' . $value->channel_description . '</td>
                    </tr>
                ';
            } else {
                echo '<option value="' . $value->id . '" ' . $selected . '>' . $value->channel_id . '</option>';
            }
        }
    }
}
