<?php
session_start();
// Security Check 1: Ensure user is logged in.
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

require_once "Course.php";
$course_handler = new Course();
$user_id = $_SESSION['user_id'];

// Security Check 2: Ensure a valid course ID is provided in the URL.
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: my-courses.php");
    exit;
}
$course_id = intval($_GET['id']);

// Security Check 3: Verify the logged-in user is actually enrolled in this course.
if (!$course_handler->isUserEnrolled($user_id, $course_id)) {
    // If not enrolled, set an error message and redirect them away.
    $_SESSION['message'] = "<div class='message error'>You do not have permission to view this course.</div>";
    header("Location: my-courses.php");
    exit;
}

// If all security checks pass, fetch the course data for display.
$course = $course_handler->getCourseById($course_id);
$chapters = $course_handler->getChaptersByCourseId($course_id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($course['title']); ?> - LearnEase</title>
    <style>
        /* Base and Layout Styles */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #f8f9fa; color: #111; }
        .container { padding: 2rem 8%; max-width: 1600px; margin: 0 auto; }

        /* Navbar Styles */
        .navbar { display: flex; justify-content: space-between; align-items: center; padding: 2rem 8%; position: sticky; width: 100%; top: 0; z-index: 1000; background: rgba(255, 255, 255, 0.8); backdrop-filter: blur(20px); border-bottom: 1px solid #eee; }
        .logo { font-size: 1.5rem; font-weight: 700; color: #111; text-decoration: none; }
        .nav-btns a { padding: 0.6rem 1.5rem; border-radius: 8px; text-decoration: none; color: #111; font-weight: 500; transition: background 0.2s; }
        .nav-btns a:hover { background: #f0f0f0; }

        /* Header Styles */
        .page-header { text-align: center; padding: 4rem 8%; background: #fff; }
        .page-header h1 { font-size: 3.5rem; font-weight: 700; }
        .page-header .highlight { background: linear-gradient(120deg, #667eea, #764ba2); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }

        /* Course Viewer Specific Styles */
        .course-viewer { display: flex; flex-direction: column; gap: 2rem; }
        @media (min-width: 992px) { .course-viewer { flex-direction: row; align-items: flex-start; } }

        .sidebar { flex: 1; min-width: 300px; background: #fff; border-radius: 20px; padding: 1.5rem; box-shadow: 0 10px 40px rgba(0,0,0,0.05); }
        .sidebar h2 { font-size: 1.5rem; margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 1px solid #eee; }
        
        .main-content { flex: 3; background: #fff; border-radius: 20px; box-shadow: 0 10px 40px rgba(0,0,0,0.05); overflow: hidden; }
        
        .chapter-group { margin-bottom: 1.5rem; }
        .chapter-group h3 { font-size: 1.2rem; margin-bottom: 1rem; color: #333; }
        
        .content-item { display: block; padding: 0.8rem 1rem; margin-bottom: 0.5rem; border-radius: 8px; text-decoration: none; color: #333; transition: background 0.2s, color 0.2s; font-weight: 500; border: 1px solid transparent; }
        .content-item:hover { background: #f9fafb; border-color: #eee; }
        .content-item.active { background: #f0f2fe; color: #667eea; font-weight: 600; border-color: #c7d2fe; }
        
        #content-title { font-size: 1.8rem; font-weight: 600; margin-bottom: 1.5rem; padding: 2rem 2rem 0; line-height: 1.3; }
        
        #content-display-wrapper { position: relative; padding-bottom: 56.25%; /* 16:9 Aspect Ratio */ height: 0; background: #000; }
        #content-display { position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: none; }

        .complete-btn { display: none; padding: 0 2rem 1.5rem; text-align: right; }
        .complete-btn button { background: #2ecc71; color: white; padding: 0.8rem 1.5rem; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 1rem; transition: background 0.2s; }
        .complete-btn button:hover { background: #27ae60; }
        .complete-btn button:disabled { background: #95a5a6; cursor: not-allowed; }
    </style>
</head>
<body>
    <nav class="navbar">
        <a href="index.php" class="logo">LearnEase</a>
        <div class="nav-btns">
            <a href="my-courses.php">&larr; Back to My Courses</a>
        </div>
    </nav>
    <header class="page-header">
        <h1><?php echo htmlspecialchars($course['title']); ?></h1>
    </header>
    <main class="container course-viewer">
        <aside class="sidebar">
            <h2>Course Curriculum</h2>
            <?php foreach ($chapters as $chapter): ?>
                <div class="chapter-group">
                    <h3><?php echo htmlspecialchars($chapter['title']); ?></h3>
                    <?php $contents = $course_handler->getContentByChapterId($chapter['id']); ?>
                    <?php foreach ($contents as $content): ?>
                        <a href="#" class="content-item" onclick="loadContent(event, '<?php echo $content['content_type']; ?>', '<?php echo htmlspecialchars($content['content_path']); ?>', '<?php echo htmlspecialchars($content['title']); ?>', <?php echo $content['id']; ?>)">
                            ▶ <?php echo htmlspecialchars($content['title']); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        </aside>
        <section class="main-content">
            <h2 id="content-title">Welcome! Select a lesson from the curriculum to begin.</h2>
            <div id="content-display-wrapper">
                <iframe id="content-display" src="about:blank" allow="fullscreen; picture-in-picture"></iframe>
            </div>
            <div id="complete-button-wrapper" class="complete-btn">
                <button onclick="markComplete()">Mark as Complete</button>
            </div>
        </section>
    </main>

    <script>
        let currentContentId = null; // Variable to store the ID of the current lesson

        function loadContent(event, type, path, title, contentId) {
            event.preventDefault();
            const displayFrame = document.getElementById('content-display');
            const contentTitle = document.getElementById('content-title');
            
            // Update the title above the content
            contentTitle.textContent = title;

            let embedUrl = 'about:blank'; // Default to a blank page if URL is invalid

            if (type === 'youtube') {
                // Regular expression to find the YouTube video ID from various URL formats
                const youtubeRegex = /(?:https?:\/\/)?(?:www\.)?(?:youtube\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?)\/|\S*?[?&]v=)|youtu\.be\/)([a-zA-Z0-9_-]{11})/;
                const match = path.match(youtubeRegex);
                if (match && match[1]) {
                    const videoId = match[1];
                    embedUrl = `https://www.youtube.com/embed/${videoId}?autoplay=1&rel=0`;
                } else {
                    contentTitle.textContent = 'Error: Invalid YouTube URL provided.';
                }
            } else if (type === 'pdf') {
                embedUrl = path;
            }
            
            displayFrame.src = embedUrl;

            // Store the current content ID for the completion button
            currentContentId = contentId;
            
            // Show and reset the "Mark as Complete" button
            const completeBtnWrapper = document.getElementById('complete-button-wrapper');
            completeBtnWrapper.style.display = 'block';
            const button = completeBtnWrapper.querySelector('button');
            button.disabled = false;
            button.textContent = 'Mark as Complete';

            // Highlight the currently active lesson in the sidebar
            document.querySelectorAll('.content-item').forEach(item => item.classList.remove('active'));
            event.currentTarget.classList.add('active');
        }

        async function markComplete() {
            if (!currentContentId) return;

            const button = document.getElementById('complete-button-wrapper').querySelector('button');
            button.disabled = true;
            button.textContent = 'Saving...';

            // Send the request to the server in the background using the Fetch API
            const formData = new FormData();
            formData.append('content_id', currentContentId);

            try {
                const response = await fetch('progress-handler.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                if (result.success) {
                    button.textContent = 'Completed ✔';
                } else {
                    button.textContent = 'Error! Try Again';
                    button.disabled = false;
                }
            } catch (error) {
                console.error('Error:', error);
                button.textContent = 'Error! Try Again';
                button.disabled = false;
            }
        }
    </script>
</body>
</html>