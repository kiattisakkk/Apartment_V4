<?php
require_once 'config.php'; // Include the configuration file for database settings

// Initialize variables
$room_number = "";
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

function calculateWaterCost($room_number)
{
    // Define room groups with specific water costs
    $rooms_150 = ['201', '202', '302', '303', '304', '305', '306'];
    $rooms_200 = ['203', '204', '205', '206', '301'];

    if (in_array($room_number, $rooms_150)) {
        return 150;
    } elseif (in_array($room_number, $rooms_200)) {
        return 200;
    }
    return 0;
}

function getUserDetails($conn, $room_number)
{
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

function getLastMonthElectric($conn, $room_number)
{
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

function getLastMonthWater($conn, $room_number)
{
    try {
        $stmt = $conn->prepare("SELECT meter_water FROM water WHERE user_id = (SELECT id FROM users WHERE Room_number = ?) ORDER BY date_record DESC LIMIT 1");
        $stmt->bind_param("s", $room_number);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            return $result->fetch_assoc()['meter_water'];
        } else {
            return 0;
        }
    } catch (Exception $e) {
        error_log($e->getMessage());
        return 0;
    }
}

function saveElectricMeter($conn, $room_number, $current_electric, $difference_electric)
{
    try {
        $stmt = $conn->prepare("INSERT INTO electric (user_id, meter_electric, difference_electric, date_record) VALUES ((SELECT id FROM users WHERE Room_number = ?), ?, ?, CURDATE())");
        $stmt->bind_param("sdd", $room_number, $current_electric, $difference_electric);
        return $stmt->execute();
    } catch (Exception $e) {
        error_log($e->getMessage());
        return false;
    }
}

function saveWaterMeter($conn, $room_number, $current_water, $difference_water)
{
    try {
        $stmt = $conn->prepare("INSERT INTO water (user_id, meter_water, difference_water, date_record) VALUES ((SELECT id FROM users WHERE Room_number = ?), ?, ?, CURDATE())");
        $stmt->bind_param("sdd", $room_number, $current_water, $difference_water);
        return $stmt->execute();
    } catch (Exception $e) {
        error_log($e->getMessage());
        return false;
    }
}

function saveBill($conn, $room_number, $Electricity_total, $Water_total, $price, $total, $difference_electric, $difference_water)
{
    $water_cost = calculateWaterCost($room_number);
    $total_with_water = $total + $water_cost;

    try {
        $stmt = $conn->prepare("INSERT INTO bill (user_id, month, year, electric_cost, water_cost, room_cost, total_cost, difference_electric, difference_water) VALUES ((SELECT id FROM users WHERE Room_number = ?), MONTH(CURDATE()), YEAR(CURDATE()), ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sdddsdd", $room_number, $Electricity_total, $Water_total, $price, $total_with_water, $difference_electric, $difference_water);
        return $stmt->execute();
    } catch (Exception $e) {
        error_log($e->getMessage());
        return false;
    }
}

function fetchRoomNumbers($conn)
{
    try {
        $result = $conn->query("SELECT Room_number FROM users");
        if ($result->num_rows > 0) {
            return $result;
        } else {
            return [];
        }
    } catch (Exception $e) {
        error_log($e->getMessage());
        return [];
    }
}

$roomNumbers = fetchRoomNumbers($conn);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['room_number'])) {
        $room_number = $_POST['room_number'];
        $userDetails = getUserDetails($conn, $room_number);

        if ($userDetails) {
            $room_number = $userDetails['Room_number'];
            $first_name = $userDetails['First_name'];
            $last_name = $userDetails['Last_name'];
            $price = $userDetails['price'];
            $type_name = $userDetails['type_name'];
        }

        $last_month_electric = getLastMonthElectric($conn, $room_number);
        $last_month_water = getLastMonthWater($conn, $room_number);
    }

    if (isset($_POST['calculate'])) {
        $current_electric = $_POST['current_electric'];
        $last_month_electric = $_POST['last_month_electric'];
        $price = $_POST['price'];

        $difference_electric = $current_electric - $last_month_electric;
        $Electricity_total = $difference_electric * 7; // Calculate cost based on electricity usage
        $total = $Electricity_total + $price; // Total cost including room price

        saveElectricMeter($conn, $room_number, $current_electric, $difference_electric);

        if ($room_number == 'S1') {
            $current_water = $_POST['current_water'];
            $last_month_water = $_POST['last_month_water'];

            $difference_water = $current_water - $last_month_water;
            $Water_total = $difference_water * 22; // Calculate cost based on water usage

            saveWaterMeter($conn, $room_number, $current_water, $difference_water);

            $total += $Water_total;
        }
    }

    if (isset($_POST['save'])) {
        $room_number = $_POST['room_number'];
        $current_electric = $_POST['current_electric'];
        $last_month_electric = $_POST['last_month_electric'];
        $difference_electric = $current_electric - $last_month_electric;
        $Electricity_total = $difference_electric * 7;
        $price = $_POST['price'];
        $total = $Electricity_total + $price;

        if ($room_number == 'S1') {
            $current_water = $_POST['current_water'];
            $last_month_water = $_POST['last_month_water'];

            $difference_water = $current_water - $last_month_water;
            $Water_total = $difference_water * 22; // Calculate cost based on water usage

            $total += $Water_total;
        }

        if (saveBill($conn, $room_number, $Electricity_total, $Water_total, $price, $total, $difference_electric, $difference_water)) {
            echo "<script>alert('บันทึกข้อมูลเรียบร้อย');</script>";
        } else {
            echo "Error: ไม่สามารถบันทึกข้อมูลได้";
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>คำนวณค่าไฟฟ้าและค่าน้ำ</title>
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
        <!-- Section 1: Select Room Number -->
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <div class="form-group">
                <label for="room_number">หมายเลขห้อง:</label>
                <select id="room_number" name="room_number" required>
                    <option value="">เลือกหมายเลขห้อง</option>
                    <?php if (!empty($roomNumbers)) { ?>
                        <?php while ($row = $roomNumbers->fetch_assoc()) { ?>
                            <option value="<?php echo $row['Room_number']; ?>"><?php echo $row['Room_number']; ?></option>
                        <?php } ?>
                    <?php } ?>
                </select>
            </div>

            <div class="form-group">
                <button type="submit" name="fetch">ยืนยัน</button>
            </div>
        </form>

        <!-- Section 2: User Information -->
        <h2>ข้อมูลผู้ใช้</h2>
        <p>หมายเลขห้อง: <?php echo htmlspecialchars($room_number); ?></p>
        <p>ชื่อ: <?php echo htmlspecialchars($first_name); ?></p>
        <p>นามสกุล: <?php echo htmlspecialchars($last_name); ?></p>
        <p>ประเภทห้อง: <?php echo htmlspecialchars($type_name); ?></p>
        <p>ราคาห้อง: <?php echo htmlspecialchars($price); ?> บาท</p>
        <p>ยอดค่าน้ำประจำห้อง: <?php echo calculateWaterCost($room_number); ?> บาท</p>

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

            <?php if ($room_number == 'S1') { ?>
                <div class="form-group">
                    <label for="current_water">เลขมิเตอร์น้ำปัจจุบัน:</label>
                    <input type="number" id="current_water" name="current_water" required>
                    <input type="hidden" name="last_month_water" value="<?php echo htmlspecialchars($last_month_water); ?>">
                </div>

                <div class="form-group">
                    <label for="last_month_water_display">เลขมิเตอร์น้ำครั้งก่อน:</label>
                    <input type="number" id="last_month_water_display" value="<?php echo htmlspecialchars($last_month_water); ?>" readonly>
                </div>

                <div class="form-group">
                    <label for="unit_price_water">ราคาต่อหน่วยน้ำ:</label>
                    <input type="number" id="unit_price_water" value="22" readonly>
                </div>
            <?php } ?>

            <div class="form-group">
                <button type="submit" name="calculate">คำนวณ</button>
            </div>
        </form>

        <?php if (isset($_POST['calculate'])) { ?>
            <h2>ยอดชำระค่าใช้จ่ายประจำเดือน</h2>
            <p>ผลต่างของเลขมิเตอร์ไฟฟ้า: <?php echo htmlspecialchars($difference_electric); ?> หน่วย</p>
            <p>ยอดค่าไฟ: <?php echo htmlspecialchars($Electricity_total); ?> บาท</p>

            <?php if ($room_number == 'S1') { ?>
                <p>ผลต่างของเลขมิเตอร์น้ำ: <?php echo htmlspecialchars($difference_water); ?> หน่วย</p>
                <p>ยอดค่าน้ำ: <?php echo htmlspecialchars($Water_total); ?> บาท</p>
            <?php } ?>

            <?php
            $water_cost = 0;
            if (in_array($room_number, ['201', '202', '302', '303', '304', '305', '306'])) {
                $water_cost = 150;
            } elseif (in_array($room_number, ['203', '204', '205', '206', '301'])) {
                $water_cost = 200;
            }

            $total_with_water = $total + $water_cost;
            ?>

            <?php if ($water_cost > 0) { ?>
                <p>ยอดค่าน้ำที่กำหนด: <?php echo $water_cost; ?> บาท</p>
            <?php } ?>

            <p>ค่าห้องพัก: <?php echo htmlspecialchars($price); ?> บาท</p>
            <p>ค่าใช้จ่ายทั้งหมด: <?php echo htmlspecialchars($total_with_water); ?> บาท</p>

            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <input type="hidden" name="room_number" value="<?php echo htmlspecialchars($room_number); ?>">
                <input type="hidden" name="current_electric" value="<?php echo htmlspecialchars($current_electric); ?>">
                <input type="hidden" name="last_month_electric" value="<?php echo htmlspecialchars($last_month_electric); ?>">
                <input type="hidden" name="price" value="<?php echo htmlspecialchars($price); ?>">
                <input type="hidden" name="Electricity_total" value="<?php echo htmlspecialchars($Electricity_total); ?>">
                <input type="hidden" name="total" value="<?php echo htmlspecialchars($total_with_water); ?>">

                <?php if ($room_number == 'S1') { ?>
                    <input type="hidden" name="current_water" value="<?php echo htmlspecialchars($current_water); ?>">
                    <input type="hidden" name="last_month_water" value="<?php echo htmlspecialchars($last_month_water); ?>">
                <?php } ?>

                <div class="form-group">
                    <button type="submit" name="save">บันทึกข้อมูล</button>
                </div>
            </form>
        <?php } ?>
    </div>

</body>

</html>