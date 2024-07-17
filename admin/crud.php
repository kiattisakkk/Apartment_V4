<?php
require_once 'config.php';

function readUsers() {
    global $conn;
    return $conn->query("SELECT * FROM users");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container my-5">
        <h1 class="text-center">User Management</h1>
        <div class="text-right mb-3">
            <a href="create_user.php" class="btn btn-success">Add New User</a>
        </div>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Room Number</th>
                    <th>Role</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $users = readUsers();
                while ($user = $users->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $user['id'] . "</td>";
                    echo "<td>" . htmlspecialchars($user['Room_number']) . "</td>";
                    echo "<td>" . htmlspecialchars($user['urole']) . "</td>";
                    echo "<td><a href='edit_user.php?id=" . $user['id'] . "'>Edit</a> | <a href='delete_user.php?id=" . $user['id'] . "' onclick='return confirm(\"Are you sure?\")'>Delete</a></td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>
