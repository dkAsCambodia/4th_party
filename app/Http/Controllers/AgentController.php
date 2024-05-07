<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateAgentUserFormRequest;
use App\Models\Agent;
use App\Models\Merchant;
use App\Models\PaymentAccount;
use App\Models\User;
use App\Models\Billing;
use App\Models\Timezone;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
//use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use DB;
use Yajra\DataTables\DataTables;
use Carbon\Carbon;

class AgentController extends Controller
{
	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index(Request $request)
	{
		$timezones = Timezone::where('status', 'active')->get();

		if ($request->ajax()) {
			
			$data = Agent::query()
				->with([
					'userData:id,user_name,mobile_number,email,agent_id,timezone_id',
				])
				->select('id', 'agent_name', 'agent_code', 'created_at', 'status');

			return DataTables::of($data)
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

					if (auth()->user()->can('Agent: Add Agent')) {
						$action .= '
							<a title="' . trans("messages.Settlement settings") . '" class="btn btn-danger shadow btn-xs sharp me-1 add_record" href="' . route("Agent: View Billing Agent", $data->id) . '">
								<i class="fa fa-plus"></i>
							</a>
						';
					}

					if (auth()->user()->can('Agent: Update Agent')) {
						$action .= '
					        <a title="' . trans("messages.Edit Merchant") . '" class="btn btn-primary shadow btn-xs sharp me-1 edit_agent" href="#" data-toggle="modal" data-target="#edit_agent" data-id="' . $data->id . '" data-agent_name="' . $data->agent_name . '" data-agent_code="' . $data->agent_code . '" data-user_name="' . $data->userData->user_name . '" data-email="' . $data->userData->email . '" data-mobile_number="' . $data->userData->mobile_number . '" data-status="' . $data->status . '" data-timezone="'. $data->userData->timezone_id .'">
								<i class="fas fa-pencil-alt"></i>
							</a>
					    ';
					}

					if (auth()->user()->can('Agent: Delete Agent')) {
						$action .= '
					        <a title="' . trans('messages.Delete Merchant') . '" class="btn btn-danger shadow btn-xs sharp delete_user"href="#" data-toggle="modal" data-target="#delete_user" data-id="' . $data->id . '">
								<i class="fa fa-trash"></i>
							</a>
					    ';
					}

					$action .= '</div>';

					return $action;
				})
				->filter(function ($data) use ($request) {
					if ($request->status) {
						$data->where('status', $request->status);
					}

					if (!empty($request->search)) {
						$data->where(function ($q) use ($request) {
							$q->orWhere('agent_code', 'LIKE', '%' . $request->search . '%')
								->orWhere('agent_name', 'LIKE', '%' . $request->search . '%')
								->orWhere('status', 'LIKE', '%' . $request->search . '%')
								->orWhere('created_at', 'LIKE', '%' . $request->search . '%');
						});
					}
				})
				->rawColumns(['action', 'status'])
				->make(true);
		}

		return view('form.agent.agentTable', compact('timezones'));
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function create()
	{
		//
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
            'unique'    => __('validation.The :attribute must be unique.'),
			'required' => __('validation.The :attribute field is required.'),
			'confirmed' => __('validation.The :attribute confirmation does not match.'),
			'alpha_num' => __('validation.The :attribute must only contain letters and numbers.'),
			'agent_name.regex'    =>  __('validation.The :attribute must be alphabets.'),
			'mobile_number.regex' => __('validation.The :attribute must be numbers.'),
			'email' => __('validation.The :attribute must be email.'),
			'agent_code.regex' => __('validation.The :attribute must be alpha numerics and dash.'),
		];

		$validator = Validator::make($request->all(), [
			'agent_name' => 'required|regex:/^[\pL\s\-]+$/u|unique:agents,agent_name',
			'agent_code' => 'required|unique:agents,agent_code|regex:/^[a-zA-Z0-9\s-]+$/',
			'user_name' => 'required|alpha_num|unique:users,user_name',
			'email' => 'required|unique:users,email|email',
			'mobile_number' => 'nullable|regex:/^[0-9]+$/|unique:users,mobile_number',
			'password' => 'required|confirmed',
			'password_confirmation' => 'required',
			'timezone' => 'required',
		], $messages);

		if ($validator->passes()) {
			$agent = Agent::create([
				'agent_name' => $request->agent_name,
				'agent_code' => $request->agent_code,
				'status' => 'Enable',
			]);

			User::create([
				'name' => $request->agent_name,
				'email' => $request->email,
				'status' => 'active',
				'role_name' => 'Agent',
				'password' => Hash::make($request->password),
				'agent_id' => $agent->id,
				'user_name' => $request->user_name,
				'mobile_number' => $request->mobile_number,
				'timezone_id' => $request->timezone,
			]);
			$succMsg = __('messages.Added Successfully');
            $success = __('messages.Success');
			Toastr::success($succMsg, $success);
		}

		return response()->json(['error' => $validator->errors()]);
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  \App\Models\Agent  $agent
	 * @return \Illuminate\Http\Response
	 */
	public function show(Agent $agent)
	{
		//
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  \App\Models\Agent  $agent
	 * @return \Illuminate\Http\Response
	 */
	public function edit(Agent $agent)
	{
		//
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \App\Models\Agent  $agent
	 * @return \Illuminate\Http\Response
	 */
	public function update(Request $request)
	{
		$agent = Agent::findOrFail($request->id);
		$user = User::where('agent_id', $request->id)->first();

		$messages = [
            'unique'    => __('validation.The :attribute must be unique.'),
			'required' => __('validation.The :attribute field is required.'),
			'confirmed' => __('validation.The :attribute confirmation does not match.'),
			'alpha_num' => __('validation.The :attribute must only contain letters and numbers.'),
			'agent_name.regex'    =>  __('validation.The :attribute must be alphabets.'),
			'mobile_number.regex' => __('validation.The :attribute must be numbers.'),
			'email' => __('validation.The :attribute must be email.'),
			'agent_code.regex' => __('validation.The :attribute must be alpha numerics and dash.'),
		];

		$validator = Validator::make($request->all(), [
			'agent_name' => 'required|regex:/^[\pL\s\-]+$/u|unique:agents,agent_name,' . $agent->id,
			'agent_code' => 'required|regex:/^[a-zA-Z0-9\s-]+$/|unique:agents,agent_code,' . $agent->id,
			'user_name' => 'required|alpha_num|unique:users,user_name,' . $user->id,
			'email' => 'required|email|unique:users,email,' . $user->id,
			'mobile_number' => 'nullable|regex:/^[0-9]+$/|unique:users,mobile_number,' . $user->id,
			'password' => 'nullable|confirmed',
			'password_confirmation' => 'nullable',
			'timezone' => 'required',
		], $messages);

		if ($validator->passes()) {
			$agent->update([
				'agent_name' => $request->agent_name,
				'agent_code' => $request->agent_code,
				'status' => $request->status,
			]);

			$user->update([
				'name' => $request->agent_name,
				'email' => $request->email,
				'user_name' => $request->user_name,
				'mobile_number' => $request->mobile_number,
				'timezone_id' => $request->timezone,
			]);

			if ($request->password != null) {
				$user->update([
					'password' => Hash::make($request->password),
				]);
			}
			$updateMsg = __('messages.Updated Successfully');
            $success = __('messages.Success');
			Toastr::success($updateMsg, $success);
		}

		return response()->json(['error' => $validator->errors()]);
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  \App\Models\Agent  $agent
	 * @return \Illuminate\Http\Response
	 */
	public function destroy(Agent $agent)
	{
		//
	}

	public function deleteRecord(Request $request)
	{
		try {
			Agent::destroy($request->id);
			User::where('agent_id', $request->id)->delete();
			$merchant = Merchant::where('agent_id', $request->id)->first();
			if ($merchant) {
				$merchant->update(['agent_id', null]);
			}
			$delMsg = __('messages.Deleted Successfully');
            $success = __('messages.Success');
			Toastr::success($delMsg, $success);
			return redirect()->back();
		} catch (\Exception $e) {

			DB::rollback();
			$delMsg_er = __('messages.Failed to delete');
			Toastr::error($delMsg_er, 'Error');
			return redirect()->back();
		}
	}

	public function addAccount(Request $request, Agent $agent)
	{
		DB::beginTransaction();
		try {
			$agent_account_details = PaymentAccount::whereAgentId($agent->id)->first();

			if ($agent_account_details) {
				$addRecord = [
					'bank_name' => $request->bank_name,
					'account_name' => $request->account_name,
					'account_province' => $request->province,
					'account_number' => $request->account_number,
					'account_outlet' => $request->outlet,
					'account_city' => $request->city,
					'agent_id' => $agent->id,
				];
				$agent_account_details->update($addRecord);
				$msg_updates = __('messages.Updated Successfully');
                $success = __('messages.Success');
				Toastr::success($msg_updates, $success);
			} else {
				$addRecord = [
					'bank_name' => $request->bank_name,
					'account_name' => $request->account_name,
					'account_province' => $request->province,
					'account_number' => $request->account_number,
					'account_outlet' => $request->outlet,
					'account_city' => $request->city,
					'agent_id' => $agent->id,
				];
				PaymentAccount::create($addRecord);
				$msg_success = __('messages.Added Successfully');
                $success = __('messages.Success');
				Toastr::success($msg_success, $success);
			}
			DB::commit();
			return redirect()->back();
		} catch (\Exception $e) {
			DB::rollback();
			Log::error($e);
			$addMsg_er = __('messages.Failed to delete');
			Toastr::error($addMsg_er, 'Error');
			return redirect()->back();
		}
	}

	public function getAgentAccount($agentId)
	{
		$agent = Agent::find($agentId)->paymentAccount;
		return  response()->json([
			'data' => $agent
		]);
	}

	public function createAgentauth(Request $request,  Agent $agent)
	{
		DB::beginTransaction();
		try {

			$userEmail = User::whereEmail($request->email)->whereAgentId($agent->id)->first();

			if ($userEmail) {
				$updateRecord = [
					'name'      => $request->name,
					'email'     => $request->email,
					'user_name' => $request->user_name,
					'mobile_number' => $request->mobile_number,
					'agent_id'  => $agent->id,
					'role_name' => 'Agent',
					'status' => "active",
					'password'  => Hash::make($request->password),
				];
				$userEmail->update($updateRecord);
				$msg_success1 = __('messages.Updated Successfully');
                $success = __('messages.Success');
				Toastr::success($msg_success1, $success);
			} else {

				User::create([
					'name'      => $request->name,
					'email'     => $request->email,
					'user_name' => $request->user_name,
					'mobile_number' => $request->mobile_number,
					'agent_id'  => $agent->id,
					'role_name' => 'Agent',
					'status' => "active",
					'password'  => Hash::make($request->password),
				]);
				$msg_success2 = __('messages.Added Successfully');
                $success = __('messages.Success');
				Toastr::success($msg_success2, $success);
			}
			DB::commit();

			return redirect()->back();
		} catch (\Exception $e) {
			DB::rollback();
			$updMsg_er = __('messages.Failed to delete');
			Toastr::error($updMsg_er, 'Error');
			return redirect()->back();
		}
	}

	public function billingAgent(Agent $agent)
	{
		$agent_billing = Agent::where('id', $agent->id)->first();
		$billing = Billing::where('agent_id', $agent->id)->first();
		return view('form.agent.agentBillingTable', compact('agent_billing', 'billing'));
	}
}
