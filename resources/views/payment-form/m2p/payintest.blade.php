<?php
$referenceNo = "GZTRN" . time() . (function ($length = 3) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
})();
// $apiUrl = "http://127.0.0.1:8000/api/m2p/payin";
$apiUrl = "https://payment.implogix.com/api/m2p/payin";
$data = [
    'merchant_code' => $_GET['merchant_code'], 
    'product_id' => '9',
    'referenceId' => $referenceNo, 
    // 'callback_url' => 'http://127.0.0.1:8000/api/m2p/payin/callbackURL',
    'callback_url' => 'https://payment.implogix.com/api/m2p/payin/callbackURL',
    'Currency' => $_GET['Currency'], 
    'amount' => $_GET['amount'], 
    'customer_name' => 'dk testing m2p', 
];
$fullUrl = $apiUrl . '?' . http_build_query($data);
?>
<script>
    window.location.href = '<?php echo $fullUrl; ?>';
</script>