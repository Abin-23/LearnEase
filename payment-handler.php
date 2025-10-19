<?php
session_start();
require('config.php');
require('razorpay-php/Razorpay.php');
require('Course.php');
use Razorpay\Api\Api;
use Razorpay\Api\Errors\SignatureVerificationError;

$success = true;
$error = "Payment Failed";

if (empty($_POST['razorpay_payment_id']) === false) {
    $api = new Api($keyId, $keySecret);
    try {
        $attributes = [
            'razorpay_order_id' => $_SESSION['razorpay_order_id'],
            'razorpay_payment_id' => $_POST['razorpay_payment_id'],
            'razorpay_signature' => $_POST['razorpay_signature']
        ];
        $api->utility->verifyPaymentSignature($attributes);
    } catch(SignatureVerificationError $e) {
        $success = false;
        $error = 'Razorpay Error : ' . $e->getMessage();
    }
}

if ($success === true) {
    // Payment was successful. Now enroll the user.
    $user_id = $_SESSION['user_id'];
    $course_ids = $_SESSION['cart'];
    $payment_id = $_POST['razorpay_payment_id'];
    
    $course_handler = new Course();
    $result = $course_handler->enrollUser($user_id, $course_ids, $payment_id);
    
    if ($result['success']) {
        // Clear the cart
        unset($_SESSION['cart']);
        unset($_SESSION['razorpay_order_id']);
        
        // Redirect to My Courses page
        header("Location: my-courses.php");
        exit;
    } else {
        // Handle database enrollment error
        echo "Error: Could not enroll you in the course. Please contact support.";
    }
} else {
    // Payment failed
    echo "Payment failed: " . $error;
}