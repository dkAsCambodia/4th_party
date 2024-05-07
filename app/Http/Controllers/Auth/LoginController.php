<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Auth;
use DB;
use App\Models\User;
use Carbon\Carbon;
use Session;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Http;
use App\Models\LoginLog;
use Illuminate\Support\Facades\Log;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
    * Where to redirect users after login.
    *
    * @var string
    */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
    * Create a new controller instance.
    *
    * @return void
    */
    public function __construct()
    {
        $this->middleware('guest')->except([
            'logout',
            'locked',
            'unlock'
        ]);
    }
    /** index page login */
    public function login()
    {
        if(Auth::user()){
            return redirect()->route('home');
        }
        return view('auth.login');
    }

    /** login with databases */
    public function authenticate(Request $request)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'username' => 'required|string',
                'password' => 'required|string',
            ]);

            // $email     = '@gmail.com';
            // $username  = $request->username . $email;
            $password  = $request->password;



                if (Auth::attempt(['user_name'=>$request->username,'password'=>$password])) {
                /** get session */

                LoginLog::create([
                    'user_id' => Auth::User()->id,
                    'user_type' => User::class,
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);


                    $user = Auth::User();
                    Session::put('name', $user->name);
                    Session::put('email', $user->email);
                    Session::put('user_id', $user->user_id);
                    Session::put('join_date', $user->join_date);
                    Session::put('phone_number', $user->phone_number);
                    Session::put('status', $user->status);
                    Session::put('role_name', $user->role_name);
                    Session::put('avatar', $user->avatar);
                    Session::put('position', $user->position);
                    Session::put('agent_id', $user->agent_id);
                    Session::put('locale', 'en');
                   // Toastr::success('Login successfully :)','Success');
                    DB::commit();
                    if(Auth::User()->role_name == 'Merchant'){
                       // return redirect()->route('details-payment/list-merchant');
                       return redirect()->intended('home');
                    }else{
                        return redirect()->intended('home');
                    }

                } else {

                    $errdMsg = __('messages.WRONG USERNAME OR PASSWORD');
                    Toastr::error($errdMsg, 'Error');
                    return redirect('login');
                }

        } catch(\Exception $e) {

            DB::rollback();
            $err = __('messages.LOGIN');
            Toastr::error($err, 'Error');
            return redirect()->back();
        }
    }

    /** logout */
    public function logout( Request $request)
    {
        Auth::logout();
        // forget login session
        $request->session()->forget('name');
        $request->session()->forget('email');
        $request->session()->forget('user_id');
        $request->session()->forget('join_date');
        $request->session()->forget('phone_number');
        $request->session()->forget('status');
        $request->session()->forget('role_name');
        $request->session()->forget('avatar');
        $request->session()->forget('position');
        $request->session()->forget('agent_id');
        $request->session()->flush();

       // Toastr::success('Logout successfully :)','Success');
        return redirect('login');
    }

}
