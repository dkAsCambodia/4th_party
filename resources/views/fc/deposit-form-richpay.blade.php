
@extends('layouts.app')
@section('content')
<style>
    .auth-form {
        padding: 20px 20px !important;
    }
    .form-control {
        height: 2.5rem !important;
        border: 2px solid gray;
    }
    .justify-content-center {
        margin-top: 120px;
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
    .spinner-container img {
        width: 100px;
        height: 100px;
    }
</style>
{!! Toastr::message() !!}
<div class="row justify-content-center align-items-center">
    <div class="col-md-8">
        <div class="authincation-content">
            <div class="row no-gutters">
                <div class="col-xl-12">
                    <div class="auth-form">
                        <h3 class="text-center mb-4"><b>POIPET RESORT</b></h3>
                        <form class="form-horizontal" action="{{ route('apiroute.r2p.payin') }}" method="GET" id="paymentForm">
                            <input type="hidden" name="merchant_code" value="FCmerchant001">
                              <input type="hidden" name="product_id" value="27">    {{-- for live 27 --}}
                             {{-- <input type="hidden" name="product_id" value="25">      for local 25 --}}
                            <input type="hidden" name="callback_url" value="{{ route('apiroute.r2pPayincallbackURL') }}">
                            <input type="hidden" name="amount" value="{{ $amount ?? '' }}">
                            <input type="hidden" name="Currency" value="THB">
							<div class="row mb-4">
                                <label for="Reference" class="col-md-4 form-label">Customer Number</label>
                                <div class="col-md-8">
								<input class="form-control" name="customer_name" value="{{ $customer_name ?? '' }}" required readonly type="text">
                                </div>
                            </div>
                            <div class="row mb-4">
                                <label for="Reference" class="col-md-4 form-label">Invoice Number</label>
                                <div class="col-md-8">
								<input class="form-control" name="referenceId" value="{{ $invoice_number ?? '' }}" required readonly type="text">
                                </div>
                            </div>
                            <div class="row mb-4">
                                <label for="customer_name" class="col-md-4 form-label">Bank Account Name</label>
                                <div class="col-md-8">
								<input list="browsers" id="browser" class="form-control" required name="bank_account_name" placeholder="Enter Bank account name" type="text">
                                <datalist id="browsers">
                                    <option value="{{ $customer_name ?? '' }}">
                                </datalist>
                                </div>
                            </div>
                            <div class="row mb-4">
                                <label for="Bank-Code" class="col-md-4 form-label">Bank Code</label>
                                <div class="col-md-8">
										<select class="form-control" name="bank_code" required>
                                            <option value="">Select Bank</option>
                                            <option value="002">Bangkok Bank</option>
                                            <option value="025">Bank of Ayudhya (Krungsri)</option>
                                            <option value="017">Citibank</option>
                                            <option value="004">Karsikorn Bank (K-Bank)</option>
                                            <option value="069">Kiatnakin Bank</option>
                                            <option value="006">Krung Thai Bank</option>
                                            <option value="014">Siam Commercial Bank</option>
                                            <option value="011">TMB Bank Public Company Limited</option>
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
                                <button type="submit" id="submitBtn" class="btn btn-primary btn-block">Pay Now {{ $amount ?? '' }}฿</button>
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
