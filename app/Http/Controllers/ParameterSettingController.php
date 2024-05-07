<?php

namespace App\Http\Controllers;

use App\Models\ParameterSetting;
use App\Models\ParameterValue;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ParameterSettingController extends Controller
{
    public function index(Request $request)
    {
        $id = $request->id;
        $prameterSetting = ParameterSetting::where('channel_id', $id)->get();
        return view('form.payment.addParameterForm', compact('id', 'prameterSetting'));
    }
    public function indexVal(Request $request)
    {
        $id = $request->id;
        $channel_id = $request->channel_id;
        $parameterSetting = ParameterSetting::where('channel_id', $channel_id)->get();
        $parameterValue = ParameterValue::where('gateway_account_method_id', $id)->get();
        // dd($parameterValue);
        return view('form.payment.addParameterFormVal', compact('id', 'parameterSetting', 'parameterValue', 'channel_id'));
    }
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            ParameterValue::where('gateway_channet_id', $request->channel_id)->delete();
            if ($request->parameter_name && $request->channel_id) {
                ParameterSetting::where('channel_id', $request->channel_id)->delete();
                foreach ($request->parameter_name as $parameter) {
                    if ($parameter) {
                        ParameterSetting::create([
                            'channel_id' => $request->channel_id,
                            'parameter_name' => $parameter,
                        ]);
                    }
                }
            }

            $messages1 = __('messages.Added Successfully');
            $success = __('messages.Success');
            Toastr::success($messages1, $success);
            DB::commit();
            return redirect()->back();
        } catch (\Exception $e) {
            DB::rollback();
            $errormsg = __('messages.fail, Add Parameter Setting');
            Toastr::error($errormsg, 'Error');
            return redirect()->back();
        }
    }
}
