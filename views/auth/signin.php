<?php $errors = $errors ?? []; ?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - Creativa</title>
    <link rel="stylesheet" href="assets/css/auth.css">
</head>

<body>

<div class="login-wrapper">

    <div class="login-left">
        <div class="hero-content">
            <img src="assets/images/Container.png" class="logo" alt="CREATIVA">
            <h1>Empowering small businesses with enterprise-grade tools.</h1>
            <p>Join over 10,000+ business owners who simplified their inventory, sales, and reporting with our all-in-one management suite.</p>
        </div>
    </div>

    <div class="login-right">
        <div class="login-card">

            <h2>Welcome back</h2>
            <p class="subtitle">Please enter your details to sign in</p>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if (!empty($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>

            <form action="index.php?page=signinProcess" method="POST">

                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <div class="input-group">
                        <input type="email" name="email" class="form-control" required placeholder="Enter your email">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <div class="input-group">
                        <input type="password" name="password" class="form-control" required placeholder="••••••••">
                    </div>
                </div>

                <div class="form-footer" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 28px;">
                    <div class="form-check">
                        <input type="checkbox" name="remember" id="remember" class="form-check-input">
                        <label for="remember" class="form-check-label">Remember me</label>
                    </div>

                    <a href="#" class="forgot-link">Forgot Password?</a>
                </div>

                <button type="submit" class="login-btn" style="width: 100%;">Sign In</button>

            </form>

            <p class="register-link">
                Don't have an account? <a href="index.php?page=signup">Sign up for free</a>
            </p>

        </div>
    </div>

</div>
</body>
</html>