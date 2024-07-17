<?php
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../login.php");
    exit;
}

require_once '../config.php';

// ดึงข้อมูลผู้ใช้จากเซสชัน
$room_number = $_SESSION["room_number"];

// Initialize variables
$first_name = "";
$last_name = "";
$price = "";
$type_name = "";
$last_month_electric = 0;
$difference_electric = 0;
$Electricity_total = 0;
$total = 0;

$last_month_water = 0;
$difference_water = 0;
$Water_total = 0;

// ฟังก์ชันคำนวณค่าน้ำ
function calculateWaterCost($room_number) {
    $rooms_150 = ['201', '202', '302', '303', '304', '305', '306'];
    $rooms_200 = ['203', '204', '205', '206', '301'];

    if (in_array($room_number, $rooms_150)) {
        return 150;
    } elseif (in_array($room_number, $rooms_200)) {
        return 200;
    }
    return 0;
}

// ฟังก์ชันดึงข้อมูลผู้ใช้
function getUserDetails($conn, $room_number) {
    try {
        $stmt = $conn->prepare("SELECT u.Room_number, u.First_name, u.Last_name, t.price, t.type_name FROM users u JOIN type t ON u.type_id = t.id WHERE u.Room_number = ?");
        $stmt->bind_param("s", $room_number);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        } else {
            return null;
        }
    } catch (Exception $e) {
        error_log($e->getMessage());
        return null;
    }
}

// ฟังก์ชันดึงเลขมิเตอร์ไฟฟ้าครั้งก่อน
function getLastMonthElectric($conn, $room_number) {
    try {
        $stmt = $conn->prepare("SELECT meter_electric FROM electric WHERE user_id = (SELECT id FROM users WHERE Room_number = ?) ORDER BY date_record DESC LIMIT 1");
        $stmt->bind_param("s", $room_number);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            return $result->fetch_assoc()['meter_electric'];
        } else {
            return 0;
        }
    } catch (Exception $e) {
        error_log($e->getMessage());
        return 0;
    }
}

// ดึงข้อมูลผู้ใช้
$userDetails = getUserDetails($conn, $room_number);
if ($userDetails) {
    $room_number = $userDetails['Room_number'];
    $first_name = $userDetails['First_name'];
    $last_name = $userDetails['Last_name'];
    $price = $userDetails['price'];
    $type_name = $userDetails['type_name'];
    $last_month_electric = getLastMonthElectric($conn, $room_number); // ดึงเลขมิเตอร์ไฟฟ้าครั้งก่อน
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>คำนวณค่าใช้จ่ายประจำเดือน</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            color: #333;
        }

        .container {
            width: 80%;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 8px;
            box-sizing: border-box;
        }

        .form-group button {
            padding: 10px 20px;
            background-color: #5cb85c;
            color: #fff;
            border: none;
            cursor: pointer;
        }
    </style>
</head>

<body>

    <div class="container">
        <h1>คำนวณค่าใช้จ่ายประจำเดือน</h1>

        <!-- Section 2: User Information -->
        <h2>ข้อมูลผู้ใช้</h2>
        <p>หมายเลขห้อง: <?php echo htmlspecialchars($room_number); ?></p>
        <p>ชื่อ: <?php echo htmlspecialchars($first_name); ?></p>
        <p>นามสกุล: <?php echo htmlspecialchars($last_name); ?></p>
        <p>ประเภทห้อง: <?php echo htmlspecialchars($type_name); ?></p>
        <p>ราคาห้อง: <?php echo htmlspecialchars($price); ?> บาท</p>
        <p>ยอดค่าน้ำประจำห้อง: <?php echo calculateWaterCost($room_number); ?> บาท</p>
        <p>เลขมิเตอร์ไฟฟ้าครั้งก่อน: <?php echo htmlspecialchars($last_month_electric); ?></p>

        <!-- Section 3: Calculate Electricity and Water Costs -->
        <h2>คำนวณค่าใช้จ่ายประจำเดือน</h2>
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <div class="form-group">
                <label for="current_electric">เลขมิเตอร์ไฟฟ้าปัจจุบัน:</label>
                <input type="number" id="current_electric" name="current_electric" required>
                <input type="hidden" name="last_month_electric" value="<?php echo htmlspecialchars($last_month_electric); ?>">
                <input type="hidden" name="room_number" value="<?php echo htmlspecialchars($room_number); ?>">
                <input type="hidden" name="price" value="<?php echo htmlspecialchars($price); ?>">
            </div>

            <div class="form-group">
                <label for="last_month_electric_display">เลขมิเตอร์ไฟฟ้าครั้งก่อน:</label>
                <input type="number" id="last_month_electric_display" value="<?php echo htmlspecialchars($last_month_electric); ?>" readonly>
            </div>

            <div class="form-group">
                <label for="unit_price">ราคาต่อหน่วย:</label>
                <input type="number" id="unit_price" value="7" readonly>
            </div>

            <div class="form-group">
                <button type="submit" name="calculate">คำนวณ</button>
            </div>
        </form>

        <?php
        if (isset($_POST['calculate'])) {
            $current_electric = $_POST['current_electric'];
            $last_month_electric = $_POST['last_month_electric'];
            $difference_electric = $current_electric - $last_month_electric;
            $Electricity_total = $difference_electric * 7;

            $water_cost = calculateWaterCost($room_number);
            $total_with_water = $Electricity_total + $water_cost + $price;
        ?>
            <h2>ยอดชำระค่าใช้จ่ายประจำเดือน</h2>
            <p>ผลต่างของเลขมิเตอร์ไฟฟ้า: <?php echo htmlspecialchars($difference_electric); ?> หน่วย</p>
            <p>ยอดค่าไฟ: <?php echo htmlspecialchars($Electricity_total); ?> บาท</p>
            <p>ยอดค่าน้ำที่กำหนด: <?php echo htmlspecialchars($water_cost); ?> บาท</p>
            <p>ค่าห้องพัก: <?php echo htmlspecialchars($price); ?> บาท</p>
            <p>ค่าใช้จ่ายทั้งหมด: <?php echo htmlspecialchars($total_with_water); ?> บาท</p>

            <div class="form-group">
                <button type="button" onclick="window.location.href='<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>'">ตกลง</button>
            </div>
        <?php } ?>
    </div>

</body>

</html>
