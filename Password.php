<?php
$numbers = array(109576, 298973, 930622, 704391, 985269, 036893);

// สุ่มตำแหน่งในอาร์เรย์
$randomIndex = array_rand($numbers);

// ดึงค่าที่สุ่มได้
$randomNumber = $numbers[$randomIndex];

// แสดงผลลัพธ์
echo "เลขที่ถูกสุ่ม: $randomNumber";
?>
