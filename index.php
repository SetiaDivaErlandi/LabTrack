<?php
include 'config/db.php';
session_start();

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
        if (isset($row['status']) && $row['status'] !== 'aktif') {
            $error = "Akun Anda tidak aktif. <a href='request_reactivate.php' class='text-warning fw-semibold'>Minta aktivasi di sini</a> atau hubungi admin laboratorium.";
        } else {
            $_SESSION['id_user']  = $row['id_user'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['role']     = $row['role'];

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
    
    <style>
        .login-body {
            background: linear-gradient(135deg, #4f46e5 0%, #06b6d4 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
        }
        .login-card {
            width: 100%;
            max-width: 400px;
            padding: 30px;
            border-radius: 15px;
            background: #ffffff;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }
    </style>
</head>
<body class="login-body">

<div class="login-card">
    <div class="text-center mb-4">
        <h3 class="fw-bold text-primary">LabTrack</h3>
        <p class="text-muted small">Sistem Manajemen Peminjaman Alat Lab</p>
    </div>

    <?php if(isset($error)): ?>
        <div class="alert alert-danger py-2 small text-center"><?php echo $error; ?></div>
    <?php endif; ?>

    <form action="" method="POST">
        <div class="mb-3">
            <label class="form-label small fw-bold">Username</label>
            <input type="text" class="form-control" name="username" placeholder="Masukkan username" required>
        </div>
        <div class="mb-4">
            <label class="form-label small fw-bold">Password</label>
            <input type="password" class="form-control" name="password" placeholder="Masukkan password" required>
        </div>
            <div class="text-end mt-1">
                <a href="reset_password.php" class="text-decoration-none small text-primary">Lupa password?</a>
            </div>
        <button type="submit" name="login" class="btn btn-primary w-100 fw-bold">Masuk Sistem</button>
        <div class="text-center mt-3">
            <p class="small text-muted">Belum punya akun? <a href="register.php">Registrasi</a></p>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>