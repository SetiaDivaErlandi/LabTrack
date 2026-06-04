<?php
include 'config/db.php';
session_start();

if (!isset($_SESSION['username'])) {
    header('Location: index.php');
    exit;
}

$id_user = $_SESSION['id_user'];

if (isset($_GET['action']) && $_GET['action'] === 'deactivate') {
    // Set status to nonaktif dan destroy session
    mysqli_query($conn, "UPDATE users SET status = 'nonaktif' WHERE id_user = '$id_user'");
    session_unset();
    session_destroy();
    echo "<script>alert('Akun Anda telah dinonaktifkan. Untuk mengaktifkan kembali, gunakan halaman Request Aktivasi.'); window.location='index.php';</script>";
    exit;
}

// Simple page with button
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Akun Saya - LabTrack</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="card p-4 shadow-sm border-0 bg-white" style="max-width: 500px; margin: 0 auto;">
            <h4 class="fw-bold text-dark mb-3">Kelola Status Akun Saya</h4>
            <p class="text-muted small mb-4">Anda dapat menonaktifkan akun Anda sendiri di sini. Untuk mengaktifkan kembali, gunakan halaman <strong>Request Aktivasi</strong>.</p>
            
            <div class="d-grid gap-2">
                <a href="user_toggle_status.php?action=deactivate" class="btn btn-warning fw-bold" onclick="return confirm('Apakah Anda yakin ingin menonaktifkan akun Anda? Anda harus meminta aktivasi kembali untuk login lagi.')">Nonaktifkan Akun Saya</a>
                <a href="dashboard.php" class="btn btn-secondary">Kembali ke Dashboard</a>
            </div>
        </div>
    </div>
</body>
</html>