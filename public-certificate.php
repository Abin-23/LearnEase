<?php
// This page has no session checks, it's public.
if (!isset($_GET['token']) || empty($_GET['token'])) {
    die("Invalid certificate link.");
}

require_once "Course.php";
$course_handler = new Course();
$token = $_GET['token'];

// Fetch the certificate data using the unique token
$enrollment_data = $course_handler->getEnrollmentByToken($token);

if (!$enrollment_data) {
    die("Certificate not found or the link is invalid.");
}

$user_name = $enrollment_data['user_name'];
$course_title = $enrollment_data['course_title'];
// Use the enrollment date as the completion date
$completion_date = date('F j, Y', strtotime($enrollment_data['enrollment_date']));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Certificate of Completion</title>
    <style>
        body { font-family: 'Georgia', serif; display: flex; justify-content: center; align-items: center; min-height: 100vh; background: #f0f0f0; padding: 20px; }
        .certificate { width: 800px; height: 550px; border: 10px solid #667eea; padding: 50px; text-align: center; background: #fff; position: relative; }
        .logo { font-size: 2rem; font-weight: bold; font-family: sans-serif; }
        .cert-title { font-size: 3rem; margin: 40px 0; color: #333; }
        .cert-subtitle { font-size: 1.5rem; }
        .recipient-name { font-size: 2.5rem; border-bottom: 2px solid #333; padding-bottom: 10px; margin: 50px auto; display: inline-block; }
        .course-name { font-size: 1.8rem; font-style: italic; color: #667eea; }
        .footer { position: absolute; bottom: 50px; width: calc(100% - 100px); left: 50px; display: flex; justify-content: space-around; }
        .footer-item { border-top: 1px solid #333; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="certificate">
        <div class="logo">LearnEase</div>
        <div class="cert-subtitle">Certificate of Completion</div>
        <div class="cert-title">CERTIFICATE</div>
        <div class="cert-subtitle">This certifies that</div>
        <div class="recipient-name"><?php echo htmlspecialchars($user_name); ?></div>
        <div class="cert-subtitle">has successfully completed the course</div>
        <div class="course-name"><?php echo htmlspecialchars($course_title); ?></div>
        <div class="footer">
            <div class="footer-item">Date: <?php echo $completion_date; ?></div>
            <div class="footer-item">Issuing Authority</div>
        </div>
    </div>
</body>
</html>