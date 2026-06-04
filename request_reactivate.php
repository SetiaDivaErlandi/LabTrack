<?php
include 'config/db.php';
session_start();

// Jika sudah login, redirect ke dashboard
if (isset($_SESSION['username'])) {
    header("Location: dashboard.php");
    exit;
}

if (isset($_POST['request_reactivate'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    // Cari user dengan username dan password yang cocok
    $query = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) === 1) {
        $row = mysqli_fetch_assoc($result);
        
        // Cek apakah user ini nonaktif
        if ($row['status'] === 'nonaktif') {
            // Update flag reactivate_requested
            mysqli_query($conn, "UPDATE users SET reactivate_requested = 1 WHERE id_user = '".$row['id_user']."'");
            $success = "Permintaan aktivasi akun Anda telah dikirim ke admin. Silakan tunggu persetujuan admin.";
        } else if ($row['status'] === 'aktif') {
            $info = "Akun Anda sudah aktif! Silakan login di halaman login biasa.";
        } else {
            $error = "Status akun tidak diketahui. Hubungi admin laboratorium.";
        }
    } else {
        $error = "Username atau Password Anda salah!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Aktivasi Akun - LabTrack</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="login-body">

<div class="login-card">
    <div class="text-center mb-4">
        <h3 class="fw-bold text-primary">LabTrack</h3>
        <p class="text-muted small">Minta Aktivasi Akun</p>
    </div>

    <?php if(isset($error)): ?>
        <div class="alert alert-danger py-2 small text-center" role="alert">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <?php if(isset($success)): ?>
        <div class="alert alert-success py-2 small text-center" role="alert">
            <?php echo $success; ?>
        </div>
    <?php endif; ?>

    <?php if(isset($info)): ?>
        <div class="alert alert-info py-2 small text-center" role="alert">
            <?php echo $info; ?>
        </div>
    <?php endif; ?>

    <form action="" method="POST">
        <div class="mb-3">
            <label for="username" class="form-label small fw-semibold text-secondary">Username</label>
            <input type="text" class="form-control text-muted" id="username" name="username" placeholder="Masukkan username Anda" required>
        </div>
        <div class="mb-4">
            <label for="password" class="form-label small fw-semibold text-secondary">Password</label>
            <input type="password" class="form-control text-muted" id="password" name="password" placeholder="Masukkan password Anda" required>
        </div>
        <button type="submit" name="request_reactivate" class="btn btn-warning w-100 py-2 fw-bold shadow-sm mb-3">Minta Aktivasi Akun</button>
        
        <div class="text-center">
            <p class="small text-muted mb-2">Akun Anda masih aktif? <a href="index.php" class="text-primary fw-semibold text-decoration-none">Login di sini</a></p>
            <p class="small text-muted mb-0">Belum punya akun? <a href="register.php" class="text-success fw-semibold text-decoration-none">Daftar di sini</a></p>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>