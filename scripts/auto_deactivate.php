<?php

include __DIR__ . '/../config/db.php';

$months = 3; 

if (isset($argv[1]) && is_numeric($argv[1])) {
    $months = intval($argv[1]);
}

$sql = "UPDATE users SET status = 'nonaktif' WHERE role != 'admin' AND (last_active IS NULL OR last_active < DATE_SUB(NOW(), INTERVAL $months MONTH))";

if (mysqli_query($conn, $sql)) {
    $count = mysqli_affected_rows($conn);
    echo "Menonaktifkan $count akun yang tidak aktif lebih dari $months bulan.\n";
} else {
    echo "Gagal menjalankan query: " . mysqli_error($conn) . "\n";
}

?>