<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_POST['content_id'])) {
    // Respond with an error if the user is not logged in or no content ID is sent
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    exit;
}

require_once "Course.php";
$course_handler = new Course();
$user_id = $_SESSION['user_id'];
$content_id = intval($_POST['content_id']);

// Mark the content as completed in the database
$result = $course_handler->markContentAsCompleted($user_id, $content_id);

header('Content-Type: application/json');
echo json_encode($result);