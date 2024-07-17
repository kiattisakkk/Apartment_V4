<?php
require_once 'config.php'; // Include the database configuration file

$selected_room = $selected_start_date = $selected_end_date = "";
$rooms = [];
$dates = [];

// Fetch distinct rooms from the database for dropdown
$roomQuery = "SELECT DISTINCT Room_number FROM users WHERE Room_number IN ('201', '202', '302', '303', '304', '305', '306', '203', '204', '205', '206', '301', 'S1', 'S2') ORDER BY Room_number";
$dateQuery = "SELECT DISTINCT date_record FROM electric ORDER BY date_record";

if ($roomResult = $conn->query($roomQuery)) {
    while ($row = $roomResult->fetch_assoc()) {
        $rooms[] = $row['Room_number'];
    }
}
if ($dateResult = $conn->query($dateQuery)) {
    while ($row = $dateResult->fetch_assoc()) {
        $dates[] = $row['date_record'];
    }
}

// Handle the form submission
$bill_details = null;
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['view_bill'])) {
    $selected_room = $_POST['selected_room'];
    $selected_start_date = $_POST['selected_start_date'];
    $selected_end_date = $_POST['selected_end_date'];

    // Prepare and execute the SQL query to fetch the latest bill for the selected room and date range
    $sql = "SELECT b.*, u.Room_number, u.First_name, u.Last_name, 
                   CASE 
                       WHEN u.Room_number IN ('201', '202', '302', '303', '304', '305', '306', '203', '204', '205', '206', '301') THEN u.water_was 
                       WHEN u.Room_number = 'S1' THEN b.water_cost 
                       ELSE b.water_cost 
                   END as water_cost_display
            FROM bill b
            LEFT JOIN users u ON b.user_id = u.id
            WHERE u.Room_number = ? AND b.year >= YEAR(?) AND b.month >= MONTH(?) AND b.year <= YEAR(?) AND b.month <= MONTH(?)
            ORDER BY b.id DESC LIMIT 1";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $selected_room, $selected_start_date, $selected_start_date, $selected_end_date, $selected_end_date);
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
        .print-button { margin-top: 20px; }
    </style>
</head>
<body>
<div class="container">
    <h1>เลือกข้อมูลสำหรับการออกใบเสร็จรับเงิน</h1>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        <label for="selected_room">หมายเลขห้อง:</label>
        <select id="selected_room" name="selected_room" required>
            <?php foreach ($rooms as $room): ?>
                <option value="<?php echo $room; ?>" <?php echo $room == $selected_room ? 'selected' : ''; ?>><?php echo $room; ?></option>
            <?php endforeach; ?>
        </select>
        <label for="selected_start_date">วันที่เริ่มต้น:</label>
        <select id="selected_start_date" name="selected_start_date" required>
            <?php foreach ($dates as $date): ?>
                <option value="<?php echo $date; ?>" <?php echo $date == $selected_start_date ? 'selected' : ''; ?>><?php echo $date; ?></option>
            <?php endforeach; ?>
        </select>
        <label for="selected_end_date">วันที่สิ้นสุด:</label>
        <select id="selected_end_date" name="selected_end_date" required>
            <?php foreach ($dates as $date): ?>
                <option value="<?php echo $date; ?>" <?php echo $date == $selected_end_date ? 'selected' : ''; ?>><?php echo $date; ?></option>
            <?php endforeach; ?>
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
        <div class="print-button">
            <button onclick="window.print()">พิมพ์ใบเสร็จ</button>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
