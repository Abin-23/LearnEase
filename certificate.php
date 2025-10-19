<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit; }
require_once "Course.php";
$course_handler = new Course();
$user_id = $_SESSION['user_id'];
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) { header("Location: my-courses.php"); exit; }
$course_id = intval($_GET['id']);

if ($course_handler->getCourseProgress($user_id, $course_id) < 100) {
    header("Location: my-courses.php");
    exit;
}

$course = $course_handler->getCourseById($course_id);
$user_name = $_SESSION['user_name'];
$completion_date = date('F j, Y');

// NEW: Generate and get the certificate token
$token = $course_handler->generateCertificateToken($user_id, $course_id);
$shareable_link = "http://" . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . "/public-certificate.php?token=" . $token;
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
        
        /* NEW: Share Button Styles */
        .share-section { position: absolute; top: 20px; right: 20px; text-align: right; }
        .share-btn { background: #3498db; color: white; border: none; padding: 8px 15px; border-radius: 5px; cursor: pointer; font-family: sans-serif; font-size: 14px; }
        .share-btn:hover { background: #2980b9; }
    </style>
</head>
<body>
    <div class="certificate">
        <div class="share-section">
            <button class="share-btn" onclick="copyLink()">📋 Copy Share Link</button>
            <input type="text" value="<?php echo htmlspecialchars($shareable_link); ?>" id="shareLink" style="position: absolute; left: -9999px;">
        </div>

        <div class="logo">LearnEase</div>
        <div class="cert-subtitle">Certificate of Completion</div>
        <div class="cert-title">CERTIFICATE</div>
        <div class="cert-subtitle">This certifies that</div>
        <div class="recipient-name"><?php echo htmlspecialchars($user_name); ?></div>
        <div class="cert-subtitle">has successfully completed the course</div>
        <div class="course-name"><?php echo htmlspecialchars($course['title']); ?></div>
        <div class="footer">
            <div class="footer-item">Date: <?php echo $completion_date; ?></div>
            <div class="footer-item">Issuing Authority</div>
        </div>
    </div>

    <script>
        function copyLink() {
            const linkInput = document.getElementById('shareLink');
            linkInput.select(); // Select the text
            linkInput.setSelectionRange(0, 99999); // For mobile devices

            try {
                // Use the modern Clipboard API
                navigator.clipboard.writeText(linkInput.value).then(function() {
                    alert('Certificate link copied to clipboard!');
                });
            } catch (err) {
                // Fallback for older browsers
                document.execCommand('copy');
                alert('Certificate link copied to clipboard!');
            }
        }
    </script>
</body>
</html>