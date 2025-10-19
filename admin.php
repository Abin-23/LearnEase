<?php
session_start();
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: index.php");
    exit;
}

require_once "Course.php";
$course_handler = new Course();
$message = '';
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}

// Handle POST requests for BOTH adding and updating
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // --- Logic for Adding a New Course ---
    if (isset($_POST['add_course'])) {
        $title = trim($_POST['title']);
        $description = trim($_POST['description']);
        $category = trim($_POST['category']);
        $price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);

        if (!empty($title) && !empty($description) && $price !== false && isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $result = $course_handler->createCourse($title, $description, $category, $price, $_FILES['image']);
            $_SESSION['message'] = "<div class='message " . ($result['success'] ? 'success' : 'error') . "'>" . htmlspecialchars($result['message']) . "</div>";
        } else {
            $_SESSION['message'] = "<div class='message error'>Please fill all fields, provide a valid price, and upload an image.</div>";
        }
    }
    
    // --- NEW: Logic for Updating an Existing Course ---
    if (isset($_POST['update_course'])) {
        $course_id = intval($_POST['course_id']);
        $title = trim($_POST['title']);
        $description = trim($_POST['description']);
        $category = trim($_POST['category']);
        $price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);
        $imageFile = (isset($_FILES['image']) && $_FILES['image']['error'] == 0) ? $_FILES['image'] : null;

        if (!empty($title) && !empty($description) && $price !== false && $course_id > 0) {
            $result = $course_handler->updateCourse($course_id, $title, $description, $category, $price, $imageFile);
            $_SESSION['message'] = "<div class='message " . ($result['success'] ? 'success' : 'error') . "'>" . htmlspecialchars($result['message']) . "</div>";
        } else {
            $_SESSION['message'] = "<div class='message error'>Invalid data provided for update.</div>";
        }
    }

    header("Location: admin.php");
    exit;
}

$courses = $course_handler->getAllCourses();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - LearnEase</title>
    <style>
        /* All previous CSS remains the same... */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; overflow-x: hidden; background: #f8f9fa; color: #111; }
        .navbar { display: flex; justify-content: space-between; align-items: center; padding: 2rem 8%; position: sticky; width: 100%; top: 0; z-index: 1000; background: rgba(255, 255, 255, 0.8); backdrop-filter: blur(20px); border-bottom: 1px solid #eee; }
        .logo { font-size: 1.5rem; font-weight: 700; color: #111; letter-spacing: -0.5px; text-decoration: none; }
        .nav-btns { display: flex; gap: 1rem; align-items: center; }
        .nav-btns a { padding: 0.6rem 1.5rem; border: none; background: none; cursor: pointer; font-size: 0.95rem; font-weight: 500; transition: all 0.3s ease; border-radius: 8px; text-decoration: none; color: #111; }
        .nav-btns a:hover { background: #f5f5f5; }
        .nav-btns .logout-btn { background: #111; color: #fff; }
        .page-header { text-align: center; padding: 4rem 8%; background: #fff; }
        .page-header h1 { font-size: 3.5rem; font-weight: 700; }
        .page-header .highlight { background: linear-gradient(120deg, #667eea, #764ba2); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
        .page-header p { font-size: 1.1rem; color: #666; max-width: 600px; margin: 1rem auto 0; }
        .form-group { margin-bottom: 1.5rem; }
        .form-group label { display: block; margin-bottom: 0.6rem; font-weight: 500; }
        .form-group input, .form-group textarea { width: 100%; padding: 0.9rem 1rem; background: #fafafa; border: 2px solid #eee; border-radius: 10px; font-size: 0.95rem; }
        .form-group input:focus, .form-group textarea:focus { outline: none; border-color: #667eea; }
        .form-btn { width: 100%; padding: 1rem; background: #111; color: #fff; border: none; border-radius: 10px; font-size: 1rem; font-weight: 600; cursor: pointer; transition: all 0.3s ease; }
        .message { padding: 1rem; margin-bottom: 1.5rem; border-radius: 10px; text-align: center; font-weight: 500; border: 1px solid transparent; }
        .message.success { background: #f0fdf4; color: #166534; border-color: #bbf7d0; }
        .message.error { background: #fef2f2; color: #991b1b; border-color: #fecaca; }
        .admin-container { padding: 2rem 8%; max-width: 1600px; margin: 0 auto; }
        .admin-grid { display: grid; grid-template-columns: 1fr 2fr; gap: 2rem; }
        .card { background: #fff; padding: 2rem; border-radius: 20px; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.05); margin-bottom: 2rem; }
        .card h2 { font-size: 1.8rem; margin-bottom: 1.5rem; }
        .course-table { width: 100%; border-collapse: collapse; }
        .course-table th, .course-table td { padding: 1rem; text-align: left; border-bottom: 1px solid #f0f0f0; vertical-align: middle; }
        .course-table img { width: 120px; border-radius: 8px; }
        .action-btn { padding: 0.5rem 1rem; border-radius: 8px; text-decoration: none; font-size: 0.9rem; transition: background 0.2s; color: white; border: none; cursor: pointer; }
        .action-btn.edit { background: #3498db; }
        .action-btn.manage { background: #2ecc71; margin-left: 5px; }
        
        /* NEW: Modal Styles */
        .modal { display: none; position: fixed; z-index: 2000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.5); backdrop-filter: blur(10px); }
        .modal-content { background-color: #fefefe; margin: 10% auto; padding: 2rem; border-radius: 20px; width: 90%; max-width: 600px; position: relative; }
        .close-btn { color: #aaa; position: absolute; top: 1rem; right: 1.5rem; font-size: 28px; font-weight: bold; cursor: pointer; }
    </style>
</head>
<body>
    <nav class="navbar">
        <a href="index.php" class="logo">LearnEase</a>
        <div class="nav-btns">
            <a href="index.php">View Site</a>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </nav>
    <header class="page-header">
        <h1>Admin <span class="highlight">Dashboard</span></h1>
        <p>Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</p>
    </header>

    <main class="admin-container">
        <?php echo $message; ?>
        <div class="admin-grid">
            <div class="card">
                <h2>Add New Course</h2>
                <form action="admin.php" method="POST" enctype="multipart/form-data">
                    <div class="form-group"><label>Course Title</label><input type="text" name="title" required></div>
                    <div class="form-group"><label>Description</label><textarea name="description" rows="4" required></textarea></div>
                    <div class="form-group"><label>Category</label><input type="text" name="category"></div>
                    <div class="form-group"><label>Price (₹)</label><input type="number" name="price" step="0.01" min="0" required></div>
                    <div class="form-group"><label>Course Thumbnail</label><input type="file" name="image" accept="image/*" required></div>
                    <button type="submit" name="add_course" class="form-btn">Add Course</button>
                </form>
            </div>
            <div class="card">
                <h2>Existing Courses</h2>
                <table class="course-table">
                    <thead><tr><th>Image</th><th>Title</th><th>Price</th><th>Actions</th></tr></thead>
                    <tbody>
                        <?php foreach ($courses as $course): ?>
                        <tr>
                            <td><img src="<?php echo htmlspecialchars($course['image']); ?>" alt="Thumbnail"></td>
                            <td><?php echo htmlspecialchars($course['title']); ?></td>
                            <td>₹<?php echo htmlspecialchars(number_format($course['price'], 2)); ?></td>
                            <td>
                                <button class="action-btn edit" onclick='openEditModal(<?php echo json_encode($course); ?>)'>Edit</button>
                                <a href="manage_course.php?id=<?php echo $course['id']; ?>" class="action-btn manage">Manage</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <div id="editCourseModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeEditModal()">&times;</span>
            <h2>Edit Course</h2>
            <form action="admin.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" id="edit_course_id" name="course_id">
                <div class="form-group">
                    <label>Course Title</label>
                    <input type="text" id="edit_title" name="title" required>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea id="edit_description" name="description" rows="4" required></textarea>
                </div>
                <div class="form-group">
                    <label>Category</label>
                    <input type="text" id="edit_category" name="category">
                </div>
                <div class="form-group">
                    <label>Price (₹)</label>
                    <input type="number" id="edit_price" name="price" step="0.01" min="0" required>
                </div>
                <div class="form-group">
                    <label>Upload New Thumbnail (Optional)</label>
                    <input type="file" name="image" accept="image/*">
                </div>
                <button type="submit" name="update_course" class="form-btn">Update Course</button>
            </form>
        </div>
    </div>

    <script>
        const modal = document.getElementById('editCourseModal');

        function openEditModal(course) {
            // Populate the modal form with course data
            document.getElementById('edit_course_id').value = course.id;
            document.getElementById('edit_title').value = course.title;
            document.getElementById('edit_description').value = course.description;
            document.getElementById('edit_category').value = course.category;
            document.getElementById('edit_price').value = course.price;
            modal.style.display = 'block';
        }

        function closeEditModal() {
            modal.style.display = 'none';
        }

        // Close modal if user clicks outside of it
        window.onclick = function(event) {
            if (event.target == modal) {
                closeEditModal();
            }
        }
    </script>
</body>
</html>