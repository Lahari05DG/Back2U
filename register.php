<?php
include 'php/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = mysqli_real_escape_string($conn, $_POST['name']);
    $email    = mysqli_real_escape_string($conn, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Check for duplicate email
    $check = mysqli_query($conn, "SELECT id FROM users WHERE email='$email' LIMIT 1");
    if (mysqli_num_rows($check) > 0) {
        $error = 'An account with this email already exists.';
    } else {
        $insert = "INSERT INTO users (name, email, password) VALUES ('$name', '$email', '$password')";
        if (mysqli_query($conn, $insert)) {
            header("Location: index.php?msg=registered");
            exit();
        } else {
            $error = 'Registration failed. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register — Bck2U</title>
    <meta name="description" content="Create your Bck2U account — join the smart lost &amp; found community for college students.">
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
                <p>Join thousands of students who trust Bck2U to help recover lost items on campus.</p>
                <ul style="list-style:none;padding:0;margin-top:30px;text-align:left;max-width:260px;">
                    <li style="margin-bottom:12px;">🔍 Smart Search</li>
                    <li style="margin-bottom:12px;">💬 Real-time Chat</li>
                    <li>✅ Secure Claims</li>
                </ul>
            </div>

            <!-- Right — Register Form -->
            <div class="auth-form">
                <h2>Create Account</h2>
                <p class="text-muted">Join the Bck2U community</p>

                <?php if ($error): ?>
                    <div style="background:#FEE2E2;color:#991B1B;border:1px solid #FECACA;border-radius:8px;padding:12px 16px;margin-bottom:18px;font-size:.9rem;">
                        <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="register.php" autocomplete="on">
                    <div class="form-group">
                        <label class="form-label" for="reg-name">Full Name</label>
                        <div class="form-input-icon">
                            <i class="fas fa-user"></i>
                            <input type="text" id="reg-name" class="form-input" name="name" placeholder="Your full name" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="reg-email">Email Address</label>
                        <div class="form-input-icon">
                            <i class="fas fa-envelope"></i>
                            <input type="email" id="reg-email" class="form-input" name="email" placeholder="you@university.edu" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="reg-password">Password</label>
                        <div class="form-input-icon">
                            <i class="fas fa-lock"></i>
                            <input type="password" id="reg-password" class="form-input" name="password" placeholder="••••••••" required minlength="6">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block btn-lg" id="register-submit">
                        <i class="fas fa-user-plus"></i> Create Account
                    </button>
                </form>

                <p class="text-center text-muted" style="margin-top:22px;">
                    Already have an account? <a href="index.php">Sign in</a>
                </p>
            </div>

        </div>
    </div>

    <script src="js/app.js"></script>
</body>
</html>