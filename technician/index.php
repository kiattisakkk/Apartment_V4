<?php
session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["urole"] !== "3") {
    header("location: ../login.php");
    exit;
}

require_once '../config.php';

function fetchBills($conn, $user_id)
{
    try {
        $stmt = $conn->prepare("SELECT * FROM bill WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
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

$bills = fetchBills($conn, $_SESSION['id']);
$conn->close();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>หน้าผู้ใช้</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>หน้าผู้ใช้</h1>
        <a href="../logout.php" class="btn btn-danger">ออกจากระบบ</a>
        <h2 class="mt-3">คำนวณค่าใช้จ่ายประจำเดือน</h2>
        <form method="post" action="calculate.php">
            <div class="form-group">
                <label for="current_electric">เลขมิเตอร์ไฟฟ้าปัจจุบัน:</label>
                <input type="number" id="current_electric" name="current_electric" class="form-control" required>
            </div>
            <?php if ($_SESSION['room_number'] == 'S1') { ?>
                <div class="form-group mt-3">
                    <label for="current_water">เลขมิเตอร์น้ำปัจจุบัน:</label>
                    <input type="number" id="current_water" name="current_water" class="form-control" required>
                </div>
            <?php } ?>
            <div class="form-group mt-3">
                <button type="submit" name="calculate" class="btn btn-primary">คำนวณ</button>
            </div>
        </form>

        <h2 class="mt-5">บิล</h2>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>เดือน</th>
                    <th>ปี</th>
                    <th>ค่าไฟฟ้า</th>
                    <th>ค่าน้ำ</th>
                    <th>ค่าห้อง</th>
                    <th>ค่าใช้จ่ายทั้งหมด</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($bills)) { ?>
                    <?php while ($row = $bills->fetch_assoc()) { ?>
                        <tr>
                            <td><?php echo $row['month']; ?></td>
                            <td><?php echo $row['year']; ?></td>
                            <td><?php echo $row['electric_cost']; ?> บาท</td>
                            <td><?php echo $row['water_cost']; ?> บาท</td>
                            <td><?php echo $row['room_cost']; ?> บาท</td>
                            <td><?php echo $row['total_cost']; ?> บาท</td>
                        </tr>
                    <?php } ?>
                <?php } else { ?>
                    <tr>
                        <td colspan="6" style="text-align: center;">ไม่มีข้อมูลบิล</td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</body>
</html>
