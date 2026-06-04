<?php
include 'config/db.php';
session_start();

// Jika sudah login, redirect ke dashboard
if (isset($_SESSION['username'])) {
    header("Location: dashboard.php");
    exit;
}

$token = '';
$error = '';
$success = '';

// Cek apakah ada token di URL atau POST
if (isset($_GET['token'])) {
    $token = mysqli_real_escape_string($conn, $_GET['token']);
} elseif (isset($_POST['token'])) {
    $token = mysqli_real_escape_string($conn, $_POST['token']);
}

// Jika form di submit (reset password)
if (isset($_POST['reset_password']) && $token) {
    $password_baru = mysqli_real_escape_string($conn, $_POST['password_baru']);
    $password_confirm = mysqli_real_escape_string($conn, $_POST['password_confirm']);
    
    // Validasi password
    if (strlen($password_baru) < 5) {
        $error = "Password harus minimal 5 karakter!";
    } elseif ($password_baru !== $password_confirm) {
        $error = "Password tidak cocok! Silakan cek kembali.";
    } else {
        // Cek token valid dan belum expired - dengan 10 menit buffer toleransi
        $query = "SELECT id_user, username, reset_token_expiry FROM users WHERE reset_token = '$token'";
        $result = mysqli_query($conn, $query);
        
        if (mysqli_num_rows($result) === 1) {
            $user = mysqli_fetch_assoc($result);
            $expiry_timestamp = strtotime($user['reset_token_expiry']);
            $current_timestamp = time();
            
            // Cek jika masih dalam masa berlaku + 10 menit buffer untuk timezone tolerance
            if ($expiry_timestamp > ($current_timestamp - 600)) {
                // Update password dan hapus token
                $update_query = "UPDATE users SET password = '$password_baru', reset_token = NULL, reset_token_expiry = NULL WHERE id_user = '{$user['id_user']}'";
                
                if (mysqli_query($conn, $update_query)) {
                    $success = "Password Anda berhasil diubah! Silakan login dengan password baru.";
                } else {
                    $error = "Terjadi kesalahan saat mengubah password.";
                }
            } else {
                $error = "Token sudah expired (berlaku 2 jam). Silakan request token baru di <a href='forgot_password.php' class='text-info'>halaman lupa password</a>.";
            }
        } else {
            $error = "Token tidak ditemukan. Silakan request token baru.";
        }
    }
}

// Jika ada token di URL, tampilkan form langsung
$show_form = false;
if ($token && !$success) {
    $query = "SELECT id_user, reset_token_expiry FROM users WHERE reset_token = '$token'";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) === 1) {
        $user = mysqli_fetch_assoc($result);
        $expiry_timestamp = strtotime($user['reset_token_expiry']);
        $current_timestamp = time();
        
        // 10 menit buffer untuk toleransi timezone
        if ($expiry_timestamp > ($current_timestamp - 600)) {
            $show_form = true;
        } else {
            $error = "Token sudah expired (berlaku 2 jam). Silakan request token baru di <a href='forgot_password.php' class='text-info'>halaman lupa password</a>.";
        }
    } else {
        $error = "Token tidak ditemukan. Silakan request token baru.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - LabTrack</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="login-body">

<div class="login-card" style="max-width: 450px;">
    <div class="text-center mb-4">
        <h3 class="fw-bold text-primary">LabTrack</h3>
        <p class="text-muted small">Buat Password Baru</p>
    </div>

    <?php if($success): ?>
        <div class="alert alert-success py-3 small text-center" role="alert">
            <strong>Berhasil!</strong><br><?php echo $success; ?>
        </div>
        <div class="text-center mt-3">
            <a href="index.php" class="btn btn-primary">Masuk Sekarang</a>
        </div>
    <?php else: ?>
        <?php if($error): ?>
            <div class="alert alert-danger py-2 small text-center" role="alert">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <?php if($show_form || (!$token && !$error)): ?>
            <p class="text-muted small mb-4">
                <?php if(!$token): ?>
                    Masukkan token yang Anda terima melalui link reset password, kemudian buat password baru.
                <?php else: ?>
                    Silakan buat password baru untuk akun Anda.
                <?php endif; ?>
            </p>

            <form action="" method="POST">
                <?php if(!$token): ?>
                    <div class="mb-3">
                        <label for="token" class="form-label small fw-semibold text-secondary">Token Reset</label>
                        <input type="text" class="form-control text-muted" id="token" name="token" placeholder="Paste token di sini" required>
                    </div>
                <?php else: ?>
                    <input type="hidden" name="token" value="<?php echo $token; ?>">
                <?php endif; ?>

                <div class="mb-3">
                    <label for="password_baru" class="form-label small fw-semibold text-secondary">Password Baru</label>
                    <input type="password" class="form-control text-muted" id="password_baru" name="password_baru" placeholder="Minimal 5 karakter" required>
                </div>

                <div class="mb-4">
                    <label for="password_confirm" class="form-label small fw-semibold text-secondary">Konfirmasi Password</label>
                    <input type="password" class="form-control text-muted" id="password_confirm" name="password_confirm" placeholder="Ulangi password baru" required>
                </div>

                <button type="submit" name="reset_password" class="btn btn-primary w-100 py-2 fw-bold shadow-sm">Reset Password</button>
                <div class="text-center mt-3">
                    <p class="small text-muted mb-0"><a href="forgot_password.php" class="text-info fw-semibold text-decoration-none">Minta link reset baru</a> atau <a href="index.php" class="text-info fw-semibold text-decoration-none">kembali ke login</a></p>
                </div>
            </form>
        <?php endif; ?>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
