<?php
    require('razorpay-php/Razorpay.php');
    use Razorpay\Api\Api;

    $key_id = "rzp_test_RekL5yopva8DCU";
    $key_secret = "S2NxUG3FKnBS9Chem1DEDreY";

    $api = new Api($key_id, $key_secret);

    $payment_id = $_GET['payment_id'];
    $order_id = $_GET['order_id'];
    $signature = $_GET['signature'];

    try {
        $attributes = [
            'razorpay_order_id' => $order_id,
            'razorpay_payment_id' => $payment_id,
            'razorpay_signature' => $signature
        ];

        $api->utility->verifyPaymentSignature($attributes);

        echo "<h2>Payment Successful </h2>";
        echo "Payment ID: $payment_id";

        // Store into DB here

    } catch(SignatureVerificationError $e) {
        echo "<h2>Payment Failed </h2>";
        echo $e->getMessage();
    }
?>
