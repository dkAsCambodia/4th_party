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
// $apiUrl = "http://127.0.0.1:8000/api/s2p/payin";
$apiUrl = "https://payment.implogix.com/api/s2p/payin";
$data = [
    'merchant_code' => $_GET['merchant_code'], 
    'product_id' => '19',
    'referenceId' => $referenceNo, 
    // 'callback_url' => 'http://127.0.0.1:8000/api/s2p/payin/callbackURL',
    'callback_url' => 'https://payment.implogix.com/api/s2p/payin/callbackURL',
    'Currency' => 'THB', 
    'amount' => $_GET['amount'], 
    'customer_name' => $_GET['customer_name'], 
    'bank_code' => $_GET['bank_code'], 
    'customer_account_number' => $_GET['customer_account_number'], 
];
$fullUrl = $apiUrl . '?' . http_build_query($data);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Processing...</title>
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            font-family: Arial, sans-serif;
            background: #000;
        }
        .loading-container { text-align: center; }
        .loading-container img { width: 100px; height: 100px;}
        p{ color:white; }
    </style>
</head>
<body>
    <div class="loading-container">
        <img src="https://i.gifer.com/ZZ5H.gif" alt="Loading...">
        <p>Processing, please wait...</p>
    </div>
    <script>
        setTimeout(() => {
            window.location.href = '<?php echo $fullUrl; ?>';
        }, 2000); // 2-second delay for demo purposes (optional)
    </script>
</body>
</html>
