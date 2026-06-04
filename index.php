<?php
include 'config/db.php';
session_start();

// Jika sudah ada session login, langsung lempar ke dashboard
if (isset($_SESSION['username'])) {
    header("Location: dashboard.php");
    exit;
}

if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    $query  = "SELECT * FROM users WHERE username='$username' AND password='$password'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) === 1) {
        $row = mysqli_fetch_assoc($result);
        // Cek status akun (aktif/nonaktif)
        if (isset($row['status']) && $row['status'] !== 'aktif') {
            $error = "Akun Anda tidak aktif. <a href='request_reactivate.php' class='text-warning fw-semibold'>Minta aktivasi di sini</a> atau hubungi admin laboratorium.";
        } else {
            // Menyimpan data login ke session browser
            $_SESSION['id_user']  = $row['id_user'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['role']     = $row['role'];

            // Update last_active agar auto-deactivate bekerja
            mysqli_query($conn, "UPDATE users SET last_active = NOW() WHERE id_user = '".$row['id_user']."'");

            header("Location: dashboard.php");
            exit;
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
    <title>Login - LabTrack</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="login-body">

<div class="login-card">
    <div class="text-center mb-4">
        <h3 class="fw-bold text-primary">LabTrack</h3>
        <p class="text-muted small">Sistem Manajemen Peminjaman Alat Lab</p>
    </div>

    <?php if(isset($error)): ?>
        <div class="alert alert-danger py-2 small text-center" role="alert">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <form action="" method="POST">
        <div class="mb-3">
            <label for="username" class="form-label small fw-semibold text-secondary">Username</label>
            <input type="text" class="form-control text-muted" id="username" name="username" placeholder="Masukkan username" required>
        </div>
        <div class="mb-4">
            <label for="password" class="form-label small fw-semibold text-secondary">Password</label>
            <input type="password" class="form-control text-muted" id="password" name="password" placeholder="Masukkan password" required>
        </div>
        <button type="submit" name="login" class="btn btn-primary w-100 py-2 fw-bold shadow-sm">Masuk Sistem</button>
        <div class="text-center mt-3">
            <p class="small text-muted mb-2">Belum punya akun? <a href="register.php" class="text-success fw-semibold text-decoration-none">Registrasi Mahasiswa</a></p>
            <p class="small text-muted mb-0">Lupa password? <a href="forgot_password.php" class="text-warning fw-semibold text-decoration-none">Reset di sini</a></p>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>