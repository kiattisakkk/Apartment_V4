<?php
require_once 'config.php';

// Function to read user details
function readUser($id) {
    global $conn;
    $sql = "SELECT * FROM users WHERE id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
    return null;
}

// Function to update user details
function updateUser($id, $Room_number, $type_id, $First_name, $Last_name, $username, $password, $urole) {
    global $conn;
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $sql = "UPDATE users SET Room_number = ?, type_id = ?, First_name = ?, Last_name = ?, username = ?, password = ?, urole = ? WHERE id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("sisssssi", $Room_number, $type_id, $First_name, $Last_name, $username, $hashed_password, $urole, $id);
        return $stmt->execute();
    }
    return false;
}

// Check if user ID is provided
if (!isset($_GET['id']) && empty($_GET['id'])) {
    die('Invalid user ID.');
}
$user_id = intval($_GET['id']);
$user = readUser($user_id);
if (!$user) {
    die('User not found.');
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $Room_number = $_POST['Room_number'];
    $type_id = $_POST['type_id'];
    $First_name = $_POST['First_name'];
    $Last_name = $_POST['Last_name'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $urole = $_POST['urole'];

    if (updateUser($user_id, $Room_number, $type_id, $First_name, $Last_name, $username, $password, $urole)) {
        header("Location: crud.php");
        exit();
    } else {
        echo "Error updating user.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container my-5">
        <h1 class="text-center mb-4">Edit User</h1>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . '?id=' . $user_id; ?>" method="post">
            <div class="mb-3">
                <label for="Room_number" class="form-label">Room Number</label>
                <input type="text" class="form-control" id="Room_number" name="Room_number" value="<?php echo htmlspecialchars($user['Room_number']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="type_id" class="form-label">Type</label>
                <select class="form-select" id="type_id" name="type_id" required>
                    <option value="1" <?php if ($user['type_id'] == '1' && $user['urole'] == '2') echo 'selected'; ?>>Normal</option>
                    <option value="2" <?php if ($user['type_id'] == '2' && $user['urole'] == '2') echo 'selected'; ?>>Extra</option>
                    <option value="3" <?php if ($user['type_id'] == '3' && $user['urole'] == '1') echo 'selected'; ?>>Admin</option>
                    <option value="4" <?php if ($user['type_id'] == '4' && $user['urole'] == '3') echo 'selected'; ?>>Technician</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="First_name" class="form-label">First Name</label>
                <input type="text" class="form-control" id="First_name" name="First_name" value="<?php echo htmlspecialchars($user['First_name']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="Last_name" class="form-label">Last Name</label>
                <input type="text" class="form-control" id="Last_name" name="Last_name" value="<?php echo htmlspecialchars($user['Last_name']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password">
            </div>
            <div class="mb-3">
                <label for="urole" class="form-label">Role</label>
                <select class="form-select" id="urole" name="urole" required>
                    <option value="1" <?php if ($user['urole'] == '1') echo 'selected'; ?>>Admin</option>
                    <option value="2" <?php if ($user['urole'] == '2') echo 'selected'; ?>>User</option>
                    <option value="3" <?php if ($user['urole'] == '3') echo 'selected'; ?>>Technician</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Update User</button>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
