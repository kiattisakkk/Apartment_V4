function calculateWaterCost($room_number) {
    $additional_water_cost = 0;
    // Define room groups with specific water costs
    $rooms_150 = ['201', '202', '302', '303', '304', '305', '306'];
    $rooms_200 = ['203', '204', '205', '206', '301'];

    if (in_array($room_number, $rooms_150)) {
        $additional_water_cost = 150;
    } elseif (in_array($room_number, $rooms_200)) {
        $additional_water_cost = 200;
    }

    return $additional_water_cost;
}

function saveBill($conn, $room_number, $Electricity_total, $price, $total) {
    $water_cost = calculateWaterCost($room_number); // Calculate water cost based on room number

    try {
        $stmt = $conn->prepare("
            INSERT INTO bill (user_id, month, year, electric_cost, water_cost, room_cost, total_cost, Room_number) 
            VALUES ((SELECT id FROM users WHERE Room_number = ?), MONTH(CURDATE()), YEAR(CURDATE()), ?, ?, ?, ?, ?)
        ");
        $total_cost_with_water = $total + $water_cost; // Add water cost to total cost
        $stmt->bind_param("sddddds", $room_number, $Electricity_total, $water_cost, $price, $total_cost_with_water, $room_number);

        return $stmt->execute();
    } catch (Exception $e) {
        error_log($e->getMessage());
        return false;
    }
}
