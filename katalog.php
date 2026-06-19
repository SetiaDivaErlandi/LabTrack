<?php
include 'config/db.php';
session_start();

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'mahasiswa') {
    header("Location: index.php");
    exit;
}

$katalog = mysqli_query($conn, "SELECT * FROM inventaris");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Katalog Alat Laboratorium - LabTrack</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm py-3">
    <div class="container">
        <a class="navbar-brand fw-bold text-info" href="dashboard.php">LABTRACK</a>
        <a href="dashboard.php" class="btn btn-sm btn-outline-light">Kembali ke Dashboard</a>
    </div>
</nav>

<div class="container my-5">
    <div class="card border-0 shadow-sm p-4 bg-white rounded-3">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h4 class="fw-bold text-dark mb-1">Daftar Alat Laboratorium</h4>
                <p class="text-muted small mb-0">Berikut adalah stok barang laboratorium real-time yang tersedia.</p>
            </div>
            <!-- Tombol jalan pintas kalau mau langsung pinjam -->
            <a href="form_pinjam.php" class="btn btn-primary fw-bold btn-sm px-3">Pinjam Alat Sekarang</a>
        </div>
        
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Nama Alat</th>
                        <th class="text-center">Sisa Stok</th>
                        <th>Deskripsi Barang</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(mysqli_num_rows($katalog) > 0): ?>
                        <?php while($row = mysqli_fetch_assoc($katalog)): ?>
                        <tr>
                            <td class="fw-bold text-secondary"><?php echo $row['nama_alat']; ?></td>
                            <td class="text-center">
                                <?php if($row['stok'] > 0): ?>
                                    <span class="badge bg-success py-2 px-3"><?php echo $row['stok']; ?> Pcs</span>
                                <?php else: ?>
                                    <span class="badge bg-danger py-2 px-3">Habis</span>
                                <?php endif; ?>
                            </td>
                            <td class="small text-muted"><?php echo $row['deskripsi']; ?></td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3" class="text-center text-muted">Belum ada data inventaris alat.</td>
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