<?php
session_start();
require_once "Course.php";
$course_handler = new Course();
$courses = $course_handler->getAllCourses();
$user_id = $_SESSION['user_id'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>All Courses - LearnEase</title>
    <style>
        /* All the CSS from your previous file remains the same */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; overflow-x: hidden; background: #f8f9fa; color: #111; }
        .navbar { display: flex; justify-content: space-between; align-items: center; padding: 2rem 8%; position: sticky; width: 100%; top: 0; z-index: 1000; background: rgba(255, 255, 255, 0.8); backdrop-filter: blur(20px); border-bottom: 1px solid #eee; }
        .logo { font-size: 1.5rem; font-weight: 700; color: #111; letter-spacing: -0.5px; text-decoration: none; }
        .nav-btns { display: flex; gap: 1rem; align-items: center; }
        .nav-btns a, .nav-btns button { padding: 0.6rem 1.5rem; border: none; background: none; cursor: pointer; font-size: 0.95rem; font-weight: 500; transition: all 0.3s ease; border-radius: 8px; text-decoration: none; color: #111; }
        .nav-btns a:hover { background: #f5f5f5; }
        .nav-btns .cart-btn { background: #111; color: #fff; }
        .page-header { text-align: center; padding: 4rem 8%; background: #fff; }
        .page-header h1 { font-size: 3.5rem; font-weight: 700; }
        .page-header .highlight { background: linear-gradient(120deg, #667eea, #764ba2); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
        .page-header p { font-size: 1.1rem; color: #666; max-width: 600px; margin: 1rem auto 0; }
        .admin-container { padding: 2rem 8%; max-width: 1600px; margin: 0 auto; }
        .course-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 2rem; }
        .course-card { background: #fff; border-radius: 15px; box-shadow: 0 8px 30px rgba(0,0,0,0.08); overflow: hidden; display: flex; flex-direction: column; }
        .course-card img { width: 100%; height: 180px; object-fit: cover; }
        .course-card-content { padding: 1.5rem; flex-grow: 1; display: flex; flex-direction: column; }
        .course-card-content h3 { font-size: 1.3rem; margin-bottom: 0.5rem; }
        .course-card-content p { color: #666; font-size: 0.95rem; line-height: 1.5; margin-bottom: 1rem; flex-grow: 1; }
        .course-footer { display: flex; justify-content: space-between; align-items: center; }
        .price { font-size: 1.5rem; font-weight: 700; color: #667eea; }
        .price.free { color: #2ecc71; }
        .add-to-cart-btn { background: #111; color: #fff; padding: 0.7rem 1.2rem; border-radius: 8px; text-decoration: none; font-weight: 600; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; font-size: 0.95rem; }
        .enrolled-btn { background: #27ae60; color: #fff; padding: 0.7rem 1.2rem; border-radius: 8px; text-decoration: none; font-weight: 600; }
        
        /* Modal Styles from index.php */
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); backdrop-filter: blur(10px); z-index: 2000; justify-content: center; align-items: center; animation: fadeIn 0.3s ease; }
        .modal.active { display: flex; }
        .modal-content { background: #fff; padding: 3rem; border-radius: 20px; width: 90%; max-width: 420px; position: relative; animation: slideUp 0.4s ease; box-shadow: 0 40px 80px rgba(0, 0, 0, 0.2); }
        .close-btn { position: absolute; top: 1.5rem; right: 1.5rem; font-size: 1.5rem; cursor: pointer; color: #999; background: none; border: none; }
        .modal-content h2 { text-align: center; font-size: 2rem; margin-bottom: 2rem; font-weight: 700; }
        .form-group { margin-bottom: 1.5rem; }
        .form-group label { display: block; margin-bottom: 0.6rem; font-weight: 500; font-size: 0.9rem; }
        .form-group input { width: 100%; padding: 0.9rem 1rem; background: #fafafa; border: 2px solid transparent; border-radius: 10px; font-size: 0.95rem; }
        .form-group input:focus { outline: none; border-color: #667eea; }
        .form-btn { width: 100%; padding: 1rem; background: #111; color: #fff; border: none; border-radius: 10px; font-size: 1rem; font-weight: 600; cursor: pointer; }
        .toggle-form { text-align: center; margin-top: 1.5rem; color: #666; font-size: 0.9rem; }
        .toggle-form a { color: #667eea; cursor: pointer; text-decoration: none; font-weight: 600; }
        .response-message { padding: 1rem; margin-top: 1rem; border-radius: 10px; text-align: center; font-weight: 500; font-size: 0.9rem; display: none; }
        .response-message.success { background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; display: block; }
        .response-message.error { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; display: block; }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        @keyframes slideUp { from { transform: translateY(30px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
    </style>
</head>
<body>
    <nav class="navbar">
        <a href="index.php" class="logo">LearnEase</a>
        <div class="nav-btns">
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="my-courses.php">My Courses</a>
                <a href="cart.php" class="cart-btn">Cart (<?php echo isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0; ?>)</a>
            <?php else: ?>
                <a href="index.php">Home</a>
                <button onclick="openModal('login')">Sign In</button>
            <?php endif; ?>
        </div>
    </nav>
    <header class="page-header">
        <h1>Explore Our <span class="highlight">Courses</span></h1>
        <p>Find the perfect course to boost your skills and career.</p>
    </header>

    <main class="admin-container">
        <div class="course-grid">
            <?php foreach ($courses as $course): ?>
                <div class="course-card">
                    <img src="<?php echo htmlspecialchars($course['image']); ?>" alt="Course Thumbnail">
                    <div class="course-card-content">
                        <h3><?php echo htmlspecialchars($course['title']); ?></h3>
                        <p><?php echo htmlspecialchars($course['description']); ?></p>
                        <div class="course-footer">
                            <span class="price <?php echo ($course['price'] == 0) ? 'free' : ''; ?>">
                                <?php echo ($course['price'] == 0) ? 'Free' : '₹' . htmlspecialchars($course['price']); ?>
                            </span>
                            
                            <?php
                            $isEnrolled = $user_id ? $course_handler->isUserEnrolled($user_id, $course['id']) : false;
                            
                            if ($isEnrolled) {
                                echo '<a href="view-course.php?id='.$course['id'].'" class="enrolled-btn">View Course</a>';
                            } else {
                                // If user is logged in, show "Add to Cart" link
                                if ($user_id) {
                                    echo '<a href="cart.php?action=add&id=' . $course['id'] . '" class="add-to-cart-btn">Add to Cart</a>';
                                // If user is NOT logged in, show a button that opens the login modal
                                } else {
                                    echo '<button onclick="openModal(\'login\')" class="add-to-cart-btn">Add to Cart</button>';
                                }
                            }
                            ?>
                            </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </main>

    <div class="modal" id="authModal">
        <div class="modal-content">
            <button class="close-btn" onclick="closeModal()">×</button>
            <div id="loginForm">
                <h2>Welcome back</h2>
                <form onsubmit="handleAuth(event, 'login')">
                    <div class="form-group"><label>Email</label><input type="email" id="loginEmail" required></div>
                    <div class="form-group"><label>Password</label><input type="password" id="loginPassword" required></div>
                    <button type="submit" class="form-btn">Sign In</button>
                </form>
                <div class="toggle-form">New here? <a onclick="switchForm('register')">Create account</a></div>
                <div id="loginMessage" class="response-message"></div>
            </div>
            <div id="registerForm">
                <h2>Get started</h2>
                <form onsubmit="handleAuth(event, 'register')">
                    <div class="form-group"><label>Name</label><input type="text" id="registerName" required></div>
                    <div class="form-group"><label>Email</label><input type="email" id="registerEmail" required></div>
                    <div class="form-group"><label>Password</label><input type="password" id="registerPassword" required></div>
                    <button type="submit" class="form-btn">Create Account</button>
                </form>
                <div class="toggle-form">Have an account? <a onclick="switchForm('login')">Sign in</a></div>
                <div id="registerMessage" class="response-message"></div>
            </div>
        </div>
    </div>

    <script>
        function openModal(type) { document.getElementById('authModal').classList.add('active'); switchForm(type); }
        function closeModal() { document.getElementById('authModal').classList.remove('active'); clearMessages(); }
        function switchForm(type) { document.getElementById('loginForm').style.display = (type==='login')?'block':'none'; document.getElementById('registerForm').style.display = (type==='register')?'block':'none'; clearMessages(); }
        function clearMessages(){ ['loginMessage','registerMessage'].forEach(id=>{ const el=document.getElementById(id); el.className='response-message'; el.textContent=''; }); }
        async function handleAuth(event,action){ event.preventDefault(); const messageEl=document.getElementById(action+'Message'); let data={action}; if(action==='login'){data.email=document.getElementById('loginEmail').value;data.password=document.getElementById('loginPassword').value;}else{data.name=document.getElementById('registerName').value;data.email=document.getElementById('registerEmail').value;data.password=document.getElementById('registerPassword').value;} try{ const response=await fetch('auth.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(data)}); const result=await response.json(); messageEl.textContent=result.message; if(result.success){ messageEl.className='response-message success'; setTimeout(()=>{ if(result.role==='admin'){window.location.href='admin.php';}else{window.location.reload();} },1000);}else{ messageEl.className='response-message error';}}catch(error){ messageEl.textContent='An error occurred.'; messageEl.className='response-message error';}}
        document.getElementById('authModal').addEventListener('click',function(e){ if(e.target===this) closeModal(); });
    </script>
</body>
</html>