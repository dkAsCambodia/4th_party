<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Merchant;
use App\Models\Agent;
use App\Models\User;
use App\Models\Bank;
use App\Models\PaymentAccount;
use App\Http\Requests\PaymentFormRequest;
use App\Http\Requests\UpdateMerchantUserFormRequest;
use Carbon\Carbon;
use DB;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class AccountController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $allMerchant = Merchant::get();
        $allAgent = Agent::get();
        $allBank = Bank::all();

        if ($request->ajax()) {
            $data = PaymentAccount::query()
                ->with('merchant:id,merchant_code', 'agent:id,agent_code', 'bank:id,account_type')
                ->when($request->checkValue == null, fn ($q) => $q->where('merchant_id', '!=', null))
                ->when($request->checkValue == 'agent', fn ($q) => $q->where('agent_id', '!=', null))
                ->select('id', 'bank_name', 'account_name', 'bank_id', 'account_number', 'merchant_id', 'agent_id', 'status', 'created_at', 'default');

            return DataTables::of($data)
                ->addColumn('account_type', function ($data) {
                    return $data->bank?->account_type;
                })
                ->addColumn('merchant_code', function ($data) {
                    return $data->merchant->merchant_code ?? '-';
                })
                ->addColumn('agent_code', function ($data) {
                    return $data->agent->agent_code ?? '-';
                })
                ->editColumn('default', function ($data) {
                    return $data->default == 'yes'
                        ? trans('messages.Yes')
                        : trans('messages.No');
                })
                ->editColumn('status', function ($data) {
                    return $data->status == 'enable'
                        ? '<span class="badge light badge-success">' . trans('messages.Enable') . '</span>'
                        : '<span class="badge light badge-danger">' . trans('messages.Disable') . '</span>';
                })
                ->editColumn('created_at', function ($data) {
                    return getAuthPreferenceTimezone($data->created_at);
                })
                ->filter(function ($data) use ($request) {
                    if ($request->bank) {
                        $data->where('bank_id', $request->bank);
                    }

                    if ($request->merchant) {
                        $data->where('merchant_id', $request->merchant);
                    }

                    if ($request->agent) {
                        $data->where('agent_id', $request->agent);
                    }

                    if ($request->status) {
                        $data->where('status', $request->status);
                    }

                    if (!empty($request->search)) {
                        $data->where(function ($q) use ($request) {
                            $q->orWhere('bank_name', 'LIKE', '%' . $request->search . '%')
                                ->orWhere('account_name', 'LIKE', '%' . $request->search . '%')
                                ->orWhere('account_number', 'LIKE', '%' . $request->search . '%')
                                ->orWhere('status', 'LIKE', '%' . $request->search . '%')
                                ->orWhere('default', 'LIKE', '%' . $request->search . '%')
                                ->orWhere('created_at', 'LIKE', '%' . $request->search . '%');
                        });
                    }
                })
                ->rawColumns(['status'])
                ->with('checkValue', $request->checkValue)
                ->make(true);
        }

        return view('form.account.accountTable', compact('allMerchant', 'allAgent', 'allBank'));
    }

    public function indexMerchant(Request $request)
    {
        $account_type = Bank::all();

        if ($request->ajax()) {
            $data = PaymentAccount::query()
                ->where('merchant_id', auth()->user()->merchant_id)
                ->with('bank:id,account_type')
                ->select(
                    'id',
                    'bank_name',
                    'account_name',
                    'bank_id',
                    'account_number',
                    'merchant_id',
                    'status',
                    'created_at',
                    'remark',
                    'default',
                );

            return DataTables::of($data)
                ->addColumn('account_type', function ($data) {
                    return $data->bank?->account_type;
                })
                ->editColumn('default', function ($data) {
                    return $data->default == 'yes'
                        ? trans('messages.Yes')
                        : trans('messages.No');
                })
                ->editColumn('status', function ($data) {
                    return $data->status == 'enable'
                        ? '<span class="badge light badge-success">' . trans('messages.Enable') . '</span>'
                        : '<span class="badge light badge-danger">' . trans('messages.Disable') . '</span>';
                })
                ->editColumn('created_at', function ($data) {
                    return getAuthPreferenceTimezone($data->created_at);
                })
                ->addColumn('action', function ($data) {
                    return '<div class="d-flex">
                        <a class="btn btn-primary shadow btn-xs sharp  update_account" href="#" data-toggle="modal" data-target="#update_account"
                        data-id="' . $data->id . '" data-bank_name="' . $data->bank_name . '"
                        data-account_name="' . $data->account_name . '"
                        data-account_number="' . $data->account_number . '"
                        data-remark="' . $data->remark . '"
                        data-status="' . $data->status . '"
                        data-default="' . $data->default . '"
                        data-bank_id="' . $data->bank_id . '">
                            <i class="fas fa-pencil-alt"></i>
                        </a>
                        <a class="btn btn-danger shadow btn-xs sharp delete_account ms-2" href="#" data-toggle="modal" data-target="#delete_account" data-id="' . $data->id . '">
                            <i class="fa fa-trash"></i>
                        </a>
                    </div>';
                })
                ->filter(function ($data) use ($request) {
                    if ($request->bank) {
                        $data->where('bank_id', $request->bank);
                    }

                    if ($request->status) {
                        $data->where('status', $request->status);
                    }

                    if (!empty($request->search)) {
                        $data->where(function ($q) use ($request) {
                            $q->orWhere('bank_name', 'LIKE', '%' . $request->search . '%')
                                ->orWhere('account_name', 'LIKE', '%' . $request->search . '%')
                                ->orWhere('account_number', 'LIKE', '%' . $request->search . '%')
                                ->orWhere('status', 'LIKE', '%' . $request->search . '%')
                                ->orWhere('default', 'LIKE', '%' . $request->search . '%')
                                ->orWhere('created_at', 'LIKE', '%' . $request->search . '%');
                        });
                    }
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        }

        return view('form.account.merchantAccountTable', compact('account_type'));
    }

    public function indexAgent(Request $request)
    {
        $account_type = Bank::all();

        if ($request->ajax()) {
            $data = PaymentAccount::query()
                ->where('agent_id', auth()->user()->agent_id)
                ->with('bank:id,account_type')
                ->select(
                    'id',
                    'bank_name',
                    'account_name',
                    'bank_id',
                    'account_number',
                    'agent_id',
                    'status',
                    'created_at',
                    'remark',
                    'default',
                );

            return DataTables::of($data)
                ->addColumn('account_type', function ($data) {
                    return $data->bank?->account_type;
                })
                ->editColumn('default', function ($data) {
                    return $data->default == 'yes'
                        ? trans('messages.Yes')
                        : trans('messages.No');
                })
                ->editColumn('status', function ($data) {
                    return $data->status == 'enable'
                        ? '<span class="badge light badge-success">' . trans('messages.Enable') . '</span>'
                        : '<span class="badge light badge-danger">' . trans('messages.Disable') . '</span>';
                })
                ->editColumn('created_at', function ($data) {
                    return getAuthPreferenceTimezone($data->created_at);
                })
                ->addColumn('action', function ($data) {
                    return '<div class="d-flex">
                        <a class="btn btn-primary shadow btn-xs sharp  update_account" href="#" data-toggle="modal" data-target="#update_account"
                        data-id="' . $data->id . '" data-bank_name="' . $data->bank_name . '"
                        data-account_name="' . $data->account_name . '"
                        data-account_number="' . $data->account_number . '"
                        data-remark="' . $data->remark . '"
                        data-status="' . $data->status . '"
                        data-default="' . $data->default . '"
                        data-bank_id="' . $data->bank_id . '">
                            <i class="fas fa-pencil-alt"></i>
                        </a>
                        <a class="btn btn-danger shadow btn-xs sharp delete_account ms-2" href="#" data-toggle="modal" data-target="#delete_account" data-id="' . $data->id . '">
                            <i class="fa fa-trash"></i>
                        </a>
                    </div>';
                })
                ->filter(function ($data) use ($request) {
                    if ($request->bank) {
                        $data->where('bank_id', $request->bank);
                    }

                    if ($request->status) {
                        $data->where('status', $request->status);
                    }

                    if (!empty($request->search)) {
                        $data->where(function ($q) use ($request) {
                            $q->orWhere('bank_name', 'LIKE', '%' . $request->search . '%')
                                ->orWhere('account_name', 'LIKE', '%' . $request->search . '%')
                                ->orWhere('account_number', 'LIKE', '%' . $request->search . '%')
                                ->orWhere('status', 'LIKE', '%' . $request->search . '%')
                                ->orWhere('default', 'LIKE', '%' . $request->search . '%')
                                ->orWhere('created_at', 'LIKE', '%' . $request->search . '%');
                        });
                    }
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        }

        return view('form.account.agentAccountTable', compact('account_type'));
    }

    public function store(Request $request)
    {
        $messages = [
            'required' => 'The :attribute field is required.',
        ];

        $validator = Validator::make($request->all(), [
            'account_type' => 'required',
            'bank_name' => 'required',
            'account_name' => 'required',
            'account_number' => 'required',
            'status' => 'required',
        ], $messages);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()]);
        }

        if (auth()->user()->role_name == 'Merchant') {
            $defaultAccount = PaymentAccount::where('default', 'yes')->where('merchant_id', auth()->user()->merchant_id)->first();
        } else {
            $defaultAccount = PaymentAccount::where('default', 'yes')->where('agent_id', auth()->user()->agent_id)->first();
        }

        $pa = PaymentAccount::create([
            'bank_id' => $request->account_type,
            'bank_name' => $request->bank_name,
            'account_name' => $request->account_name,
            'account_number' => $request->account_number,
            'remark' => $request->remark,
            'status' => $request->status,
            'merchant_id' => Auth()->user()->role_name === 'Merchant' ? Auth()->user()->merchant_id : null,
            'agent_id' => Auth()->user()->role_name === 'Agent' ? Auth()->user()->agent_id : null,
        ]);

        if ($request->default && $defaultAccount != null) {
            if (auth()->user()->role_name == 'Merchant') {
                $defaultAccount = PaymentAccount::where('default', 'yes')->where('merchant_id', auth()->user()->merchant_id)->update(array('default' => 'no'));
            } else {
                $defaultAccount = PaymentAccount::where('default', 'yes')->where('agent_id', auth()->user()->agent_id)->update(array('default' => 'no'));
            }
            $pa->update([
                'default' => 'yes',
            ]);
        } else if ($request->default && $defaultAccount == null) {
            $pa->update([
                'default' => 'yes',
            ]);
        } else {
            $pa->update([
                'default' => 'no',
            ]);
        }
        $success = __('messages.Success');
        Toastr::success(__('messages.Added Successfully'), $success);

        // return back();
    }

    public function update(Request $request)
    {
        $paymentAccount = PaymentAccount::findOrFail($request->id);

        $messages = [
            'required' => 'The :attribute field is required.',
        ];

        $validator = Validator::make($request->all(), [
            'account_type' => 'required',
            'bank_name' => 'required',
            'account_name' => 'required',
            'account_number' => 'required',
            'status' => 'required',
        ], $messages);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()]);
        }

        if (auth()->user()->role_name == 'Merchant') {
            $defaultAccount = PaymentAccount::where('default', 'yes')->where('merchant_id', auth()->user()->merchant_id)->first();
        } else {
            $defaultAccount = PaymentAccount::where('default', 'yes')->where('agent_id', auth()->user()->agent_id)->first();
        }

        $paymentAccount->update([
            'bank_id' => $request->account_type,
            'bank_name' => $request->bank_name,
            'account_name' => $request->account_name,
            'account_number' => $request->account_number,
            'remark' => $request->remark,
            'status' => $request->status,
        ]);

        if ($request->default && $defaultAccount != null) {
            if (auth()->user()->role_name == 'Merchant') {
                $defaultAccount = PaymentAccount::where('default', 'yes')->where('merchant_id', auth()->user()->merchant_id)->update(array('default' => 'no'));
                $defaultAccount = PaymentAccount::where('id', $request->id)->where('merchant_id', auth()->user()->merchant_id)->update(['default' => 'yes']);
            } else {
                $defaultAccount = PaymentAccount::where('default', 'yes')->where('agent_id', auth()->user()->agent_id)->update(array('default' => 'no'));
                $defaultAccount = PaymentAccount::where('id', $request->id)->where('agent_id', auth()->user()->agent_id)->update(['default' => 'yes']);
            }
        } else if ($request->default && $defaultAccount == null) {
            if (auth()->user()->role_name == 'Merchant') {
                $defaultAccount = PaymentAccount::where('id', $request->id)->where('merchant_id', auth()->user()->merchant_id)->update(['default' => 'yes']);
            } else {
                $defaultAccount = PaymentAccount::where('id', $request->id)->where('agent_id', auth()->user()->agent_id)->update(['default' => 'yes']);
            }
        } else {
            if (auth()->user()->role_name == 'Merchant') {
                $defaultAccount = PaymentAccount::where('id', $request->id)->where('merchant_id', auth()->user()->merchant_id)->update(['default' => 'no']);
            } else if (auth()->user()->role_name == 'Agent') {
                $defaultAccount = PaymentAccount::where('id', $request->id)->where('agent_id', auth()->user()->agent_id)->update(['default' => 'no']);
            }
        }
        $success = __('messages.Success');
        Toastr::success(trans('messages.Updated Successfully'), $success);
        //return back();
    }

    public function deleteRecord(Request $request)
    {
        try {
            PaymentAccount::where('id', $request->id)->delete();
            $delMsg = __('messages.Deleted Successfully');
            $success = __('messages.Success');
            Toastr::success($delMsg, $success);
            return redirect()->back();
        } catch (\Exception $e) {

            DB::rollback();
            $delMsg_error = __('messages.Failed to delete');
            Toastr::error($delMsg_error, 'Error');
            return redirect()->back();
        }
    }
}
