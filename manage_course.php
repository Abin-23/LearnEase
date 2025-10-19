<?php
session_start();
// Security check: redirect non-admins to the homepage.
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: index.php");
    exit;
}

require_once "Course.php";
$course_handler = new Course();

// Use a "flash message" from the session to show success/error after redirect.
$message = '';
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']); // Clear the message after displaying it once
}

// Ensure a valid course ID is provided in the URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: admin.php");
    exit;
}
$course_id = intval($_GET['id']);

// Handle all form submissions (POST requests)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // --- Logic for Adding a New Chapter ---
    if (isset($_POST['add_chapter'])) {
        $chapter_title = trim($_POST['chapter_title']);
        $sort_order = filter_input(INPUT_POST, 'sort_order', FILTER_VALIDATE_INT);
        if (!empty($chapter_title) && $sort_order !== false) {
            $result = $course_handler->addChapter($course_id, $chapter_title, $sort_order);
            $_SESSION['message'] = "<div class='message " . ($result['success'] ? 'success' : 'error') . "'>" . htmlspecialchars($result['message']) . "</div>";
        } else {
             $_SESSION['message'] = "<div class='message error'>Please provide a valid title and sort order.</div>";
        }
    }
    
    // --- Logic for Updating an Existing Chapter ---
    if (isset($_POST['update_chapter'])) {
        $chapter_id = intval($_POST['chapter_id']);
        $title = trim($_POST['title']);
        $sort_order = filter_input(INPUT_POST, 'sort_order', FILTER_VALIDATE_INT);
        if (!empty($title) && $sort_order !== false && $chapter_id > 0) {
            $result = $course_handler->updateChapter($chapter_id, $title, $sort_order);
            $_SESSION['message'] = "<div class='message " . ($result['success'] ? 'success' : 'error') . "'>" . htmlspecialchars($result['message']) . "</div>";
        } else {
            $_SESSION['message'] = "<div class='message error'>Invalid data for chapter update.</div>";
        }
    }
    
    // --- Logic for Adding New Content ---
    if (isset($_POST['add_content'])) {
        $chapter_id = intval($_POST['chapter_id']);
        $content_title = trim($_POST['content_title']);
        $content_type = $_POST['content_type'];
        $content_data = ($content_type === 'youtube') ? trim($_POST['content_path']) : $_FILES['content_file'];
        if (!empty($content_title) && (!empty($content_data) || (isset($content_data['error']) && $content_data['error'] == 0))) {
            $result = $course_handler->addContent($chapter_id, $content_title, $content_type, $content_data);
            $_SESSION['message'] = "<div class='message " . ($result['success'] ? 'success' : 'error') . "'>" . htmlspecialchars($result['message']) . "</div>";
        }
    }
    
    // Redirect back to the same page to prevent resubmission on refresh
    header("Location: manage_course.php?id=" . $course_id);
    exit;
}

// Fetch course data for display
$course = $course_handler->getCourseById($course_id);
$chapters = $course_handler->getChaptersByCourseId($course_id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Course - LearnEase</title>
    <style>
        /* Base Styles */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; overflow-x: hidden; background: #f8f9fa; color: #111; }
        
        /* Navbar */
        .navbar { display: flex; justify-content: space-between; align-items: center; padding: 2rem 8%; position: sticky; width: 100%; top: 0; z-index: 1000; background: rgba(255, 255, 255, 0.8); backdrop-filter: blur(20px); border-bottom: 1px solid #eee; }
        .logo { font-size: 1.5rem; font-weight: 700; color: #111; letter-spacing: -0.5px; text-decoration: none; }
        .nav-btns { display: flex; gap: 1rem; align-items: center; }
        .nav-btns a { padding: 0.6rem 1.5rem; border: none; background: none; cursor: pointer; font-size: 0.95rem; font-weight: 500; transition: all 0.3s ease; border-radius: 8px; text-decoration: none; color: #111; }
        .nav-btns a:hover { background: #f5f5f5; }
        .nav-btns .logout-btn { background: #111; color: #fff; }
        .nav-btns .logout-btn:hover { background: #333; }
        
        /* Page Header */
        .page-header { text-align: center; padding: 4rem 8%; background: #fff; }
        .page-header h1 { font-size: 3.5rem; font-weight: 700; }
        .page-header .highlight { background: linear-gradient(120deg, #667eea, #764ba2); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
        .page-header p { font-size: 1.1rem; color: #666; max-width: 600px; margin: 1rem auto 0; }
        
        /* Form Styles */
        .form-group { margin-bottom: 1.5rem; }
        .form-group label { display: block; margin-bottom: 0.6rem; font-weight: 500; }
        .form-group input, .form-group textarea, .form-group select { width: 100%; padding: 0.9rem 1rem; background: #fafafa; border: 2px solid #eee; border-radius: 10px; font-size: 0.95rem; }
        .form-group input:focus, .form-group textarea:focus, .form-group select:focus { outline: none; border-color: #667eea; }
        .form-btn { width: 100%; padding: 1rem; background: #111; color: #fff; border: none; border-radius: 10px; font-size: 1rem; font-weight: 600; cursor: pointer; transition: all 0.3s ease; }
        .form-btn:hover { background: #333; transform: translateY(-1px); }
        
        /* Message Styles */
        .message { padding: 1rem; margin-bottom: 1.5rem; border-radius: 10px; text-align: center; font-weight: 500; border: 1px solid transparent; }
        .message.success { background: #f0fdf4; color: #166534; border-color: #bbf7d0; }
        .message.error { background: #fef2f2; color: #991b1b; border-color: #fecaca; }
        
        /* Admin-Specific Styles */
        .admin-container { padding: 2rem 8%; max-width: 1200px; margin: 0 auto; }
        .card { background: #fff; padding: 2rem; border-radius: 20px; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.05); margin-bottom: 2rem; }
        .card h2, .card h4 { font-size: 1.8rem; margin-bottom: 1.5rem; }
        .card h4 { font-size: 1.2rem; }
        .content-list { list-style: none; padding: 0; }
        .content-list li { background: #f8f9fa; padding: 0.8rem 1rem; border-radius: 8px; margin-bottom: 0.5rem; display: flex; justify-content: space-between; align-items: center; }
        .content-list em { font-size: 0.8rem; font-style: normal; background: #eee; padding: 0.2rem 0.5rem; border-radius: 5px; }
        .item-actions { display: flex; gap: 10px; align-items: center; }
        .item-actions a, .item-actions button { font-size: 0.8rem; padding: 4px 8px; text-decoration: none; border-radius: 5px; color: white; border: none; cursor: pointer; }
        .item-actions .edit-btn { background: #3498db; }
        .item-actions .delete-btn { background: #e74c3c; }
        
        /* Modal Styles */
        .modal { display: none; position: fixed; z-index: 2000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.5); backdrop-filter: blur(10px); }
        .modal-content { background-color: #fefefe; margin: 10% auto; padding: 2rem; border-radius: 20px; width: 90%; max-width: 600px; position: relative; animation: slideUp 0.4s ease; }
        .close-btn { color: #aaa; position: absolute; top: 1rem; right: 1.5rem; font-size: 28px; font-weight: bold; cursor: pointer; }
        @keyframes slideUp { from { transform: translateY(30px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
    </style>
</head>
<body>
    <nav class="navbar">
        <a href="admin.php" class="logo">LearnEase Admin</a>
        <div class="nav-btns">
            <a href="admin.php">&larr; Back to Dashboard</a>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </nav>
    <header class="page-header">
        <h1>Manage Course</h1>
        <p>You are editing <span class="highlight"><?php echo htmlspecialchars($course['title']); ?></span></p>
    </header>

    <main class="admin-container">
        <?php echo $message; ?>
        <div class="card">
            <h2>Add New Chapter</h2>
            <form action="manage_course.php?id=<?php echo $course_id; ?>" method="POST">
                <div class="form-group">
                    <label>Chapter Title</label>
                    <input type="text" name="chapter_title" required placeholder="e.g., Chapter 1: Introduction">
                </div>
                <div class="form-group">
                    <label>Sort Order</label>
                    <input type="number" name="sort_order" required placeholder="e.g., 10, 20, 30..." value="10">
                </div>
                <button type="submit" name="add_chapter" class="form-btn">Add Chapter</button>
            </form>
        </div>

        <h2>Course Content</h2>
        <?php if (empty($chapters)): ?>
            <div class="card" style="text-align: center;">No chapters yet. Add one above to begin.</div>
        <?php endif; ?>

        <?php foreach ($chapters as $chapter): ?>
            <div class="card">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                    <h2>(<?php echo htmlspecialchars($chapter['sort_order']); ?>) <?php echo htmlspecialchars($chapter['title']); ?></h2>
                    <div class="item-actions">
                        <button class="edit-btn" onclick='openEditChapterModal(<?php echo json_encode($chapter); ?>)'>Edit</button>
                        <form action="delete.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this chapter and all its content?');">
                            <input type="hidden" name="type" value="chapter">
                            <input type="hidden" name="id" value="<?php echo $chapter['id']; ?>">
                            <input type="hidden" name="course_id" value="<?php echo $course_id; ?>">
                            <button type="submit" class="delete-btn">Delete</button>
                        </form>
                    </div>
                </div>

                <?php $contents = $course_handler->getContentByChapterId($chapter['id']); ?>
                <?php if (!empty($contents)): ?>
                    <ul class="content-list">
                        <?php foreach($contents as $content): ?>
                            <li>
                                <strong><?php echo htmlspecialchars($content['title']); ?></strong>
                                <div class="item-actions">
                                    <em><?php echo $content['content_type']; ?></em>
                                    <form action="delete.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this content item?');">
                                        <input type="hidden" name="type" value="content">
                                        <input type="hidden" name="id" value="<?php echo $content['id']; ?>">
                                        <input type="hidden" name="course_id" value="<?php echo $course_id; ?>">
                                        <button type="submit" class="delete-btn">Delete</button>
                                    </form>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>No content added to this chapter yet.</p>
                <?php endif; ?>

                <hr style="margin: 2rem 0; border: 0; border-top: 1px solid #eee;">
                <h4>Add New Content to this Chapter</h4>
                <form action="manage_course.php?id=<?php echo $course_id; ?>" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="chapter_id" value="<?php echo $chapter['id']; ?>">
                    <div class="form-group">
                        <label>Content Title</label>
                        <input type="text" name="content_title" required placeholder="e.g., What is HTML?">
                    </div>
                    <div class="form-group">
                        <label>Content Type</label>
                        <select name="content_type" onchange="toggleContentInput(this)">
                            <option value="youtube">YouTube Video</option>
                            <option value="pdf">PDF File</option>
                        </select>
                    </div>
                    <div class="form-group" data-type="youtube">
                        <label>YouTube Video URL</label>
                        <input type="text" name="content_path">
                    </div>
                    <div class="form-group" data-type="pdf" style="display:none;">
                        <label>Upload PDF</label>
                        <input type="file" name="content_file" accept=".pdf">
                    </div>
                    <button type="submit" name="add_content" class="form-btn">Add Content</button>
                </form>
            </div>
        <?php endforeach; ?>
    </main>

    <div id="editChapterModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeEditChapterModal()">&times;</span>
            <h2>Edit Chapter</h2>
            <form action="manage_course.php?id=<?php echo $course_id; ?>" method="POST">
                <input type="hidden" id="edit_chapter_id" name="chapter_id">
                <div class="form-group">
                    <label>Chapter Title</label>
                    <input type="text" id="edit_chapter_title" name="title" required>
                </div>
                <div class="form-group">
                    <label>Sort Order</label>
                    <input type="number" id="edit_sort_order" name="sort_order" required>
                </div>
                <button type="submit" name="update_chapter" class="form-btn">Update Chapter</button>
            </form>
        </div>
    </div>

    <script>
        // JS for toggling content type input (YouTube/PDF)
        function toggleContentInput(selectElement) {
            const form = selectElement.closest('form');
            const youtubeInput = form.querySelector('[data-type="youtube"]');
            const pdfInput = form.querySelector('[data-type="pdf"]');
            
            if (selectElement.value === 'pdf') {
                youtubeInput.style.display = 'none';
                pdfInput.style.display = 'block';
            } else {
                youtubeInput.style.display = 'block';
                pdfInput.style.display = 'none';
            }
        }

        // JS for the Edit Chapter Modal
        const chapterModal = document.getElementById('editChapterModal');
        function openEditChapterModal(chapter) {
            document.getElementById('edit_chapter_id').value = chapter.id;
            document.getElementById('edit_chapter_title').value = chapter.title;
            document.getElementById('edit_sort_order').value = chapter.sort_order;
            chapterModal.style.display = 'block';
}
        function closeEditChapterModal() {
            chapterModal.style.display = 'none';
        }
        window.addEventListener('click', function(event) {
            if (event.target == chapterModal) {
                closeEditChapterModal();
            }
        });
    </script>
</body>
</html>