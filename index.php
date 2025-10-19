<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LearnEase - Learn Better</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            overflow-x: hidden;
            background: #fff;
            color: #111;
        }

        /* Navbar */
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 2rem 8%;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(20px);
        }

        .logo {
            font-size: 1.5rem;
            font-weight: 700;
            color: #111;
            letter-spacing: -0.5px;
            text-decoration: none;
        }

        .nav-btns {
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        .nav-btns button, .nav-btns a {
            padding: 0.6rem 1.5rem;
            border: none;
            background: none;
            cursor: pointer;
            font-size: 0.95rem;
            font-weight: 500;
            transition: all 0.3s ease;
            border-radius: 8px;
            text-decoration: none;
        }

        .btn-login {
            color: #111;
        }

        .btn-login:hover {
            background: #f5f5f5;
        }

    .btn-register {
                    color: #111;

    }

.btn-register:hover {
            background: #f5f5f5;
}

        /* Hero */
        .hero {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            padding: 0 8%;
            position: relative;
        }

        .hero h1 {
            font-size: 6rem;
            font-weight: 700;
            line-height: 1.1;
            margin-bottom: 1.5rem;
            letter-spacing: -3px;
            animation: fadeUp 0.8s ease;
        }

        .hero .highlight {
            background: linear-gradient(120deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .hero p {
            font-size: 1.3rem;
            color: #666;
            max-width: 500px;
            margin-bottom: 3rem;
            line-height: 1.6;
            animation: fadeUp 0.8s ease 0.2s backwards;
        }

        .hero-btn {
            padding: 1rem 2.5rem;
            font-size: 1rem;
            background: #111;
            color: #fff;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            animation: fadeUp 0.8s ease 0.4s backwards;
            text-decoration: none;
        }

        .hero-btn:hover {
            background: #333;
            transform: translateY(-2px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }

        /* Scroll Indicator */
        .scroll {
            position: absolute;
            bottom: 3rem;
            font-size: 0.9rem;
            color: #999;
            animation: bounce 2s infinite;
        }

        /* Features */
        .features {
            padding: 8rem 8%;
            background: #fafafa;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 3rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .feature {
            text-align: center;
            animation: fadeUp 0.8s ease backwards;
        }

        .feature:nth-child(1) { animation-delay: 0.1s; }
        .feature:nth-child(2) { animation-delay: 0.2s; }
        .feature:nth-child(3) { animation-delay: 0.3s; }

        .feature-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 1.5rem;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            transition: transform 0.3s ease;
            color: white;
        }

        .feature:hover .feature-icon {
            transform: translateY(-5px) rotate(5deg);
        }

        .feature h3 {
            font-size: 1.3rem;
            margin-bottom: 0.8rem;
            color: #111;
        }

        .feature p {
            color: #666;
            line-height: 1.6;
            font-size: 0.95rem;
        }

        /* Stats */
        .stats {
            padding: 6rem 8%;
            text-align: center;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 4rem;
            max-width: 900px;
            margin: 0 auto;
        }

        .stat h2 {
            font-size: 3.5rem;
            background: linear-gradient(120deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.5rem;
            font-weight: 700;
        }

        .stat p {
            color: #666;
            font-size: 1rem;
        }

        /* Modal and Form Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(10px);
            z-index: 2000;
            justify-content: center;
            align-items: center;
            animation: fadeIn 0.3s ease;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: #fff;
            padding: 3rem;
            border-radius: 20px;
            width: 90%;
            max-width: 420px;
            position: relative;
            animation: slideUp 0.4s ease;
            box-shadow: 0 40px 80px rgba(0, 0, 0, 0.2);
        }

        .close-btn {
            position: absolute;
            top: 1.5rem;
            right: 1.5rem;
            font-size: 1.5rem;
            cursor: pointer;
            color: #999;
            background: none;
            border: none;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: all 0.3s;
        }

        .close-btn:hover {
            color: #111;
            background: #f5f5f5;
        }

        .modal-content h2 {
            text-align: center;
            font-size: 2rem;
            margin-bottom: 2rem;
            color: #111;
            font-weight: 700;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.6rem;
            color: #111;
            font-weight: 500;
            font-size: 0.9rem;
        }

        .form-group input {
            width: 100%;
            padding: 0.9rem 1rem;
            background: #fafafa;
            border: 2px solid transparent;
            border-radius: 10px;
            font-size: 0.95rem;
            color: #111;
            transition: all 0.3s;
        }

        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            background: #fff;
        }

        .form-btn {
            width: 100%;
            padding: 1rem;
            background: #111;
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .form-btn:hover {
            background: #333;
            transform: translateY(-1px);
        }

        .toggle-form {
            text-align: center;
            margin-top: 1.5rem;
            color: #666;
            font-size: 0.9rem;
        }

        .toggle-form a {
            color: #667eea;
            cursor: pointer;
            text-decoration: none;
            font-weight: 600;
        }

        .response-message {
            padding: 1rem;
            margin-top: 1rem;
            border-radius: 10px;
            text-align: center;
            font-weight: 500;
            font-size: 0.9rem;
            display: none;
        }

        .response-message.success {
            background: #f0fdf4;
            color: #166534;
            border: 1px solid #bbf7d0;
            display: block;
        }

        .response-message.error {
            background: #fef2f2;
            color: #991b1b;
            border: 1px solid #fecaca;
            display: block;
        }

        /* Animations */
        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(10px); }
        }

        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }

        @keyframes slideUp {
            from { transform: translateY(30px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        @keyframes fadeUp {
            from { transform: translateY(20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        /* Responsive Design */
        @media (max-width: 968px) {
            .hero h1 { font-size: 4rem; }
            .features-grid, .stats-grid { grid-template-columns: 1fr; gap: 2rem; }
            .navbar, .hero, .features, .stats { padding-left: 5%; padding-right: 5%; }
        }
    </style>
</head>
<body>
<nav class="navbar">
    <a href="index.php" class="logo">LearnEase</a>
    <div class="nav-btns">
        <?php if (isset($_SESSION['user_id'])): ?>
            
            <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true): ?>
                <a href="admin.php" class="btn-login">Admin Dashboard</a>
            <?php else: ?>
                <a href="courses.php" class="btn-login">Courses</a>
                <a href="my-courses.php" class="btn-login">My Courses</a>
            <?php endif; ?>
            
            <a href="logout.php" class="btn-logout">Logout</a>

        <?php else: ?>
            <button class="btn-login" onclick="openModal('login')">Sign In</button>
            <button class="btn-register" onclick="openModal('register')">Get Started</button>
        <?php endif; ?>
    </div>
</nav>

    <section class="hero">
        <h1>Learn<br><span class="highlight">Anything.</span></h1>
        <p>Master new skills with our simple, effective learning platform designed for the modern learner.</p>
        <a href="courses.php" class="hero-btn">Start Learning Free</a>
        <div class="scroll">↓</div>
    </section>

    <section class="features">
        <div class="features-grid">
            <div class="feature">
                <div class="feature-icon">⚡️</div>
                <h3>Fast Learning</h3>
                <p>Bite-sized lessons that fit your schedule. Learn at your own pace, anywhere.</p>
            </div>
            <div class="feature">
                <div class="feature-icon">🎯</div>
                <h3>Smart Paths</h3>
                <p>Personalized learning journeys that adapt to your goals and progress.</p>
            </div>
            <div class="feature">
                <div class="feature-icon">🏆</div>
                <h3>Real Results</h3>
                <p>Track your growth with insights and earn certificates that matter.</p>
            </div>
        </div>
    </section>

    <section class="stats">
        <div class="stats-grid">
            <div class="stat">
                <h2>10K+</h2>
                <p>Active Learners</p>
            </div>
            <div class="stat">
                <h2>500+</h2>
                <p>Expert Courses</p>
            </div>
            <div class="stat">
                <h2>95%</h2>
                <p>Success Rate</p>
            </div>
        </div>
    </section>

    <div class="modal" id="authModal">
        <div class="modal-content">
            <button class="close-btn" onclick="closeModal()">×</button>
            
            <div id="loginForm">
                <h2>Welcome back</h2>
                <form onsubmit="handleAuth(event, 'login')">
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" id="loginEmail" required placeholder="your@email.com">
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" id="loginPassword" required placeholder="Enter password">
                    </div>
                    <button type="submit" class="form-btn">Sign In</button>
                </form>
                <div class="toggle-form">
                    New here? <a onclick="switchForm('register')">Create account</a>
                </div>
                <div id="loginMessage" class="response-message"></div>
            </div>

            <div id="registerForm">
                <h2>Get started</h2>
                <form onsubmit="handleAuth(event, 'register')">
                    <div class="form-group">
                        <label>Name</label>
                        <input type="text" id="registerName" required placeholder="Your name">
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" id="registerEmail" required placeholder="your@email.com">
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" id="registerPassword" required placeholder="Create password (min 6 chars)">
                    </div>
                    <button type="submit" class="form-btn">Create Account</button>
                </form>
                <div class="toggle-form">
                    Have an account? <a onclick="switchForm('login')">Sign in</a>
                </div>
                <div id="registerMessage" class="response-message"></div>
            </div>
        </div>
    </div>

    <script>
    // Open modal
function openModal(type) {
    document.getElementById('authModal').classList.add('active');
    switchForm(type);
}

// Close modal
function closeModal() {
    document.getElementById('authModal').classList.remove('active');
    clearMessages();
}

// Switch forms
function switchForm(type) {
    document.getElementById('loginForm').style.display = (type==='login')?'block':'none';
    document.getElementById('registerForm').style.display = (type==='register')?'block':'none';
    clearMessages();
}

// Clear messages
function clearMessages(){
    ['loginMessage','registerMessage'].forEach(id=>{
        const el=document.getElementById(id);
        el.style.display='none';
        el.textContent='';
    });
}

// Handle login/register
async function handleAuth(event,action){
    event.preventDefault();
    const messageEl=document.getElementById(action+'Message');
    messageEl.style.display='block';
    messageEl.style.color='red';
    messageEl.textContent='Processing...';

    let data={action};

    if(action==='login'){
        const email=document.getElementById('loginEmail').value.trim();
        const password=document.getElementById('loginPassword').value.trim();
        if(!email||!password){ messageEl.textContent='Please fill in all fields'; return; }
        data.email=email; data.password=password;

    } else { // register
        const name=document.getElementById('registerName').value.trim();
        const email=document.getElementById('registerEmail').value.trim();
        const password=document.getElementById('registerPassword').value.trim();
        if(!name||!email||!password){ messageEl.textContent='Please fill in all fields'; return; }
        if(password.length<6){ messageEl.textContent='Password must be at least 6 characters'; return; }
        data.name=name; data.email=email; data.password=password;
    }

    try{
        const response=await fetch('auth.php',{
            method:'POST',
            headers:{'Content-Type':'application/json'},
            body:JSON.stringify(data)
        });
        const result=await response.json();

        if(result.success){
            messageEl.style.color='green';
            messageEl.textContent=result.message;
            setTimeout(()=>{
                closeModal();
                if(result.role==='admin') window.location.href='admin.php';
                else window.location.reload();
            },1000);
        } else {
            messageEl.style.color='red';
            messageEl.textContent=result.message;
        }
    } catch(err){
        console.error(err);
        messageEl.style.color='red';
        messageEl.textContent='An error occurred. Try again.';
    }
}

// Close modal when clicking outside
document.getElementById('authModal').addEventListener('click',function(e){
    if(e.target===this) closeModal();
});

</script>

</body>
</html>