<?php

// if (env('APP_ENV') === 'production') {
//     URL::forceSchema('https');
// }

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AgentController;
use App\Http\Controllers\AgentMerchantController;
use App\Http\Controllers\ApiDocumentController;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\GatewayAccountController;
use App\Http\Controllers\GatewayAccountMethodController;
use App\Http\Controllers\GatewayPaymentChannelController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LangController;
use App\Http\Controllers\LoginLogController;
use App\Http\Controllers\MerchantController;
use App\Http\Controllers\MyMemberController;
use App\Http\Controllers\ParameterSettingController;
use App\Http\Controllers\PaymentChannelController;
use App\Http\Controllers\PaymentDetailController;
use App\Http\Controllers\PaymentDetailReportController;
use App\Http\Controllers\PaymentMapController;
use App\Http\Controllers\PaymentMethodController;
use App\Http\Controllers\PaymentSourceController;
use App\Http\Controllers\PaymentUrlController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\TimezoneController;
use App\Http\Controllers\TypeFormController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\WhitelistIPController;
use App\Http\Controllers\PayoutController;
use App\Models\CurrencyExchangeRate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// below code is for testing notification
// Route::get('/test-noti', function () {
//     $user = App\Models\User::find(1);
//     $payment = App\Models\PaymentDetail::first();

//     $user->notify(new App\Notifications\PaymentDetailNotification($payment));

//     echo 'send notification successfully';
// });

Route::get('/get-unread-notification', [HomeController::class, 'unreadNoti'])->name('get-unread-notification');

Route::get('lang/home', [LangController::class, 'index']);
Route::get('lang/change', [LangController::class, 'change'])->name('changeLang');
Route::get('updateTimezone', [TimezoneController::class, 'updateTimezone'])->name('updateTimezone');

Route::get('/', function () {
    if (Auth::user()) {
        return redirect()->route('home');
    }

    return view('auth.login');
});

Auth::routes();

// -----------------------------login-------------------------------//
//Route::group(['middleware' => 'whitelist_ip'], function () {
    Route::controller(LoginController::class)->group(function () {
        Route::post('/login', 'authenticate');
    });
    Route::get('/login', [LoginController::class, 'login'])->name('login');
//});
    Route::get('/logout', [LoginController::class, 'logout'])->name('logout');

// -------------------------- main dashboard ----------------------//
Route::controller(HomeController::class)->group(function () {
    Route::get('/home', 'index')->middleware('auth')->name('home');
    Route::get('/home/data', 'dataByDate')->middleware('auth')->name('chart-data-by-date');
    Route::get('/dashboard', 'index')->middleware('auth')->name('dashboard');
    Route::get('/graph-datas/{days}', 'getGraphDatas')->middleware('auth')->name('graph-datas');
    Route::get('/', 'index')->middleware('auth')->name('home');
});

// ------------------------------ register ---------------------------------//
Route::controller(RegisterController::class)->group(function () {
    Route::get('/register', 'register')->name('register');
    Route::post('/register', 'storeUser')->name('register');
});

Route::controller(AccountController::class)->group(function () {
    Route::post('account-add', 'store')->name('Account: Add Account');
    Route::post('account-edit', 'update')->name('Account: Edit Account');
    Route::post('account/delete', 'deleteRecord')->name('Account: Delete Account');
});

Route::get('/mark-all-read', [HomeController::class, 'markAsRead'])->name('mark-all-as-read');

Route::group(['prefix' => 'admin', 'middleware' => ['auth:sanctum', 'is_admin', 'permission']], function () {
    Route::controller(RoleController::class)->group(function () {
        Route::get('roles/all', 'all')->name('Role: View Role');
        Route::get('roles/{role}', 'get')->name('Role: Show Role')->where(['role' => '[0-9]+']);
        Route::post('roles', 'store')->name('Role: Create Role');
        Route::post('update-role', 'update')->name('Role: Edit/Update Role')->where(['role' => '[0-9]+']);
        Route::delete('roles/{role}/{params?}', 'delete')->name('Role: Delete Role');

        Route::get('permissions/paginate/{params?}', 'paginatePermissions')->name('Permission: View Permission');
        Route::get('permissions/all', 'permissions')->name('Permission: View Permission');
    });

    // -------------------------- user management ----------------------//
    Route::controller(UserManagementController::class)->group(function () {
        Route::get('user/table', 'index')->name('User: View All User');
        Route::post('user/update', 'updateRecord')->name('User: Update User');
        Route::post('user/add', 'addRecord')->name('User: Add User');
        Route::post('user/delete', 'deleteRecord')->name('User: Delete User');
        Route::get('user/get-by-merchant/{merchantId}', 'getMerchantUser')->name('User: Get Merchant User');
        Route::get('user/get-by-agent/{agentId}', 'getAgentUser')->name('User: Get Agent User');
        Route::get('user/roleLists', 'getRoleName')->name('roleLists');
    });

    // -------------------------- type form ----------------------//
    Route::controller(TypeFormController::class)->group(function () {
        Route::get('form/input/new', 'index')->name('form/input/new');
    });

    Route::controller(MerchantController::class)->group(function () {
        Route::get('merchant-list', 'index')->name('Merchant: View Merchant');
        Route::post('merchant-add', 'store')->name('Merchant: Add Merchant');
        Route::post('merchant-edit', 'update')->name('Merchant: Update Merchant');
        Route::get('show-payment-map/{merchant}', 'sowPaymentMap')->name('Merchant: View PaymentMap Merchant');
        Route::post('merchant/delete', 'deleteRecord')->name('Merchant: Delete Merchant');
        Route::get('merchant-billing/{merchant}', 'billingMerchant')->name('Merchant: View Billing Merchant');
        Route::post('payment-account/{merchant?}', 'addAccount')->name('Merchant: Add Account Merchant');
        Route::post('merchant-account/{merchant?}', 'createAuth')->name('Merchant: Add Login Merchant');
        Route::get('merchant-account-details/{merchant?}', 'getAccountDetails')->name('Merchant: View AccountDetails Merchant');
        Route::get('agent-lists', 'getAgentList')->name('agent-lists');
        Route::get('get-ChannelData', 'getChannelData');
    });

    Route::controller(ApiDocumentController::class)->group(function () {
        Route::get('api-documentation', 'apiDocuments')->name('ApiDocument: View ApiDocument');
        Route::post('add-api-documentation', 'addApiDocuments')->name('ApiDocument: Add ApiDocument');
        Route::post('insert-api-document', 'insertApiDocument')->name('ApiDocument: Add ApiData');
    });

    Route::controller(AgentController::class)->group(function () {
        Route::get('agent-list', 'index')->name('Agent: View Agent');
        Route::post('agent-add', 'store')->name('Agent: Add Agent');
        Route::post('agent-edit', 'update')->name('Agent: Update Agent');
        Route::post('agent/delete', 'deleteRecord')->name('Agent: Delete Agent');
        Route::post('agent-account/{agent?}', 'addAccount')->name('Agent: Add Account Agent');
        Route::get('agent-account-details/{agent?}', 'getAgentAccount')->name('Agent: View AccountDetails Agent');
        Route::post('agent-update/{agent?}', 'createAgentauth')->name('Agent: Add Login Agent');
        Route::get('agent-billing/{agent?}', 'billingAgent')->name('Agent: View Billing Agent');
    });

    Route::controller(PaymentChannelController::class)->group(function () {
        Route::get('channel-payment-list', 'index')->name('Channel: View Channel');
        Route::post('channel-payment-add', 'store')->name('Channel: Add Channel');
        Route::post('channel-payment-edit', 'update')->name('Channel: Update Channel');
        Route::post('channel-payment/delete', 'deleteRecord')->name('Channel: Delete Channel');
    });
    Route::controller(PaymentMethodController::class)->group(function () {
        Route::get('method-payment-list', 'index')->name('Method: View Method');
        Route::post('method-payment-add', 'store')->name('Method: Add Method');
        Route::post('method-payment-edit', 'update')->name('Method: Update Method');
        Route::post('method-payment/delete', 'deleteRecord')->name('Method: Delete Method');
    });
    // Route::controller(PaymentSourceController::class)->group(function () {
    //     Route::get('source-payment-list', 'index')->name('Source: View Source');
    //     Route::post('source-payment-add', 'store')->name('Source: Add Source');
    //     Route::post('source-payment-edit', 'update')->name('Source: Update Source');
    //     Route::post('source-payment/delete', 'deleteRecord')->name('Source: Delete Source');
    // });
    Route::controller(PaymentUrlController::class)->group(function () {
        Route::get('url-payment-list', 'index')->name('PaymentUrl: View PaymentUrl');
        Route::post('url-payment-add', 'store')->name('PaymentUrl: Add PaymentUrl');
        Route::post('url-payment/delete', 'deleteRecord')->name('PaymentUrl: Delete PaymentUrl');
        Route::post('url-payment-edit', 'update')->name('PaymentUrl: Update PaymentUrl');
    });

    Route::controller(PaymentDetailController::class)->group(function () {
        Route::get('payment-list-details', 'index')->name('PaymentDetails: View PaymentDetails');
        Route::get('payment-details/{payment}', 'getPaymentdetails')->name('PaymentDetails: View By Id PaymentDetails');
        Route::get('payment-filter', 'paymentFilter')->name('PaymentDetails: Filter PaymentDetails');
    });

    Route::controller(PaymentDetailReportController::class)->group(function () {
        Route::get('export-payment-details', 'adminExportPaymentDetails');
    });

    Route::controller(PaymentMapController::class)->group(function () {
        Route::post('map-payment-add', 'store')->name('PaymentMap: Add PaymentMap');
        Route::get('copy-payment-link', 'copyPaymentLink')->name('PaymentMap: Copy payment link PaymentMap');
        Route::post('map-payment-edit', 'update')->name('PaymentMap: Update PaymentMap');
        Route::post('map-payment/delete', 'deleteRecord')->name('PaymentMap: Delete PaymentMap');
    });

    Route::controller(BillingController::class)->group(function () {
        Route::post('agent-billing-add', 'agentStore')->name('Settlement: Billing Agent Settlement');
        Route::get('billing_view', 'index')->name('Settlement: Billing View Settlement');
        Route::post('billing_add', 'store')->name('Settlement: Billing Add Settlement');
        Route::post('change-settle/status', 'changeRequestStatus')->name('Settlement: Approve Settlement');
        Route::get('settleRequest', 'indexSettleRequestAdmin')->name('Settlement: Settle Request View Settlement');
        Route::get('settleApproved', 'indexSettleApprovedAdmin')->name('Settlement: Settle Approved View Settlement');
        Route::get('settled', 'indexSettledAdmin')->name('Settlement: Settled View Settlement');
        Route::get('settleRequestHistory', 'indexSettleHistory')->name('Settlement: Settled History Settlement');
    });

    Route::controller(WhitelistIPController::class)->group(function () {
        Route::get('whitelist-ip', 'index')->name('whitelist/list');
        Route::post('whitelist-add', 'store')->name('add/whitelist');
        Route::post('whitelist-edit', 'update')->name('edit/whitelist');
        Route::post('whitelist/delete', 'deleteRecord')->name('whitelist/delete');
    });

    Route::controller(AuditController::class)->group(function () {
        Route::get('auditlist', 'index')->name('audit/list');
    });

    Route::controller(LoginLogController::class)->group(function () {
        Route::get('loginlogs', 'index')->name('login/logs');
    });

    Route::controller(SettingController::class)->group(function () {
        Route::get('setting-list', 'index')->name('setting.list');
        Route::get('account-type-list', 'accountList')->name('setting.account.list');
        Route::post('account-type-add', 'store')->name('setting.account.add');
        Route::post('account-type-update', 'update')->name('setting.account.update');
        Route::post('account-type-delete', 'delete')->name('setting.account.delete');
        Route::get('get-accountType/{Id}', 'getAccountType')->name('setting.account.get');
    });

    // bank details
    Route::controller(AccountController::class)->group(function () {
        Route::get('account-list-details', 'index')->name('Account: View Account');
    });

    // summary report
    Route::controller(ReportController::class)->group(function () { 
        Route::get('summary-report', 'indexAdmin')->name('admin-summary-report');
        Route::get('summary-report/{date}/{merchant_code?}', 'indexAdminReportByDate')->name('admin-summary-report-by-date');
        Route::get('report/export/{date}/{merchant_code?}', 'exportAdminReport')->name('exportAdminReport');
    });

    // Gateway Acount
    Route::controller(GatewayAccountController::class)->group(function () {
        Route::get('gateway-account-list', 'index')->name('GatewayAccount: View Gateway Account');
        Route::post('gateway-account-add', 'store')->name('GatewayAccount: Add Gateway Account');
        Route::post('gateway-account-edit', 'update')->name('GatewayAccount: Update Gateway Account');
        Route::post('gateway-account/delete', 'deleteRecord')->name('GatewayAccount: Delete Gateway Account');
        Route::post('gateway-account-check', 'checkAccount')->name('GatewayAccount: Check Account');
    });

    // Gateway Acount Method
    Route::controller(GatewayAccountMethodController::class)->group(function () {
        Route::get('gateway-account-method-list/{gatewayAccountId}/{gatewayChannet?}', 'index')->name('GatewayAccountMethod: View Method Account');
        Route::post('gateway-account-method-add', 'store')->name('GatewayAccountMethod: Add Method Account');
        Route::post('gateway-account-method-edit', 'update')->name('GatewayAccountMethod: Update Method Account');
        Route::post('gateway-account-method/delete', 'deleteRecord')->name('GatewayAccountMethod: Delete Method Account');
        Route::get('get-selected-payment-method', 'selectedPaymentMethod');
    });

    // Gateway Params Settings
    Route::controller(ParameterSettingController::class)->group(function () {
        Route::get('open-form-add-parameter', 'index')->name('ParameterSetting: View Parameter Setting');
        Route::get('open-form-add-parameter-val', 'indexVal');
        Route::post('parameter-setting-add', 'store')->name('ParameterSetting: Add Parameter Setting');
        Route::post('parameter-setting/delete', 'deleteRecord')->name('ParameterSetting: Delete Parameter Setting');
    });

    // Gateway Payment Channel
    Route::controller(GatewayPaymentChannelController::class)->group(function () {
        Route::get('gateway-payment-channel-list', 'index')->name('GatewayPaymentChannel: View GatewayPaymentChannel');
        Route::post('gateway-payment-channel-add', 'store')->name('GatewayPaymentChannel: Add GatewayPaymentChannel');
        Route::post('gateway-payment-channel-edit', 'update')->name('GatewayPaymentChannel: Update GatewayPaymentChannel');
        Route::post('gateway-payment-channel/delete', 'deleteRecord')->name('GatewayPaymentChannel: Delete GatewayPaymentChannel');
        Route::get('get-MethodData', 'getMethodData');
    });

    // Timezone
    Route::controller(TimezoneController::class)->group(function () {
        Route::get('timezone-list', 'timezoneList')->name('setting.timezone.list');
        Route::post('timezone-add', 'store')->name('setting.timezone.add');
        Route::post('timezone-update', 'update')->name('setting.timezone.update');
        Route::post('timezone-delete', 'delete')->name('setting.timezone.delete');
    });
});

//////////////////////////////////////////////////////////////////////////////

Route::group(['prefix' => 'merchant', 'middleware' => ['auth', 'is_merchant']], function () {

    Route::controller(MerchantController::class)->group(function () {
        Route::get('show-payment-map', 'sowPaymentProduct')->name('sow/payment-product');
    });
    Route::controller(ApiDocumentController::class)->group(function () {
        Route::get('api-documentation', 'apiDocuments')->name('show/api-documentation');
    });
    Route::controller(PaymentDetailController::class)->group(function () {
        Route::get('payment-list-details', 'indexMerchant')->name('details-payment/list-merchant');
    });

    Route::controller(BillingController::class)->group(function () {
        Route::get('unsettled', 'indexUnsettled')->name('view/unsettled-merchant');
        Route::post('unsettledRequest', 'storeRequest')->name('unsettledRequest/unsettled-merchant');
        Route::get('settleRequest', 'indexSettleRequest')->name('view/settleRequest-merchant');
        Route::get('settled', 'indexSettled')->name('view/settled-merchant');
        Route::get('settleRequestHistory', 'indexSettleHistoryMerchant')->name('view/settledHistory-merchant');
        Route::get('list-bank-merchant', 'listBankMerchantAll')->name('list-bank-merchant');
    });

    Route::controller(ReportController::class)->group(function () {
        Route::get('summary-report', 'indexMerchant')->name('merchant-summary-report');
        Route::get('summary-report/{date}', 'indexMerchantReportByDate')->name('merchant-summary-report-by-date');
        Route::get('report/export/{date}', 'exportMerchantReport')->name('exportMerchantReport');
    });

    Route::controller(AccountController::class)->group(function () {
        Route::get('account-list-details', 'indexMerchant')->name('Account: View Merchant Account');
    });

    // payment-detail report
    Route::controller(PaymentDetailReportController::class)->group(function () {
        Route::get('export-payment-details', 'merchantExportPaymentDetails');
    });
});

//////////////////////////////////////////////////////////////////////////////////

Route::group(['prefix' => 'agent', 'middleware' => ['auth', 'is_agent']], function () {
    Route::controller(BillingController::class)->group(function () {
        Route::get('unsettled', 'indexUnsettledAgent')->name('view/unsettled-agent');
        Route::post('unsettledRequest', 'storeRequestAgent')->name('unsettledRequest/unsettled-agent');
        Route::get('settleRequest', 'indexSettleRequestAgent')->name('view/settleRequest-agent');
        Route::get('settled', 'indexSettledAgent')->name('view/settled-agent');
        Route::get('settleRequestHistory', 'indexSettleHistoryAgent')->name('view/settledHistory-agent');
        Route::get('list-bank-agent', 'listBankAgentAll')->name('list-bank-agent');
    });
    Route::controller(PaymentDetailController::class)->group(function () {
        Route::get('payment-list-details', 'indexAgent')->name('details-payment/list-agent');
    });
    Route::controller(AgentMerchantController::class)->group(function () {
        Route::get('agent-merchant', 'index')->name('view/agent-merchant');
    });

    Route::controller(AccountController::class)->group(function () {
        Route::get('account-list-details', 'indexAgent')->name('Account: View Agent Account');
    });

    // payment-detail report
    Route::controller(PaymentDetailReportController::class)->group(function () {
        Route::get('export-payment-details', 'agentExportPaymentDetails');
    });
});

/*  Payments  */
Route::get('/payment', function () {
    $currency = CurrencyExchangeRate::all();

    return view('payment.index', compact('currency'));
});
Route::post('/payment/store', [MyMemberController::class, 'store']);
Route::get('/payment_status', [MyMemberController::class, 'payment_status']);


// ------------------------------ Gtech DK START ---------------------------------//
// Route::get('/gtechz/payin', [PayinController::class, 'payinform']);
// Route::post('/fetch_bank_code', [PayinController::class, 'fetch_bank_list']);
// Route::post('/payincode', [PayinController::class, 'payincode'])->name('payincode');
Route::controller(PayoutController::class)->group(function () {
    Route::get('payout_status', 'payout_status');
});
// ------------------------------ Gtech DK END ---------------------------------//

