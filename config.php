<?php
// กำหนดรายละเอียดการเชื่อมต่อฐานข้อมูล
$servername = "localhost";
$username = "root";
$password = "";
$database = "apartment_manage";

// สร้างการเชื่อมต่อ
$conn = new mysqli($servername, $username, $password, $database);

// ตรวจสอบการเชื่อมต่อ
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>