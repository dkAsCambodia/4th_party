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
// $apiUrl = "http://127.0.0.1:8000/api/xpz/deposit";
$apiUrl = "https://payment.pay2rax.com/api/xpz/deposit";
$data = [
    'merchant_code' => $_GET['merchant_code'], 
    'product_id' => '21',
    'referenceId' => $referenceNo, 
    // 'callback_url' => 'http://127.0.0.1:8000/api/xpz/depositResponse',
    'callback_url' => 'https://payment.pay2rax.com/api/xpz/depositResponse',
    'Currency' => $_GET['Currency'],
    'amount' => $_GET['amount'], 
    'customer_name' => $_GET['card_holder_name'],    // account holder name 
    'card_number' => $_GET['card_number'],
    'expiryMonth' => $_GET['expiryMonth'],
    'expiryYear' => $_GET['expiryYear'],
    'cvv' => $_GET['cvv'],   
];
$fullUrl = $apiUrl . '?' . http_build_query($data);
?>
<script>
    window.location.href = '<?php echo $fullUrl; ?>';
</script>