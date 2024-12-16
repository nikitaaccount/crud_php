<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit;
}

$username = $_SESSION['username'];
$avatar = $_SESSION['avatar'];
$role = $_SESSION['role'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="container">
    <h1>Welcome <?php echo htmlspecialchars($username); ?> <?php echo htmlspecialchars($role); ?></h1>
    <div class="user-details">
        <?php if (!empty($avatar)): ?>
            <img src="<?php echo htmlspecialchars($avatar); ?>" alt="User Avatar" class="avatar">
        <?php endif; ?>
    </div>
    <div class="admin-actions">
        <h2>Actions</h2>
        <ul>
            <li><a href="../manage_users.php">Manage Users</a></li>
        </ul>
        <a href="../auth/logout.php"><button>Logout</button></a>
    </div>
</div>
</body>
</html>
