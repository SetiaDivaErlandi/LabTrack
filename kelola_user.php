<?php
include 'config/db.php';
session_start();

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

if (isset($_GET['hapus'])) {
    $id_user = mysqli_real_escape_string($conn, $_GET['hapus']);
    
    $query_hapus = "DELETE FROM users WHERE id_user = '$id_user' AND role != 'admin'";
    if (mysqli_query($conn, $query_hapus)) {
        $success = "Akun mahasiswa berhasil dihapus dari sistem.";
    }
}

if (isset($_GET['toggle']) && isset($_GET['to'])) {
    $id_toggle = mysqli_real_escape_string($conn, $_GET['toggle']);
    $to = mysqli_real_escape_string($conn, $_GET['to']);
    if (in_array($to, ['aktif','nonaktif'])) {
        $q = "UPDATE users SET status = '$to' WHERE id_user = '$id_toggle' AND role != 'admin'";
        if (mysqli_query($conn, $q)) {
            $success = "Status akun berhasil diubah menjadi: $to";
        } else {
            $error = "Gagal mengubah status: " . mysqli_error($conn);
        }
    }
}

if (isset($_GET['approve_reactivate'])) {
    $id_approve = mysqli_real_escape_string($conn, $_GET['approve_reactivate']);
    $q = "UPDATE users SET status = 'aktif', reactivate_requested = 0, last_active = NOW() WHERE id_user = '$id_approve' AND role != 'admin'";
    if (mysqli_query($conn, $q)) {
        $success = "Akun berhasil diaktifkan kembali.";
    } else {
        $error = "Gagal mengaktifkan akun: " . mysqli_error($conn);
    }
}

if (isset($_GET['reject_reactivate'])) {
    $id_reject = mysqli_real_escape_string($conn, $_GET['reject_reactivate']);
    $q = "UPDATE users SET reactivate_requested = 0 WHERE id_user = '$id_reject' AND role != 'admin'";
    if (mysqli_query($conn, $q)) {
        $success = "Permintaan aktivasi ditolak.";
    } else {
        $error = "Gagal menolak permintaan: " . mysqli_error($conn);
    }
}

$users_query = mysqli_query($conn, "SELECT id_user, username, role, COALESCE(status,'aktif') AS status, last_active FROM users WHERE role != 'admin'");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen User - LabTrack</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm py-3">
    <div class="container">
        <a class="navbar-brand fw-bold text-info" href="dashboard.php">LABTRACK</a>
        <div class="ms-auto">
            <a href="dashboard.php" class="btn btn-sm btn-outline-light px-3">Kembali ke Dashboard</a>
        </div>
    </div>
</nav>

<div class="container my-5">
    <div class="card p-4 shadow-sm border-0 bg-white mb-5">
        <h3 class="fw-bold text-info m-0">Permintaan Aktivasi Akun (Pending)</h3>
        <p class="text-muted small mt-1 mb-4">User yang telah menonaktifkan akun mereka dan meminta untuk diaktifkan kembali.</p>

        <?php 
        $pending_requests = mysqli_query($conn, "SELECT id_user, username, last_active FROM users WHERE role != 'admin' AND reactivate_requested = 1 ORDER BY id_user DESC");
        ?>

        <div class="table-responsive">
            <table class="table table-hover align-middle text-center border-light">
                <thead class="table-dark text-uppercase small">
                    <tr>
                        <th style="width: 15%">ID User</th>
                        <th style="width: 50%">Username</th>
                        <th style="width: 35%">Aksi</th>
                    </tr>
                </thead>
                <tbody class="small">
                    <?php if(mysqli_num_rows($pending_requests) > 0): ?>
                        <?php while($req = mysqli_fetch_assoc($pending_requests)): ?>
                        <tr>
                            <td class="fw-bold text-secondary">#USR-0<?php echo $req['id_user']; ?></td>
                            <td class="fw-bold text-dark"><?php echo htmlspecialchars($req['username']); ?></td>
                            <td>
                                <a href="kelola_user.php?approve_reactivate=<?php echo $req['id_user']; ?>" class="btn btn-sm btn-success me-2">Setujui</a>
                                <a href="kelola_user.php?reject_reactivate=<?php echo $req['id_user']; ?>" class="btn btn-sm btn-outline-danger">Tolak</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3" class="text-muted py-4">Tidak ada permintaan aktivasi saat ini.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card p-4 shadow-sm border-0 bg-white">
        <h3 class="fw-bold text-warning m-0">Manajemen Kontrol User</h3>
        <p class="text-muted small mt-1 mb-4">Pantau status akun teridstribusi dan hapus hak akses login mahasiswa jika diperlukan.</p>

        <?php if(isset($success)): ?>
            <div class="alert alert-success small mb-4 py-2 px-3"><?php echo $success; ?></div>
        <?php endif; ?>

        <div class="table-responsive">
                <table class="table table-hover align-middle text-center border-light">
                <thead class="table-dark text-uppercase small">
                    <tr>
                        <th style="width: 12%">ID User</th>
                        <th style="width: 38%">Username / Nama Mahasiswa</th>
                        <th style="width: 15%">Role</th>
                        <th style="width: 15%">Status</th>
                        <th style="width: 20%">Aksi</th>
                    </tr>
                </thead>
                <tbody class="small">
                    <?php if(mysqli_num_rows($users_query) > 0): ?>
                        <?php while($row = mysqli_fetch_assoc($users_query)): ?>
                        <tr>
                            <td class="fw-bold text-secondary">#USR-0<?php echo $row['id_user']; ?></td>
                            <td class="fw-bold text-dark"><?php echo htmlspecialchars($row['username']); ?></td>
                            <td><span class="badge bg-secondary px-3 py-1"><?php echo $row['role']; ?></span></td>
                            <td>
                                <?php if($row['status'] == 'aktif'): ?>
                                    <span class="badge bg-success py-2 px-3">Aktif</span>
                                <?php else: ?>
                                    <span class="badge bg-danger py-2 px-3">Nonaktif</span>
                                <?php endif; ?>
                                <div class="small text-muted mt-1"><?php echo ($row['last_active']) ? date('d-m-Y', strtotime($row['last_active'])) : '-'; ?></div>
                            </td>
                            <td>
                                <?php if($row['status'] == 'aktif'): ?>
                                    <a href="kelola_user.php?toggle=<?php echo $row['id_user']; ?>&to=nonaktif" class="btn btn-sm btn-warning me-2">Nonaktifkan</a>
                                <?php else: ?>
                                    <a href="kelola_user.php?toggle=<?php echo $row['id_user']; ?>&to=aktif" class="btn btn-sm btn-success me-2">Aktifkan</a>
                                <?php endif; ?>

                                <a href="kelola_user.php?hapus=<?php echo $row['id_user']; ?>" 
                                   class="btn btn-sm btn-danger fw-bold py-1 px-3" 
                                   onclick="return confirm('Apakah Anda yakin ingin menghapus akun mahasiswa bernama <?php echo $row['username']; ?>?')">
                                   Hapus Akun
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-muted py-4">Belum ada data mahasiswa yang terdaftar di database.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>