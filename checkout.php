<?php
session_start();
require 'config.php';
require 'razorpay-php/Razorpay.php';
require 'Course.php';
use Razorpay\Api\Api;

// Redirect if user is not logged in or cart is empty
if (!isset($_SESSION['user_id']) || empty($_SESSION['cart'])) {
    header('Location: cart.php');
    exit();
}

$api = new Api($keyId, $keySecret);
$course_handler = new Course();
$total_price = 0;
foreach ($_SESSION['cart'] as $course_id) {
    $course = $course_handler->getCourseById($course_id);
    if($course) $total_price += $course['price'];
}
$orderAmount = $total_price * 100; // Amount in paise

// Create the Razorpay Order
$orderData = [
    'receipt'         => uniqid(),
    'amount'          => $orderAmount,
    'currency'        => 'INR',
    'payment_capture' => 1 // Auto capture
];
$razorpayOrder = $api->order->create($orderData);
$razorpayOrderId = $razorpayOrder['id'];
$_SESSION['razorpay_order_id'] = $razorpayOrderId;

$displayAmount = $amount = $orderData['amount'];
$data = [
    "key"               => $keyId,
    "amount"            => $amount,
    "name"              => "LearnEase",
    "description"       => "Online Course Purchase",
    "image"             => "https://i.imgur.com/3g7cmhA.png", // A logo for the checkout form
    "prefill"           => [
        "name"              => $_SESSION['user_name'],
        "email"             => "customer@example.com", // You should fetch customer email from DB
    ],
    "theme"             => [
        "color"             => "#667eea"
    ],
    "order_id"          => $razorpayOrderId,
];
$json = json_encode($data);
?>

<p>Please wait, redirecting to payment gateway...</p>
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<form name='razorpayform' action="payment-handler.php" method="POST">
    <input type="hidden" name="razorpay_payment_id" id="razorpay_payment_id">
    <input type="hidden" name="razorpay_signature"  id="razorpay_signature" >
</form>
<script>
var options = <?php echo $json?>;
options.handler = function (response){
    document.getElementById('razorpay_payment_id').value = response.razorpay_payment_id;
    document.getElementById('razorpay_signature').value = response.razorpay_signature;
    document.razorpayform.submit();
};
options.modal = {
    ondismiss: function(){
        // Redirect back to cart if user closes the payment window
        window.location.href = 'cart.php';
    }
};
var rzp = new Razorpay(options);
rzp.open();
</script>