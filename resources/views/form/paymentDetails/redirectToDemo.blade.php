Loding...
<form style="visibility: hidden" name="myForm" id="myForm" action="{{ $paymentDetail->callback_url }}" method="GET">

    <input type="text" placeholder="merchant_code" name="merchant_code" value="{{ $paymentDetail->merchant_code }}">
    <input type="text" placeholder="transaction_id" name="transaction_id" value="{{ $paymentDetail->fourth_party_transection }}">
    <input type="text" placeholder="amount" name="amount" value="{{ $paymentDetail->amount }}">
    <input type="text" placeholder="customer_name" name="customer_name" value="{{ $paymentDetail->customer_name }}">
    <!-- <input type="text" placeholder="payment_channel" name="payment_channel" value="{{ $paymentDetail->payment_channel }}"> -->
    <input type="text" placeholder="payment_method" name="payment_method" value="{{ $paymentDetail->payment_method }}">
    {{-- <input type="text" placeholder="payment_source" name="payment_source" value="{{ $paymentDetail->payment_source }}"> --}}
    <input type="text" placeholder="product_id" name="product_id" value="{{ $paymentDetail->product_id }}">
    <!-- <input type="text" placeholder="order_id" name="order_id" value="{{ $paymentDetail->order_id }}"> -->
    <input type="text" placeholder="order_date" name="order_date" value="{{ $paymentDetail->order_date }}">

    <input type="text" placeholder="order_status" name="order_status" value="{{ $paymentDetail->order_status }}">
    <input type="text" placeholder="Currency" name="Currency" value="{{ $paymentDetail->Currency }}">
    <input type="text" placeholder="merchant_trans_id" name="merchant_trans_id" value="{{ $paymentDetail->transaction_id }}">

    <!-- <input type="text" placeholder="Status" name="Status" value="{{ $paymentDetail->Status }}"> -->
    <input type="text" placeholder="ErrDesc" name="ErrDesc" value="{{ $paymentDetail->ErrDesc }}">
    <input type="text" placeholder="payment_status" name="payment_status" value="{{ $paymentDetail->payment_status }}">

    <input type="submit" value="Submit" />
</form>
<script type="text/javascript">
    window.onload=function(){
        function submitform(){
          document.forms["myForm"].submit();
        }
        submitform();
    }
</script>
