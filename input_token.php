<?php
include 'config/db.php';
session_start();

// Jika sudah login, redirect ke dashboard
if (isset($_SESSION['username'])) {
    header("Location: dashboard.php");
    exit;
}

$error = '';

// Jika user submit token
if (isset($_POST['submit_token'])) {
    $token = mysqli_real_escape_string($conn, trim($_POST['token']));
    
    if (empty($token)) {
        $error = "Silakan masukkan token!";
    } else {
        // Cek token valid
        $query = "SELECT id_user, reset_token_expiry FROM users WHERE reset_token = '$token'";
        $result = mysqli_query($conn, $query);
        
        if (mysqli_num_rows($result) === 1) {
            $user = mysqli_fetch_assoc($result);
            $expiry_timestamp = strtotime($user['reset_token_expiry']);
            $current_timestamp = time();
            
            // Toleransi 10 menit tambahan untuk timezone issues dan delay
            if ($expiry_timestamp > ($current_timestamp - 600)) {
                // Token valid, redirect ke reset_password dengan token di URL
                header("Location: reset_password.php?token=" . urlencode($token));
                exit;
            } else {
                $error = "Token sudah expired. Silakan request token baru di <a href='forgot_password.php' class='text-info'>halaman lupa password</a>.";
            }
        } else {
            $error = "Token tidak ditemukan atau tidak valid. Periksa kembali token Anda.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Input Token Reset - LabTrack</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="login-body">

<div class="login-card" style="max-width: 500px;">
    <div class="text-center mb-4">
        <h3 class="fw-bold text-primary">LabTrack</h3>
        <p class="text-muted small">Input Token Reset Password</p>
    </div>

    <?php if($error): ?>
        <div class="alert alert-danger py-2 small text-center" role="alert">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <div class="alert alert-info p-3 mb-4">
        <strong>Instruksi Paste Token:</strong>
        <ol class="mt-2 mb-0">
            <li>Di halaman lupa password, klik tombol <strong>"Salin Token"</strong> (jangan copy manual!)</li>
            <li>Paste token di kolom bawah ini</li>
            <li>Tekan tombol "Verifikasi Token"</li>
            <li>Anda akan diarahkan ke halaman reset password</li>
        </ol>
    </div>

    <form action="" method="POST">
        <div class="mb-3">
            <label for="token" class="form-label fw-semibold text-secondary">Paste Token Reset di Sini:</label>
            <textarea class="form-control" id="token" name="token" placeholder="Contoh: dc47f42cf75e87b0f311d8a2f738dd7e..." rows="5" required autofocus></textarea>
            <small class="text-muted d-block mt-2">Token adalah deretan karakter panjang (64 karakter alfanumerik)</small>
        </div>

        <button type="submit" name="submit_token" class="btn btn-primary w-100 py-2 fw-bold shadow-sm">Verifikasi Token</button>

        <div class="text-center mt-3">
            <p class="small text-muted mb-2">Belum punya token? <a href="forgot_password.php" class="text-info fw-semibold text-decoration-none">Kembali ke halaman lupa password</a></p>
            <p class="small text-muted mb-0"><a href="index.php" class="text-secondary fw-semibold text-decoration-none">Kembali ke login</a></p>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
