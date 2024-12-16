<?php
session_start();
require_once '../conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $avatar = $_FILES['avatar'];
    $role = $_POST['role'];
    $errors = [];

    if (empty($username) || empty($password) || empty($confirm_password) || empty($role)) {
        $errors[] = "All fields are required.";
    } elseif ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }

    $avatar_path = null;
    if (!empty($avatar['name'])) {
        $target_dir = "../uploads/";
        $avatar_path = $target_dir . basename($avatar['name']);
        if (!move_uploaded_file($avatar['tmp_name'], $avatar_path)) {
            $errors[] = "Failed to upload avatar.";
        }
    }
    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO users2 (username, password, avatar, role) VALUES (?, ?, ?, ?)");
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        $stmt->bind_param("ssss", $username, $hashed_password, $avatar_path, $role);

        if ($stmt->execute()) {
            $_SESSION['message'] = "Registration successful. Please log in.";
            header('Location: register.php');
            exit;
        } else {
            $errors[] = "Registration failed. Please try again.";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="container">
    <h1>Register</h1>

    <?php if (isset($_SESSION['message'])): ?>
        <div class="message"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="error"><?php echo implode("<br>", $errors); ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <label for="username">Username</label>
        <input type="text" id="username" name="username">

        <label for="password">Password</label>
        <input type="password" id="password" name="password" >

        <label for="confirm_password">Confirm Password</label>
        <input type="password" id="confirm_password" name="confirm_password">

        <label for="avatar">Upload Image</label>
        <input type="file" id="avatar" name="avatar" accept="image/*">

        <label for="role">Role</label>
        <select id="role" name="role" required>
            <option value="admin">Admin</option>
            <option value="user">User</option>
        </select>

        <button type="submit">Register</button>
    </form>
    <p>Already have an account? <a href="login.php">Login here</a>.</p>
</div>
</body>
</html>
