<?php
session_start();

// Already logged in — skip to dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = '';

// Handle login POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include 'php/db.php';

    $email    = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    $query  = "SELECT * FROM users WHERE email='$email' LIMIT 1";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) === 1) {
        $user = mysqli_fetch_assoc($result);
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            header("Location: dashboard.php");
            exit();
        } else {
            $error = 'Invalid email or password.';
        }
    } else {
        $error = 'Invalid email or password.';
    }
}

// Flash messages via query string
$flash = '';
$flashType = '';
if (isset($_GET['msg'])) {
    switch ($_GET['msg']) {
        case 'registered':
            $flash     = 'Registration successful! Please sign in.';
            $flashType = 'success';
            break;
        case 'loggedout':
            $flash     = 'You have been logged out.';
            $flashType = 'info';
            break;
        case 'error':
            $flash     = 'Login failed. Please try again.';
            $flashType = 'danger';
            break;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Bck2U</title>
    <meta name="description" content="Sign in to Bck2U — the smart lost &amp; found platform for college students.">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/dark-mode.css">
</head>
<body>

    <div class="auth-wrapper">
        <div class="auth-container">

            <!-- Left — Branding -->
            <div class="auth-brand">
                <i class="fas fa-map-marker-alt" style="font-size:3rem;margin-bottom:20px;"></i>
                <h1>Bck2U</h1>
                <p>The smart lost &amp; found platform for college students. Report, search, and recover your belongings with ease.</p>
                <ul style="list-style:none;padding:0;margin-top:30px;text-align:left;max-width:260px;">
                    <li style="margin-bottom:12px;">🔍 Smart Search</li>
                    <li style="margin-bottom:12px;">💬 Real-time Chat</li>
                    <li>✅ Secure Claims</li>
                </ul>
            </div>

            <!-- Right — Login Form -->
            <div class="auth-form">
                <h2>Welcome Back</h2>
                <p class="text-muted">Sign in to your account</p>

                <?php if ($error): ?>
                    <div style="background:#FEE2E2;color:#991B1B;border:1px solid #FECACA;border-radius:8px;padding:12px 16px;margin-bottom:18px;font-size:.9rem;">
                        <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <?php if ($flash): ?>
                    <div style="background:<?php echo $flashType === 'success' ? '#D1FAE5' : '#DBEAFE'; ?>;
                                color:<?php echo $flashType === 'success' ? '#065F46' : '#1E40AF'; ?>;
                                border:1px solid <?php echo $flashType === 'success' ? '#A7F3D0' : '#BFDBFE'; ?>;
                                border-radius:8px;padding:12px 16px;margin-bottom:18px;font-size:.9rem;">
                        <i class="fas fa-<?php echo $flashType === 'success' ? 'check-circle' : 'info-circle'; ?>"></i>
                        <?php echo htmlspecialchars($flash); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="index.php" autocomplete="on">
                    <div class="form-group">
                        <label class="form-label" for="login-email">Email Address</label>
                        <div class="form-input-icon">
                            <i class="fas fa-envelope"></i>
                            <input type="email" id="login-email" class="form-input" name="email" placeholder="you@university.edu" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="login-password">Password</label>
                        <div class="form-input-icon">
                            <i class="fas fa-lock"></i>
                            <input type="password" id="login-password" class="form-input" name="password" placeholder="••••••••" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block btn-lg" id="login-submit">
                        <i class="fas fa-sign-in-alt"></i> Sign In
                    </button>
                </form>

                <p class="text-center text-muted" style="margin-top:22px;">
                    Don't have an account? <a href="register.php">Create one</a>
                </p>
            </div>

        </div>
    </div>

    <script src="js/app.js"></script>
</body>
</html>
