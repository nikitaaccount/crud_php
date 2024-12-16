<?php
session_start();
require_once 'conn.php';

// Ensure admin access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit;
}

// Handle Create or Update user
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['user_id']) ? intval($_POST['user_id']) : null;
    $username = $_POST['username'];
    $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_BCRYPT) : null;
    $role = $_POST['role'];

    if ($id) {
        // Update user
        if ($password) {
            $stmt = $conn->prepare("UPDATE users2 SET username = ?, password = ?, role = ? WHERE id = ?");
            $stmt->bind_param("sssi", $username, $password, $role, $id);
        } else {
            $stmt = $conn->prepare("UPDATE users2 SET username = ?, role = ? WHERE id = ?");
            $stmt->bind_param("ssi", $username, $role, $id);
        }
    } else {
        // Create user
        $stmt = $conn->prepare("INSERT INTO users2 (username, password, role) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $password, $role);
    }
    $stmt->execute();
    $stmt->close();
    header("Location: manage_users.php");
    exit;
}

// Handle Delete user
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $stmt = $conn->prepare("DELETE FROM users2 WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $stmt->close();
    header("Location: manage_users.php");
    exit;
}

$result = $conn->query("SELECT id, username, role, created_at FROM users2");
$users = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users</title>
    <style>
      
        .top-bar {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 20px;
            gap:16px;
        }

        .top-bar button {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
        }

        .top-bar button:hover {
            background-color: #0056b3;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            width: 400px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .modal-content h2 {
            margin-top: 0;
        }

        .modal-content form input, .modal-content form select {
            width: 100%;
            margin-bottom: 10px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .modal-content form button {
            width: 100%;
            padding: 10px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .modal-content form button:hover {
            background-color: #0056b3;
        }

        table {
            width: 80%;
            border-collapse: collapse;
            margin:0 auto;
        }

        table th, table td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }

        table th {
            background-color: #1f3f49;
            color: white;
        }

        table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        table tr:hover {
            background-color: #f1f1f1;
        }

        table .action-buttons {
            display: flex;
            gap: 10px;
        }

        table .action-buttons a {
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            text-decoration: none;
        }

        table .action-buttons .edit {
            background-color: #28a745;
        }

        table .action-buttons .delete {
            background-color: #dc3545;
        }

        table .action-buttons .edit:hover {
            background-color: #218838;
        }

        table .action-buttons .delete:hover {
            background-color: #c82333;
        }
        .empty-message {
            text-align: center;
            color: #555;
            font-size: 1.2rem;
            margin: 20px 0;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="top-bar">
        <h1>Manage Users</h1>
        <button id="create-user-btn">Create User</button>
    </div>

    <?php if (count($users) > 0): ?>
        <table>
            <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Role</th>
                <th>Created At</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php 
                $counter = 1; 
                foreach ($users as $user): ?>
                <tr>
                    <td><?php echo $counter++;?></td>
                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                    <td><?php echo htmlspecialchars($user['role']); ?></td>
                    <td><?php echo htmlspecialchars($user['created_at']); ?></td>
                    <td>
                        <div class="action-buttons">
                            <a href="#" class="edit" data-id="<?php echo htmlspecialchars($user['id']); ?>" 
                               data-username="<?php echo htmlspecialchars($user['username']); ?>" 
                               data-role="<?php echo htmlspecialchars($user['role']); ?>">Edit</a>
                            <a href="manage_users.php?delete_id=<?php echo htmlspecialchars($user['id']); ?>" 
                               class="delete" 
                               onclick="return confirm('Are you sure you want to delete this user?');">Delete</a>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <!-- Message When No Users Exist -->
        <p class="empty-message">No users found. Use the "Create User" button to add a new user.</p>
    <?php endif; ?>
</div>

<!-- Modal -->
<div id="user-modal" class="modal">
    <div class="modal-content">
        <h2 id="modal-title">Create User</h2>
        <form id="user-form" method="POST">
            <input type="hidden" name="user_id" id="user-id">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" required>
            <label for="password">Password</label>
            <input type="password" id="password" name="password">
            <label for="role">Role</label>
            <select id="role" name="role" required>
                <option value="admin">Admin</option>
                <option value="user">User</option>
            </select>
            <button type="submit">Save</button>
        </form>
    </div>
</div>

<script>
  
    const modal = document.getElementById('user-modal');
    const createUserBtn = document.getElementById('create-user-btn');
    const userForm = document.getElementById('user-form');
    const modalTitle = document.getElementById('modal-title');
    const userIdInput = document.getElementById('user-id');
    const usernameInput = document.getElementById('username');
    const passwordInput = document.getElementById('password');
    const roleInput = document.getElementById('role');

    createUserBtn.addEventListener('click', () => {
        modal.classList.add('active');
        modalTitle.textContent = 'Create  a new User';
        userIdInput.value = '';
        usernameInput.value = '';
        passwordInput.value = '';
        roleInput.value = 'user';
    });

    
    document.querySelectorAll('.edit').forEach(button => {
        button.addEventListener('click', (event) => {
            event.preventDefault();
            const id = button.getAttribute('data-id');
            const username = button.getAttribute('data-username');
            const role = button.getAttribute('data-role');

            modal.classList.add('active');
            modalTitle.textContent = 'Edit the User';
            userIdInput.value = id;
            usernameInput.value = username;
            passwordInput.value = ''; 
            roleInput.value = role;
        });
    });

    modal.addEventListener('click', (event) => {
        if (event.target === modal) {
            modal.classList.remove('active');
        }
    });
</script>
</body>
</html>
