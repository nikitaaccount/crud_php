<?php
session_start();
if (isset($_SESSION['user_id'])) {
    if (isset($_SESSION['role'])) {
        $role = $_SESSION['role'];
        header('Location: dashboard/' . ($role === 'admin' ? 'admin.php' : 'user.php'));
        exit;
    } else {
        session_destroy();
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to the Application</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="index-container">
    <h1>Welcome to the Role-Based Application</h1>
    <p>Please log in or register to access your dashboard.</p>
    <div>
        <a href="auth/login.php"><button>Login</button></a>
        <a href="auth/register.php"><button>Register</button></a>
    </div>
</div>
</body>
</html>
