<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: /payroll_system/dashboard/index.php");
    exit;
}
require_once '../config/db.php';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    if (empty($username) || empty($password)) {
        $error = "Please enter both username and password.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        if ($user && $password === $user['password']) {
            if ($user['status'] === 'Active') {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['role'] = $user['role'];
                header("Location: /payroll_system/dashboard/index.php");
                exit;
            } else { $error = "Your account is inactive."; }
        } else { $error = "Invalid username or password."; }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Payroll System</title>
    <link rel="stylesheet" href="/payroll_system/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
<div class="login-wrapper">
    <div class="login-card">
        <div class="login-header">
            <h1>Payroll System</h1>
            <p>Administrator Login</p>
        </div>
        <?php if (!empty($error)): ?>
            <div class="alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form action="login.php" method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" class="form-control" placeholder="admin" required autocomplete="off">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <div class="password-wrapper">
                    <input type="password" id="password" name="password" class="form-control" placeholder="........" required>
                    <i class="fas fa-eye password-toggle" id="togglePassword"></i>
                </div>
            </div>
            <button type="submit" class="btn-login">Login</button>
        </form>
        <div class="login-footer"><a href="/payroll_system/index.php">&larr; Back to Home</a></div>
    </div>
</div>
<script>
    const togglePassword = document.querySelector('#togglePassword');
    const password = document.querySelector('#password');
    togglePassword.addEventListener('click', function () {
        const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
        password.setAttribute('type', type);
        this.classList.toggle('fa-eye');
        this.classList.toggle('fa-eye-slash');
    });
</script>
</body>
</html>