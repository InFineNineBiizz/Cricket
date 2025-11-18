<?php
require('razorpay-php/Razorpay.php');
use Razorpay\Api\Api;

$key_id = "rzp_test_RekL5yopva8DCU";
$key_secret = "S2NxUG3FKnBS9Chem1DEDreY";

$api = new Api($key_id, $key_secret);

$orderData = [
    'receipt'         => 'order_rcptid_11',
    'amount'          => 500 * 100,   // 500 INR
    'currency'        => 'INR',
    'payment_capture' => 1            // auto capture
];

$order = $api->order->create($orderData);

$order_id = $order['id'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Razorpay Payment</title>
</head>
<body>

<button id="rzpBtn">Pay Now</button>

<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
var options = {
    "key": "<?php echo $key_id; ?>",
    "amount": "50000",
    "currency": "INR",
    "name": "Test Company",
    "description": "Test Transaction",
    "order_id": "<?php echo $order_id; ?>",

    "handler": function (response){
        // After success → send to success page
        window.location.href = 
        "success.php?payment_id=" + response.razorpay_payment_id +
        "&order_id=" + response.razorpay_order_id +
        "&signature=" + response.razorpay_signature;
    },

    "prefill": {
        "name": "Test User",
        "email": "test@example.com",
        "contact": "9999999999"
    },

    "theme": {
        "color": "#3399cc"
    }
};
var rzp1 = new Razorpay(options);

document.getElementById('rzpBtn').onclick = function(e){
    rzp1.open();
    e.preventDefault();
}
</script>

</body>
</html>
