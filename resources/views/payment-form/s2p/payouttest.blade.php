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
// $apiUrl = "http://127.0.0.1:8000/api/s2p/payout";
$apiUrl = "https://payment.implogix.com/api/s2p/payout";
$data = [
    'merchant_code' => $_GET['merchant_code'], 
    'product_id' => '20',
    'referenceId' => $referenceNo, 
    // 'callback_url' => 'http://127.0.0.1:8000/api/s2p/payout/callbackURL',
    'callback_url' => 'https://payment.implogix.com/api/s2p/payout/callbackURL',
    'Currency' => 'THB', 
    'amount' => $_GET['amount'], 
    'customer_name' => $_GET['customer_name'], 
    'bank_code' => $_GET['bank_code'], 
    'customer_account_number' => $_GET['customer_account_number'], 
];
$fullUrl = $apiUrl . '?' . http_build_query($data);
?>
<script>
    window.location.href = '<?php echo $fullUrl; ?>';
</script>