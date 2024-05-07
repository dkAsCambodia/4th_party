<?php
function iPay88_signature($source)
{
    return base64_encode(hex2bin(sha1($source)));
}

if (!function_exists('hex2bin')) {
    function hex2bin($hexSource)
    {
        $bin = '';
        for ($i = 0; $i < strlen($hexSource); $i = $i + 2) {
            $bin .= chr(hexdec(substr($hexSource, $i, 2)));
        }
        return $bin;
    }
}
$merchantKey = $data->MERCHANT_KEY;
$merchantCode = $data->MERCHANT_CODE;
$paymentId = $data->PAYMENT_ID;
$refNo = 'A' . rand(100000, 999999);
$amount = $data->amount;
$currency = 'USD';
$prodDesc = 'Product Description';
$userName = $data->customer_name;
$userEmail = 'test@mail.com';
$userContact = '12452354';
$remark = '';
$lang = 'UTF-8';
$responseURL = url('api/ipay-response');
$backEndURL = url('ipay-backend');
$signature = iPay88_signature($merchantKey . $merchantCode . $refNo . str_replace(',', '', str_replace('.', '', $amount)) . $currency);
$pre_sign = $data->pre_sign;

?>
Redirect...

<form style="visibility: hidden" method="post" name="ePayment" id="ePayment" action="{{ $data->url }}">
    <input type="hidden" name="MerchantCode" value="{{ $merchantCode }}">
    <input type="hidden" name="PaymentId" value="{{ $paymentId }}">
    <input type="hidden" id="service" value="ipay88">
    <input type="hidden" id="method" value="alipay">
    <input type="hidden" name="RefNo" value="{{ $refNo }}">
    <input type="hidden" name="Amount" value="{{ $amount }}">
    <input type="hidden" name="Currency" value="{{ $currency }}">
    <input type="hidden" name="ProdDesc" value="{{ $prodDesc }}">
    <input type="hidden" name="UserName" value="{{ $userName }}">
    <input type="hidden" name="UserEmail" value="{{ $userEmail }}">
    <input type="hidden" name="UserContact" value="{{ $userContact }}">
    <input type="hidden" name="Remark" value="">
    <input type="hidden" name="Lang" value="{{ $lang }}">
    <input type="hidden" name="Signature" value="{{ $signature }}">
    <input type="hidden" name="ResponseURL" value="{{ $responseURL }}">
    <input type="hidden" name="BackendURL" value="{{ $backEndURL }}">
    <input type="submit">
</form>

<script type="text/javascript">
    window.onload=function(){
        function submitform(){
          document.forms["ePayment"].submit();
        }
        submitform();
    }
</script>
