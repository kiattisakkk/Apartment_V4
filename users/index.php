<?php
session_start();

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: ../login.php");
    exit;
}

require_once '../config.php';

// ดึงข้อมูลผู้ใช้จากเซสชัน
$username = $_SESSION["username"];

// เตรียมคำสั่ง SQL เพื่อดึง room_number
$sql = "SELECT room_number FROM users WHERE username = ?";

if($stmt = mysqli_prepare($conn, $sql)){
    mysqli_stmt_bind_param($stmt, "s", $param_username);
    $param_username = $username;

    if(mysqli_stmt_execute($stmt)){
        mysqli_stmt_bind_result($stmt, $room_number);
        if(mysqli_stmt_fetch($stmt)){
            // $room_number ถูกตั้งค่าแล้ว
            $_SESSION["room_number"] = $room_number;
        } else {
            // หากไม่พบ room_number ให้ตั้งค่าเป็นข้อความที่เหมาะสม
            $room_number = "ไม่พบหมายเลขห้อง";
        }
    } else {
        echo "มีบางอย่างผิดพลาด กรุณาลองใหม่ภายหลัง";
    }

    mysqli_stmt_close($stmt);
} else {
    echo "มีบางอย่างผิดพลาด กรุณาลองใหม่ภายหลัง";
}

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <title>User Dashboard</title>
</head>
<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-6 offset-md-3">
                <div class="card">
                    <div class="card-body">
                        <h1 class="card-title mb-4">Welcome, <?php echo htmlspecialchars($username); ?>!</h1>
                        <p>Room Number: <?php echo htmlspecialchars($room_number); ?></p>
                        <a href="../logout.php" class="btn btn-danger">Logout</a>
                        <a href="total.php" class="btn btn-success mt-3">เช็คค่าใช้จ่าย</a>
                        <a href="bill_back.php" class="btn btn-primary mt-3">บิลย้อนหลัง</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
