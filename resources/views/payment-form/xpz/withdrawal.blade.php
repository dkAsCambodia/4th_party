
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
                        <h3 class="text-center mb-4"><b>Pay2rax Merchant Payout or Withdrawal</b></h3>
                        <form class="form-horizontal" action="{{ route('apiroute.xpz.withdrawalApi') }}" method="GET" id="paymentForm">
                            <input type="hidden" name="merchant_code" value="testmerchant005">
                            <input type="hidden" name="product_id" value="22">
                            <input type="hidden" name="callback_url" value="{{ route('apiroute.xpzWithdrawalResponse') }}">
							<div class="row mb-4">
                                <label for="Reference" class="col-md-3 form-label">Reference ID</label>
                                <div class="col-md-9">
								<input class="form-control" name="referenceId" placeholder="Enter Reference ID" value="<?php echo $referenceNo; ?>" required readonly type="text">
                                </div>
                            </div>
                            <div class="row mb-4">
                                <label for="Currency" class="col-md-3 form-label">Currency</label>
                                <div class="col-md-9">
										<select class="form-control" name="Currency" required>
											<option value="">---</option>
											<option value="EUR">EUR</option>
											{{-- <option value="MYR">MYR</option>
                                            <option value="THB">THB</option>
                                            <option value="VND">VND</option>
                                            <option value="IDR">IDR</option> --}}
                                            <option value="USD" selected>USD</option>
                                            {{-- <option value="PHP">PHP</option>
                                            <option value="INR">INR</option>
                                            <option value="CNY">CNY</option> --}}
										</select>
                                </div>
                            </div>
                            <div class="row mb-4">
                                <label for="price" class="col-md-3 form-label">Amount max (850)</label>
                                <div class="col-md-9">
									<input class="form-control" required name="amount" placeholder="Enter your Amount" value="10" type="text">
                                </div>
                            </div>
                            <div class="row mb-4">
                                <label for="customer_name" class="col-md-3 form-label">Card Holder Name</label>
                                <div class="col-md-9">
								<input class="form-control" required name="customer_name" id="customer_name" placeholder="Enter Card Holder Name" type="text" value="dktesting xprizo">
                                </div>
                            </div>
                            <div class="row mb-4 hidden cardFiled">
                                <label for="card_number" class="col-md-3 form-label">Card Number</label>
                                <div class="col-md-9">
                                    <input type="text" class="form-control " name="card_number" id="card_number" placeholder="Card number" maxlength='16' value="4444444455551111" required>
                                </div>
                            </div>
                            <div class="row mb-4 hidden cardFiled">
                                <label for="expiration" class="col-md-3 form-label">Expiration</label>
                                <div class="col-md-9">
                                    <input type="text" class="form-control expirationInput" name="expiration" id="expiration"  maxlength='5' placeholder="MM/YY" value="02/26" required>
                                    <p class="expirationInput-warning text text-danger" style="display:none">Please fillup
                                    correct!</p>
                                </div>
                            </div>
                            <div class="row mb-4 hidden cardFiled">
                                <label for="cvv" class="col-md-3 form-label">CVC</label>
                                <div class="col-md-9">
                                    <input type="text" class="form-control" name="cvv" id="cvv" placeholder="Enter your cvv" maxlength='3' value="" required>
                                </div>
                            </div>
                             <!-- Spinner -->
                            <div class="spinner-container">
                                <img src="https://i.gifer.com/ZZ5H.gif" alt="Loading..."> <!-- Replace with your spinner image URL -->
                            </div>
                            <div class="text-center">
                                <button type="submit" id="submitBtn" class="btn btn-primary btn-block">Payout Now</button>
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

    // On keyUp validate Expiry Moth and Year START
    $(document).ready(function(){
        $('.expirationInput').on('keyup', function(){
            var val = $(this).val();
            // Remove any non-numeric characters
            val = val.replace(/\D/g,'');
            if(val.length > 2){
                // If more than 2 characters, trim it
                val = val.slice(0,2) + '/' + val.slice(2);
            }
            else if (val.length === 2){
                // If exactly 2 characters, add "/"
                val = val + '/';
            }
            $(this).val(val);

            // Check if the entered date is in the future
            var today = new Date();
            var currentYear = today.getFullYear().toString().substr(-2);
            var currentMonth = today.getMonth() + 1;
            var enteredYear = parseInt(val.substr(3));
            var enteredMonth = parseInt(val.substr(0, 2));

            if (enteredYear < currentYear || (enteredYear == currentYear && enteredMonth < currentMonth)) {
                // Entered date is not in the future, clear the input
                $('.expirationInput-warning').css("display", "block");
                $('.expirationInput').addClass("inputerror");
                $('button.card-btn').prop('disabled', true);
                // alert("Please enter a future expiry date.");
            }else{
                $('.expirationInput-warning').css("display", "none");
                $('.expirationInput').removeClass("inputerror");
                $('button.card-btn').prop('disabled', false);
            }
        });
    });
    // On keyUp validate Expiry Moth and Year END
</script>
@endsection
