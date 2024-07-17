<?php
require_once "config.php";

// Check if the form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
    // Get the data from the POST form
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);
    $First_name = trim($_POST["First_name"]);
    $Last_name = trim($_POST["Last_name"]);
    $room_number = trim($_POST["room_number"]);
    $urole = $_POST["role"]; 

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Prepare SQL statement to insert new user data into the database
    $sql = "INSERT INTO users (username, password, First_name, Last_name, room_number, urole) VALUES (?, ?, ?, ?, ?, ?)";

    if($stmt = mysqli_prepare($conn, $sql)){
        mysqli_stmt_bind_param($stmt, "ssssss", $param_username, $param_password, $param_First_name, $param_Last_name, $param_room_number, $param_urole);

        $param_username = $username;
        $param_password = $hashed_password;
        $param_First_name = $First_name;
        $param_Last_name= $Last_name;
        $param_room_number = $room_number;
        $param_urole = $urole;

        if(mysqli_stmt_execute($stmt)){
            echo "สมัครสมาชิกสำเร็จ";
            header("location: login.php");
            exit;
        } else{
            echo "มีบางอย่างผิดพลาด กรุณาลองใหม่ภายหลัง";
        }

        mysqli_stmt_close($stmt);
    }

    mysqli_close($conn);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center">Register</h2>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="mt-3">
            <div class="mb-3">
                <label for="username" class="form-label">Username:</label>
                <input type="text" id="username" name="username" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password:</label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="First_name" class="form-label">First Name:</label>
                <input type="text" id="First_name" name="First_name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="Last_name" class="form-label">Last Name:</label>
                <input type="text" id="Last_name" name="Last_name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="role" class="form-label">Role:</label>
                <select id="role" name="role" class="form-select" required>
                    <option value="1">Admin</option>
                    <option value="2">User</option>
                    <option value="3">Technician</option>
                </select>
            </div>
            <div class="mb-3">
                <input type="submit" value="Register" class="btn btn-primary">
            </div>
        </form>
    </div>
</body>
</html>
