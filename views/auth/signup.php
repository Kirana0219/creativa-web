<?php $errors = $errors ?? []; ?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Creativa</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/auth.css">
</head>

<body>

<div class="login-wrapper">

    <div class="login-left">
        <div class="hero-content">
            <img src="assets/images/Container.png" class="logo" alt="CREATIVA">
            <h1>Empowering small businesses with enterprise-grade tools.</h1>
            <p>
                Join thousands of users who manage inventory, sales, and reporting in one system built for growth and clarity.
            </p>
        </div>
    </div>
    <div class="login-right">
        <div class="login-card">
            <h2>Create your account</h2>
            <p class="subtitle">Please enter your details to create an account</p>

            <!-- ERROR -->
            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <!-- FORM -->
             <form action="index.php?page=signupProcess" method="POST">

                <div class="mb-3">
                    <label for="name" class="form-label">Full Name</label>
                    <div class="input-group">
                        <span class="input-icon">
                            <i class="ri-user-line"></i>
                        </span>
                        <input type="text" id="name" name="name" class="form-control" required placeholder="Enter your full name">
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <div class="input-group">
                        <span class="input-icon">
                            <i class="ri-mail-line"></i>
                        </span>
                        <input type="email" id="email" name="email" class="form-control" required placeholder="Enter your email">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group">
                        <span class="input-icon">
                            <i class="ri-lock-line"></i>
                        </span>
                        <input type="password" id="password" name="password" class="form-control" required placeholder="••••••••">
                        <span class="eye-icon" onclick="togglePassword()">
                            <i class="ri-eye-line" id="eyeIcon"></i>
                        </span>
                    </div>
                </div>

                <div class="form-check mb-3">
                    <input type="checkbox" id="terms" name="terms" value="1" class="form-check-input" required>
                    <label for="terms" class="form-check-label">
                        I agree to Terms & Conditions
                    </label>
                </div>

                <button type="submit" class="login-btn" style="width: 100%;">
                    Create Account
                </button>

            </form>

            <p class="register-link">
                Already have an account?
                <a href="index.php?page=signin">Sign in</a>
            </p>

        </div>

    </div>
</div>
<script>
function togglePassword() {
    const input = document.getElementById("password");
    const icon = document.getElementById("eyeIcon");

    if (input.type === "password") {
        input.type = "text";
        icon.classList.remove("ri-eye-line");
        icon.classList.add("ri-eye-off-line");
    } else {
        input.type = "password";
        icon.classList.remove("ri-eye-off-line");
        icon.classList.add("ri-eye-line");
    }
}
</script>
</body>
</html>