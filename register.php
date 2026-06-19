<?php
include 'config/db.php';
session_start();

if (isset($_SESSION['username'])) {
    header("Location: dashboard.php");
    exit;
}

if (isset($_POST['register'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $confirm_password = mysqli_real_escape_string($conn, $_POST['confirm_password']);

    if ($password !== $confirm_password) {
        $error = "Konfirmasi password tidak cocok!";
    } else {
        $check_user = "SELECT * FROM users WHERE username = '$username'";
        $result_check = mysqli_query($conn, $check_user);

        if (mysqli_num_rows($result_check) > 0) {
            $error = "Username sudah digunakan!";
        } else {
            $query = "INSERT INTO users (username, password, role, status, last_active) VALUES ('$username', '$password', 'mahasiswa', 'aktif', NOW())";
            
            if (mysqli_query($conn, $query)) {
                $success = "Registrasi berhasil! Silakan <a href='index.php'>login</a>.";
            } else {
                $error = "Gagal registrasi: " . mysqli_error($conn);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi - LabTrack</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/style.css" rel="stylesheet">
    
</head>
<body class="login-body">

<div class="login-card">
    <div class="text-center mb-4">
        <h3 class="fw-bold text-primary">LabTrack</h3>
        <p class="text-muted small">Pendaftaran Akun Baru Mahasiswa</p>
    </div>

    <?php if(isset($error)): ?>
        <div class="alert alert-danger py-2 small text-center"><?php echo $error; ?></div>
    <?php endif; ?>

    <?php if(isset($success)): ?>
        <div class="alert alert-success py-2 small text-center"><?php echo $success; ?></div>
    <?php endif; ?>

    <form action="" method="POST">
        <div class="mb-3">
            <label class="form-label small fw-semibold text-secondary">Username Baru</label>
            <input type="text" class="form-control" name="username" placeholder="Masukkan username" required>
        </div>
        <div class="mb-3">
            <label class="form-label small fw-semibold text-secondary">Password</label>
            <input type="password" class="form-control" name="password" placeholder="Buat password" required>
        </div>
        <div class="mb-4">
            <label class="form-label small fw-semibold text-secondary">Konfirmasi Password</label>
            <input type="password" class="form-control" name="confirm_password" placeholder="Ulangi password" required>
        </div>
        
        <button type="submit" name="register" class="btn btn-primary w-100 py-2 fw-bold shadow-sm mb-3">Daftar Akun</button>
        
        <div class="text-center">
            <p class="small text-muted mb-0">Sudah punya akun? <a href="index.php" class="text-primary fw-semibold text-decoration-none">Login di sini</a></p>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>