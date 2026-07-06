<?php $errors = $errors ?? []; ?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Creativa</title>

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
                        <input type="text" id="name" name="name" class="form-control" required placeholder="Enter your full name">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <div class="input-group">
                        <input type="email" id="email" name="email" class="form-control" required placeholder="Enter your email">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group">
                        <input type="password" id="password" name="password" class="form-control" required placeholder="••••••••">
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
</body>
</html>