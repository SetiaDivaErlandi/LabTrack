<?php
include 'config/db.php';
session_start();

// Jika sudah login, redirect ke dashboard
if (isset($_SESSION['username'])) {
    header("Location: dashboard.php");
    exit;
}

$success = '';
$error = '';

if (isset($_POST['request_reset'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    
    // Cek username ada atau tidak
    $query = "SELECT id_user, username, role FROM users WHERE username = '$username'";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) === 1) {
        $user = mysqli_fetch_assoc($result);
        
        // Generate random token
        $reset_token = bin2hex(random_bytes(32)); // Token 64 karakter
        $expiry_time = date('Y-m-d H:i:s', strtotime('+2 hours')); // Berlaku 2 jam untuk testing
        
        // Simpan token ke database
        $update_query = "UPDATE users SET reset_token = '$reset_token', reset_token_expiry = '$expiry_time' WHERE id_user = '{$user['id_user']}'";
        
        if (mysqli_query($conn, $update_query)) {
            // Buat link reset dinamis berdasarkan URL aktual
            $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'];
            $path = dirname($_SERVER['PHP_SELF']);
            $reset_link = $protocol . "://" . $host . $path . "/reset_password.php?token=" . $reset_token;
            
            $success = "Link reset password telah digenerate! Pilih salah satu cara di bawah ini:<br><br>" .
                      "<div class='alert alert-info p-3'>" .
                      "<strong>CARA 1: Klik Link Langsung (Paling Mudah)</strong><br>" .
                      "<a href='" . $reset_link . "' target='_blank' class='btn btn-sm btn-primary mt-2'>Buka Link Reset Password</a>" .
                      "</div><br>" .
                      "<div class='alert alert-warning p-3'>" .
                      "<strong>CARA 2: Copy-Paste Token</strong><br><br>" .
                      "1. Klik tombol COPY di bawah (jangan copy manual):<br>" .
                      "<div style='display: flex; gap: 10px; margin-top: 10px; align-items: center;'>" .
                      "<div id='tokenBox' style='background: white; padding: 10px; border-radius: 5px; flex: 1; font-family: monospace; word-break: break-all; font-size: 12px; border: 1px solid #ddd;'>" . $reset_token . "</div>" .
                      "<button type='button' class='btn btn-sm btn-warning' onclick='copyToken()' style='white-space: nowrap;'>Salin Token</button>" .
                      "</div>" .
                      "<small class='text-success d-block mt-2' id='copyStatus'></small><br>" .
                      "2. <a href='input_token.php' class='btn btn-sm btn-secondary'>Buka Halaman Input Token</a><br>" .
                      "3. Paste token dan reset password Anda<br>" .
                      "</div><br>" .
                      "<p class='text-muted small'>Token berlaku selama <strong>2 jam</strong> dari pembuatan.</p>" .
                      "<script>" .
                      "function copyToken() {" .
                      "  const tokenBox = document.getElementById('tokenBox');" .
                      "  const token = tokenBox.textContent;" .
                      "  navigator.clipboard.writeText(token).then(() => {" .
                      "    const status = document.getElementById('copyStatus');" .
                      "    status.textContent = '✓ Token berhasil disalin!';" .
                      "    setTimeout(() => { status.textContent = ''; }, 3000);" .
                      "  }).catch(() => {" .
                      "    alert('Gagal menyalin. Silakan copy manual.');" .
                      "  });" .
                      "}" .
                      "</script>";
        } else {
            $error = "Terjadi kesalahan. Silakan coba lagi.";
        }
    } else {
        $error = "Username tidak ditemukan dalam sistem.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password - LabTrack</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="login-body">

<div class="login-card" style="max-width: 450px;">
    <div class="text-center mb-4">
        <h3 class="fw-bold text-primary">LabTrack</h3>
        <p class="text-muted small">Reset Password</p>
    </div>

    <?php if($success): ?>
        <div class="alert alert-success py-3 small" role="alert">
            <?php echo $success; ?>
        </div>
        <div class="text-center mt-3">
            <a href="index.php" class="btn btn-secondary">Kembali ke Login</a>
        </div>
    <?php else: ?>
        <?php if($error): ?>
            <div class="alert alert-danger py-2 small text-center" role="alert">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <p class="text-muted small mb-4">Masukkan username Anda untuk mendapatkan link reset password. Anda akan menerima token yang berlaku selama 30 menit.</p>

        <form action="" method="POST">
            <div class="mb-3">
                <label for="username" class="form-label small fw-semibold text-secondary">Username</label>
                <input type="text" class="form-control text-muted" id="username" name="username" placeholder="Masukkan username" required>
            </div>
            <button type="submit" name="request_reset" class="btn btn-primary w-100 py-2 fw-bold shadow-sm">Generate Link Reset</button>
            <div class="text-center mt-3">
                <p class="small text-muted mb-0">Ingat password Anda? <a href="index.php" class="text-info fw-semibold text-decoration-none">Kembali ke Login</a></p>
            </div>
        </form>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
