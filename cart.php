<?php
session_start();
require_once "Course.php";

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle adding an item to the cart
if (isset($_GET['action']) && $_GET['action'] === 'add' && isset($_GET['id'])) {
    $course_id = intval($_GET['id']);
    // Add course to cart only if it's not already there
    if (!in_array($course_id, $_SESSION['cart'])) {
        $_SESSION['cart'][] = $course_id;
    }
    // Redirect back to courses page or to the cart itself
    header("Location: cart.php");
    exit;
}

// Handle removing an item from the cart
if (isset($_GET['action']) && $_GET['action'] === 'remove' && isset($_GET['id'])) {
    $course_id_to_remove = intval($_GET['id']);
    $_SESSION['cart'] = array_filter($_SESSION['cart'], function($id) use ($course_id_to_remove) {
        return $id != $course_id_to_remove;
    });
    header("Location: cart.php");
    exit;
}

$cart_courses = [];
$total_price = 0;
if (!empty($_SESSION['cart'])) {
    $course_handler = new Course();
    foreach ($_SESSION['cart'] as $course_id) {
        $course = $course_handler->getCourseById($course_id);
        if ($course) {
            $cart_courses[] = $course;
            $total_price += $course['price'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your Cart - LearnEase</title>
    <style> 
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
    .total-section { text-align: right; margin-top: 2rem; font-size: 1.5rem; font-weight: bold; } </style>
</head>
<body>
    <nav class="navbar"><a href="index.php" class="logo">LearnEase</a><div class="nav-btns"><a href="courses.php">&larr; Continue Shopping</a></div></nav>
    <header class="page-header"><h1>Shopping <span class="highlight">Cart</span></h1></header>

    <main class="admin-container">
        <div class="card">
            <?php if (empty($cart_courses)): ?>
                <p style="text-align:center;">Your cart is empty.</p>
            <?php else: ?>
                <table class="course-table">
                    <thead><tr><th>Course</th><th>Price</th><th>Action</th></tr></thead>
                    <tbody>
                        <?php foreach ($cart_courses as $course): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($course['title']); ?></td>
                            <td>₹<?php echo htmlspecialchars($course['price']); ?></td>
                            <td><a href="cart.php?action=remove&id=<?php echo $course['id']; ?>" class="action-btn" style="background:#e74c3c;">Remove</a></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <div class="total-section">
                    Total: ₹<?php echo number_format($total_price, 2); ?>
                </div>
                <hr style="margin:2rem 0;">
                <div style="text-align: right;">
                    <a href="checkout.php" class="action-btn" style="background:#2ecc71;padding:1rem 2rem; font-size:1rem;">Proceed to Checkout</a>
                </div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>