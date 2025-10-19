<?php
session_start();
// Security Check: Redirect user to the homepage if they are not logged in.
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

require_once "Course.php";
$course_handler = new Course();
$user_id = $_SESSION['user_id'];

// Fetch all courses this specific user is enrolled in
$enrolled_courses = $course_handler->getUserCourses($user_id);

// Get any message passed from another page (e.g., from view-course.php security check)
$message = '';
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']); // Clear the message after displaying it
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Courses - LearnEase</title>
    <style>
        /* Base and Layout Styles */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #f8f9fa; color: #111; }
        .container { padding: 2rem 8%; max-width: 1600px; margin: 0 auto; }
        
        /* Navbar Styles */
        .navbar { display: flex; justify-content: space-between; align-items: center; padding: 2rem 8%; position: sticky; width: 100%; top: 0; z-index: 1000; background: rgba(255, 255, 255, 0.8); backdrop-filter: blur(20px); border-bottom: 1px solid #eee; }
        .logo { font-size: 1.5rem; font-weight: 700; color: #111; text-decoration: none; }
        .nav-btns { display: flex; gap: 1rem; align-items: center; }
        .nav-btns a { padding: 0.6rem 1.5rem; border-radius: 8px; text-decoration: none; color: #111; font-weight: 500; transition: background 0.2s; }
        .nav-btns a:hover { background: #f0f0f0; }
        .nav-btns .logout-btn { background: #111; color: #fff; }
        .nav-btns .logout-btn:hover { background: #333; }
        
        /* Header Styles */
        .page-header { text-align: center; padding: 4rem 8%; background: #fff; }
        .page-header h1 { font-size: 3.5rem; font-weight: 700; }
        .page-header .highlight { background: linear-gradient(120deg, #667eea, #764ba2); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        
        /* Course Grid and Card Styles */
        .course-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 2rem; }
        .course-card { background: #fff; border-radius: 15px; box-shadow: 0 8px 30px rgba(0,0,0,0.08); overflow: hidden; display: flex; flex-direction: column; }
        .course-card img { width: 100%; height: 180px; object-fit: cover; }
        .course-card-content { padding: 1.5rem; flex-grow: 1; display: flex; flex-direction: column; }
        .course-card-content h3 { font-size: 1.3rem; margin-bottom: 1rem; flex-grow: 1; }
        .start-learning-btn { display: block; text-align: center; background: #667eea; color: #fff; padding: 0.8rem; border-radius: 8px; text-decoration: none; font-weight: 600; margin-top: 1rem; transition: background 0.2s; }
        .start-learning-btn:hover { background: #5a67d8; }

        /* Progress Bar Styles */
        .progress-bar-container { margin-bottom: 1rem; }
        .progress-bar-container p { font-size: 0.9rem; font-weight: 500; color: #555; margin-bottom: 0.5rem; text-align: right; }
        .progress-bar { background: #eee; border-radius: 10px; height: 10px; width: 100%; overflow: hidden; }
        .progress { background: #2ecc71; height: 100%; border-radius: 10px; transition: width 0.5s ease-in-out; }
        
        /* Certificate Button Style */
        .certificate-btn { background: #f1c40f; } /* Gold color for certificate */
        .certificate-btn:hover { background: #f39c12; }
        
        /* Message Styles */
        .message { padding: 1rem; margin-bottom: 1.5rem; border-radius: 10px; text-align: center; font-weight: 500; border: 1px solid transparent; }
        .message.error { background: #fef2f2; color: #991b1b; border-color: #fecaca; }
    </style>
</head>
<body>

    <nav class="navbar">
        <a href="index.php" class="logo">LearnEase</a>
        <div class="nav-btns">
            <a href="courses.php">Browse Courses</a>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </nav>

    <header class="page-header">
        <h1>My Learning <span class="highlight">Dashboard</span></h1>
    </header>

    <main class="container">
        
        <?php echo $message; // Display any messages from redirects ?>

        <?php if (empty($enrolled_courses)): ?>
            <div class="card" style="text-align:center; padding: 3rem;">
                <h2>You haven't enrolled in any courses yet.</h2>
                <p style="margin-top: 1rem;">Start your learning journey today!</p>
                <a href="courses.php" style="margin-top: 1.5rem; display: inline-block;" class="start-learning-btn">Browse Courses</a>
            </div>
        <?php else: ?>
            <div class="course-grid">
                <?php foreach ($enrolled_courses as $course): ?>
                    <div class="course-card">
                        <img src="<?php echo htmlspecialchars($course['image']); ?>" alt="Course Thumbnail">
                        <div class="course-card-content">
                            <h3><?php echo htmlspecialchars($course['title']); ?></h3>
                            
                            <?php $progress = $course_handler->getCourseProgress($user_id, $course['id']); ?>
                            <div class="progress-bar-container">
                                <p><?php echo round($progress); ?>% Complete</p>
                                <div class="progress-bar">
                                    <div class="progress" style="width: <?php echo round($progress); ?>%;"></div>
                                </div>
                            </div>
                            
                            <?php if ($progress >= 100): ?>
                                <a href="certificate.php?id=<?php echo $course['id']; ?>" class="start-learning-btn certificate-btn">View Certificate</a>
                            <?php else: ?>
                                <a href="view-course.php?id=<?php echo $course['id']; ?>" class="start-learning-btn">Continue Learning</a>
                            <?php endif; ?>

                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

</body>
</html>