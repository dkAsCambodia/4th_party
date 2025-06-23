<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PaymentDetailController;
use App\Http\Controllers\MerchantController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentEcommController;
use App\Http\Controllers\PaymentMapController;
use App\Http\Controllers\PayoutController;
use App\Http\Controllers\SpeedpayPayinPayoutController;
use App\Http\Controllers\H2pController;
use App\Http\Controllers\M2pController;
use App\Http\Controllers\RichPayController;
use App\Http\Controllers\XprizoPaymentController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::controller(PaymentDetailController::class)->group(function () {
    // Route::get('payment', 'paymentNewNew');
    // Route::get('payment', 'payment_new');
    // Route::post('payment', 'payment');
    Route::get('payment-submit', 'paymentSubmit');
    Route::get('ipay-response', 'showResponse');
    Route::post('payment-response', 'getPaymentResponse');
    // Route::post('ipay-response', 'showResponse');

    Route::post('demo-payment-form', 'demoPaymentForm');

    Route::post('test-callBackUrl', 'testCallBackUrl');
});

Route::controller(PaymentEcommController::class)->group(function () {
    Route::get('payment', 'paymentNewNew');
});

Route::controller(MerchantController::class)->group(function () {
    Route::get('sow-payment-map/{merchant}', 'sowPaymentMapApi');
});

Route::controller(BillingController::class)->group(function () {
    Route::get('view-billing/{merchant}', 'viewBilling')->name('view/billing');
    Route::get('view-billing-agent/{agent}', 'viewBillingAgent')->name('view/billing-agent');
});

Route::controller(OrderController::class)->group(function () {
    Route::post('orders/create', 'create');
    Route::get('orders/paginate', 'paginate');
});

/*  Payment api for Oneshop  */
Route::controller(PaymentMapController::class)->group(function () {
    Route::match(['get', 'post'], 'get-payment-prices', 'getPaymentPrices');
    Route::match(['get', 'post'], 'get-payment-url', 'getPaymentPricesNew');
});

// ------------------------------ Gtech DK START ---------------------------------//
Route::controller(PayoutController::class)->group(function () {
    Route::get('gpayout', 'payoutRequest');
    // Route::get('api_payout_status', 'api_payout_status');     //for webhook callback
});
// ------------------------------ Gtech DK END ---------------------------------//
Route::controller(SpeedpayPayinPayoutController::class)->group(function () {
    Route::get('s2p/payin', 'payin')->name('apiroute.s2p.payin');                      // For call API
    Route::post('s2p/payin/callbackURL', 's2pPayinCallbackURL')->name('apiroute.s2pPayincallbackURL');    // For sending callback on frontend
    Route::post('/s2pDepositNotifiication', 's2pDepositNotifiication');                // For sending callback on Backend

    Route::get('s2p/payout', 'payout')->name('apiroute.s2p.payout');                      // For call API
    Route::post('s2p/payout/callbackURL', 's2pPayoutcallbackURL')->name('apiroute.s2pPayoutcallbackURL');    // For sending callback on frontend
    Route::post('/s2pWithdrawNotifiication', 's2pWithdrawNotifiication');     
    
});

Route::controller(H2pController::class)->group(function () {
    Route::get('h2p/payin', 'payin')->name('apiroute.h2p.payin');                      // For call API
    Route::post('h2p/payinResponse', 'payinResponse');              // for receive gateway response 
    Route::post('h2p/payin/callbackURL', 'h2pPayinCallbackURL')->name('apiroute.h2pPayincallbackURL');    // For sending callback on frontend
    Route::post('/h2pDepositNotifiication', 'h2pDepositNotifiication');                // For sending callback on Backend

    Route::get('h2p/payout', 'payout')->name('apiroute.h2p.payout');                      // For call API
    Route::post('h2p/payout/verifytransaction', 'verifyPayoutTransaction');    // For veryfy payout transaction URL
    Route::post('h2p/payout/callbackURL', 'h2pPayoutcallbackURL')->name('apiroute.h2pPayoutcallbackURL');    // For sending callback on frontend
    Route::post('/h2p/withdraw/notifiication', 'h2pWithdrawNotifiication');     
    
});

Route::controller(M2pController::class)->group(function () {
    Route::get('m2p/payin', 'payin')->name('apiroute.m2p.payin');                      // For call API
    Route::post('m2p/callDepositAPI', 'callDepositAPI')->name('apiroute.m2p.callDepositAPI');            // for calling gateway deposit API
    Route::post('m2p/payin/callbackURL', 'm2pPayinCallbackURL')->name('apiroute.m2pPayincallbackURL');    // For sending callback on frontend
    Route::post('/m2pDepositNotifiication', 'm2pDepositNotifiication')->name('apiroute.m2p.DepositNotifiication');                // For sending callback on Backend

    Route::get('m2p/payout', 'payout')->name('apiroute.m2p.payout');                      // For call API
    Route::post('m2p/callWithdarwAPI', 'callWithdarwAPI')->name('apiroute.m2p.callWithdarwAPI');            // for calling gateway Withdarw API
    Route::post('m2p/payout/callbackURL', 'm2pPayoutcallbackURL')->name('apiroute.m2pPayoutcallbackURL');    // For sending callback on frontend
    Route::post('/m2pWithdrawNotification', 'm2pWithdrawNotification');     
    
});

Route::controller(RichPayController::class)->group(function () {
    Route::get('r2p/payin', 'payin')->name('apiroute.r2p.payin');                      // For call API
    Route::post('r2p/payin/callbackURL', 'r2pPayinCallbackURL')->name('apiroute.r2pPayincallbackURL');    // For sending callback on frontend
    Route::post('/r2pDepositNotifiication', 'r2pDepositNotifiication');                // For sending callback on Backend

    Route::get('r2p/payout', 'payout')->name('apiroute.r2p.payout');                      // For call API
    Route::post('r2p/payout/callbackURL', 'r2pPayoutcallbackURL')->name('apiroute.r2pPayoutcallbackURL');    // For sending callback on frontend
    Route::post('/r2pWithdrawNotifiication', 'r2pWithdrawNotifiication');                           // For sending callback on Backend
    
});

Route::controller(XprizoPaymentController::class)->group(function () {
    Route::get('xpz/deposit/', 'xpzDepositApifun')->name('apiroute.xpz.depositApi');
    Route::post('xpz/depositResponse', 'xpzDepositResponse')->name('apiroute.xpzDepositResponse'); 
    Route::post('/xpzWebhookNotifiication', 'xpzWebhookNotifiication'); 

    Route::get('xpz/withdrawal/', 'xpzwithdrawApifun')->name('apiroute.xpz.withdrawalApi');
    Route::post('xpz/withdrawalResponse', 'xpzWithdrawalResponse')->name('apiroute.xpzWithdrawalResponse'); 
});