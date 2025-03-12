
@extends('layouts.app')
@section('content')
<?php
    $referenceNo="GZTRN".time().generateRandomString(3);
    function generateRandomString($length = 3) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {$randomString .= $characters[rand(0, $charactersLength - 1)];}
        return $randomString;
    }
?>
<style>
    .form-control {
    height: 2.5rem !important;
    border: 2px solid gray;
}
 /* Fullscreen spinner container */
 .spinner-container {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5); /* Opaque background */
            z-index: 9999;
            align-items: center;
            justify-content: center;
        }

        /* Spinner image */
        .spinner-container img {
            width: 100px;
            height: 100px;
        }
</style>
{!! Toastr::message() !!}
<div class="row justify-content-center h-100 align-items-center">
    <div class="col-md-8">
        <div class="authincation-content">
            <div class="row no-gutters">
                <div class="col-xl-12">
                    <div class="auth-form">
                        <h3 class="text-center mb-4"><b>POIPET RESORT - WITHDRAWAL</b></h3>
                        <form class="form-horizontal" action="{{ route('apiroute.s2p.payout') }}" method="GET" id="paymentForm">
                            <input type="hidden" name="merchant_code" value="FCmerchant001">
                            <input type="hidden" name="product_id" value="25">
                            <input type="hidden" name="callback_url" value="{{ route('apiroute.s2pPayoutcallbackURL') }}">
							<div class="row mb-4">
                                <label for="Reference" class="col-md-4 form-label">Reference ID</label>
                                <div class="col-md-8">
								<input class="form-control" name="referenceId" placeholder="Enter Reference ID" value="<?php echo $referenceNo; ?>" required readonly type="text">
                                </div>
                            </div>
                            <div class="row mb-4">
                                <label for="Reference" class="col-md-4 form-label">Currency</label>
                                <div class="col-md-8">
								<input class="form-control" name="Currency" value="THB" required readonly type="text">
                                </div>
                            </div>
                            <div class="row mb-4">
                                <label for="price" class="col-md-4 form-label">Amount</label>
                                <div class="col-md-8">
									<input class="form-control" required name="amount" placeholder="Enter your Amount" value="350" type="text">
                                </div>
                            </div>
                            <div class="row mb-4">
                                <label for="customer_name" class="col-md-4 form-label">Bank Account Name</label>
                                <div class="col-md-8">
								<input  class="form-control" required name="customer_name" placeholder="Enter Bank account name" type="text">
                                </div>
                            </div>
                            <div class="row mb-4">
                                <label for="Bank-Code" class="col-md-4 form-label">Bank Code</label>
                                <div class="col-md-8">
										<select class="form-control select2-show-search form-select text-dark" name="bank_code" required data-placeholder="---">
                                            <option value="">Select Bank</option>
                                            <option value="BBL">Bangkok Bank</option>
                                            <option value="BOA">Bank of AYUDHYA</option>
                                            <option value="KTB">Krung Thai Bank</option>
                                            <option value="SCB">Siam Commercial Bank</option>
                                            <option value="KKR">Kasikorn Bank</option>
                                            <option value="GSB">Government Savings Bank</option>
                                            <option value="SCBT">Standard Chartered Bank</option>
                                            <option value="KNK">KIATNAKIN PHATRA Bank</option>
                                            <option value="TMB">Thai Military Bank (TMB THANACHART Bank)</option>
										</select>
                                </div>
                            </div>
                            <div class="row mb-4">
								<label for="customer_account_number" class="col-md-4 form-label">Bank Account Number</label>
								<div class="col-md-8">
									<input class="form-control" required name="customer_account_number" id="customer_account_number" placeholder="Enter Bank Account Number" type="text">
								</div>
							</div>
                             <!-- Spinner -->
                            <div class="spinner-container">
                                <img src="https://i.gifer.com/ZZ5H.gif" alt="Loading..."> <!-- Replace with your spinner image URL -->
                            </div>
                            <div class="text-center">
                                <button type="submit" id="submitBtn" class="btn btn-primary btn-block">Pay Now</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    const form = document.getElementById('paymentForm');
    const spinnerContainer = document.querySelector('.spinner-container');
    form.addEventListener('submit', function () {
        var btn = $("#submitBtn");
                btn.html('<span class="spinner-border spinner-border-sm"></span> Processing...'); 
                btn.prop("disabled", true);
        spinnerContainer.style.display = 'flex'; // Show spinner with opaque background
    });
</script>
@endsection
