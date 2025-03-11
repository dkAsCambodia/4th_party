<script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/4.1.1/crypto-js.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.js"></script>
  
    <form action="{{ route('apiroute.m2p.callWithdarwAPI') }}" method="POST" class="form-horizontal">
    <input  id="secretKey" name="secretKey" type="hidden" value="{{ $res['secretKey']}}">
    <input  name="Reference" type="hidden" value="{{ $res['Reference']}}">
    <input name="api_url" id="api_url" type="hidden" value="{{ $res['api_url']}}">

    <input  name="address" type="hidden" value="{{ $res['address']}}">
    <input id="amount" name="amount" type="hidden" value="{{ $res['amount']}}">
    <input name="apiToken" id="apiToken" type="hidden" value="{{ $res['apiToken']}}">
    <input  name="callbackUrl" id="callbackUrl" type="hidden" value="{{ $res['callbackUrl'] }}">
    <input id="currency" name="currency" type="hidden" value="{{ $res['currency']}}">
    <input name="paymentGatewayName" id="paymentGatewayName" type="hidden" value="USDT TRC20">
    <input name="signature" id="signature" type="hidden" value="" readonly>
    <input name="timestamp" id="timestamp" type="hidden" value="<?php echo time(); ?>">
    <input name="withdrawCurrency" id="withdrawCurrency" type="hidden" value="USX">       
    <input name="tradingAccountLogin" id="tradingAccountLogin" type="hidden" value="">       
    <button id="cartCheckout" name="" class="btn btn-primary" OnClick="generatecontrol(this.form);" style="display:none;">Submit</button>
    </form>
       
<script>
      jQuery(function(){
           jQuery('#cartCheckout').click();
       });  
  function generatecontrol(pform)
      {
        if(pform.currency.value==''){
            exit;
        }
   // {"address": "amount": "apiToken" : "callbackUrl" : "currency" :"paymentGatewayName" :"timestamp":"withdrawCurrency" }
   var finalString  =   pform.address.value+
                        pform.amount.value+
                        pform.apiToken.value+
                        pform.callbackUrl.value+
                        pform.currency.value+
                        pform.paymentGatewayName.value+
                        pform.timestamp.value+
                        pform.withdrawCurrency.value+
                        pform.secretKey.value;  
        // var secretKey='mI7SosPOnQBJ8L8eBrPvCSP7fqD1X5T9GNKZ';
        // var finalString=s+secretKey;
          // Calculate the SHA-384 hash
        var hash = CryptoJS.SHA384(finalString);
        // Get the hexadecimal representation of the hash
        var hexHash = hash.toString(CryptoJS.enc.Hex);
          pform.signature.value = hexHash;
          pform.submit();
      }
</script>


