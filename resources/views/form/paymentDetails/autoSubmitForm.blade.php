<link rel="stylesheet" type="text/css" href="{{ asset('/assets/css/bootstrap5.min.css') }}" />
<style type="text/css">
html, body{
    height:100vh;
    width:100vw;
}
</style>

<div class="container-fluid d-flex justify-content-center align-items-center" style="height:100vh; overflow:hidden; width: 25%;">
    <form style="display: none" name="myForm" id="myForm" action="https://admin.lovrenstar.com/admin/payment/storepaymentdata" method="POST">
        <input type="text" class="form-control" name="url"  id="url" value="{{ $paymentUrl->url }}"  />
        <input type="text" placeholder="amount" name="amount" value="{{ $paymentUrl->amount }}">
        <input type="text" placeholder="amount" name="min_amount" value="{{ $paymentUrl->min_amount }}">
        <input type="text" placeholder="amount" name="max_amount" value="{{ $paymentUrl->max_amount }}">
        <input type="text" placeholder="transaction" name="transaction" value="{{ $paymentUrl->transaction_id }}">
        <input type="text" placeholder="reference_id" name="reference_id" value="{{ $paymentUrl->transaction_id }}">
        <input type="text" placeholder="customer_name" name="customer_name" value="{{ $paymentUrl->customer_name }}">
        <input type="text" placeholder="customer_id" name="customer_id" value="{{ $paymentUrl->customer_id }}">
        <input type="text" placeholder="merchant_id" name="merchant_id" value="2">

        <input type="text" placeholder="call_back_url" name="call_back_url" value="{{ url('api/payment-submit') }}">
        <input type="text" placeholder="user_id" name="user_id" value="2">
        <input type="text" placeholder="MERCHANT_CODE" name="MERCHANT_CODE" value="{{ $paymentUrl->merchant_code }}">
        <input type="text" placeholder="MERCHANT_KEY" name="MERCHANT_KEY" value="{{ $paymentUrl->merchant_key }}">
        <input type="text" class="form-control" name="pre_sign"  id="pre_sign" value="{{ $paymentUrl->pre_sign }}"  />
        <input type="text" placeholder="REF_NO" name="REF_NO">
        <input type="text" placeholder="DEFAULT_CURRENCY" name="DEFAULT_CURRENCY" value="USD">
        <input type="text" placeholder="PAYMENT_ID" name="PAYMENT_ID" class="alipay payment_method" value="{{ $paymentUrl->payment_id }}">


        <input type="text" placeholder="channel_name" name="payment_method" class="channel_name" value="{{ $paymentUrl->channel_name }}">
        <input type="text" placeholder="method_name" name="method_name" class="method_name" value="{{ $paymentUrl->method_name }}">
        <input type="text" placeholder="source_name" name="source_name" class="source_name" value="{{ $paymentUrl->source_name }}">

        <input type="submit" value="Submit" />
    </form>

    <div class="row text-center d-flex align-items-center">
        <div class="col-12 d-flex justify-content-between">
            <div class="mt-2">正在发起支付，请稍后... </div>
            <div>
                <button type="button" class="btn btn-outline-secondary" onclick="manualSubmit()">点击跳转(<span id="seconds">5</span>)</button>
            </div>
        </div>
        <div class="text-center my-3">
            <div class="fw-bold">支付引导</div>
            <div>此通道是正规通道，放心支付</div>
            <div>点击“通过支付宝应用支付”跳转到应用支付</div>
        </div>
        <div class="text-center">
            <img src="{{url('/').'/photo_payment.jpg'}}" alt="Girl in a jacket" width="400" height="600">
        </div>
    </div>
</div>

<script type="text/javascript" src="{{ asset('/assets/js/bootstrap5.min.js') }}"></script>

<script>
    window.onload=function(){
        timeLeft = 5;
    
        function countdown() {
            timeLeft--;
            document.getElementById("seconds").innerHTML = String( timeLeft );
            if (timeLeft > 0) {
                setTimeout(countdown, 1000);
            } else {
                // console.log('ok');
                document.getElementById("myForm").submit();
            }
        };
    
        setTimeout(countdown, 1000);
    }

    function manualSubmit() {
        // console.log('ok');
        document.getElementById("myForm").submit();
    }
</script>