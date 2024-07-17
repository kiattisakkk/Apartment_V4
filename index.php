<?php
include 'config.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ลงทะเบียน</title>
</head>
<body>
    <h2>ลงทะเบียนผู้ใช้ใหม่</h2>
    <form action="register.php" method="post">
        <label for="username">ชื่อผู้ใช้:</label><br>
        <input type="text" id="username" name="username" required><br>
        <label for="password">รหัสผ่าน:</label><br>
        <input type="password" id="password" name="password" required><br>
        <label for="fname">ชื่อจริง:</label><br>
        <input type="text" id="fname" name="fname" required><br>
        <label for="lname">นามสกุล:</label><br>
        <input type="text" id="lname" name="lname" required><br>
        <label for="lname">หมายเลขห้อง:</label><br>
        <input type="int" id="room_number" name="room_number" required><br>
        <input type="hidden" name="role" value="0">
        <input type="submit" value="ลงทะเบียน">
    </form>
</body>
</html>

</body>
</html>