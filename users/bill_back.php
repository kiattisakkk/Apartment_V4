<?php
session_start();

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: ../login.php");
    exit;
}

require_once '../config.php';

// ดึงข้อมูลผู้ใช้จากเซสชัน
$room_number = $_SESSION["room_number"];
$username = $_SESSION["username"];

// Handle the form submission
$bill_details = null;
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['view_bill'])) {
    $selected_month = $_POST['selected_month'];
    $selected_year = $_POST['selected_year'];

    // Prepare and execute the SQL query to fetch the latest bill for the selected room and date range
    $sql = "SELECT b.*, u.Room_number, u.First_name, u.Last_name, 
                   CASE 
                       WHEN u.Room_number IN ('201', '202', '302', '303', '304', '305', '306', '203', '204', '205', '206', '301') THEN u.water_was 
                       WHEN u.Room_number = 'S1' THEN b.water_cost 
                       ELSE b.water_cost 
                   END as water_cost_display
            FROM bill b
            LEFT JOIN users u ON b.user_id = u.id
            WHERE u.Room_number = ? AND b.month = ? AND b.year = ?
            ORDER BY b.id DESC LIMIT 1";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $room_number, $selected_month, $selected_year);
    $stmt->execute();
    $result = $stmt->get_result();
    $bill_details = $result->fetch_assoc();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ใบเสร็จรับเงินย้อนหลัง</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .container { width: 80%; margin: auto; padding: 20px; background: #f4f4f4; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #f2f2f2; }
        form { margin-bottom: 20px; }
        label { margin-right: 10px; }
        select, button { padding: 5px; }
        .total { margin-top: 20px; font-weight: bold; }
    </style>
</head>
<body>
<div class="container">
    <h1>ค่าใช้จ่ายย้อนหลัง</h1>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        <label for="selected_month">เดือน:</label>
        <select id="selected_month" name="selected_month" required>
            <?php for ($m=1; $m<=12; $m++): ?>
                <option value="<?php echo $m; ?>" <?php echo isset($selected_month) && $selected_month == $m ? 'selected' : ''; ?>>
                    <?php echo $m; ?>
                </option>
            <?php endfor; ?>
        </select>
        <label for="selected_year">ปี:</label>
        <select id="selected_year" name="selected_year" required>
            <?php for ($y = date('Y'); $y >= date('Y') - 10; $y--): ?>
                <option value="<?php echo $y; ?>" <?php echo isset($selected_year) && $selected_year == $y ? 'selected' : ''; ?>>
                    <?php echo $y; ?>
                </option>
            <?php endfor; ?>
        </select>
        <button type="submit" name="view_bill">ดูใบเสร็จรับเงิน</button>
    </form>

    <?php if (!empty($bill_details)): ?>
        <h2>ใบเสร็จรับเงิน</h2>
        <table>
            <tr>
                <th>หมายเลขห้อง</th>
                <td><?php echo htmlspecialchars($bill_details['Room_number']); ?></td>
            </tr>
            <tr>
                <th>ชื่อ</th>
                <td><?php echo htmlspecialchars($bill_details['First_name']); ?> <?php echo htmlspecialchars($bill_details['Last_name']); ?></td>
            </tr>
            <tr>
                <th>เดือน</th>
                <td><?php echo htmlspecialchars($bill_details['month']); ?></td>
            </tr>
            <tr>
                <th>ปี</th>
                <td><?php echo htmlspecialchars($bill_details['year']); ?></td>
            </tr>
            <tr>
                <th>ค่าไฟฟ้า</th>
                <td><?php echo htmlspecialchars($bill_details['electric_cost']); ?> บาท</td>
            </tr>
            <tr>
                <th>ค่าน้ำ</th>
                <td><?php echo htmlspecialchars($bill_details['water_cost_display']); ?> บาท</td>
            </tr>
            <tr>
                <th>ค่าห้อง</th>
                <td><?php echo htmlspecialchars($bill_details['room_cost']); ?> บาท</td>
            </tr>
            <tr>
                <th>ค่าใช้จ่ายทั้งหมด</th>
                <td><?php echo htmlspecialchars($bill_details['total_cost']); ?> บาท</td>
            </tr>
        </table>
        <div class="total">
            <strong>ยอดรวม: <?php echo htmlspecialchars($bill_details['total_cost']); ?> บาท</strong>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
