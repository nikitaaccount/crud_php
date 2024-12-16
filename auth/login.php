<?php
session_start();
require_once '../conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $errors = [];

    $stmt = $conn->prepare("SELECT id, password, avatar, role FROM users2 WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->bind_result($id, $hashed_password, $avatar, $role);
    $stmt->fetch();

    if ($id && password_verify($password, $hashed_password)) {
       
        $_SESSION['user_id'] = $id;
        $_SESSION['username'] = $username;
        $_SESSION['avatar'] = $avatar;
        $_SESSION['role'] = $role;

        header("Location: ../dashboard/" . ($role === 'admin' ? 'admin.php' : 'user.php'));
        exit;
    } else {
        $errors[] = "Invalid username or password.";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="container">
    <h1>Login</h1>

    <?php if (!empty($errors)): ?>
        <div class="error"><?php echo implode("<br>", $errors); ?></div>
    <?php endif; ?>

    <form method="POST">
        <label for="username">Username</label>
        <input type="text" id="username" name="username" >

        <label for="password">Password</label>
        <input type="password" id="password" name="password">

        <button type="submit">Login</button>
    </form>

    <p>Don't have an account? <a href="register.php">Register here</a>.</p>
</div>
</body>
</html>
